<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */

namespace test\unit\FestivalsApi;

use FestivalsApi\FestivalsApiClient;
use FestivalsApi\FestivalsApiClientException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class FestivalsApiClientTest extends TestCase
{
    /** @var Client */
    protected $guzzle;

    /**
     * @var array
     */
    protected $history = [];

    /**
     * @var array
     */
    protected $response_queue = [];

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(FestivalsApiClient::class, $this->newSubject());
    }

    /**
     * @testWith ["loadEvent", 1234]
     *           ["searchEvents", {"title": "Foo"}]
     *
     * @param string $method
     * @param mixed  $args
     */
    public function test_it_throws_if_you_attempt_to_use_it_without_setting_credentials($method, $args)
    {
        $subject = $this->newSubject();
        $this->expectException(FestivalsApiClientException::class);
        $this->expectExceptionMessage('Missing credentials');
        $subject->$method($args);
    }

    public function test_it_performs_requests_with_initialised_key_and_secret()
    {
        $this->mockGuzzleWithEmptySuccessResponse();
        $subject = $this->newSubject();
        $subject->setCredentials('mykey', 'mysecret');
        $subject->searchEvents([]);
        $this->assertSame(
            'key=mykey&signature=9c638d91ba50f39da6ecc1d4e8846ae30c318f55',
            $this->getRequest(0)->getUri()->getQuery()
        );
    }

    public function test_setting_base_url_overrides_default()
    {
        $this->mockGuzzleWithResponses(
            [
                new Response(200, [], "[]"),
                new Response(200, [], "[]"),
            ]
        );

        $subject = $this->newSubjectWithValidCredentials();

        $subject->searchEvents([]);
        $this->assertSame(
            'https://api.edinburghfestivalcity.com/events?key=test-key&signature=7793ae3038669f76de954f197f1818727a12a037',
            (string) $this->getRequest(0)->getUri()
        );

        $subject->setBaseUrl('http://example.test');
        $subject->searchEvents([]);
        $this->assertSame(
            'http://example.test/events?key=test-key&signature=7793ae3038669f76de954f197f1818727a12a037',
            (string) $this->getRequest(1)->getUri()
        );
    }

    public function test_it_calls_the_api_with_the_event_id_specified()
    {
        $this->mockGuzzleWithEmptySuccessResponse();
        $subject = $this->newSubjectWithValidCredentials();

        $result = $subject->loadEvent('1234');

        // calls the API only once
        $this->assertEquals(1, count($this->history));

        $expected = 'https://api.edinburghfestivalcity.com/events/1234?key=test-key&signature=93604dba44ed6a988bb6b25f480c66f1d7978ec1';
        // it calls the API correctly
        $this->assertEquals($expected, (string) $this->getRequest(0)->getUri());
        //it records the same url in the result object
        $this->assertEquals($expected, $result->getUrl());
    }

    public function test_load_event_returns_single_event_from_response()
    {
        $this->mockGuzzleWithResponse(new Response(200, [], '{"title": "Foo Bar", "id":4321}'));
        $subject = $this->newSubjectWithValidCredentials();
        $this->assertEquals($subject->loadEvent('4321')->getEvent(), ['title' => 'Foo Bar', 'id' => 4321]);
    }

    /**
     * @testWith [{"title": "\"Foo Bar\""}, "title=%22Foo+Bar%22&key=test-key&signature=b313169e84c3922f07c6010e2191486a5192c039"]
     *           [{"artist": "Amélie"}, "artist=Am%C3%A9lie&key=test-key&signature=6fdb17738b2fcb841105585fa02c8052075c3d89"]
     *
     * @param array  $query
     * @param string $expected
     */
    public function test_it_correctly_url_encodes_search_query($query, $expected)
    {
        $this->mockGuzzleWithEmptySuccessResponse();
        $subject = $this->newSubjectWithValidCredentials();

        $result = $subject->searchEvents($query);

        $expected = 'https://api.edinburghfestivalcity.com/events?'.$expected;
        //it queries the API with the correct URL
        $this->assertEquals($expected, (string) $this->getRequest(0)->getUri());
        //it records the same url in the result object
        $this->assertEquals($expected, $result->getUrl());
    }

    /**
     * @testWith [{}, 0]
     *           [{"x-total-results": 1021}, 1021]
     *           [{"x-total-results": 0}, 0]
     *
     * @param array $headers
     * @param int   $expected
     */
    public function test_event_search_result_holds_total_result_count_from_header($headers, $expected)
    {
        $this->mockGuzzleWithResponse(new Response(200, $headers, "[]"));
        $subject = $this->newSubjectWithValidCredentials();
        $result  = $subject->searchEvents([]);
        $this->assertEquals($expected, $result->getTotalResults());
    }

    public function test_search_event_returns_events_from_response()
    {
        $this->mockGuzzleWithResponse(
            new Response(
                200,
                [],
                '[{"title": "Test event 1", "id":101},{"title": "Test event 2", "id":102}]'
            )
        );

        $subject = $this->newSubjectWithValidCredentials();
        $this->assertEquals(
            $subject->searchEvents(['title' => 'Test'])->getEvents(),
            [
                ['title' => 'Test event 1', 'id' => 101],
                ['title' => 'Test event 2', 'id' => 102],
            ]
        );
    }

    /**
     * @testWith [200, "<p>HTML</p>", "API responded with invalid JSON"]
     *           [200, "", "API responded with invalid JSON"]
     *           [404, "{\"error\":\"Event not found\"}", "Event not found"]
     *           [404, "{\"msg\":\"No error key\"}", "{\"msg\":\"No error key\"}"]
     *           [403, "Forbidden", "Forbidden"]
     *           [500, "Server Error", "Server Error"]
     *           [501, "<h1>Not Implemented</h1>", "<h1>Not Implemented</h1>"]
     * @param $code
     * @param $body
     * @param $exception_message
     */
    public function test_it_throws_if_api_responds_with_error($code, $body, $exception_message)
    {
        $this->mockGuzzleWithResponse(new Response($code, [], $body));
        $subject = $this->newSubjectWithValidCredentials();

        $this->expectException(FestivalsApiClientException::class);
        $this->expectExceptionMessage($exception_message);
        $subject->loadEvent('1234');
    }

    public function test_client_exception_contains_url_requested()
    {
        $this->mockGuzzleWithResponse(new Response(404, [], '{"error":"Something went wrong"}'));
        $subject = $this->newSubjectWithValidCredentials();
        try {
            $subject->searchEvents([]);
        } catch (FestivalsApiClientException $e) {
            $this->assertEquals('Something went wrong', $e->getMessage());
            $this->assertEquals(
                "https://api.edinburghfestivalcity.com/events?key=test-key&signature=7793ae3038669f76de954f197f1818727a12a037",
                $e->getUrl()
            );
        }
    }

    protected function mockGuzzleWithEmptySuccessResponse()
    {
        $this->mockGuzzleWithResponses([new Response(200, [], "[]")]);
    }

    protected function mockGuzzleWithResponse(Response $response)
    {
        $this->mockGuzzleWithResponses([$response]);
    }

    protected function mockGuzzleWithResponses(array $responses)
    {
        $mock    = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        $history = Middleware::history($this->history);
        $handler->push($history);

        $this->guzzle = new Client(['handler' => $handler]);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->mockGuzzleWithEmptySuccessResponse();
    }

    /**
     * @return FestivalsApiClient
     */
    protected function newSubject()
    {
        return new FestivalsApiClient($this->guzzle);
    }

    /**
     * @return FestivalsApiClient
     */
    protected function newSubjectWithValidCredentials()
    {
        $subject = $this->newSubject();
        $subject->setCredentials('test-key', 'test-secret');

        return $subject;
    }

    /**
     * @param int $id
     *
     * @return GuzzleHttp\Psr7\Request
     */
    protected function getRequest(int $id)
    {
        return $this->history[$id]['request'];
    }
}
