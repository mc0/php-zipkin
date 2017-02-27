<?php
namespace Drefined\Zipkin\Core;

use Drefined\Zipkin\Tracer;
use Drefined\Zipkin\Transport\HTTPLogger;
use GuzzleHttp\Client;

class Trace
{
    /** @var Identifier $traceId */
    private $traceId;

    /** @var Span[] $spans */
    private $spans;

    /** @var Tracer $tracer */
    private $tracer;

    /** @var Endpoint $endpoint */
    private $endpoint;

    /** @var float $sampled */
    private $sampled;

    /** @var bool $debug */
    private $debug;

    /**
     * @param Tracer|null   $tracer
     * @param Endpoint|null $endpoint
     * @param float         $sampled
     * @param bool          $debug
     */
    public function __construct(
        Tracer $tracer,
        Endpoint $endpoint,
        $sampled = 1.0,
        $debug = false
    ) {
        $this->tracer = $tracer;
        $this->endpoint = $endpoint;
        $this->sampled = $sampled;
        $this->debug   = $debug;

        $this->traceId = Identifier::generate();
    }

    public function createNewSpan(
        $name,
        Identifier $traceId = null,
        Identifier $spanId = null,
        Identifier $parentSpanId = null,
        $timestamp = null,
        $duration = null
    ) {
        $traceId = $traceId ?: $this->traceId;
        $spanId  = $spanId ?: Identifier::generate();

        if (!empty($this->spans) && empty($parentSpanId)) {
            $parentSpan   = end($this->spans);
            $parentSpanId = $parentSpan->getSpanId();
        }

        $this->spans[] = new Span($name, $traceId, $spanId, $parentSpanId, [], [], $this->debug, $timestamp, $duration);
    }

    /**
     * @param Span               $span
     * @param Annotation[]       $annotations
     * @param BinaryAnnotation[] $binaryAnnotations
     */
    public function record(Span $span, array $annotations = [], array $binaryAnnotations = [])
    {
        foreach ($annotations as $annotation) {
            if (empty($annotation->getEndpoint()) && $this->endpoint) {
                $annotation->setEndpoint($this->endpoint);
            }
        }

        foreach ($binaryAnnotations as $binaryAnnotation) {
            if (empty($binaryAnnotation->getEndpoint()) && $this->endpoint) {
                $binaryAnnotation->setEndpoint($this->endpoint);
            }
        }

        $span->setAnnotations($annotations);
        $span->setBinaryAnnotations($binaryAnnotations);

        if (!empty($this->tracer)) {
            $this->tracer->record([$span]);
        }
    }

    /**
     * @return Identifier
     */
    public function getTraceId()
    {
        return $this->traceId;
    }

    /**
     * @return Span[]
     */
    public function getSpans()
    {
        return $this->spans;
    }

    /**
     * @return Span
     */
    public function popSpan()
    {
        return array_pop($this->spans);
    }

    /**
     * @param Span $span
     */
    public function pushSpan(Span $span)
    {
        $this->spans[] = $span;
    }

    /**
     * @param Tracer $tracer
     */
    public function setTracer(Tracer $tracer)
    {
        $this->tracer = $tracer;
    }

    /**
     * @param Endpoint $endpoint
     */
    public function setEndpoint(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }
}
