<?php

use Drefined\Zipkin\Core\Annotation;
use Drefined\Zipkin\Core\BinaryAnnotation;
use Drefined\Zipkin\Core\Endpoint;
use Drefined\Zipkin\Core\Identifier;
use Drefined\Zipkin\Recorder;
use Drefined\Zipkin\Tracer;

class TraceTest extends \PHPUnit\Framework\TestCase
{
    // Ideally we should refactor this file for unit tests instead of an integration test.
    public function testTrace()
    {
        $client   = new \GuzzleHttp\Client();
        $logger   = new \Drefined\Zipkin\Transport\HTTPLogger($client, 'http://localhost:9411/api/v1/spans');
        $endpoint = new Endpoint('test-trace', '127.0.0.1', 8080);
        $recorder = new Recorder($logger, $endpoint, true);
        $tracer = new Tracer($recorder, 1.0);

        $traceId = Identifier::generate();

        $serverSpan = $tracer->newSpan('test-server-trace', $traceId);
        $serverSpan->setAnnotations([$recorder->newServerRecvAnnotation()]);
        $serverSpan->setBinaryAnnotations([$recorder->newBinaryAnnotation('server.request.uri', '/server')]);

        $recorder->record([$serverSpan]);
        sleep(1);

        // parent: test-server-trace
        $clientSpan = $tracer->newSpan('test-client-trace', $traceId, null, $serverSpan->getSpanId());
        $clientSpan->setAnnotations([$recorder->newClientSendAnnotation()]);
        $clientSpan->setBinaryAnnotations([$recorder->newBinaryAnnotation('client.request.uri', '/client')]);

        $recorder->record([$clientSpan]);

        // parent: test-client-trace
        $span = $tracer->newSpan('test-server-trace-2', $traceId, null, $clientSpan->getSpanId());
        $span->setAnnotations([$recorder->newServerRecvAnnotation()]);
        $span->setBinaryAnnotations([$recorder->newBinaryAnnotation('server.request.uri', '/server2')]);
        sleep(1);
        $span->finish();

        $recorder->record([$span]);

        // parent: test-server-trace
        $span = $tracer->newSpan('test-client-trace-2', $traceId, null, $serverSpan->getSpanId());
        $span->setAnnotations([$recorder->newClientSendAnnotation()]);
        $span->setBinaryAnnotations([$recorder->newBinaryAnnotation('client.request.uri', '/client2')]);
        sleep(1);
        $span->finish();
        $recorder->record([$span]);

        $clientSpan->finish();
        $clientSpan->setAnnotations([$recorder->newClientRecvAnnotation()]);
        $clientSpan->setBinaryAnnotations([$recorder->newBinaryAnnotation('client.response', 200)]);
        $recorder->record([$clientSpan]);

        $serverSpan->finish();
        $serverSpan->setAnnotations([$recorder->newServerRecvAnnotation()]);
        $serverSpan->setBinaryAnnotations([$recorder->newBinaryAnnotation('client.response', 200)]);
        $recorder->record([$serverSpan]);

        echo "http://localhost:9411/api/v1/trace/{$traceId}";
        // TODO: Assert trace is the same from api call http://localhost:9411/api/v1/trace/{traceId}
    }
}
