<?php

namespace PE\Component\Cronos\HTTP\Tests;

use PE\Component\Cronos\Core\Serializer;
use PE\Component\Cronos\HTTP\HTTPClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\RequestFactory;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UriFactory;

class HTTPClientTest extends TestCase
{
    /**
     * @var string
     */
    private $baseURI;

    /**
     * @var ClientInterface|MockObject
     */
    private $client;

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
     * @var RequestFactory
     */
    private $factoryRequest;

    /**
     * @var ResponseFactory
     */
    private $factoryResponse;

    protected function setUp()
    {
        $this->baseURI         = 'http://example.com';
        $this->client          = $this->createMock(ClientInterface::class);
        $this->serializer      = new Serializer();
        $this->factoryURI      = new UriFactory();
        $this->factoryStream   = new StreamFactory();
        $this->factoryRequest  = new RequestFactory();
        $this->factoryResponse = new ResponseFactory();
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function testRequest(): void
    {
        $httpClient = new HTTPClient(
            $this->baseURI,
            $this->client,
            $this->serializer,
            $this->factoryURI,
            $this->factoryStream,
            $this->factoryRequest
        );

        $this->client
            ->expects(self::once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) {
                self::assertSame('http://example.com?action=ACTION', (string) $request->getUri());
                self::assertSame('["PARAM"]', (string) $request->getBody());

                return $this->factoryResponse->createResponse()->withBody(
                    $this->factoryStream->createStream('["RESULT"]')
                );
            });

        self::assertSame(['RESULT'], $httpClient->request('ACTION', ['PARAM']));
    }
}
