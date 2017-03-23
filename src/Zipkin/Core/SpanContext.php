<?php
namespace Drefined\Zipkin\Core;

class SpanContext
{
    /** @var bool $sampled */
    private $sampled;

    /** @var Identifier $traceId */
    private $traceId;

    /** @var Identifier|null $parentSpanId */
    private $parentSpanId;

    public function __construct($sampled, $traceId, $parentSpanId = null)
    {
        $this->sampled = $sampled;
        $this->traceId = $traceId;
        $this->parentSpanId = $parentSpanId;
    }

    /**
     * @return bool
     */
    public function getSampled()
    {
        return $this->sampled;
    }

    /**
     * @return Identifier
     */
    public function getTraceId()
    {
        return $this->traceId;
    }

    /**
     * @return Identifier|null
     */
    public function getParentSpanId()
    {
        return $this->parentSpanId;
    }
}
