<?php

namespace Cable8mm\Xeed\Generators;

use Cable8mm\Xeed\Interfaces\Generator;
use Cable8mm\Xeed\Support\Inflector;
use Cable8mm\Xeed\Support\Path;
use Cable8mm\Xeed\Table;

/**
 * Generator for `dist/database/seeders/*.php`.
 */
final class SeederGenerator implements Generator
{
    /**
     * @var string Stub string from the stubs folder file.
     */
    private string $stub;

    /**
     * @var string The seeder class name.
     */
    private string $seeder;

    private function __construct(
        private string $class,
        private ?string $namespace = null,
        private ?string $dist = null
    ) {
        if (is_null($dist)) {
            $this->dist = Path::seeder();
        }

        if (is_null($namespace)) {
            $this->namespace = '\App\Models';
        }

        $this->stub = file_get_contents(Path::stub().'Seeder.stub');

        $this->seeder = $this->class.'Seeder';
    }

    /**
     * {@inheritDoc}
     */
    public function run(): void
    {
        $seederClass = str_replace(
            ['{class}', '{namespace_class}'],
            [$this->class, $this->namespace.'\\'.$this->class],
            $this->stub
        );

        file_put_contents(
            $this->dist.$this->seeder.'.php',
            $seederClass
        );
    }

    /**
     * Factory method.
     *
     * @param  string|Table  $class  The model class name
     * @param  string  $namespace  The model namespace
     * @param  string  $dist  The path to the dist folder
     */
    public static function make(
        string|Table $class,
        ?string $namespace = null,
        ?string $dist = null
    ): static {

        if ($class instanceof Table) {
            $class = Inflector::classify($class->name);
        }

        return new self($class, $namespace, $dist);
    }
}
