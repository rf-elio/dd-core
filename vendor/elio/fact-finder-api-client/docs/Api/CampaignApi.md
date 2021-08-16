# Swagger\Client\CampaignApi

All URIs are relative to *https://ng-demo.fact-finder.de/fact-finder*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getPageCampaignsUsingGET**](CampaignApi.md#getPageCampaignsUsingGET) | **GET** /rest/v4/campaign/{channel}/page | Get page campaigns
[**getProductCampaignsUsingGET**](CampaignApi.md#getProductCampaignsUsingGET) | **GET** /rest/v4/campaign/{channel}/product | Get product campaigns
[**getShoppingCartCampaignsUsingGET**](CampaignApi.md#getShoppingCartCampaignsUsingGET) | **GET** /rest/v4/campaign/{channel}/shoppingcart | Get shopping cart campaigns


# **getPageCampaignsUsingGET**
> \Swagger\Client\Model\Campaign[] getPageCampaignsUsingGET($channel, $page_id, $ids_only, $purchaser_id, $sid, $latitude, $longitude, $market_ids)

Get page campaigns

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

$apiInstance = new Swagger\Client\Api\CampaignApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$page_id = "page_id_example"; // string | Use this parameter to pass a page ID for which you wish to obtain campaigns.
$ids_only = false; // bool | If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$sid = "sid_example"; // string | This parameter is used to pass an id for the user session. This is important for recognising the user, if you want to trigger personalised campaigns, as well as for FACT-Finder tracking.
$latitude = 1.2; // double | The latitude coordinate of the current location.
$longitude = 1.2; // double | The longitude coordinate of the current location.
$market_ids = array("market_ids_example"); // string[] | Currently selected markets

try {
    $result = $apiInstance->getPageCampaignsUsingGET($channel, $page_id, $ids_only, $purchaser_id, $sid, $latitude, $longitude, $market_ids);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CampaignApi->getPageCampaignsUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **page_id** | **string**| Use this parameter to pass a page ID for which you wish to obtain campaigns. |
 **ids_only** | **bool**| If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance. | [optional] [default to false]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **sid** | **string**| This parameter is used to pass an id for the user session. This is important for recognising the user, if you want to trigger personalised campaigns, as well as for FACT-Finder tracking. | [optional]
 **latitude** | **double**| The latitude coordinate of the current location. | [optional]
 **longitude** | **double**| The longitude coordinate of the current location. | [optional]
 **market_ids** | [**string[]**](../Model/string.md)| Currently selected markets | [optional]

### Return type

[**\Swagger\Client\Model\Campaign[]**](../Model/Campaign.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getProductCampaignsUsingGET**
> \Swagger\Client\Model\Campaign[] getProductCampaignsUsingGET($channel, $id, $ids_only, $id_type, $purchaser_id, $sid, $latitude, $longitude, $market_ids)

Get product campaigns

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

$apiInstance = new Swagger\Client\Api\CampaignApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$id = "id_example"; // string | Use this parameter to pass a ID (master or product) for which you wish to obtain campaigns.
$ids_only = false; // bool | If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance.
$id_type = "productNumber"; // string | Specifies which type of id is given.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$sid = "sid_example"; // string | This parameter is used to pass an id for the user session. This is important for recognising the user, if you want to trigger personalised campaigns, as well as for FACT-Finder tracking.
$latitude = 1.2; // double | The latitude coordinate of the current location.
$longitude = 1.2; // double | The longitude coordinate of the current location.
$market_ids = array("market_ids_example"); // string[] | Currently selected markets

try {
    $result = $apiInstance->getProductCampaignsUsingGET($channel, $id, $ids_only, $id_type, $purchaser_id, $sid, $latitude, $longitude, $market_ids);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CampaignApi->getProductCampaignsUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **id** | **string**| Use this parameter to pass a ID (master or product) for which you wish to obtain campaigns. |
 **ids_only** | **bool**| If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance. | [optional] [default to false]
 **id_type** | **string**| Specifies which type of id is given. | [optional] [default to productNumber]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **sid** | **string**| This parameter is used to pass an id for the user session. This is important for recognising the user, if you want to trigger personalised campaigns, as well as for FACT-Finder tracking. | [optional]
 **latitude** | **double**| The latitude coordinate of the current location. | [optional]
 **longitude** | **double**| The longitude coordinate of the current location. | [optional]
 **market_ids** | [**string[]**](../Model/string.md)| Currently selected markets | [optional]

### Return type

[**\Swagger\Client\Model\Campaign[]**](../Model/Campaign.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getShoppingCartCampaignsUsingGET**
> \Swagger\Client\Model\Campaign[] getShoppingCartCampaignsUsingGET($channel, $product_number, $ids_only, $purchaser_id, $sid, $latitude, $longitude, $market_ids)

Get shopping cart campaigns

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

$apiInstance = new Swagger\Client\Api\CampaignApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$product_number = array("product_number_example"); // string[] | Use this parameter to pass product ID(s) for which you wish to obtain campaigns.
$ids_only = false; // bool | If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$sid = "sid_example"; // string | This parameter is used to pass an id for the user session. This is important for recognising the user, if you want to trigger personalised campaigns, as well as for FACT-Finder tracking.
$latitude = 1.2; // double | The latitude coordinate of the current location.
$longitude = 1.2; // double | The longitude coordinate of the current location.
$market_ids = array("market_ids_example"); // string[] | Currently selected markets

try {
    $result = $apiInstance->getShoppingCartCampaignsUsingGET($channel, $product_number, $ids_only, $purchaser_id, $sid, $latitude, $longitude, $market_ids);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CampaignApi->getShoppingCartCampaignsUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **product_number** | [**string[]**](../Model/string.md)| Use this parameter to pass product ID(s) for which you wish to obtain campaigns. |
 **ids_only** | **bool**| If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance. | [optional] [default to false]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **sid** | **string**| This parameter is used to pass an id for the user session. This is important for recognising the user, if you want to trigger personalised campaigns, as well as for FACT-Finder tracking. | [optional]
 **latitude** | **double**| The latitude coordinate of the current location. | [optional]
 **longitude** | **double**| The longitude coordinate of the current location. | [optional]
 **market_ids** | [**string[]**](../Model/string.md)| Currently selected markets | [optional]

### Return type

[**\Swagger\Client\Model\Campaign[]**](../Model/Campaign.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

