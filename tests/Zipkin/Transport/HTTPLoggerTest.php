<?php

use Drefined\Zipkin\Transport\HTTPLogger;
use GuzzleHttp\Client;

class HTTPLoggerTest extends \PHPUnit\Framework\TestCase
{
    public function testDefaultEndpoint()
    {
        $client = new Client();
        $httpLogger = new HTTPLogger($client, 'http://localhost:9411/api/v1/spans');
        $this->assertEquals('http://localhost:9411/api/v1/spans', $httpLogger->getEndpoint());
    }
}
