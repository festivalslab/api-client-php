<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */


namespace FestivalsApi;


use FestivalsApi\Result\EventSearchResult;
use FestivalsApi\Result\SingleEventResult;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class FestivalsApiClient
{
    const BASE_URL        = 'https://api.edinburghfestivalcity.com';
    const EVENTS_ENDPOINT = '/events';

    protected string $access_key;

    protected string $base_url;

    protected string $secret;

    public function __construct(protected Client $guzzle, string $base_url = self::BASE_URL)
    {
        $this->setBaseUrl($base_url);
    }

    /**
     * Load a single event by ID or throw if not found
     *
     * @throws GuzzleException
     * @throws FestivalsApiClientException if event not found
     */
    public function loadEvent(string $id): SingleEventResult
    {
        $this->throwEmptyCredentials();

        $request  = $this->createRequest(self::EVENTS_ENDPOINT.'/'.$id);
        $response = $this->sendRequest($request);
        $event    = $this->decodeJsonResponse($response);

        return new SingleEventResult($event, (string) $request->getUri());
    }

    /**
     * Search API for events matching query
     *
     * @throws FestivalsApiClientException
     * @throws GuzzleException
     */
    public function searchEvents(array $query): EventSearchResult
    {
        $this->throwEmptyCredentials();

        $url = self::EVENTS_ENDPOINT;
        if ( ! empty($query)) {
            $url .= '?'.http_build_query($query);
        }

        $request       = $this->createRequest($url);
        $response      = $this->sendRequest($request);
        $events        = $this->decodeJsonResponse($response);
        $total_results = (int) $response->getHeaderLine('x-total-results') ?: 0;

        return new EventSearchResult($events, (string) $request->getUri(), $total_results);
    }

    public function setBaseUrl(string $base_url): void
    {
        $this->base_url = rtrim($base_url, '/');
    }

    public function setCredentials(string $access_key, string $secret): void
    {
        $this->access_key = $access_key;
        $this->secret     = $secret;
    }

    protected function createRequest(string $url): Request
    {
        $full_url = $this->base_url.$this->getSignedUrl($url);

        return new Request('GET', $full_url);
    }

    protected function decodeJsonResponse(ResponseInterface $response): array
    {
        try {
            return \GuzzleHttp\json_decode((string) $response->getBody(), TRUE);
        } catch (InvalidArgumentException $e) {
            throw FestivalsApiClientException::invalidJsonResponse($response->getStatusCode(), $e);
        }
    }

    protected function getSignature(string $data): string
    {
        return hash_hmac('sha1', $data, $this->secret);
    }

    /**
     * Calculate signature and append it to the URL
     */
    protected function getSignedUrl(string $url): string
    {
        if (str_contains($url, '?')) {
            $url .= '&key='.$this->access_key;
        } else {
            $url .= '?key='.$this->access_key;
        }

        $url .= '&signature='.$this->getSignature($url);

        return $url;
    }

    /**
     * @throws FestivalsApiClientException
     */
    protected function handleApiError(BadResponseException $e): void
    {
        $msg  = $e->getResponse()->getBody();
        $code = $e->getResponse()->getStatusCode();
        $url  = (string) $e->getRequest()->getUri();

        try {
            $decoded = \GuzzleHttp\json_decode($e->getResponse()->getBody(), TRUE);
            if (isset($decoded['error'])) {
                $msg = $decoded['error'];
            }
        } catch (InvalidArgumentException $e) {

        }

        throw new FestivalsApiClientException($msg, $code, $url, $e);
    }

    /**
     * @throws GuzzleException
     * @throws FestivalsApiClientException
     */
    protected function sendRequest(Request $request): ResponseInterface
    {
        $response = NULL;
        try {
            $response = $this->guzzle->send($request);
        } catch (BadResponseException $e) {
            $this->handleApiError($e);
        }

        return $response;
    }

    /**
     * @throws FestivalsApiClientException if either access_key or secret have not been set
     */
    protected function throwEmptyCredentials(): void
    {
        if (empty($this->access_key) OR empty($this->secret)) {
            throw FestivalsApiClientException::missingCredentials();
        }
    }

}
