<?php
namespace Drefined\Zipkin;

use Drefined\Zipkin\Core\Annotation;
use Drefined\Zipkin\Core\BinaryAnnotation;
use Drefined\Zipkin\Core\Span;
use Drefined\Zipkin\Transport\LoggerInterface;

class Recorder
{
    /** @var LoggerInterface $logger */
    private $logger;

    /** @var Core\Endpoint $$endpoint */
    private $endpoint;

    /** @var bool $debug */
    private $debug;

    /**
     * @param LoggerInterface $logger
     * @param Core\Endpoint   $endpoint
     * @param float           $sampled
     * @param bool            $debug
     */
    public function __construct(LoggerInterface $logger, Core\Endpoint $endpoint, $debug = false)
    {
        $this->logger = $logger;
        $this->endpoint = $endpoint;

        $this->debug = $debug;
    }

    /**
     * @param Span[] $spans
     */
    public function record(array $spans)
    {
        $traceSpans = [];
        if ($this->debug) {
            $traceSpans = $spans;
        } else {
            foreach ($spans as $span) {
                $context = $span->getContext();
                if ($context && $context->getSampled()) {
                    $traceSpans []= $span;
                }
            }
        }
        if (!empty($traceSpans)) {
            $this->logger->trace($spans);
        }
    }

    /**
     * @return Core\Endpoint $endpoint
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @return boolean $debug
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @param Core\Endpoint $endpoint
     */
    public function setEndpoint(Core\Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param int|null $timestamp
     * @return Annotation
     */
    public function newClientSendAnnotation($timestamp = null)
    {
        return new Annotation(
            Annotation::CLIENT_SEND,
            $this->endpoint,
            $timestamp
        );
    }

    /**
     * @param int|null $timestamp
     * @return Annotation
     */
    public function newClientRecvAnnotation($timestamp = null)
    {
        return new Annotation(
            Annotation::CLIENT_RECV,
            $this->endpoint,
            $timestamp
        );
    }

    /**
     * @param int|null $timestamp
     * @return Core\Annotation
     */
    public function newServerSendAnnotation($timestamp = null)
    {
        return new Annotation(
            Annotation::SERVER_SEND,
            $this->endpoint,
            $timestamp
        );
    }

    /**
     * @param int|null $timestamp
     * @return Core\Annotation
     */
    public function newServerRecvAnnotation($timestamp = null)
    {
        return new Annotation(
            Annotation::SERVER_RECV,
            $this->endpoint,
            $timestamp
        );
    }

    /**
     * @param string $key
     * @param string $value
     * @param int    $type
     * @return BinaryAnnotation
     */
    public function newBinaryAnnotation($key, $value, $type = BinaryAnnotation::TYPE_STRING)
    {
        return new BinaryAnnotation(
            $key,
            $value,
            $type ?: BinaryAnnotation::TYPE_STRING,
            $this->endpoint
        );
    }
}
