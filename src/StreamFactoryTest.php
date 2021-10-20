<?php

declare(strict_types=1);

namespace BuzzingPixel\Minify;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Stream;

/** @psalm-suppress PropertyNotSetInConstructor */
class StreamFactoryTest extends TestCase
{
    public function testMake(): void
    {
        $factory = new StreamFactory();

        self::assertInstanceOf(
            Stream::class,
            $factory->make(),
        );
    }
}
