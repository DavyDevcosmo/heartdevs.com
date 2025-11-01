<?php

declare(strict_types=1);

namespace Heart\Core\Traits;

trait Singleton
{
    protected static $instance;

    /**
     * Constructor.
     */
    final protected function __construct()
    {
        $this->init();
    }

    public function __clone()
    {
        trigger_error('Cloning '.self::class.' is not allowed.', E_USER_ERROR);
    }

    public function __wakeup()
    {
        trigger_error('Unserializing '.self::class.' is not allowed.', E_USER_ERROR);
    }

    /**
     * Create a new instance of this singleton.
     */
    final public static function instance()
    {
        return static::$instance ?? static::$instance = new static;
    }

    /**
     * Forget this singleton's instance if it exists
     */
    final public static function forgetInstance(): void
    {
        static::$instance = null;
    }

    /**
     * Initialize the singleton free from constructor parameters.
     */
    protected function init() {}
}
