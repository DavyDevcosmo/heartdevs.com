<?php

declare(strict_types=1);

namespace Heart\Core\Classes;

use FilesystemIterator;
use Heart\Core\Contracts\DomainInterface;
use Heart\Core\DTO\DomainDTO;
use Heart\Core\Exceptions\DomainExtendException;
use Heart\Core\Exceptions\DomainNotExistsException;
use Heart\Core\Traits\Singleton;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class DomainManager
{
    use Singleton;

    public array $domains = [];

    public bool $booted = false;

    public bool $registered = false;

    private array $normalizedMap;

    private array $pathMap;

    public function loadDomains(): array
    {
        /**
         * Locate all domains and binds them to the container
         */
        foreach ($this->getDomainNamespaces() as $domainDTO) {
            $this->loadDomain($domainDTO);
        }

        return $this->domains;
    }

    /**
     * Returns a flat array of vendor domain namespaces and their paths
     *
     * @return array<int, DomainDTO>
     */
    public function getDomainNamespaces(): array
    {
        $domains = [];

        foreach ($this->getVendorAndDomainNames() as $vendorName => $vendorList) {
            foreach ($vendorList as $domainName => $domainPayload) {
                $namespace = '\\'.$vendorName.'\\'.$domainName;
                $namespace = $this->normalizeClassName($namespace);
                $classNames[$namespace] = $domainPayload;
                $domains[] = DomainDTO::make($namespace, $domainPayload);
            }
        }

        return $domains;
    }

    public function normalizeClassName($name): string
    {
        if (is_object($name)) {
            $name = $name::class;
        }

        return '\\'.mb_ltrim($name, '\\');
    }

    /**
     * Returns a 2 dimensional array of vendors and their domains.
     */
    public function getVendorAndDomainNames(): array
    {
        $dir = __DIR__.'/../../';
        $domains = [];
        $dirPath = realpath($dir);
        $dirPathSeparated = explode('/', $dirPath);
        $coreDomain = array_pop($dirPathSeparated);
        if (! File::isDirectory($dirPath)) {
            return $domains;
        }

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirPath, FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::SKIP_DOTS)
        );
        $it->setMaxDepth();
        $it->rewind();

        while ($it->valid()) {
            if ($it->isFile() && str_contains(mb_strtolower($it->getFilename()), 'domain.php')) {
                $filePath = dirname($it->getPathname());
                $vendorPaths = array_reverse(explode('/', $filePath));
                $domainName = basename($filePath);
                $vendorName = $this->getPathFromCoreDomain($coreDomain, $vendorPaths);
                $domains[$vendorName][$domainName] = [
                    'filePath' => $filePath,
                    'fileName' => str($it->getFilename())->remove('.php')->toString(),
                ];
            }

            $it->next();
        }

        return $domains;
    }

    /**
     * Loads a single domain into the manager.
     *
     * @param  string|object  $namespace  Eg: Lms\Auth
     * @param  string  $path  Eg: 'LMS/Auth';
     * @return void|DomainInterface
     *
     * @throws DomainNotExistsException
     * @throws DomainExtendException
     */
    public function loadDomain(DomainDTO $domainDTO)
    {
        $domainNamespace = $this->getDomainClassName($domainDTO);

        // Not a valid domain!
        throw_if(is_string($domainNamespace) && ! class_exists($domainNamespace), DomainNotExistsException::domainNotInstantiable($domainNamespace));

        throw_if(realpath($domainDTO->filePath) === false, DomainNotExistsException::pathNotFound($domainDTO->filePath));

        throw_unless(is_subclass_of($domainNamespace, DomainInterface::class), DomainExtendException::abstractClassNotExtended());

        if (! is_object($domainNamespace)) {
            /** @var DomainInterface $domainNamespace */
            $domainNamespace = new $domainNamespace();
        }

        $classId = $this->getIdentifier($domainNamespace);

        /*
         * Check for disabled domains
         */
        if ($domainNamespace->isDisabled()) {
            return;
        }

        $this->domains[$classId] = $domainNamespace;
        $this->pathMap[$classId] = $domainDTO->filePath;
        $this->normalizedMap[mb_strtolower($classId)] = $classId;

        return $domainNamespace;
    }

    /**
     * Resolves a domain identifier
     *
     * @param  mixed  $namespace  Domain class name or object
     * @return string Identifier in format of Domain
     */
    public function getIdentifier($namespace): string
    {
        $namespace = $this->normalizeClassName($namespace);
        if (str_contains($namespace, '\\')) {
            return $namespace;
        }

        $parts = explode('\\', $namespace);
        $slice = array_slice($parts, 1, 2);

        return implode('.', $slice);
    }

    public function getProviders(): array
    {
        $providers = [];
        /** @var DomainInterface $domainObj * */
        foreach ($this->domains as $domainObj) {
            $providers = array_merge($providers, $domainObj->registerProvider());
        }

        return $providers;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function init(): void
    {
        $this->loadDomains();
    }

    /**
     * Returns a path to a subdomain from the core domain
     */
    private function getPathFromCoreDomain(string $coreDomain, array $vendorPaths): string
    {
        $vendorName = '';
        $vendorPaths = array_slice($vendorPaths, 1); // Removing domain name

        foreach ($vendorPaths as $vendorPath) {
            $vendorName = $vendorPath.'\\'.$vendorName;
            if ($vendorPath === $coreDomain) {
                break;
            }
        }

        return mb_rtrim($vendorName, '\\');
    }

    /**
     * @param  object|string  $class
     * @return string|object
     */
    private function getDomainClassName(DomainDTO $domainDTO)
    {
        if (is_object($domainDTO->namespace)) {
            return $domainDTO->namespace;
        }

        if (is_string($domainDTO->namespace) && ! Str::endsWith($domainDTO->namespace, 'Domain')) {
            return sprintf('%s\%s', $domainDTO->namespace, $domainDTO->fileName);
        }

        return $domainDTO->namespace;
    }
}
