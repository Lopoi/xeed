<?php

namespace Cable8mm\Xeed\Tests\Unit\Resolvers;

use Cable8mm\Xeed\Column;
use Cable8mm\Xeed\DB;
use Cable8mm\Xeed\Resolvers\IntegerResolver;
use Cable8mm\Xeed\Support\Picker;
use PHPUnit\Framework\TestCase;

final class IntegerResolverTest extends TestCase
{
    public Column $column;

    public string $driver;

    protected function setUp(): void
    {
        $db = DB::getInstance();

        $this->column = Picker::of($db->attach()
            ->getTable('xeeds')
            ->getColumns()
        )->driver($db->driver)->field('integer')->get();

        $this->driver = $db->driver;
    }

    public function test_column_can_not_null(): void
    {
        $this->assertNotNull($this->column);
    }

    public function test_resolver_can_be_created(): void
    {
        $resolver = new IntegerResolver($this->column);

        $this->assertNotNull($resolver);
    }

    public function test_fake_method_can_working_well(): void
    {
        $resolver = new IntegerResolver($this->column);

        $this->assertEquals('\'integer\' => fake()->randomNumber(),', $resolver->fake());
    }

    public function test_migration_method_can_working_well(): void
    {
        $resolver = new IntegerResolver($this->column);

        if ($this->driver == 'mysql') {
            $this->assertEquals('$table->unsignedInteger(\''.$resolver->field.'\');', $resolver->migration());
        }

        if ($this->driver == 'sqlite') {
            $this->assertEquals('$table->integer(\''.$resolver->field.'\');', $resolver->migration());
        }
    }
}