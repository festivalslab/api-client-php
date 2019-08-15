Edinburgh Festivals Listings API client for PHP
===============================================

The Edinburgh Festivals Listings API client for PHP makes it easy for developers to access the [Edinburgh Festivals Listings API](https://api.edinburghfestivalcity.com/) in their PHP code.

You can get started quickly by [installing the client through composer](#installing)

[![Build Status](https://travis-ci.org/festivalslab/api-client-php.svg?branch=1.0.x)](https://travis-ci.org/festivalslab/api-client-php)

## Quick Examples

#### Create a client
```php
// Require the Composer autoloader.
require 'vendor/autoload.php';

use FestivalsApi\FestivalsApiClientFactory;

// Instantiate a Festivals API Client.
$client = FestivalsApiClientFactory::createWithCredentials('key', 'secret');
```

#### Find some events
```php
use FestivalsApi\FestivalsApiClientException;

try {
    $result = $client->searchEvents(['title' => 'Foo']);
    $events = $result->getEvents();
} catch (FestivalsApiClientException $e){
    echo "There was an error: " . $e->getMessage();
}
```

#### Iterate all results
The API delivers results in pages, by default 25 results at a time, configurable up to 100.
Using `FestivalsApi\FestivalsApiEventSearch` will take care for the pagination for you so you can iterate all results for a search query easily.

```php
use FestivalsApi\EventSearchIterator;

$search = new EventSearchIterator($client);
$search->setQuery(['festival' => 'jazz']);
foreach ($search as $event){
    echo $event['title'];
}
```

## Resources
 - [API Docs](https://api.edinburghfestivalcity.com/documentation) - Details about parameters & responses
 - [API Browser](https://api.edinburghfestivalcity.com/browse) - Interactive tool to explore API options and responses

## Installing

The recommended way to install the php client is through Composer.

Install Composer https://getcomposer.org/

Next, run the Composer command to install the latest stable version of the client:
```bash
composer require festivalslab/api-client-php
```

After installing, you need to require Composer's autoloader:
```php
require 'vendor/autoload.php';
```

You can then later update the client using composer:
```bash
composer update
```
