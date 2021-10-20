<?php

declare(strict_types=1);

namespace BuzzingPixel\Minify;

use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Stream;

use function assert;
use function fopen;
use function is_resource;

class StreamFactory
{
    public function make(): StreamInterface
    {
        $stream = fopen('php://temp', mode: 'r+');

        assert(is_resource($stream));

        return new Stream($stream);
    }
}
