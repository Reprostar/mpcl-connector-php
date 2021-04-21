# MyPCList API connector library for PHP
Official implementation of MyPCList V1 API connector, compatible with PHP 7.0 and later. This library contains a connector
class used to communicate with an endpoint and classes for all API-accessible MPCL entities.

### Installation
Package can be easily installed via Composer:

```
composer require reprostar/mpcl-connector-php
```

### Authentication
In the legacy version of MyPCList, the API credentials were split into separate key and token. In the future version 
of the API there will be a single-string token for authorization, so usage of separate credentials is now discouraged.

The new format of API_TOKEN consists of key and token, joined by a colon ':', for example:
```YYY:XX**************************XXXX```

This combined token can be used as a first argument for MpclConnector or passed through 'api_token' parameter. 

### Sample usage
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$mpcl = new \Reprostar\MpclConnector\MpclConnector('API_TOKEN');

try{
    $user = $mpcl->getUser();
    print_r($user);
} catch (\Reprostar\MpclConnector\MpclConnectorException $e) {
    echo "Error received: " . $e->getMessage() . "\n";
}
```

### Documentation of calls and models
This library is a wrapper for MyPCList V1 API. For more detailed documentation of the API, please refer to the
official documentation, available at [https://mypclist.net/apidocs](https://mypclist.net/apidocs). For usage of
specific methods, please refer to PHPDoc comments in the source code.