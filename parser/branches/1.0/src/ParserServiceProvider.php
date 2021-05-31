<?php

declare(strict_types=1);

namespace Pollen\Parser;

use Pollen\Container\ServiceProvider;

class ParserServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected $provides = [ParserManagerInterface::class];

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share(ParserManagerInterface::class, function() {
            return new ParserManager([], $this->getContainer());
        });
    }
}