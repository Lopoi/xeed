<?php

namespace Cable8mm\Xeed\Generators;

use Cable8mm\Xeed\Interfaces\GeneratorInterface;
use Cable8mm\Xeed\Mergers\MergerContainer;
use Cable8mm\Xeed\Support\File;
use Cable8mm\Xeed\Support\Path;
use Cable8mm\Xeed\Table;

/**
 * Generator for `dist/database/migrations/*.php`.
 */
final class RelationGenerator implements GeneratorInterface
{
    /**
     * @var string Stub string from the stubs folder file.
     */
    private string $stub;

    /**
     * The left padding for the body of the generated.
     */
    public const INTENT = '            ';

    /**
     * Engines for MergerContainer.
     *
     * @var ?array<\Cable8mm\Xeed\Mergers\Merger>
     */
    private ?array $mergerEngines = null;

    private function __construct(
        private Table $table,
        private ?string $namespace = null,
        private ?string $destination = null
    )
    {
        if (is_null($destination))
        {
            $this->destination = Path::model();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function run(bool $force = false): void
    {
        $model = File::system()->read($this->destination . DIRECTORY_SEPARATOR . $this->table->model() . '.php');
        [$before, $after] = explode('use HasFactory;', $model);
        $belongsToRelation = '';

        foreach ($this->table->getForeignKeys() as $key)
        {
            $belongsToRelation .= PHP_EOL . $key->belongsTo() . PHP_EOL;
            $relatedModel = File::system()->read($this->destination . DIRECTORY_SEPARATOR . $key->referenced_table . '.php');
            [$relatedBefore, $relatedAfter] = explode('use HasFactory;', $relatedModel);

            $hasManyRelation = $key->hasMany();
            $relatedModel = $relatedBefore . 'use HasFactory;' . PHP_EOL . $hasManyRelation . PHP_EOL . $relatedAfter;
            File::system()->write(
                $this->destination . DIRECTORY_SEPARATOR . $key->referenced_table . '.php',
                $relatedModel,
                true
            );
        }

        $model = $before . 'use HasFactory;' . PHP_EOL . $belongsToRelation . PHP_EOL . $after;

        File::system()->write(
            $this->destination . DIRECTORY_SEPARATOR . $this->table->model() . '.php',
            $model,
            true
        );
    }

    /**
     * Set merger engines.
     *
     * @param  array<\Cable8mm\Xeed\Mergers\Merger>  $engines  An array of merger engines.
     * @return static The method returns the current instance that enables methods chaining.
     */
    public function merging(array $engines): static
    {
        $this->mergerEngines = $engines;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public static function make(
        Table $table,
        ?string $namespace = null,
        ?string $destination = null
    ): static
    {
        return new self($table, $namespace, $destination);
    }
}
