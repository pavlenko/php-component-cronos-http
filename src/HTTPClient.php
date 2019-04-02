<?php

namespace PE\Component\Cronos\HTTP;

use PE\Component\Cronos\Core\ClientInterface;
use PE\Component\Cronos\Core\SerializerInterface;
use Psr\Http\Client\ClientInterface as HTTPClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final class HTTPClient implements ClientInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $baseURI;

    /**
     * @var HTTPClientInterface
     */
    private $client;

    /**
     * @var UriFactoryInterface
     */
    private $factoryURI;

    /**
     * @var StreamFactoryInterface
     */
    private $factoryStream;

    /**
     * @var RequestFactoryInterface
     */
    private $factoryRequest;

    /**
     * @param string                  $baseURI
     * @param HTTPClientInterface     $client
     * @param SerializerInterface     $serializer
     * @param UriFactoryInterface     $factoryURI
     * @param StreamFactoryInterface  $factoryStream
     * @param RequestFactoryInterface $factoryRequest
     */
    public function __construct(
        string $baseURI,
        HTTPClientInterface $client,
        SerializerInterface $serializer,
        UriFactoryInterface $factoryURI,
        StreamFactoryInterface $factoryStream,
        RequestFactoryInterface $factoryRequest
    ) {
        $this->baseURI        = $baseURI;
        $this->client         = $client;
        $this->serializer     = $serializer;
        $this->factoryURI     = $factoryURI;
        $this->factoryStream  = $factoryStream;
        $this->factoryRequest = $factoryRequest;
    }

    /**
     * @inheritDoc
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function request(string $action, $request)
    {
        $uri = $this->factoryURI->createUri($this->baseURI)->withQuery(http_build_query(['action' => $action]));

        $request = $this->factoryRequest->createRequest('POST', $uri)->withBody(
            $this->factoryStream->createStream(
                $this->serializer->encode($request)
            )
        );

        $response = $this->client->sendRequest($request);

        return $this->serializer->decode((string) $response->getBody());
    }
}