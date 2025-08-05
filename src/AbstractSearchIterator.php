<?php

namespace FestivalsApi;

use GuzzleHttp\Exception\GuzzleException;
use IteratorAggregate;
use LogicException;
use Traversable;
use function get_class;
use function is_array;

abstract class AbstractSearchIterator implements IteratorAggregate
{
    protected int $last_result_count;

    protected ?int $page_size = NULL;

    protected ?array $query = NULL;

    protected int $request_count = 0;

    public function __construct(
        protected FestivalsApiClient $client
    ) {
    }

    /**
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
            $results = $this->makeApiCall();
            ++$this->request_count;

            $this->last_result_count = count($results);

            foreach ($results as $result) {
                yield $result;
            }
            $this->query['from'] += $this->page_size;
        } while ($this->last_result_count === $this->page_size);
    }

    /**
     * Total number of calls to API made by this query.
     */
    public function getNoOfRequestsMadeByQuery(): int
    {
        return $this->request_count;
    }

    /**
     * Sets the search query.
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
     * Execute the query and return the events.
     *
     * @throws GuzzleException
     * @throws FestivalsApiClientException
     */
    protected abstract function makeApiCall(): array;

    /**
     * @throws LogicException if no query set
     */
    protected function throwIfNoQuerySet(): void
    {
        if ( ! is_array($this->query)) {
            throw new LogicException('You must call '.get_class($this).'::setQuery() before iterating result');
        }
    }
}
