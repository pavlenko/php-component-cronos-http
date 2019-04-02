<?php

namespace PE\Component\Cronos\HTTP\Tests;

use PE\Component\Cronos\Core\ClientAction;
use PE\Component\Cronos\Core\Serializer;
use PE\Component\Cronos\Core\ServerInterface;
use PE\Component\Cronos\HTTP\HTTPResponder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UriFactory;

class HTTPResponderTest extends TestCase
{
    /**
     * @var ServerInterface|MockObject
     */
    private $server;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var UriFactory
     */
    private $factoryURI;

    /**
     * @var StreamFactory
     */
    private $factoryStream;

    /**
     * @var ServerRequestFactory
     */
    private $factoryRequest;

    /**
     * @var ResponseFactory
     */
    private $factoryResponse;

    protected function setUp()
    {
        $this->server          = $this->createMock(ServerInterface::class);
        $this->serializer      = new Serializer();
        $this->factoryURI      = new UriFactory();
        $this->factoryStream   = new StreamFactory();
        $this->factoryRequest  = new ServerRequestFactory();
        $this->factoryResponse = new ResponseFactory();
    }


    public function testHandle(): void
    {
        $this->server
            ->expects(self::once())
            ->method('trigger')
            ->willReturnCallback(static function (string $event, ClientAction $action) {
                self::assertSame(ServerInterface::EVENT_CLIENT_ACTION, $event);
                self::assertSame('ACTION', $action->getName());
                self::assertSame(['PARAM'], $action->getParams());

                $action->setResult(['RESULT']);
                return 1;
            });

        $responder = new HTTPResponder(
            $this->server,
            $this->serializer,
            $this->factoryStream,
            $this->factoryResponse
        );

        $uri = $this->factoryURI->createUri('http://example.com?action=ACTION');

        $request = $this->factoryRequest->createServerRequest('POST', $uri)->withBody(
            $this->factoryStream->createStream('["PARAM"]')
        );

        $response = $responder->handle($request);

        self::assertSame('["RESULT"]', (string) $response->getBody());
    }
}
