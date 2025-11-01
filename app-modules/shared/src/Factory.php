<?php

declare(strict_types=1);

namespace He4rt\Shared;

use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;

abstract class Factory extends EloquentFactory
{
    protected $connection = 'testing';
}
