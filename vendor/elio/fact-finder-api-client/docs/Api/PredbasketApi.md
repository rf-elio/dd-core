# Swagger\Client\PredbasketApi

All URIs are relative to *https://ng-demo.fact-finder.de/fact-finder*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getPredictionsUsingGET**](PredbasketApi.md#getPredictionsUsingGET) | **GET** /rest/v4/predictivebasket/{channel} | Get predictions


# **getPredictionsUsingGET**
> \Swagger\Client\Model\PredictiveBasketResult getPredictionsUsingGET($channel, $user_id, $max_results, $filter, $blacklist, $purchaser_id, $ids_only)

Get predictions

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

$apiInstance = new Swagger\Client\Api\PredbasketApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$user_id = "user_id_example"; // string | This parameter contains a unique user identifier. It is essential for predictive basket results.
$max_results = 0; // int | Limit for the amount of predictions to return.
$filter = array("filter_example"); // string[] | Filters for field values of returned records.
$blacklist = array("blacklist_example"); // string[] | IDs of products to exclude from predictions.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$ids_only = false; // bool | If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance.

try {
    $result = $apiInstance->getPredictionsUsingGET($channel, $user_id, $max_results, $filter, $blacklist, $purchaser_id, $ids_only);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PredbasketApi->getPredictionsUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **user_id** | **string**| This parameter contains a unique user identifier. It is essential for predictive basket results. |
 **max_results** | **int**| Limit for the amount of predictions to return. | [optional] [default to 0]
 **filter** | [**string[]**](../Model/string.md)| Filters for field values of returned records. | [optional]
 **blacklist** | [**string[]**](../Model/string.md)| IDs of products to exclude from predictions. | [optional]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **ids_only** | **bool**| If the value true is passed, then only the record IDs will be returned, streamlining the results. If you do not need the other information in the results, this will help you to improve performance. | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\PredictiveBasketResult**](../Model/PredictiveBasketResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

