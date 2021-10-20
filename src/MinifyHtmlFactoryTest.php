<?php

declare(strict_types=1);

namespace BuzzingPixel\Minify;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/** @psalm-suppress PropertyNotSetInConstructor */
class MinifyHtmlFactoryTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    private function getPrivate(object $object, string $prop): mixed
    {
        $reflection = new ReflectionClass($object);

        $property = $reflection->getProperty($prop);

        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @throws ReflectionException
     */
    public function testMakeWhenNoOptions(): void
    {
        $factory = new MinifyHtmlFactory();

        $minifier = $factory->make('testHtml');

        self::assertSame(
            '\Minify_CSSmin::minify',
            $this->getPrivate($minifier, '_cssMinifier'),
        );

        self::assertSame(
            '\JSMin\JSMin::minify',
            $this->getPrivate($minifier, '_jsMinifier'),
        );

        self::assertTrue(
            $this->getPrivate(
                $minifier,
                '_jsCleanComments',
            ),
        );

        self::assertNull(
            $this->getPrivate($minifier, '_isXhtml'),
        );

        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress UndefinedPropertyFetch
         */
        self::assertSame('testHtml', $minifier->_html);
    }
}
