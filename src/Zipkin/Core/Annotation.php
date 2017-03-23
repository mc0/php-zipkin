<?php
namespace Drefined\Zipkin\Core;

class Annotation
{
    const CLIENT_SEND     = 'cs';
    const CLIENT_RECV     = 'cr';
    const SERVER_SEND     = 'ss';
    const SERVER_RECV     = 'sr';

    /** @var string $value */
    private $value;

    /** @var Endpoint $endpoint */
    private $endpoint;

    /** @var int $timestamp */
    private $timestamp;

    /**
     * @param int           $value
     * @param Endpoint|null $endpoint
     * @param string|null   $timestamp
     */
    public function __construct($value, Endpoint $endpoint, $timestamp = null)
    {
        $this->value     = $value;
        $this->endpoint  = $endpoint;
        $this->timestamp = $timestamp ?: Time::microseconds();
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param Endpoint $endpoint
     */
    public function setEndpoint(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'value'     => (string)$this->getValue(),
            'timestamp' => (int)$this->getTimestamp(),
            'endpoint'  => $this->getEndpoint()->toArray(),
        ];
    }
}
