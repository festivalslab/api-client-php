<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */


namespace FestivalsApi;


use FestivalsApi\Result\EventSearchResult;
use GuzzleHttp\Exception\GuzzleException;
use IteratorAggregate;
use LogicException;
use Traversable;
use function get_class;
use function is_array;

class EventSearchIterator implements IteratorAggregate
{
    /**
     * @var FestivalsApiClient
     */
    protected $client;

    /**
     * @var EventSearchResult
     */
    protected $last_result;

    /**
     * @var int
     */
    protected $page_size;

    /**
     * @var array
     */
    protected $query;

    /**
     * @var int
     */
    protected $request_count = 0;

    /**
     * @param FestivalsApiClient $client
     */
    public function __construct(FestivalsApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * @return Traversable
     *
     * @throws FestivalsApiClientException if API client encounters an error
     * @throws GuzzleException if Guzzle encounters an error
     * @throws LogicException if no query set
     */
    public function getIterator(): Traversable
    {
        $this->throwIfNoQuerySet();

        $this->query['size'] = $this->page_size;
        $this->query['from'] = 0;
        do {
            $events = $this->makeApiCall();
            foreach ($events as $event) {
                yield $event;
            }
            $this->query['from'] += $this->page_size;
        } while (count($this->last_result->getEvents()) === $this->page_size);
    }

    /**
     * Total number of calls to API made by this query
     *
     * @return int
     */
    public function getNoOfRequestsMadeByQuery(): int
    {
        return $this->request_count;
    }

    /**
     * Sets the search query
     *
     * @param array $query     the query parameters eg ['festival'=>'jazz', title='Blue']
     * @param int   $page_size the number of events to return per request, defaults to API max limit
     */
    public function setQuery(array $query, int $page_size = 100): void
    {
        $this->query         = $query;
        $this->page_size     = $page_size;
        $this->request_count = 0;
    }

    /**
     * Execute the query and return the events
     *
     * @return array
     *
     * @throws GuzzleException
     * @throws FestivalsApiClientException
     */
    protected function makeApiCall(): array
    {
        $this->request_count++;
        $this->last_result = $this->client->searchEvents($this->query);

        return $this->last_result->getEvents();
    }

    /**
     * @throws LogicException if no query set
     */
    protected function throwIfNoQuerySet(): void
    {
        if ( ! is_array($this->query)) {
            throw new LogicException("You must call ".get_class($this)."::setQuery() before iterating result");
        }
    }

}
