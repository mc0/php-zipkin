<?php
namespace Drefined\Zipkin\Instrumentation\Laravel\Services;

use Illuminate\Contracts\Container\Container;
use Drefined\Zipkin\Core\Trace;
use Drefined\Zipkin\Tracer;
use GuzzleHttp\Client;

/**
 * Class ZipkinTracingService
 * @package Drefined\Zipkin\Instrumentation\Laravel\Services
 */
class ZipkinTracingService
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function createTrace($endpoint,
                                $sampled,
                                $debug)
    {
        $config = $this->container->make('config')->get('zipkin');

        $tracer = new Tracer(
            $config,
            new Client(),
            $sampled,
            $debug
        );

        return new Trace($tracer, $endpoint, $sampled, $debug);
    }
}
