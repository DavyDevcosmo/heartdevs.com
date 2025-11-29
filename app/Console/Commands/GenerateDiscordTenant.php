<?php

declare(strict_types=1);

namespace App\Console\Commands;

use He4rt\Provider\Enums\ProviderEnum;
use He4rt\Tenant\Models\Tenant;
use Illuminate\Console\Command;

class GenerateDiscordTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'misc:generate-provider-tenant {slug} {provider} {providerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $tenant = Tenant::query()->where('slug', $this->argument('slug'))->first();
        $tenant
            ->providers()
            ->create([
                'tenant_id' => $tenant->getKey(),
                'provider' => ProviderEnum::from($this->argument('provider')),
                'provider_id' => $this->argument('providerId'),
            ]);

        $this->info('Tenant created successfully!');
    }
}
