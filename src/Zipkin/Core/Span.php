<?php
namespace Drefined\Zipkin\Core;

class Span
{
    /** @var string $name */
    private $name;

    /** @var Identifier $traceId */
    private $traceId;

    /** @var Identifier $spanId */
    private $spanId;

    /** @var Identifier|null $parentSpanId (optional) */
    private $parentSpanId;

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

    /**
     * @param string             $name
     * @param Identifier         $traceId
     * @param Identifier         $spanId
     * @param Identifier|null    $parentSpanId
     * @param Annotation[]       $annotations
     * @param BinaryAnnotation[] $binaryAnnotations
     * @param bool|null          $debug
     * @param int|null           $timestamp
     * @param int|null           $duration
     */
    public function __construct(
        $name,
        Identifier $traceId,
        Identifier $spanId,
        Identifier $parentSpanId = null,
        array $annotations = [],
        array $binaryAnnotations = [],
        $debug = false,
        $timestamp = null,
        $duration = null
    ) {
        $this->name              = $name;
        $this->traceId           = $traceId;
        $this->spanId            = $spanId;
        $this->parentSpanId      = $parentSpanId;
        $this->annotations       = $annotations;
        $this->binaryAnnotations = $binaryAnnotations;
        $this->debug             = $debug;
        $this->timestamp         = $timestamp;
        $this->duration          = $duration;
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
    public function getTraceId()
    {
        return $this->traceId;
    }

    /**
     * @return Identifier
     */
    public function getSpanId()
    {
        return $this->spanId;
    }

    /**
     * @return Identifier|null
     */
    public function getParentSpanId()
    {
        return $this->parentSpanId;
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
     * @param int $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
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
     * @param int $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
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
     * @return array
     */
    public function toArray()
    {
        $parentSpanId = (string)$this->getParentSpanId();

        return [
            'id'                => (string)$this->getSpanId(),
            'name'              => (string)$this->getName(),
            'traceId'           => (string)$this->getTraceId(),
            'parentId'          => (empty($parentSpanId)) ? null : (string)$parentSpanId,
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
