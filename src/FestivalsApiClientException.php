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
    public function __construct(
        string $message,
        int $code = 0,
        protected ?string $url = NULL,
        ?Exception $previous_exception = NULL
    ) {
        parent::__construct($message, $code, $previous_exception);
    }

    public static function invalidJsonResponse(int $code, ?Exception $previous_exception = NULL): FestivalsApiClientException
    {
        return new static('API responded with invalid JSON', $code, NULL, $previous_exception);
    }

    public static function missingCredentials(): FestivalsApiClientException
    {
        return new static('Missing credentials');
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
