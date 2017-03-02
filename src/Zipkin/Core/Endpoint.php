<?php
namespace Drefined\Zipkin\Core;

class Endpoint
{
    /** @var string $ipv4 */
    private $ipv4;

    /** @var int $port */
    private $port;

    /** @var string $serviceName */
    private $serviceName;

    /** @var string $ipv6 (optional) */
    private $ipv6;

    /**
     * @param string      $ipv4
     * @param int         $port
     * @param string      $serviceName
     * @param string|null $ipv6
     */
    public function __construct($ipv4, $port, $serviceName, $ipv6 = null)
    {
        $this->ipv4        = $ipv4;
        $this->port        = $port;
        $this->serviceName = $serviceName;
        $this->ipv6        = $ipv6;
    }

    /**
     * @return string
     */
    public function getIpv4()
    {
        return $this->ipv4;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @return string|null
     */
    public function getIpv6()
    {
        return $this->ipv6;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [
            'serviceName' => (string)$this->getServiceName(),
        ];

        if (!empty($this->getIpv4())) {
            $data['ipv4'] = (string)$this->getIpv4();
        }

        if (!empty($this->getPort())) {
            $data['port'] = (int)$this->getPort();
        }

        if (!empty($this->getIpv6())) {
            $data['ipv6'] = (string)$this->getIpv6();
        }

        return $data;
    }
}
