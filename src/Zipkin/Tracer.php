<?php
namespace Drefined\Zipkin;

use Drefined\Zipkin\Core\Identifier;
use Drefined\Zipkin\Core\Span;

class Tracer
{
    /** @var Recorder $tracer */
    private $recorder;

    /** @var bool $sample */
    private $sample;

    /**
     * @param Recorder $recorder
     * @param float $sample
     */
    public function __construct(Recorder $recorder, $sample = 1.0) {
        $this->recorder = $recorder;
        $this->sample = $sample;
    }

    /**
     * @param string $name
     * @param string|null $traceId
     * @param string|null $spanId
     * @param string|null $parentSpanId
     * @return Core\Span
     */
    public function newSpan($name, $traceId = null, $spanId = null, $parentSpanId = null)
    {
        $span = new Span(
            $name,
            $spanId ?: Identifier::generate(),
            [],
            [],
            $this->recorder->getDebug()
        );

        if ($this->sample == 0) {
            $sampled = false;
        } elseif ($this->sample >= 1.0) {
            $sampled = true;
        } else {
            $sampled = $this->sample > (mt_rand() / mt_getrandmax());
        }
        $context = new Core\SpanContext(
            $sampled,
            $traceId ?: Identifier::generate(),
            $parentSpanId ?: null
        );
        $span->setContext($context);

        return $span;
    }
}
