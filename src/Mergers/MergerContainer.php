<?php

namespace Cable8mm\Xeed\Mergers;

use Cable8mm\Xeed\Interfaces\MergerInterface;
use InvalidArgumentException;
use Stringable;

/**
 * Container for mergers.
 */
class MergerContainer implements Stringable
{
    /**
     * @var array<MergerInterface>
     */
    private array $engine = [];

    /**
     * An array of migration files per lines.
     *
     * @var array<string>
     */
    private array $lines;

    private function __construct(
        private ?string $migration = null,
        private ?string $body = null
    ) {
        if (is_null($this->migration)) {
            $this->lines = file($this->body = $migration);

            return;
        }

        if (! file_exists($migration)) {
            throw new InvalidArgumentException('File does not exist in '.$migration);
        }
    }

    /**
     * Add engine.
     *
     * @param  Merger  $merger  An engine to be added.
     * @return static The method returns the current instance that enables method chaining.
     */
    public function engine(Merger $merger): static
    {
        $this->engine[] = $merger;

        return $this;
    }

    /**
     * Add engines.
     *
     * @param  array<MergerInstance>  $mergers  An array of engines to be added.
     * @return static The method returns the current instance that enables methods chaining.
     */
    public function engines(array $mergers): static
    {
        $this->engine += $mergers;

        return $this;
    }

    /**
     * Execute mergers.
     *
     * @return static The method returns this instance that was execute all mergers.
     */
    public function operating(): static
    {
        $i = 0;
        $total = count($this->lines);

        foreach ($this->lines as $key => $line) {
            if ($i++ + 1 === $total) {
                break;
            }

            /* @var MergerInterface $engine */
            foreach ($this->engine as $engine) {
                $replace = $engine->start($this->lines[$key], $this->lines[$key + 1]);

                if ($replace !== null) {
                    $this->lines[$key + 1] = $replace;
                    $this->lines[$key] = null;
                }
            }
        }

        return $this;
    }

    /**
     * Write a string to a file from the `$this->lines` array.
     *
     * @return int|false The method returns the number of bytes that were written to the file, or false on failure.
     */
    public function write(): int|false
    {
        return file_put_contents($this->migration, implode(PHP_EOL, $this->lines));
    }

    /**
     * Print lines to string.
     *
     * @return string The method returns the string representation.
     */
    public function verbose(): string
    {
        return implode(PHP_EOL, $this->lines);
    }

    /**
     * Class magic method to get the real migration file path.
     *
     * @return string The method returns the real file path.
     */
    public function __toString(): string
    {
        return $this->migration;
    }

    /**
     * Constructor factory.
     *
     * @param  string  $migration  Path to the migration file from root folder.
     * @param  string  $body  Migration string.
     * @return string The method return the instance that called the constructor.
     *
     * @throws InvalidArgumentException
     *
     * @example MergerContainer::from(migration: <migration_path>)->engines([...engines...])->operating()->write();
     * @example MergerContainer::from(body : <body>)->engines([...engines...])->operating()->verbose();
     */
    public static function from(?string $migration = null, ?string $body = null): static
    {
        if (! is_null($migration) !== ! is_null($body)) {
            return new static($migration, $body);
        }

        throw new InvalidArgumentException('One of the arguments must be null');
    }
}