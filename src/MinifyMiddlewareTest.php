<?php

declare(strict_types=1);

namespace BuzzingPixel\Minify;

use Minify_HTML;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress MixedArrayAccess
 */
class MinifyMiddlewareTest extends TestCase
{
    private MinifyMiddleware $middleware;

    /** @var StreamFactory&MockObject */
    private mixed $streamFactoryStub;
    /** @var MinifyHtmlFactory&MockObject */
    private mixed $minifyHtmlFactory;
    /** @var MockObject&ServerRequestInterface */
    private mixed $requestStub;
    /** @var MockObject&RequestHandlerInterface */
    private mixed $handlerStub;
    private ResponseInterface $responseStub;
    /** @var MockObject&StreamInterface */
    private mixed $bodyStub;

    /** @var mixed[] */
    private array $handlerCalls = [];

    /** @var mixed[] */
    private array $streamFactoryCalls = [];

    /** @var mixed[] */
    private array $bodyCalls = [];

    /** @var mixed[] */
    private array $minifyHtmlFactoryCalls = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlerCalls = [];

        $this->streamFactoryCalls = [];

        $this->bodyCalls = [];

        $this->minifyHtmlFactoryCalls = [];

        $this->responseStub = (new ResponseFactory())->createResponse();

        $this->bodyStub = $this->createMock(
            StreamInterface::class,
        );

        $this->bodyStub->method('write')->willReturnCallback(
            function (string $content): int {
                $this->bodyCalls[] = [
                    'method' => 'write',
                    'content' => $content,
                ];

                return 123;
            }
        );

        $this->streamFactoryStub = $this->createMock(
            StreamFactory::class,
        );

        $this->streamFactoryStub->method('make')->willReturnCallback(
            function (): StreamInterface {
                $this->streamFactoryCalls[] = ['method' => 'make'];

                return $this->bodyStub;
            }
        );

        $this->minifyHtmlFactory = $this->createMock(
            MinifyHtmlFactory::class,
        );

        $this->minifyHtmlFactory->method('make')->willReturnCallback(
            function (
                string $content,
                ?array $options = null,
            ): Minify_HTML {
                $this->minifyHtmlFactoryCalls[] = [
                    'method' => 'make',
                    'content' => $content,
                    'options' => $options,
                ];

                $minifyHtmlStub = $this->createMock(
                    Minify_HTML::class,
                );

                $minifyHtmlStub->method('process')->willReturn(
                    'processedHtml',
                );

                return $minifyHtmlStub;
            }
        );

        $this->requestStub = $this->createMock(
            ServerRequestInterface::class,
        );

        $this->handlerStub = $this->createMock(
            RequestHandlerInterface::class,
        );

        $this->handlerStub->method('handle')->willReturnCallback(
            function (
                ServerRequestInterface $request,
            ): ResponseInterface {
                $this->handlerCalls[] = [
                    'method' => 'handle',
                    'request' => $request,
                ];

                return $this->responseStub;
            }
        );

        $this->middleware = new MinifyMiddleware(
            streamFactory: $this->streamFactoryStub,
            minifyHtmlFactory: $this->minifyHtmlFactory,
        );
    }

    public function testProcessWhenNotTextHtml(): void
    {
        $this->responseStub = $this->responseStub->withHeader(
            'Content-Type',
            'text/javascript',
        );

        $response = $this->middleware->process(
            request: $this->requestStub,
            handler: $this->handlerStub,
        );

        self::assertSame(
            $this->responseStub,
            $response,
        );

        self::assertCount(1, $this->handlerCalls);

        self::assertSame(
            'handle',
            $this->handlerCalls[0]['method'],
        );

        self::assertSame(
            $this->requestStub,
            $this->handlerCalls[0]['request'],
        );

        self::assertCount(
            0,
            $this->streamFactoryCalls
        );

        self::assertCount(0, $this->bodyCalls);

        self::assertCount(
            0,
            $this->minifyHtmlFactoryCalls,
        );
    }

    public function testProcessWhenBodyIsEmpty(): void
    {
        $response = $this->middleware->process(
            request: $this->requestStub,
            handler: $this->handlerStub,
        );

        self::assertSame(
            $this->responseStub,
            $response,
        );

        self::assertCount(1, $this->handlerCalls);

        self::assertSame(
            'handle',
            $this->handlerCalls[0]['method'],
        );

        self::assertSame(
            $this->requestStub,
            $this->handlerCalls[0]['request'],
        );

        self::assertCount(
            0,
            $this->streamFactoryCalls
        );

        self::assertCount(0, $this->bodyCalls);

        self::assertCount(
            0,
            $this->minifyHtmlFactoryCalls,
        );
    }

    public function testProcess(): void
    {
        $this->responseStub = $this->responseStub->withHeader(
            'Content-Type',
            'text/html',
        );

        $body = (new StreamFactory())->make();

        $body->write('testIncomingBody');

        $this->responseStub = $this->responseStub->withBody($body);

        $response = $this->middleware->process(
            request: $this->requestStub,
            handler: $this->handlerStub,
        );

        self::assertSame(
            $response->getBody(),
            $this->bodyStub,
        );

        self::assertCount(1, $this->handlerCalls);

        self::assertSame(
            'handle',
            $this->handlerCalls[0]['method'],
        );

        self::assertSame(
            $this->requestStub,
            $this->handlerCalls[0]['request'],
        );

        self::assertCount(
            1,
            $this->streamFactoryCalls
        );

        self::assertSame(
            'make',
            $this->streamFactoryCalls[0]['method'],
        );

        self::assertCount(
            1,
            $this->bodyCalls,
        );

        self::assertSame(
            'write',
            $this->bodyCalls[0]['method'],
        );

        self::assertSame(
            'processedHtml',
            $this->bodyCalls[0]['content'],
        );

        self::assertCount(
            1,
            $this->minifyHtmlFactoryCalls,
        );

        self::assertSame(
            'make',
            $this->minifyHtmlFactoryCalls[0]['method'],
        );

        self::assertSame(
            'testIncomingBody',
            $this->minifyHtmlFactoryCalls[0]['content'],
        );

        self::assertNull($this->minifyHtmlFactoryCalls[0]['options']);
    }
}
