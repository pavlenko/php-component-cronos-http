<?php

namespace PE\Component\Cronos\HTTP;

use PE\Component\Cronos\Core\ClientAction;
use PE\Component\Cronos\Core\SerializerInterface;
use PE\Component\Cronos\Core\ServerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class HTTPResponder implements RequestHandlerInterface
{
    /**
     * @var ServerInterface
     */
    private $server;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var StreamFactoryInterface
     */
    private $factoryStream;

    /**
     * @var ResponseFactoryInterface
     */
    private $factoryResponse;

    /**
     * @param ServerInterface          $server
     * @param SerializerInterface      $serializer
     * @param StreamFactoryInterface   $factoryStream
     * @param ResponseFactoryInterface $factoryResponse
     */
    public function __construct(
        ServerInterface $server,
        SerializerInterface $serializer,
        StreamFactoryInterface $factoryStream,
        ResponseFactoryInterface $factoryResponse
    ) {
        $this->server          = $server;
        $this->serializer      = $serializer;
        $this->factoryStream   = $factoryStream;
        $this->factoryResponse = $factoryResponse;
    }


    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        parse_str($request->getUri()->getQuery(), $query);

        $clientAction = new ClientAction(
            $query['action'],
            $this->serializer->decode((string) $request->getBody())
        );

        $this->server->trigger(ServerInterface::EVENT_CLIENT_ACTION, $clientAction);

        return $this->factoryResponse->createResponse()->withBody(
            $this->factoryStream->createStream(
                $this->serializer->encode($clientAction->getResult())
            )
        );
    }
}
