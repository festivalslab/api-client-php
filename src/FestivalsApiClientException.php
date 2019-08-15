<?php
/**
 * @author    Festivals Edinburgh <support@api.edinburghfestivalcity.com>
 * @licence   BSD-3-Clause
 */


namespace FestivalsApi;


use Exception;
use RuntimeException;

class FestivalsApiClientException extends RuntimeException
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @param string    $message
     * @param integer   $code
     * @param string    $url
     * @param Exception $previous_exception
     */
    public function __construct(string $message, int $code = NULL, string $url = NULL, $previous_exception = NULL)
    {
        parent::__construct($message, $code, $previous_exception);
        $this->url = $url;
    }

    /**
     * @param int            $code
     * @param Exception|null $previous_exception
     *
     * @return FestivalsApiClientException
     */
    public static function invalidJsonResponse(int $code, $previous_exception = NULL): FestivalsApiClientException
    {
        return new static('API responded with invalid JSON', $code, NULL, $previous_exception);
    }

    /**
     * @return FestivalsApiClientException
     */
    public static function missingCredentials(): FestivalsApiClientException
    {
        return new static('Missing credentials');
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
