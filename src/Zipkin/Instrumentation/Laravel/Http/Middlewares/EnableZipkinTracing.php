<?php
/**
 * Created by PhpStorm.
 * User: JohnWang <takato@vip.qq.com>
 * Date: 2017/2/24
 * Time: 10:16
 */

namespace Drefined\Zipkin\Instrumentation\Laravel\Http\Middlewares;


use Drefined\Zipkin\Core\Annotation;
use Drefined\Zipkin\Core\BinaryAnnotation;
use Drefined\Zipkin\Core\Endpoint;
use Drefined\Zipkin\Core\Identifier;
use Drefined\Zipkin\Core\Span;
use Drefined\Zipkin\Instrumentation\Laravel\Jobs\PushToZipkin;
use Illuminate\Contracts\Container\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class EnableZipkinTracing
 * @package Drefined\Zipkin\Instrumentation\Laravel\Http\Middlewares
 */
class EnableZipkinTracing
{
    /**
     * @return \Closure
     * @author         JohnWang <takato@vip.qq.com>
     */
    public static function register(Container $container)
    {
        return function (callable $handler) use ($container) {
            return function (
                RequestInterface $request,
                array $options
            ) use ($handler, $container) {
                $method = $request->getMethod();
                $uri = $request->getUri();
                $name = "{$method} {$uri}";

                if ($container->bound('zipkin.trace_id')) {
                    /**
                     * @var Span $parentSpan
                     */
                    $parentSpan = $container->make('zipkin.request.span');
                    $traceId = $parentSpan->getTraceId();
                    $parentSpanId = $parentSpan->getSpanId();
                    $debug = $container->make('zipkin.debug');
                    $endpoint = $container->make('zipkin.endpoint');
                    $sampled = $container->make('zipkin.sampled');

                    $span = new Span(
                        $name,
                        $traceId,
                        Identifier::generate(),
                        $parentSpanId,
                        [],
                        [],
                        $debug,
                        time()
                    );
                } else {
                    $serverRequest = $container->make('request');
                    $config = $container->make('config')->get('zipkin');
                    $traceId = Identifier::generate();
                    $debug = false;
                    $sampled = 1.0;
                    $endpoint = new Endpoint(
                        $serverRequest->server('SERVER_ADDR', '127.0.0.1'),
                        $serverRequest->server('SERVER_PORT', '80'),
                        $config['name']
                    );

                    $span = new Span(
                        $name,
                        $traceId,
                        $traceId,
                        null,
                        [],
                        [],
                        $debug,
                        time()
                    );

                    $container->singleton('zipkin.trace_id', function () use ($traceId) {
                        return $traceId;
                    });
                }

                $requestAnnotations = [
                    'annotations'       => [Annotation::generateClientSend()],
                    'binaryAnnotations' => [
                        BinaryAnnotation::generateString('server.env', $container->environment()),
                        BinaryAnnotation::generateString('server.request.uri', $uri),
                        BinaryAnnotation::generateString('server.request.query', json_encode($request->getBody()->getContents())),
                    ]
                ];

                $promise = $handler($request, $options);

                return $promise->then(
                    function (ResponseInterface $response) use ($endpoint, $span, $sampled, $debug, $requestAnnotations) {
                        $annotation = Annotation::generateClientRecv();

                        $span->setDuration($annotation->getTimestamp() - ($requestAnnotations['annotations'][0])->getTimestamp());

                        // 推入队列
                        dispatch(
                            new PushToZipkin(
                                $endpoint,
                                $sampled,
                                $debug,
                                $span,
                                [
                                    'annotations'       => array_merge(
                                        $requestAnnotations['annotations'],
                                        [$annotation]
                                    ),
                                    'binaryAnnotations' => array_merge(
                                        $requestAnnotations['binaryAnnotations'],
                                        [
                                            BinaryAnnotation::generateString('server.response.http_status_code', $response->getStatusCode())
                                        ]
                                    )
                                ]
                            )
                        );

                        return $response;
                    }
                );
            };
        };
    }
}