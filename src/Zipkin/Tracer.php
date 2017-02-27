<?php
namespace Drefined\Zipkin;

use Drefined\Zipkin\Core\Span;
use Drefined\Zipkin\Transport\HTTPLogger;
use Drefined\Zipkin\Transport\LoggerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class Tracer
{
    /** @var LoggerInterface $logger */
    private $logger;

    /** @var float $sampled */
    private $sampled;

    /** @var bool $debug */
    private $debug;

    /**
     * @param LoggerInterface $logger
     * @param float           $sampled
     * @param bool            $debug
     */
    public function __construct(array $config, ClientInterface $client, $sampled = 1.0, $debug = false)
    {
        $transport = $config['default'];

        $this->logger = $this->makeLogger($config['transports'][$transport], $client);

        if ($sampled < 1.0) {
            $this->sampled = ($sampled == 0) ? false : ($sampled > (mt_rand() / mt_getrandmax()));
        } else {
            $this->sampled = $sampled;
        }

        $this->debug = $debug;
    }

    /**
     * @param                 $config
     * @param ClientInterface $client
     * @return LoggerInterface
     * @throws \Exception
     */
    public function makeLogger($config, ClientInterface $client)
    {
        switch ($config['driver']) {
            case 'http':
                return new HTTPLogger($client, $config['uri']);
            default:
                throw new \Exception('Zipkin Logger Not Exist');
        }
    }

    /**
     * @param Span[] $spans
     */
    public function record(array $spans)
    {
        if ($this->sampled || $this->debug) {
            $this->logger->trace($spans);
        }
    }

    /**
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }
}
