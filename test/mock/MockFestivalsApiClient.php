<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */

namespace test\mock\FestivalsApi;

use Exception;
use FestivalsApi\FestivalsApiClient;
use FestivalsApi\Result\EventSearchResult;
use FestivalsApi\Result\SingleEventResult;
use PHPUnit\Framework\Assert;
use function array_pop;
use function array_reverse;
use function count;

class MockFestivalsApiClient extends FestivalsApiClient
{
    /**
     * @var array
     */
    protected $called_with = [];

    /**
     * @var array
     */
    protected $responses = [];

    /**
     * @var int
     */
    protected $total_results;

    public function __construct()
    {
        //do nothing
    }

    /**
     * @param array $array
     * @param int   $total_results
     *
     * @return MockFestivalsApiClient
     */
    public static function willReturn(array $array, int $total_results = 0)
    {
        $me                = new self;
        $me->responses     = array_reverse($array);
        $me->total_results = $total_results;

        return $me;
    }

    /**
     * @param int $expected
     */
    public function assertApiCallsCount(int $expected)
    {
        $actual = count($this->called_with);
        Assert::assertEquals($expected, $actual, "API was queried ".$actual." times, expected ".$expected);
    }

    /**
     * @param $expected
     */
    public function assertCalledWith($expected)
    {
        Assert::assertSame($expected, $this->called_with);
    }

    public function assertZeroCallsMade()
    {
        $this->assertApiCallsCount(0);
    }

    /**
     * @param string $id
     *
     * @return SingleEventResult
     * @throws Exception
     */
    public function loadEvent(string $id): SingleEventResult
    {
        $this->called_with[] = $id;
        throw new Exception("not implemented yet");
    }

    /**
     * @param array $query
     *
     * @return EventSearchResult
     */
    public function searchEvents(array $query): EventSearchResult
    {
        $this->called_with[] = $query;
        $response            = array_pop($this->responses);

        return new EventSearchResult($response, 'WORK IT OUT YOURSELF', $this->total_results);
    }

}
