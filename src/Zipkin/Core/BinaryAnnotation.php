<?php
namespace Drefined\Zipkin\Core;

class BinaryAnnotation
{
    const TYPE_BOOL   = 0;
    const TYPE_BYTES  = 1;
    const TYPE_I16    = 2;
    const TYPE_I32    = 3;
    const TYPE_I64    = 4;
    const TYPE_DOUBLE = 5;
    const TYPE_STRING = 6;

    /** @var string $key */
    private $key;

    /** @var string $value */
    private $value;

    /** @var int $type */
    private $type;

    /** @var Endpoint $endpoint */
    private $endpoint;

    /**
     * @param string        $key
     * @param string        $value
     * @param int           $type
     * @param Endpoint      $endpoint
     */
    public function __construct($key, $value, $type, Endpoint $endpoint)
    {
        $this->key      = $key;
        $this->value    = $value;
        $this->type     = $type;
        $this->endpoint = $endpoint;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param Endpoint $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'key'      => (string)$this->getKey(),
            'value'    => (string)$this->getValue(),
            'endpoint' => $this->getEndpoint()->toArray(),
        ];
    }
}
