# Swagger\Client\RecordsApi

All URIs are relative to *https://ng-demo.fact-finder.de/fact-finder*

Method | HTTP request | Description
------------- | ------------- | -------------
[**compareUsingGET**](RecordsApi.md#compareUsingGET) | **GET** /rest/v4/records/{channel}/compare | Compare products
[**deleteCustomerPricesUsingDELETE**](RecordsApi.md#deleteCustomerPricesUsingDELETE) | **DELETE** /rest/v4/records/{channel}/customerprices | Delete customer prices
[**deleteGeoUsingDELETE**](RecordsApi.md#deleteGeoUsingDELETE) | **DELETE** /rest/v4/records/{channel}/geo | Delete geo data
[**deleteMarketUsingDELETE**](RecordsApi.md#deleteMarketUsingDELETE) | **DELETE** /rest/v4/markets/{channel} | Delete geo market
[**deleteUsingDELETE**](RecordsApi.md#deleteUsingDELETE) | **DELETE** /rest/v4/records/{channel} | Delete records
[**getCustomerPricesUsingGET**](RecordsApi.md#getCustomerPricesUsingGET) | **GET** /rest/v4/records/{channel}/customerprices | Get customer specific prices
[**getDetailPageUsingGET**](RecordsApi.md#getDetailPageUsingGET) | **GET** /rest/v4/detail/{channel}/{id} | Get the detail page
[**getFullRecordsUsingGET**](RecordsApi.md#getFullRecordsUsingGET) | **GET** /rest/v4/records/{channel}/full | Get full records
[**getGeoUsingGET**](RecordsApi.md#getGeoUsingGET) | **GET** /rest/v4/records/{channel}/geo | Get geo data
[**getMarketsUsingGET**](RecordsApi.md#getMarketsUsingGET) | **GET** /rest/v4/markets/{channel} | Get markets with location
[**getRecommendationUsingGET**](RecordsApi.md#getRecommendationUsingGET) | **GET** /rest/v4/records/{channel}/recommendation | Get recommendations
[**getRecordMetaGetUsingGET**](RecordsApi.md#getRecordMetaGetUsingGET) | **GET** /rest/v4/records/{channel}/meta | Retrieve record metadata
[**getRecordMetaPostUsingPOST**](RecordsApi.md#getRecordMetaPostUsingPOST) | **POST** /rest/v4/records/{channel}/meta | Retrieve record metadata
[**getRecordsUsingGET**](RecordsApi.md#getRecordsUsingGET) | **GET** /rest/v4/records/{channel} | Get records
[**getSimilarProductsUsingGET**](RecordsApi.md#getSimilarProductsUsingGET) | **GET** /rest/v4/records/{channel}/similar | Get similar products
[**insertRecordsUsingPOST**](RecordsApi.md#insertRecordsUsingPOST) | **POST** /rest/v4/records/{channel} | Insert records
[**updateCustomerPricesUsingPOST**](RecordsApi.md#updateCustomerPricesUsingPOST) | **POST** /rest/v4/records/{channel}/customerprices | Update customer prices
[**updateGeoUsingPOST**](RecordsApi.md#updateGeoUsingPOST) | **POST** /rest/v4/records/{channel}/geo | Update geo data
[**updateMarketsUsingPOST**](RecordsApi.md#updateMarketsUsingPOST) | **POST** /rest/v4/markets/{channel} | Update markets
[**updateUsingPUT**](RecordsApi.md#updateUsingPUT) | **PUT** /rest/v4/records/{channel} | Update records
[**upsertRecordsUsingPUT**](RecordsApi.md#upsertRecordsUsingPUT) | **PUT** /rest/v4/records/{channel}/upsert | Upsert records


# **compareUsingGET**
> \Swagger\Client\Model\CompareResult compareUsingGET($channel, $ids_only, $id, $purchaser_id)

Compare products

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$ids_only = false; // bool | If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance.
$id = array("id_example"); // string[] | Use this parameter to pass product ID(s) which should be compared.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.

try {
    $result = $apiInstance->compareUsingGET($channel, $ids_only, $id, $purchaser_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->compareUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **ids_only** | **bool**| If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance. | [optional] [default to false]
 **id** | [**string[]**](../Model/string.md)| Use this parameter to pass product ID(s) which should be compared. | [optional]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]

### Return type

[**\Swagger\Client\Model\CompareResult**](../Model/CompareResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **deleteCustomerPricesUsingDELETE**
> \Swagger\Client\Model\DeleteResult[] deleteCustomerPricesUsingDELETE($channel, $id, $id_type, $delete_request, $verbose)

Delete customer prices

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$id = array("id_example"); // string[] | The ids from the records which should be deleted.
$id_type = "productNumber"; // string | Specifies which type of id is given.
$delete_request = new \Swagger\Client\Model\DeleteRequest(); // \Swagger\Client\Model\DeleteRequest | deleteRequest
$verbose = false; // bool | verbose

try {
    $result = $apiInstance->deleteCustomerPricesUsingDELETE($channel, $id, $id_type, $delete_request, $verbose);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->deleteCustomerPricesUsingDELETE: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **id** | [**string[]**](../Model/string.md)| The ids from the records which should be deleted. | [optional]
 **id_type** | **string**| Specifies which type of id is given. | [optional] [default to productNumber]
 **delete_request** | [**\Swagger\Client\Model\DeleteRequest**](../Model/DeleteRequest.md)| deleteRequest | [optional]
 **verbose** | **bool**| verbose | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\DeleteResult[]**](../Model/DeleteResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **deleteGeoUsingDELETE**
> \Swagger\Client\Model\DeleteResult[] deleteGeoUsingDELETE($channel, $id, $delete_request, $verbose)

Delete geo data

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$id = array("id_example"); // string[] | The ids from the records which should be deleted.
$delete_request = new \Swagger\Client\Model\DeleteRequest(); // \Swagger\Client\Model\DeleteRequest | deleteRequest
$verbose = false; // bool | verbose

try {
    $result = $apiInstance->deleteGeoUsingDELETE($channel, $id, $delete_request, $verbose);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->deleteGeoUsingDELETE: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **id** | [**string[]**](../Model/string.md)| The ids from the records which should be deleted. | [optional]
 **delete_request** | [**\Swagger\Client\Model\DeleteRequest**](../Model/DeleteRequest.md)| deleteRequest | [optional]
 **verbose** | **bool**| verbose | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\DeleteResult[]**](../Model/DeleteResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **deleteMarketUsingDELETE**
> \Swagger\Client\Model\DeleteResult[] deleteMarketUsingDELETE($channel, $market_id, $delete_market_request, $verbose)

Delete geo market

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$market_id = array("market_id_example"); // string[] | The ids of the markets which should be deleted.
$delete_market_request = new \Swagger\Client\Model\DeleteMarketRequest(); // \Swagger\Client\Model\DeleteMarketRequest | deleteMarketRequest
$verbose = false; // bool | verbose

try {
    $result = $apiInstance->deleteMarketUsingDELETE($channel, $market_id, $delete_market_request, $verbose);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->deleteMarketUsingDELETE: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **market_id** | [**string[]**](../Model/string.md)| The ids of the markets which should be deleted. | [optional]
 **delete_market_request** | [**\Swagger\Client\Model\DeleteMarketRequest**](../Model/DeleteMarketRequest.md)| deleteMarketRequest | [optional]
 **verbose** | **bool**| verbose | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\DeleteResult[]**](../Model/DeleteResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **deleteUsingDELETE**
> \Swagger\Client\Model\DeleteResult[] deleteUsingDELETE($channel, $id, $delete_request, $id_type, $verbose)

Delete records

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$id = array("id_example"); // string[] | The ids from the records which should be deleted.
$delete_request = new \Swagger\Client\Model\DeleteRequest(); // \Swagger\Client\Model\DeleteRequest | deleteRequest
$id_type = "productNumber"; // string | Specifies the type of id given.
$verbose = false; // bool | verbose

try {
    $result = $apiInstance->deleteUsingDELETE($channel, $id, $delete_request, $id_type, $verbose);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->deleteUsingDELETE: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **id** | [**string[]**](../Model/string.md)| The ids from the records which should be deleted. | [optional]
 **delete_request** | [**\Swagger\Client\Model\DeleteRequest**](../Model/DeleteRequest.md)| deleteRequest | [optional]
 **id_type** | **string**| Specifies the type of id given. | [optional] [default to productNumber]
 **verbose** | **bool**| verbose | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\DeleteResult[]**](../Model/DeleteResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCustomerPricesUsingGET**
> \Swagger\Client\Model\CustomerPriceRecord[] getCustomerPricesUsingGET($channel, $id, $id_type)

Get customer specific prices

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$id = array("id_example"); // string[] | The product numbers of the records for which customer prices should be returned.
$id_type = "productNumber"; // string | Specifies which type of id is given.

try {
    $result = $apiInstance->getCustomerPricesUsingGET($channel, $id, $id_type);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->getCustomerPricesUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **id** | [**string[]**](../Model/string.md)| The product numbers of the records for which customer prices should be returned. |
 **id_type** | **string**| Specifies which type of id is given. | [optional] [default to productNumber]

### Return type

[**\Swagger\Client\Model\CustomerPriceRecord[]**](../Model/CustomerPriceRecord.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getDetailPageUsingGET**
> \Swagger\Client\Model\DetailPage getDetailPageUsingGET($channel, $id, $id_type, $ids_only, $max_results_recommendations, $max_results_similar_products, $use_personalization, $sid, $purchaser_id, $latitude, $longitude, $market_ids, $max_count_variants, $with_campaigns, $with_recommendations, $with_similar_products, $with_record)

Get the detail page

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$id = "id_example"; // string | The id for which the detailpage should be returned.
$id_type = "id"; // string | Specifies which type of id is given.
$ids_only = false; // bool | If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance.
$max_results_recommendations = 0; // int | Use this parameter to specify the number of recommendations you would like. The default value from the configuration is used if the parameter is not specified.
$max_results_similar_products = 10; // int | Use this parameter to specify the number of similar articles you would like. The default value from the configuration is used if the parameter is not specified.
$use_personalization = true; // bool | Allows the activation/deactivation of the personalization of queries. true = the search result will be personalized if the personalization module is activated and all other requirements are met; false = the search result will not be personalized.
$sid = "sid_example"; // string | This parameter is used to pass an id for the user session. This is important for recognising the user, if you want to trigger personalised campaigns, as well as for FACT-Finder tracking.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$latitude = 1.2; // double | The latitude coordinate of the current location.
$longitude = 1.2; // double | The longitude coordinate of the current location.
$market_ids = array("market_ids_example"); // string[] | Currently selected markets
$max_count_variants = 5; // int | The maximum number of variants to return for every record
$with_campaigns = true; // bool | withCampaigns
$with_recommendations = true; // bool | withRecommendations
$with_similar_products = true; // bool | withSimilarProducts
$with_record = true; // bool | withRecord

try {
    $result = $apiInstance->getDetailPageUsingGET($channel, $id, $id_type, $ids_only, $max_results_recommendations, $max_results_similar_products, $use_personalization, $sid, $purchaser_id, $latitude, $longitude, $market_ids, $max_count_variants, $with_campaigns, $with_recommendations, $with_similar_products, $with_record);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->getDetailPageUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **id** | **string**| The id for which the detailpage should be returned. |
 **id_type** | **string**| Specifies which type of id is given. | [optional] [default to id]
 **ids_only** | **bool**| If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance. | [optional] [default to false]
 **max_results_recommendations** | **int**| Use this parameter to specify the number of recommendations you would like. The default value from the configuration is used if the parameter is not specified. | [optional] [default to 0]
 **max_results_similar_products** | **int**| Use this parameter to specify the number of similar articles you would like. The default value from the configuration is used if the parameter is not specified. | [optional] [default to 10]
 **use_personalization** | **bool**| Allows the activation/deactivation of the personalization of queries. true &#x3D; the search result will be personalized if the personalization module is activated and all other requirements are met; false &#x3D; the search result will not be personalized. | [optional] [default to true]
 **sid** | **string**| This parameter is used to pass an id for the user session. This is important for recognising the user, if you want to trigger personalised campaigns, as well as for FACT-Finder tracking. | [optional]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **latitude** | **double**| The latitude coordinate of the current location. | [optional]
 **longitude** | **double**| The longitude coordinate of the current location. | [optional]
 **market_ids** | [**string[]**](../Model/string.md)| Currently selected markets | [optional]
 **max_count_variants** | **int**| The maximum number of variants to return for every record | [optional] [default to 5]
 **with_campaigns** | **bool**| withCampaigns | [optional] [default to true]
 **with_recommendations** | **bool**| withRecommendations | [optional] [default to true]
 **with_similar_products** | **bool**| withSimilarProducts | [optional] [default to true]
 **with_record** | **bool**| withRecord | [optional] [default to true]

### Return type

[**\Swagger\Client\Model\DetailPage**](../Model/DetailPage.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getFullRecordsUsingGET**
> \Swagger\Client\Model\FullRecordsResult getFullRecordsUsingGET($channel, $id, $purchaser_id, $id_type, $max_count_variants)

Get full records

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$id = array("id_example"); // string[] | The record ids of the records which should be returned
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$id_type = "id"; // string | Specifies which type of id is given.
$max_count_variants = 5; // int | The maximum number of variants to return for every record

try {
    $result = $apiInstance->getFullRecordsUsingGET($channel, $id, $purchaser_id, $id_type, $max_count_variants);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->getFullRecordsUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **id** | [**string[]**](../Model/string.md)| The record ids of the records which should be returned |
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **id_type** | **string**| Specifies which type of id is given. | [optional] [default to id]
 **max_count_variants** | **int**| The maximum number of variants to return for every record | [optional] [default to 5]

### Return type

[**\Swagger\Client\Model\FullRecordsResult**](../Model/FullRecordsResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getGeoUsingGET**
> \Swagger\Client\Model\GeoRecord[] getGeoUsingGET($channel, $id)

Get geo data

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$id = array("id_example"); // string[] | The product numbers of the records for which geo data should be returned.

try {
    $result = $apiInstance->getGeoUsingGET($channel, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->getGeoUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **id** | [**string[]**](../Model/string.md)| The product numbers of the records for which geo data should be returned. |

### Return type

[**\Swagger\Client\Model\GeoRecord[]**](../Model/GeoRecord.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getMarketsUsingGET**
> \Swagger\Client\Model\MarketRecord[] getMarketsUsingGET($channel, $market_id)

Get markets with location

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$market_id = array("market_id_example"); // string[] | The market ids.

try {
    $result = $apiInstance->getMarketsUsingGET($channel, $market_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->getMarketsUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **market_id** | [**string[]**](../Model/string.md)| The market ids. | [optional]

### Return type

[**\Swagger\Client\Model\MarketRecord[]**](../Model/MarketRecord.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getRecommendationUsingGET**
> \Swagger\Client\Model\RecommendationResultWithFieldRoles getRecommendationUsingGET($channel, $id, $max_results, $sid, $filter, $ids_only, $use_personalization, $purchaser_id)

Get recommendations

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$id = array("id_example"); // string[] | Use this parameter to pass product ID(s) for which you wish to obtain recommendations.
$max_results = 0; // int | Use this parameter to specify the number of recommendations you would like. The default value from the configuration is used if the parameter is not specified.
$sid = "sid_example"; // string | This parameter is used to pass an id for the user session. This is important for recognising the user, if you want to give him personalised recommendations, as well as for FACT-Finder tracking.
$filter = array("filter_example"); // string[] | Filters for field values of recommended records.
$ids_only = false; // bool | If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance.
$use_personalization = true; // bool | Allows the activation/deactivation of the personalization of queries. true = the search result will be personalized if the personalization module is activated and all other requirements are met; false = the search result will not be personalized.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.

try {
    $result = $apiInstance->getRecommendationUsingGET($channel, $id, $max_results, $sid, $filter, $ids_only, $use_personalization, $purchaser_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->getRecommendationUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **id** | [**string[]**](../Model/string.md)| Use this parameter to pass product ID(s) for which you wish to obtain recommendations. |
 **max_results** | **int**| Use this parameter to specify the number of recommendations you would like. The default value from the configuration is used if the parameter is not specified. | [optional] [default to 0]
 **sid** | **string**| This parameter is used to pass an id for the user session. This is important for recognising the user, if you want to give him personalised recommendations, as well as for FACT-Finder tracking. | [optional]
 **filter** | [**string[]**](../Model/string.md)| Filters for field values of recommended records. | [optional]
 **ids_only** | **bool**| If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance. | [optional] [default to false]
 **use_personalization** | **bool**| Allows the activation/deactivation of the personalization of queries. true &#x3D; the search result will be personalized if the personalization module is activated and all other requirements are met; false &#x3D; the search result will not be personalized. | [optional] [default to true]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]

### Return type

[**\Swagger\Client\Model\RecommendationResultWithFieldRoles**](../Model/RecommendationResultWithFieldRoles.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getRecordMetaGetUsingGET**
> \Swagger\Client\Model\RecordMetaResult getRecordMetaGetUsingGET($channel, $id, $purchaser_id, $id_type)

Retrieve record metadata

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$id = array("id_example"); // string[] | The record ids of the records which should be returned
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$id_type = "id"; // string | Specifies which type of id is given.

try {
    $result = $apiInstance->getRecordMetaGetUsingGET($channel, $id, $purchaser_id, $id_type);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->getRecordMetaGetUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **id** | [**string[]**](../Model/string.md)| The record ids of the records which should be returned |
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **id_type** | **string**| Specifies which type of id is given. | [optional] [default to id]

### Return type

[**\Swagger\Client\Model\RecordMetaResult**](../Model/RecordMetaResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getRecordMetaPostUsingPOST**
> \Swagger\Client\Model\RecordMetaResult getRecordMetaPostUsingPOST($channel, $meta_request, $id_type, $purchaser_id)

Retrieve record metadata

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$meta_request = new \Swagger\Client\Model\MetaRequest(); // \Swagger\Client\Model\MetaRequest | metaRequest
$id_type = "id"; // string | Specifies which type of id is given.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.

try {
    $result = $apiInstance->getRecordMetaPostUsingPOST($channel, $meta_request, $id_type, $purchaser_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->getRecordMetaPostUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **meta_request** | [**\Swagger\Client\Model\MetaRequest**](../Model/MetaRequest.md)| metaRequest |
 **id_type** | **string**| Specifies which type of id is given. | [optional] [default to id]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]

### Return type

[**\Swagger\Client\Model\RecordMetaResult**](../Model/RecordMetaResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getRecordsUsingGET**
> \Swagger\Client\Model\FlatRecordsResult getRecordsUsingGET($channel, $product_number, $purchaser_id, $verbose)

Get records

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$product_number = array("product_number_example"); // string[] | The product numbers of the records which should be returned.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$verbose = false; // bool | verbose

try {
    $result = $apiInstance->getRecordsUsingGET($channel, $product_number, $purchaser_id, $verbose);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->getRecordsUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **product_number** | [**string[]**](../Model/string.md)| The product numbers of the records which should be returned. |
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **verbose** | **bool**| verbose | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\FlatRecordsResult**](../Model/FlatRecordsResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getSimilarProductsUsingGET**
> \Swagger\Client\Model\SimilarProductsWithFieldRoles getSimilarProductsUsingGET($channel, $id, $id_type, $filter, $ids_only, $purchaser_id, $max_results)

Get similar products

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$id = "id_example"; // string | Use this parameter to pass the product ID for which you wish to obtain similar products.
$id_type = "id_type_example"; // string | Use this parameter to determine the type of id passed to the method.
$filter = array("filter_example"); // string[] | Filters for field values of recommended records.
$ids_only = false; // bool | If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$max_results = 10; // int | Use this parameter to specify the number of similar articles you would like. The default value from the configuration is used if the parameter is not specified.

try {
    $result = $apiInstance->getSimilarProductsUsingGET($channel, $id, $id_type, $filter, $ids_only, $purchaser_id, $max_results);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->getSimilarProductsUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **id** | **string**| Use this parameter to pass the product ID for which you wish to obtain similar products. |
 **id_type** | **string**| Use this parameter to determine the type of id passed to the method. |
 **filter** | [**string[]**](../Model/string.md)| Filters for field values of recommended records. | [optional]
 **ids_only** | **bool**| If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance. | [optional] [default to false]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **max_results** | **int**| Use this parameter to specify the number of similar articles you would like. The default value from the configuration is used if the parameter is not specified. | [optional] [default to 10]

### Return type

[**\Swagger\Client\Model\SimilarProductsWithFieldRoles**](../Model/SimilarProductsWithFieldRoles.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **insertRecordsUsingPOST**
> \Swagger\Client\Model\DeltaUpdateResult[] insertRecordsUsingPOST($channel, $records, $verbose)

Insert records

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$records = array(new \Swagger\Client\Model\FlatRecord()); // \Swagger\Client\Model\FlatRecord[] | The records which should be inserted
$verbose = false; // bool | verbose

try {
    $result = $apiInstance->insertRecordsUsingPOST($channel, $records, $verbose);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->insertRecordsUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **records** | [**\Swagger\Client\Model\FlatRecord[]**](../Model/FlatRecord.md)| The records which should be inserted |
 **verbose** | **bool**| verbose | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\DeltaUpdateResult[]**](../Model/DeltaUpdateResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **updateCustomerPricesUsingPOST**
> \Swagger\Client\Model\CustomerPriceUpdateResult[] updateCustomerPricesUsingPOST($channel, $records, $verbose)

Update customer prices

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$records = array(new \Swagger\Client\Model\CustomerPriceRecord()); // \Swagger\Client\Model\CustomerPriceRecord[] | The records which should be inserted
$verbose = false; // bool | verbose

try {
    $result = $apiInstance->updateCustomerPricesUsingPOST($channel, $records, $verbose);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->updateCustomerPricesUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **records** | [**\Swagger\Client\Model\CustomerPriceRecord[]**](../Model/CustomerPriceRecord.md)| The records which should be inserted |
 **verbose** | **bool**| verbose | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\CustomerPriceUpdateResult[]**](../Model/CustomerPriceUpdateResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **updateGeoUsingPOST**
> \Swagger\Client\Model\GeoUpdateResult[] updateGeoUsingPOST($channel, $records, $verbose)

Update geo data

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$records = array(new \Swagger\Client\Model\GeoRecord()); // \Swagger\Client\Model\GeoRecord[] | The records which should be updated
$verbose = false; // bool | verbose

try {
    $result = $apiInstance->updateGeoUsingPOST($channel, $records, $verbose);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->updateGeoUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **records** | [**\Swagger\Client\Model\GeoRecord[]**](../Model/GeoRecord.md)| The records which should be updated |
 **verbose** | **bool**| verbose | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\GeoUpdateResult[]**](../Model/GeoUpdateResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **updateMarketsUsingPOST**
> \Swagger\Client\Model\MarketUpdateResult[] updateMarketsUsingPOST($channel, $markets, $verbose)

Update markets

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$markets = array(new \Swagger\Client\Model\MarketRecord()); // \Swagger\Client\Model\MarketRecord[] | The markets which should be updated
$verbose = false; // bool | verbose

try {
    $result = $apiInstance->updateMarketsUsingPOST($channel, $markets, $verbose);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->updateMarketsUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **markets** | [**\Swagger\Client\Model\MarketRecord[]**](../Model/MarketRecord.md)| The markets which should be updated |
 **verbose** | **bool**| verbose | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\MarketUpdateResult[]**](../Model/MarketUpdateResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **updateUsingPUT**
> \Swagger\Client\Model\DeltaUpdateResult[] updateUsingPUT($channel, $records, $verbose)

Update records

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$records = array(new \Swagger\Client\Model\FlatRecord()); // \Swagger\Client\Model\FlatRecord[] | The records which should be updated
$verbose = false; // bool | verbose

try {
    $result = $apiInstance->updateUsingPUT($channel, $records, $verbose);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->updateUsingPUT: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **records** | [**\Swagger\Client\Model\FlatRecord[]**](../Model/FlatRecord.md)| The records which should be updated |
 **verbose** | **bool**| verbose | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\DeltaUpdateResult[]**](../Model/DeltaUpdateResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **upsertRecordsUsingPUT**
> \Swagger\Client\Model\DeltaUpdateResult[] upsertRecordsUsingPUT($channel, $records, $verbose)

Upsert records

Update or insert records; Without variants: inserts records when they do not already exist, or replaces them if they do. With Variants: inserts a master document when it does not exists; inserts a variant and replaces the master values if the variant does not exist; replaces a variant and replaces the master values if the variant does already exist.

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

$apiInstance = new Swagger\Client\Api\RecordsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$records = array(new \Swagger\Client\Model\FlatRecord()); // \Swagger\Client\Model\FlatRecord[] | The records which should be upserted
$verbose = false; // bool | verbose

try {
    $result = $apiInstance->upsertRecordsUsingPUT($channel, $records, $verbose);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecordsApi->upsertRecordsUsingPUT: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **records** | [**\Swagger\Client\Model\FlatRecord[]**](../Model/FlatRecord.md)| The records which should be upserted |
 **verbose** | **bool**| verbose | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\DeltaUpdateResult[]**](../Model/DeltaUpdateResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

