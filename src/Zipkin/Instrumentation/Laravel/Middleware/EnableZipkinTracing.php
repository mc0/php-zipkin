<?php
namespace Drefined\Zipkin\Instrumentation\Laravel\Middleware;

use Closure;
use Drefined\Zipkin\Core\Annotation;
use Drefined\Zipkin\Core\BinaryAnnotation;
use Drefined\Zipkin\Core\Endpoint;
use Drefined\Zipkin\Core\Identifier;
use Drefined\Zipkin\Core\Span;
use Drefined\Zipkin\Core\Time;
use Drefined\Zipkin\Instrumentation\Laravel\Jobs\PushToZipkin;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnableZipkinTracing
{
    /**
     * The application instance.
     *
     * @var Container $container
     */
    protected $container;

    /**
     * @var
     */
    protected $requestAnnotations;

    /**
     * Create a new middleware instance.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $spanId = Identifier::generate()->__toString();
        $traceId = $request->header('X-B3-TraceId', null);
        if ($traceId === null) {
            $traceId = $spanId;
        }

        $parentSpanId = $request->header('X-B3-SpanId', null);

        $config = $this->container->make('config')->get('zipkin');

        $endpoint = new Endpoint(
            $request->server('SERVER_ADDR', '127.0.0.1'),
            $request->server('SERVER_PORT', '80'),
            $config['name']
        );

        $sampled = $request->header('X-B3-Sampled', 1.0);
        $debug = $request->header('X-B3-Flags', false);
        $uri = $request->getUri();
        $name = $request->getMethod();

        $span = new Span(
            $name,
            new Identifier($traceId),
            new Identifier($spanId),
            $parentSpanId ? new Identifier($parentSpanId) : null,
            [],
            [],
            $debug,
            Time::microseconds()
        );

        $this->container->singleton('zipkin.trace_id', function () use ($traceId) {
            return $traceId;
        });

        $this->container->singleton('zipkin.endpoint', function () use ($endpoint) {
            return $endpoint;
        });

        $this->container->singleton('zipkin.sampled', function () use ($sampled) {
            return $sampled;
        });

        $this->container->singleton('zipkin.debug', function () use ($debug) {
            return $debug;
        });

        $this->container->singleton('zipkin.request.span', function () use ($span) {
            return $span;
        });

        $this->requestAnnotations = [
            'annotations'       => [Annotation::generateServerRecv()],
            'binaryAnnotations' => [
                BinaryAnnotation::generateString('server.env', $this->container->environment()),
                BinaryAnnotation::generateString('server.request.uri', $uri),
                BinaryAnnotation::generateString('server.request.query', json_encode($request->query->all())),
            ]
        ];

        $response = $next($request);

        $this->terminate($request, $response);

        return $response;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @author         JohnWang <takato@vip.qq.com>
     */
    public function terminate(Request $request, Response $response)
    {
        $endpoint = $this->container->make('zipkin.endpoint');
        $sampled = $this->container->make('zipkin.sampled');
        $debug = $this->container->make('zipkin.debug');

        /**
         * @var Span $span
         */
        $span = $this->container->make('zipkin.request.span');

        $annotation = Annotation::generateServerSend();

        $requestAnnotation = $this->requestAnnotations['annotations'][0];
        $span->setDuration((int)($annotation->getTimestamp() - $requestAnnotation->getTimestamp()));

        // 推入队列
        dispatch(
            new PushToZipkin(
                $endpoint,
                $sampled,
                $debug,
                $span,
                [
                    'annotations'       => array_merge(
                        $this->requestAnnotations['annotations'],
                        [$annotation]
                    ),
                    'binaryAnnotations' => array_merge(
                        $this->requestAnnotations['binaryAnnotations'],
                        [
                            BinaryAnnotation::generateString('server.response.http_status_code', $response->getStatusCode())
                        ]
                    )
                ]
            )
        );
    }
}
