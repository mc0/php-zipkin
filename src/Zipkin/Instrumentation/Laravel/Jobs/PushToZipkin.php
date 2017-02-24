<?php
/**
 * Created by PhpStorm.
 * User: JohnWang <takato@vip.qq.com>
 * Date: 2017/2/23
 * Time: 17:44
 */

namespace Drefined\Zipkin\Instrumentation\Laravel\Jobs;

use Drefined\Zipkin\Core\Endpoint;
use Drefined\Zipkin\Core\Span;
use Drefined\Zipkin\Instrumentation\Laravel\Services\ZipkinTracingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class PushToZipkin
 * @package Drefined\Zipkin\Instrumentation\Laravel\Jobs
 */
class PushToZipkin implements ShouldQueue
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "queueOn" and "delay" queue helper methods.
    |
    */

    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Endpoint $endpoint
     */
    protected $endpoint;

    /**
     * @var float
     */
    protected $sampled;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var Span
     */
    protected $span;

    /**
     * @var array
     */
    protected $annotations;

    /**
     * PushToZipkin constructor.
     */
    public function __construct($endpoint,
                                $sampled,
                                $debug,
                                $span,
                                $annotations)
    {
        $this->endpoint = $endpoint;
        $this->sampled = $sampled;
        $this->debug = $debug;
        $this->span = $span;
        $this->annotations = $annotations;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var ZipkinTracingService $tracingService */
        $tracingService = app(ZipkinTracingService::class);

        $trace = $tracingService->createTrace(
            $this->endpoint,
            $this->sampled,
            $this->debug
        );

        try {
            $trace->record(
                $this->span,
                $this->annotations['annotations'],
                $this->annotations['binaryAnnotations']
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}