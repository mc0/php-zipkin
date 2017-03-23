<?php
namespace Drefined\Zipkin\Core;

class Span
{
    /** @var Identifier $spanId */
    private $spanId;

    /** @var string $name */
    private $name;

    /** @var Annotation[] $annotations */
    private $annotations;

    /** @var BinaryAnnotation[] $binaryAnnotations */
    private $binaryAnnotations;

    /** @var bool $debug (optional) */
    private $debug;

    /** @var int|null $timestamp (optional) */
    private $timestamp;

    /** @var int|null $duration (optional) */
    private $duration;

    /** @var SpanContext $context (optional) */
    private $context;

    /**
     * @param string             $name
     * @param Identifier         $spanId
     * @param Annotation[]       $annotations
     * @param BinaryAnnotation[] $binaryAnnotations
     * @param bool               $debug
     * @param int|null           $timestamp
     * @param int|null           $duration
     * @param SpanContext|null   $context
     */
    public function __construct(
        $name,
        Identifier $spanId,
        array $annotations = [],
        array $binaryAnnotations = [],
        $debug = false,
        $timestamp = null,
        $duration = null,
        SpanContext $context = null
    ) {
        $this->name              = $name;
        $this->spanId            = $spanId;
        $this->annotations       = $annotations;
        $this->binaryAnnotations = $binaryAnnotations;
        $this->debug             = $debug;
        $this->timestamp         = $timestamp ?: Time::microseconds();
        $this->duration          = $duration;
        $this->context           = $context;
    }

    /**
     * @return void
     */
    public function finish()
    {
        $this->duration = Time::microseconds() - $this->timestamp;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Identifier
     */
    public function getSpanId()
    {
        return $this->spanId;
    }

    /**
     * @return Annotation[]
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * @return BinaryAnnotation[]
     */
    public function getBinaryAnnotations()
    {
        return $this->binaryAnnotations;
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @return int|null
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return int|null
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @return SpanContext|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param Annotation[] $annotations
     */
    public function setAnnotations(array $annotations)
    {
        $this->annotations = $annotations;
    }

    /**
     * @param BinaryAnnotation[] $binaryAnnotations
     */
    public function setBinaryAnnotations(array $binaryAnnotations)
    {
        $this->binaryAnnotations = $binaryAnnotations;
    }

    /**
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param int $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @param SpanContext|null $context
     */
    public function setContext(SpanContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $context = $this->getContext();
        $parentSpanId = (string)$context->getParentSpanId();

        return [
            'id'                => (string)$this->getSpanId(),
            'name'              => (string)$this->getName(),
            'traceId'           => (string)$context->getTraceId(),
            'parentId'          => empty($parentSpanId) ? null : (string)$parentSpanId,
            'timestamp'         => (int)$this->getTimestamp(),
            'duration'          => (int)$this->getDuration(),
            'debug'             => (boolean)$this->getDebug(),
            'annotations'       => array_map([$this, 'annotationToArray'], $this->getAnnotations()),
            'binaryAnnotations' => array_map([$this, 'binaryAnnotationToArray'], $this->getBinaryAnnotations()),
        ];
    }

    /**
     * @param Annotation $annotation
     * @return array
     */
    public function annotationToArray(Annotation $annotation)
    {
        return $annotation->toArray();
    }

    /**
     * @param BinaryAnnotation $binaryAnnotation
     * @return array
     */
    public function binaryAnnotationToArray(BinaryAnnotation $binaryAnnotation)
    {
        return $binaryAnnotation->toArray();
    }
}
