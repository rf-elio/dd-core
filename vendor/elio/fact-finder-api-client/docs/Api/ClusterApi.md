# Swagger\Client\ClusterApi

All URIs are relative to *https://ng-demo.fact-finder.de/fact-finder*

Method | HTTP request | Description
------------- | ------------- | -------------
[**fullSyncUsingPOST**](ClusterApi.md#fullSyncUsingPOST) | **POST** /rest/v4/cluster/database/sync/full | Fully synchronize the worldmatch database of this node.
[**getDatabaseStateUsingGET**](ClusterApi.md#getDatabaseStateUsingGET) | **GET** /rest/v4/cluster/{channel}/database/state | Show the current state of the worldmatch database.
[**getImportStateUsingGET**](ClusterApi.md#getImportStateUsingGET) | **GET** /rest/v4/cluster/{channel}/import/state | Show the import state of this node.
[**pruneUsingPOST**](ClusterApi.md#pruneUsingPOST) | **POST** /rest/v4/cluster/{channel}/database/prune | Prune delta updates.
[**syncDatabaseUsingPOST**](ClusterApi.md#syncDatabaseUsingPOST) | **POST** /rest/v4/cluster/{channel}/database/sync | Synchronize the worldmatch database of this node.


# **fullSyncUsingPOST**
> fullSyncUsingPOST()

Fully synchronize the worldmatch database of this node.

Applies missing delta updates to the worldmatch database if this node is on the same databaseVersion as the director. Otherwise it reloads the worldmatch database from postgres.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure HTTP basic authorization: basicAuth
$config = Swagger\Client\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');

// Configure OAuth2 access token for authorization: oAuth2
$config = Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$apiInstance = new Swagger\Client\Api\ClusterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $apiInstance->fullSyncUsingPOST();
} catch (Exception $e) {
    echo 'Exception when calling ClusterApi->fullSyncUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters
This endpoint does not need any parameter.

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getDatabaseStateUsingGET**
> \Swagger\Client\Model\DatabaseState getDatabaseStateUsingGET($channel)

Show the current state of the worldmatch database.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure HTTP basic authorization: basicAuth
$config = Swagger\Client\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');

// Configure OAuth2 access token for authorization: oAuth2
$config = Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$apiInstance = new Swagger\Client\Api\ClusterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel

try {
    $result = $apiInstance->getDatabaseStateUsingGET($channel);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ClusterApi->getDatabaseStateUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |

### Return type

[**\Swagger\Client\Model\DatabaseState**](../Model/DatabaseState.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getImportStateUsingGET**
> \Swagger\Client\Model\ImportState getImportStateUsingGET($channel)

Show the import state of this node.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure HTTP basic authorization: basicAuth
$config = Swagger\Client\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');

// Configure OAuth2 access token for authorization: oAuth2
$config = Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$apiInstance = new Swagger\Client\Api\ClusterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel

try {
    $result = $apiInstance->getImportStateUsingGET($channel);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ClusterApi->getImportStateUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |

### Return type

[**\Swagger\Client\Model\ImportState**](../Model/ImportState.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **pruneUsingPOST**
> string pruneUsingPOST($channel)

Prune delta updates.

Deletes the delta updates table and increments the databaseVersion. After prune every worker has to reload the worldmatch database once to get in sync with the director. In contrast to a full re-import this does not discard any delta updates which are already applied to the intermediate database.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure HTTP basic authorization: basicAuth
$config = Swagger\Client\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');

// Configure OAuth2 access token for authorization: oAuth2
$config = Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$apiInstance = new Swagger\Client\Api\ClusterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel

try {
    $result = $apiInstance->pruneUsingPOST($channel);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ClusterApi->pruneUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |

### Return type

**string**

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **syncDatabaseUsingPOST**
> \Swagger\Client\Model\DeltaUpdateResult[] syncDatabaseUsingPOST($channel, $verbose)

Synchronize the worldmatch database of this node.

Applies missing delta updates to the worldmatch database if this node is on the same databaseVersion as the director. Otherwise a reload of the worldmatch database is necessary to synchronize this node with the director.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure HTTP basic authorization: basicAuth
$config = Swagger\Client\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');

// Configure OAuth2 access token for authorization: oAuth2
$config = Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$apiInstance = new Swagger\Client\Api\ClusterApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$verbose = false; // bool | verbose

try {
    $result = $apiInstance->syncDatabaseUsingPOST($channel, $verbose);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ClusterApi->syncDatabaseUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **verbose** | **bool**| verbose | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\DeltaUpdateResult[]**](../Model/DeltaUpdateResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

