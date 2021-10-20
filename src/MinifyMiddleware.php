<?php

declare(strict_types=1);

namespace BuzzingPixel\Minify;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function trim;

class MinifyMiddleware implements MiddlewareInterface
{
    public function __construct(
        private StreamFactory $streamFactory,
        private MinifyHtmlFactory $minifyHtmlFactory,
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $response = $handler->handle($request);

        $contentType = $response->getHeader('Content-Type');

        $contentTypeString = $contentType[0] ?? 'text/html';

        if ($contentTypeString !== 'text/html') {
            return $response;
        }

        $content = (string) $response->getBody();

        if (trim($content) === '') {
            return $response;
        }

        $body = $this->streamFactory->make();

        $body->write($this->minifyHtmlFactory->make(
            html: $content,
        )->process());

        return $response->withBody($body);
    }
}
