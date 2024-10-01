<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */

namespace test\unit\FestivalsApi;

use FestivalsApi\EventSearchIterator;
use FestivalsApi\MockFestivalsApiClient;
use IteratorAggregate;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function iterator_count;
use function iterator_to_array;

class EventSearchIteratorTest extends TestCase
{
    protected MockFestivalsApiClient $client;

    public function test_it_is_initialisable(): void
    {
        $this->assertInstanceOf(EventSearchIterator::class, $this->newSubject());
    }

    public function test_it_is_an_iterator_aggregate(): void
    {
        $this->assertInstanceOf(IteratorAggregate::class, $this->newSubject());
    }

    public function test_it_throws_if_no_search_query_set(): void
    {
        $subject = $this->newSubject();
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            "You must call FestivalsApi\EventSearchIterator::setQuery() before iterating result"
        );
        iterator_to_array($subject);
    }

    public function test_setting_search_query_makes_no_calls_to_api(): void
    {
        $subject = $this->newSubject();
        $subject->setQuery(['festival' => 'jazz']);
        $this->client->assertZeroCallsMade();
    }

    #[DataProvider('multiple_page_dataprovider')]
    public function test_it_iterates_api_until_no_more_events(
        array $client_responses,
        int   $page_size,
        array $expected
    ): void
    {
        $this->client = MockFestivalsApiClient::willReturn($client_responses);

        $subject = $this->newSubject();
        $subject->setQuery([], $page_size);

        //run iterator
        iterator_to_array($subject);

        $this->client->assertCalledWith($expected);
    }

    public static function multiple_page_dataprovider(): array
    {
        return [
            //no results
            [
                [[]],
                100,
                [['size' => 100, 'from' => 0]],
            ],
            //full page final result
            [
                [['event1', 'event2'], ['event3', 'event4'], []],
                2,
                [['size' => 2, 'from' => 0], ['size' => 2, 'from' => 2], ['size' => 2, 'from' => 4]],
            ],
            //less than full page final result
            [
                [['event1', 'event2', 'event3'], ['event4', 'event5']],
                3,
                [['size' => 3, 'from' => 0], ['size' => 3, 'from' => 3]],
            ],
        ];
    }

    public function test_searches_with_provided_query_overriding_size_or_from(): void
    {
        $this->client = MockFestivalsApiClient::willReturn([['A', 'B',], ['C', 'D'], []]);
        $subject      = $this->newSubject();
        $subject->setQuery(['title' => 'Anything', 'size' => 20, 'from' => 123], 2);

        //run iterator
        iterator_to_array($subject);

        $this->client->assertCalledWith(
            [
                ['title' => 'Anything', 'size' => 2, 'from' => 0],
                ['title' => 'Anything', 'size' => 2, 'from' => 2],
                ['title' => 'Anything', 'size' => 2, 'from' => 4],
            ]
        );
    }

    #[DataProvider('result_order_dataprovider')]
    public function test_it_returns_all_results_in_correct_order(
        array $client_responses,
        int   $page_size,
        array $expected
    ): void
    {
        $this->client = MockFestivalsApiClient::willReturn($client_responses);
        $subject      = $this->newSubject();
        $subject->setQuery([], $page_size);
        $this->assertEquals($expected, iterator_to_array($subject));
    }

    public static function result_order_dataprovider(): array
    {
        return [
            //no results
            [
                [[]],
                2,
                [],
            ],
            //full page final result
            [
                [['event1', 'event2', 'event3'], ['event4', 'event5', 'event6'], []],
                3,
                ['event1', 'event2', 'event3', 'event4', 'event5', 'event6'],
            ],
            //less than full page final result
            [
                [['event1', 'event2', 'event3'], ['event4', 'event5']],
                3,
                ['event1', 'event2', 'event3', 'event4', 'event5'],
            ],
        ];
    }

    public function test_it_returns_number_of_calls_to_api_made_per_search_query(): void
    {
        $this->client = MockFestivalsApiClient::willReturn([['1', '2',], ['3']]);
        $subject      = $this->newSubject();
        $subject->setQuery([], 2);

        $this->assertEquals(
            0,
            $subject->getNoOfRequestsMadeByQuery(),
            'No requests should have been made setting query'
        );

        //iterate all results
        iterator_count($subject);

        $this->assertEquals(
            2,
            $subject->getNoOfRequestsMadeByQuery(),
            '2 requests should have been made to iterate full result set'
        );
    }

    protected function newSubject(): EventSearchIterator
    {
        return new EventSearchIterator($this->client);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new MockFestivalsApiClient();
    }

}
