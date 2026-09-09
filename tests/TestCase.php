<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $destructiveTraits = [
            \Illuminate\Foundation\Testing\RefreshDatabase::class,
            \Illuminate\Foundation\Testing\DatabaseMigrations::class,
            \Illuminate\Foundation\Testing\DatabaseTruncation::class,
        ];

        if (array_intersect($destructiveTraits, class_uses_recursive(static::class))) {
            $connection = $app->make('db')->connection();
            $database = $connection->getDatabaseName();

            if ($database !== ':memory:' && ! str_ends_with($database, '_testing')) {
                throw new \LogicException('Destructive database tests require a dedicated _testing database. Run PHPUnit with phpunit.xml.');
            }
        }

        return $app;
    }
}
