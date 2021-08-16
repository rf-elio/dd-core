# Swagger\Client\SearchApi

All URIs are relative to *https://ng-demo.fact-finder.de/fact-finder*

Method | HTTP request | Description
------------- | ------------- | -------------
[**categoryNavigationUsingGET**](SearchApi.md#categoryNavigationUsingGET) | **GET** /rest/v4/navigation/category/{channel} | Category ASN for Navigation
[**getSuggestionsUsingGET**](SearchApi.md#getSuggestionsUsingGET) | **GET** /rest/v4/suggest/{channel} | Get suggestions
[**getSuggestionsUsingPOST**](SearchApi.md#getSuggestionsUsingPOST) | **POST** /rest/v4/suggest | Get suggestions with POST
[**navigationUsingGET**](SearchApi.md#navigationUsingGET) | **GET** /rest/v4/navigation/{channel} | Navigation
[**navigationUsingPOST**](SearchApi.md#navigationUsingPOST) | **POST** /rest/v4/navigation | Navigation with POST
[**searchUsingGET**](SearchApi.md#searchUsingGET) | **GET** /rest/v4/search/{channel} | Search
[**searchUsingPOST**](SearchApi.md#searchUsingPOST) | **POST** /rest/v4/search | Search with POST


# **categoryNavigationUsingGET**
> \Swagger\Client\Model\CategoryNavigation categoryNavigationUsingGET($channel, $sid, $start_level, $end_level, $filter, $substring_filter, $force_ab_variant, $market_id, $latitude, $longitude, $max_distance, $exclude_products_not_in_range, $purchaser_id, $use_cache, $use_geo, $use_ab_test)

Category ASN for Navigation

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

$apiInstance = new Swagger\Client\Api\SearchApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$sid = "sid_example"; // string | The session id
$start_level = 56; // int | Category start level
$end_level = 56; // int | Category end level
$filter = array("filter_example"); // string[] | Filter for the whole field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) 'and' = \\_\\_\\_ 'or' = ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green = red or not green. If the filter name equals '*', the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix.
$substring_filter = array("substring_filter_example"); // string[] | Filter for a sub string of the field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) 'and' = \\_\\_\\_ 'or' = ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green = red or not green. If the filter name equals '*', the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix.
$force_ab_variant = array("force_ab_variant_example"); // string[] | Forces to apply certain ab variants to the search result; format: abTestId:AbVariantId; example 1b7f3b1a-600f-4d23-b1bf-ac9978628d17:A
$market_id = array("market_id_example"); // string[] | Only show products that have values for these market IDs.
$latitude = 1.2; // double | The latitude coordinate of the location.
$longitude = 1.2; // double | The longitude coordinate of the location.
$max_distance = 1.2; // double | Use this parameter to override the \"maximum distance\" setting of the geo search.
$exclude_products_not_in_range = true; // bool | Use this parameter to override the \"exclude products not in range\" setting of the geo search.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$use_cache = true; // bool | If true, the search result will be returned from cache memory, if a possible matching result exists.
$use_geo = true; // bool | If true geoSearch features will be active.
$use_ab_test = true; // bool | If true AbTest features will be active.

try {
    $result = $apiInstance->categoryNavigationUsingGET($channel, $sid, $start_level, $end_level, $filter, $substring_filter, $force_ab_variant, $market_id, $latitude, $longitude, $max_distance, $exclude_products_not_in_range, $purchaser_id, $use_cache, $use_geo, $use_ab_test);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SearchApi->categoryNavigationUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **sid** | **string**| The session id | [optional]
 **start_level** | **int**| Category start level | [optional]
 **end_level** | **int**| Category end level | [optional]
 **filter** | [**string[]**](../Model/string.md)| Filter for the whole field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) &#39;and&#39; &#x3D; \\_\\_\\_ &#39;or&#39; &#x3D; ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green &#x3D; red or not green. If the filter name equals &#39;*&#39;, the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix. | [optional]
 **substring_filter** | [**string[]**](../Model/string.md)| Filter for a sub string of the field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) &#39;and&#39; &#x3D; \\_\\_\\_ &#39;or&#39; &#x3D; ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green &#x3D; red or not green. If the filter name equals &#39;*&#39;, the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix. | [optional]
 **force_ab_variant** | [**string[]**](../Model/string.md)| Forces to apply certain ab variants to the search result; format: abTestId:AbVariantId; example 1b7f3b1a-600f-4d23-b1bf-ac9978628d17:A | [optional]
 **market_id** | [**string[]**](../Model/string.md)| Only show products that have values for these market IDs. | [optional]
 **latitude** | **double**| The latitude coordinate of the location. | [optional]
 **longitude** | **double**| The longitude coordinate of the location. | [optional]
 **max_distance** | **double**| Use this parameter to override the \&quot;maximum distance\&quot; setting of the geo search. | [optional]
 **exclude_products_not_in_range** | **bool**| Use this parameter to override the \&quot;exclude products not in range\&quot; setting of the geo search. | [optional]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **use_cache** | **bool**| If true, the search result will be returned from cache memory, if a possible matching result exists. | [optional] [default to true]
 **use_geo** | **bool**| If true geoSearch features will be active. | [optional] [default to true]
 **use_ab_test** | **bool**| If true AbTest features will be active. | [optional] [default to true]

### Return type

[**\Swagger\Client\Model\CategoryNavigation**](../Model/CategoryNavigation.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getSuggestionsUsingGET**
> \Swagger\Client\Model\SuggestionResult getSuggestionsUsingGET($channel, $query, $filter, $substring_filter, $force_ab_variant, $market_id, $sort, $cache_irrelevant, $latitude, $longitude, $max_distance, $exclude_products_not_in_range, $purchaser_id, $page, $hits_per_page, $max_count_variants, $advisor_status, $follow_search, $search_field, $article_number_search, $sid)

Get suggestions

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

$apiInstance = new Swagger\Client\Api\SearchApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$query = "query_example"; // string | The search term
$filter = array("filter_example"); // string[] | Filter for the whole field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) 'and' = \\_\\_\\_ 'or' = ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green = red or not green. If the filter name equals '*', the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix.
$substring_filter = array("substring_filter_example"); // string[] | Filter for a sub string of the field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) 'and' = \\_\\_\\_ 'or' = ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green = red or not green. If the filter name equals '*', the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix.
$force_ab_variant = array("force_ab_variant_example"); // string[] | Forces to apply certain ab variants to the search result; format: abTestId:AbVariantId; example 1b7f3b1a-600f-4d23-b1bf-ac9978628d17:A
$market_id = array("market_id_example"); // string[] | Only show products that have values for these market IDs.
$sort = array("sort_example"); // string[] | Sort the result; use FieldName Relevancy to sort the relevancy; format: FieldName:order order must be either asc or desc; example Manufacturer:asc
$cache_irrelevant = array("cache_irrelevant_example"); // string[] | Flag parameters as cache irrelevant
$latitude = 1.2; // double | The latitude coordinate of the location.
$longitude = 1.2; // double | The longitude coordinate of the location.
$max_distance = 1.2; // double | Use this parameter to override the \"maximum distance\" setting of the geo search.
$exclude_products_not_in_range = true; // bool | Use this parameter to override the \"exclude products not in range\" setting of the geo search.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$page = 56; // int | If a search result contains many results these will be divided into pages. This limits the amount of data that has to be sent in one go. You can indicate which page should be returned. Page numbering starts at 1.
$hits_per_page = 56; // int | In the FACT-Finder Management Interface you can define how many results will be returned on a page by default. If you prefer another number, you can set it with this parameter.
$max_count_variants = 56; // int | The maximum number of variants to return for every record
$advisor_status = "advisor_status_example"; // string | For specifying the current campaign id and answer path; format: campaignId-answerPath
$follow_search = "follow_search_example"; // string | Optional request linking param from a previous search result or search param object. Can improve request performance.
$search_field = "search_field_example"; // string | Normally FACT-Finder searches all fields defined as searchable. However it is possible to search only one specific field as well.
$article_number_search = "DETECT"; // string | Specifies if the query should be interpreted as article number
$sid = "sid_example"; // string | the session id from the user

try {
    $result = $apiInstance->getSuggestionsUsingGET($channel, $query, $filter, $substring_filter, $force_ab_variant, $market_id, $sort, $cache_irrelevant, $latitude, $longitude, $max_distance, $exclude_products_not_in_range, $purchaser_id, $page, $hits_per_page, $max_count_variants, $advisor_status, $follow_search, $search_field, $article_number_search, $sid);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SearchApi->getSuggestionsUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **query** | **string**| The search term |
 **filter** | [**string[]**](../Model/string.md)| Filter for the whole field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) &#39;and&#39; &#x3D; \\_\\_\\_ &#39;or&#39; &#x3D; ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green &#x3D; red or not green. If the filter name equals &#39;*&#39;, the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix. | [optional]
 **substring_filter** | [**string[]**](../Model/string.md)| Filter for a sub string of the field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) &#39;and&#39; &#x3D; \\_\\_\\_ &#39;or&#39; &#x3D; ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green &#x3D; red or not green. If the filter name equals &#39;*&#39;, the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix. | [optional]
 **force_ab_variant** | [**string[]**](../Model/string.md)| Forces to apply certain ab variants to the search result; format: abTestId:AbVariantId; example 1b7f3b1a-600f-4d23-b1bf-ac9978628d17:A | [optional]
 **market_id** | [**string[]**](../Model/string.md)| Only show products that have values for these market IDs. | [optional]
 **sort** | [**string[]**](../Model/string.md)| Sort the result; use FieldName Relevancy to sort the relevancy; format: FieldName:order order must be either asc or desc; example Manufacturer:asc | [optional]
 **cache_irrelevant** | [**string[]**](../Model/string.md)| Flag parameters as cache irrelevant | [optional]
 **latitude** | **double**| The latitude coordinate of the location. | [optional]
 **longitude** | **double**| The longitude coordinate of the location. | [optional]
 **max_distance** | **double**| Use this parameter to override the \&quot;maximum distance\&quot; setting of the geo search. | [optional]
 **exclude_products_not_in_range** | **bool**| Use this parameter to override the \&quot;exclude products not in range\&quot; setting of the geo search. | [optional]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **page** | **int**| If a search result contains many results these will be divided into pages. This limits the amount of data that has to be sent in one go. You can indicate which page should be returned. Page numbering starts at 1. | [optional]
 **hits_per_page** | **int**| In the FACT-Finder Management Interface you can define how many results will be returned on a page by default. If you prefer another number, you can set it with this parameter. | [optional]
 **max_count_variants** | **int**| The maximum number of variants to return for every record | [optional]
 **advisor_status** | **string**| For specifying the current campaign id and answer path; format: campaignId-answerPath | [optional]
 **follow_search** | **string**| Optional request linking param from a previous search result or search param object. Can improve request performance. | [optional]
 **search_field** | **string**| Normally FACT-Finder searches all fields defined as searchable. However it is possible to search only one specific field as well. | [optional]
 **article_number_search** | **string**| Specifies if the query should be interpreted as article number | [optional] [default to DETECT]
 **sid** | **string**| the session id from the user | [optional]

### Return type

[**\Swagger\Client\Model\SuggestionResult**](../Model/SuggestionResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getSuggestionsUsingPOST**
> \Swagger\Client\Model\SuggestionResult getSuggestionsUsingPOST($params)

Get suggestions with POST

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

$apiInstance = new Swagger\Client\Api\SearchApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$params = new \Swagger\Client\Model\SuggestParams(); // \Swagger\Client\Model\SuggestParams | params

try {
    $result = $apiInstance->getSuggestionsUsingPOST($params);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SearchApi->getSuggestionsUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **params** | [**\Swagger\Client\Model\SuggestParams**](../Model/SuggestParams.md)| params |

### Return type

[**\Swagger\Client\Model\SuggestionResult**](../Model/SuggestionResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **navigationUsingGET**
> \Swagger\Client\Model\Result navigationUsingGET($channel, $sid, $filter, $substring_filter, $force_ab_variant, $market_id, $sort, $cache_irrelevant, $latitude, $longitude, $max_distance, $exclude_products_not_in_range, $purchaser_id, $page, $hits_per_page, $max_count_variants, $advisor_status, $follow_search, $use_cache, $use_geo, $use_ab_test, $use_search, $use_asn, $use_found_words, $use_campaigns, $ids_only, $use_personalization, $use_semantic_enhancer, $use_aso, $use_deduplication, $deduplication_field, $use_ranking)

Navigation

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

$apiInstance = new Swagger\Client\Api\SearchApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$sid = "sid_example"; // string | The session id
$filter = array("filter_example"); // string[] | Filter for the whole field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) 'and' = \\_\\_\\_ 'or' = ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green = red or not green. If the filter name equals '*', the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix.
$substring_filter = array("substring_filter_example"); // string[] | Filter for a sub string of the field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) 'and' = \\_\\_\\_ 'or' = ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green = red or not green. If the filter name equals '*', the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix.
$force_ab_variant = array("force_ab_variant_example"); // string[] | Forces to apply certain ab variants to the search result; format: abTestId:AbVariantId; example 1b7f3b1a-600f-4d23-b1bf-ac9978628d17:A
$market_id = array("market_id_example"); // string[] | Only show products that have values for these market IDs.
$sort = array("sort_example"); // string[] | Sort the result; use FieldName Relevancy to sort the relevancy; format: FieldName:order order must be either asc or desc; example Manufacturer:asc
$cache_irrelevant = array("cache_irrelevant_example"); // string[] | Flag parameters as cache irrelevant
$latitude = 1.2; // double | The latitude coordinate of the location.
$longitude = 1.2; // double | The longitude coordinate of the location.
$max_distance = 1.2; // double | Use this parameter to override the \"maximum distance\" setting of the geo search.
$exclude_products_not_in_range = true; // bool | Use this parameter to override the \"exclude products not in range\" setting of the geo search.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$page = 56; // int | If a search result contains many results these will be divided into pages. This limits the amount of data that has to be sent in one go. You can indicate which page should be returned. Page numbering starts at 1.
$hits_per_page = 56; // int | In the FACT-Finder Management Interface you can define how many results will be returned on a page by default. If you prefer another number, you can set it with this parameter.
$max_count_variants = 56; // int | The maximum number of variants to return for every record
$advisor_status = "advisor_status_example"; // string | For specifying the current campaign id and answer path; format: campaignId-answerPath
$follow_search = "follow_search_example"; // string | Optional request linking param from a previous search result or search param object. Can improve request performance.
$use_cache = true; // bool | If true, the search result will be returned from cache memory, if a possible matching result exists.
$use_geo = true; // bool | If true geoSearch features will be active.
$use_ab_test = true; // bool | If true AbTest features will be active.
$use_search = true; // bool | If true, search will be executed for the query.
$use_asn = true; // bool | If true, filters should be generated for the result.
$use_found_words = false; // bool | If true, the words that led to the records in the search results will be determined; this may require a large amount of processing time.
$use_campaigns = true; // bool | If true, campaigns corresponding to this search result will be returned.
$ids_only = false; // bool | If true, the returned records will contain only record IDs.
$use_personalization = true; // bool | If true, the relevant products in the result will be personalized.
$use_semantic_enhancer = true; // bool | If true, the semantic enhancer will be used.
$use_aso = true; // bool | If true, automatic search optimization will be used.
$use_deduplication = true; // bool | If true, the configured deduplication of variants will be used.
$deduplication_field = "deduplication_field_example"; // string | Specifies on which field variants should be deduplicated.
$use_ranking = true; // bool | If true, ranking will be applied.

try {
    $result = $apiInstance->navigationUsingGET($channel, $sid, $filter, $substring_filter, $force_ab_variant, $market_id, $sort, $cache_irrelevant, $latitude, $longitude, $max_distance, $exclude_products_not_in_range, $purchaser_id, $page, $hits_per_page, $max_count_variants, $advisor_status, $follow_search, $use_cache, $use_geo, $use_ab_test, $use_search, $use_asn, $use_found_words, $use_campaigns, $ids_only, $use_personalization, $use_semantic_enhancer, $use_aso, $use_deduplication, $deduplication_field, $use_ranking);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SearchApi->navigationUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **sid** | **string**| The session id | [optional]
 **filter** | [**string[]**](../Model/string.md)| Filter for the whole field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) &#39;and&#39; &#x3D; \\_\\_\\_ &#39;or&#39; &#x3D; ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green &#x3D; red or not green. If the filter name equals &#39;*&#39;, the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix. | [optional]
 **substring_filter** | [**string[]**](../Model/string.md)| Filter for a sub string of the field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) &#39;and&#39; &#x3D; \\_\\_\\_ &#39;or&#39; &#x3D; ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green &#x3D; red or not green. If the filter name equals &#39;*&#39;, the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix. | [optional]
 **force_ab_variant** | [**string[]**](../Model/string.md)| Forces to apply certain ab variants to the search result; format: abTestId:AbVariantId; example 1b7f3b1a-600f-4d23-b1bf-ac9978628d17:A | [optional]
 **market_id** | [**string[]**](../Model/string.md)| Only show products that have values for these market IDs. | [optional]
 **sort** | [**string[]**](../Model/string.md)| Sort the result; use FieldName Relevancy to sort the relevancy; format: FieldName:order order must be either asc or desc; example Manufacturer:asc | [optional]
 **cache_irrelevant** | [**string[]**](../Model/string.md)| Flag parameters as cache irrelevant | [optional]
 **latitude** | **double**| The latitude coordinate of the location. | [optional]
 **longitude** | **double**| The longitude coordinate of the location. | [optional]
 **max_distance** | **double**| Use this parameter to override the \&quot;maximum distance\&quot; setting of the geo search. | [optional]
 **exclude_products_not_in_range** | **bool**| Use this parameter to override the \&quot;exclude products not in range\&quot; setting of the geo search. | [optional]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **page** | **int**| If a search result contains many results these will be divided into pages. This limits the amount of data that has to be sent in one go. You can indicate which page should be returned. Page numbering starts at 1. | [optional]
 **hits_per_page** | **int**| In the FACT-Finder Management Interface you can define how many results will be returned on a page by default. If you prefer another number, you can set it with this parameter. | [optional]
 **max_count_variants** | **int**| The maximum number of variants to return for every record | [optional]
 **advisor_status** | **string**| For specifying the current campaign id and answer path; format: campaignId-answerPath | [optional]
 **follow_search** | **string**| Optional request linking param from a previous search result or search param object. Can improve request performance. | [optional]
 **use_cache** | **bool**| If true, the search result will be returned from cache memory, if a possible matching result exists. | [optional] [default to true]
 **use_geo** | **bool**| If true geoSearch features will be active. | [optional] [default to true]
 **use_ab_test** | **bool**| If true AbTest features will be active. | [optional] [default to true]
 **use_search** | **bool**| If true, search will be executed for the query. | [optional] [default to true]
 **use_asn** | **bool**| If true, filters should be generated for the result. | [optional] [default to true]
 **use_found_words** | **bool**| If true, the words that led to the records in the search results will be determined; this may require a large amount of processing time. | [optional] [default to false]
 **use_campaigns** | **bool**| If true, campaigns corresponding to this search result will be returned. | [optional] [default to true]
 **ids_only** | **bool**| If true, the returned records will contain only record IDs. | [optional] [default to false]
 **use_personalization** | **bool**| If true, the relevant products in the result will be personalized. | [optional] [default to true]
 **use_semantic_enhancer** | **bool**| If true, the semantic enhancer will be used. | [optional] [default to true]
 **use_aso** | **bool**| If true, automatic search optimization will be used. | [optional] [default to true]
 **use_deduplication** | **bool**| If true, the configured deduplication of variants will be used. | [optional] [default to true]
 **deduplication_field** | **string**| Specifies on which field variants should be deduplicated. | [optional]
 **use_ranking** | **bool**| If true, ranking will be applied. | [optional] [default to true]

### Return type

[**\Swagger\Client\Model\Result**](../Model/Result.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **navigationUsingPOST**
> \Swagger\Client\Model\Result navigationUsingPOST($navigation_request)

Navigation with POST

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

$apiInstance = new Swagger\Client\Api\SearchApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$navigation_request = new \Swagger\Client\Model\NavigationRequest(); // \Swagger\Client\Model\NavigationRequest | navigationRequest

try {
    $result = $apiInstance->navigationUsingPOST($navigation_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SearchApi->navigationUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **navigation_request** | [**\Swagger\Client\Model\NavigationRequest**](../Model/NavigationRequest.md)| navigationRequest |

### Return type

[**\Swagger\Client\Model\Result**](../Model/Result.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **searchUsingGET**
> \Swagger\Client\Model\Result searchUsingGET($channel, $query, $sid, $user_input, $filter, $substring_filter, $force_ab_variant, $market_id, $sort, $cache_irrelevant, $latitude, $longitude, $max_distance, $exclude_products_not_in_range, $purchaser_id, $page, $hits_per_page, $max_count_variants, $advisor_status, $follow_search, $search_field, $article_number_search, $use_cache, $use_geo, $use_ab_test, $use_search, $use_asn, $use_found_words, $use_campaigns, $ids_only, $use_personalization, $use_semantic_enhancer, $use_aso, $use_deduplication, $deduplication_field, $use_ranking, $query_from_suggest)

Search

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

$apiInstance = new Swagger\Client\Api\SearchApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$query = "query_example"; // string | The search term
$sid = "sid_example"; // string | The session id
$user_input = "user_input_example"; // string | Use this parameter to send the characters, the shop user entered until the search query was triggered.
$filter = array("filter_example"); // string[] | Filter for the whole field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) 'and' = \\_\\_\\_ 'or' = ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green = red or not green. If the filter name equals '*', the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix.
$substring_filter = array("substring_filter_example"); // string[] | Filter for a sub string of the field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) 'and' = \\_\\_\\_ 'or' = ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green = red or not green. If the filter name equals '*', the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix.
$force_ab_variant = array("force_ab_variant_example"); // string[] | Forces to apply certain ab variants to the search result; format: abTestId:AbVariantId; example 1b7f3b1a-600f-4d23-b1bf-ac9978628d17:A
$market_id = array("market_id_example"); // string[] | Only show products that have values for these market IDs.
$sort = array("sort_example"); // string[] | Sort the result; use FieldName Relevancy to sort the relevancy; format: FieldName:order order must be either asc or desc; example Manufacturer:asc
$cache_irrelevant = array("cache_irrelevant_example"); // string[] | Flag parameters as cache irrelevant
$latitude = 1.2; // double | The latitude coordinate of the location.
$longitude = 1.2; // double | The longitude coordinate of the location.
$max_distance = 1.2; // double | Use this parameter to override the \"maximum distance\" setting of the geo search.
$exclude_products_not_in_range = true; // bool | Use this parameter to override the \"exclude products not in range\" setting of the geo search.
$purchaser_id = "purchaser_id_example"; // string | Use this parameter to pass the purchaser ID. This ID is only needed, if the 'customer specific pricing' module is active. Otherwise it will be ignored.
$page = 56; // int | If a search result contains many results these will be divided into pages. This limits the amount of data that has to be sent in one go. You can indicate which page should be returned. Page numbering starts at 1.
$hits_per_page = 56; // int | In the FACT-Finder Management Interface you can define how many results will be returned on a page by default. If you prefer another number, you can set it with this parameter.
$max_count_variants = 56; // int | The maximum number of variants to return for every record
$advisor_status = "advisor_status_example"; // string | For specifying the current campaign id and answer path; format: campaignId-answerPath
$follow_search = "follow_search_example"; // string | Optional request linking param from a previous search result or search param object. Can improve request performance.
$search_field = "search_field_example"; // string | Normally FACT-Finder searches all fields defined as searchable. However it is possible to search only one specific field as well.
$article_number_search = "DETECT"; // string | Specifies if the query should be interpreted as article number
$use_cache = true; // bool | If true, the search result will be returned from cache memory, if a possible matching result exists.
$use_geo = true; // bool | If true geoSearch features will be active.
$use_ab_test = true; // bool | If true AbTest features will be active.
$use_search = true; // bool | If true, search will be executed for the query.
$use_asn = true; // bool | If true, filters should be generated for the result.
$use_found_words = false; // bool | If true, the words that led to the records in the search results will be determined; this may require a large amount of processing time.
$use_campaigns = true; // bool | If true, campaigns corresponding to this search result will be returned.
$ids_only = false; // bool | If true, the returned records will contain only record IDs.
$use_personalization = true; // bool | If true, the relevant products in the result will be personalized.
$use_semantic_enhancer = true; // bool | If true, the semantic enhancer will be used.
$use_aso = true; // bool | If true, automatic search optimization will be used.
$use_deduplication = true; // bool | If true, the configured deduplication of variants will be used.
$deduplication_field = "deduplication_field_example"; // string | Specifies on which field variants should be deduplicated.
$use_ranking = true; // bool | If true, ranking will be applied.
$query_from_suggest = true; // bool | This parameter indicates that the FACT-Finder query was triggered through a selection from the suggestion list. In this case send the parameter with the value true.

try {
    $result = $apiInstance->searchUsingGET($channel, $query, $sid, $user_input, $filter, $substring_filter, $force_ab_variant, $market_id, $sort, $cache_irrelevant, $latitude, $longitude, $max_distance, $exclude_products_not_in_range, $purchaser_id, $page, $hits_per_page, $max_count_variants, $advisor_status, $follow_search, $search_field, $article_number_search, $use_cache, $use_geo, $use_ab_test, $use_search, $use_asn, $use_found_words, $use_campaigns, $ids_only, $use_personalization, $use_semantic_enhancer, $use_aso, $use_deduplication, $deduplication_field, $use_ranking, $query_from_suggest);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SearchApi->searchUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **query** | **string**| The search term |
 **sid** | **string**| The session id | [optional]
 **user_input** | **string**| Use this parameter to send the characters, the shop user entered until the search query was triggered. | [optional]
 **filter** | [**string[]**](../Model/string.md)| Filter for the whole field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) &#39;and&#39; &#x3D; \\_\\_\\_ &#39;or&#39; &#x3D; ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green &#x3D; red or not green. If the filter name equals &#39;*&#39;, the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix. | [optional]
 **substring_filter** | [**string[]**](../Model/string.md)| Filter for a sub string of the field value; a filter can have multiple values, the values can be separated with the following characters (they are configurable in the config.xml) &#39;and&#39; &#x3D; \\_\\_\\_ &#39;or&#39; &#x3D; ~~~ the filter value can be excluded with the prefix ! format: facetid:value; example Red~~~!Green &#x3D; red or not green. If the filter name equals &#39;*&#39;, the filter will be applied on any field; example: *:Red~~~Green. This filter type does not support the exclusion prefix. | [optional]
 **force_ab_variant** | [**string[]**](../Model/string.md)| Forces to apply certain ab variants to the search result; format: abTestId:AbVariantId; example 1b7f3b1a-600f-4d23-b1bf-ac9978628d17:A | [optional]
 **market_id** | [**string[]**](../Model/string.md)| Only show products that have values for these market IDs. | [optional]
 **sort** | [**string[]**](../Model/string.md)| Sort the result; use FieldName Relevancy to sort the relevancy; format: FieldName:order order must be either asc or desc; example Manufacturer:asc | [optional]
 **cache_irrelevant** | [**string[]**](../Model/string.md)| Flag parameters as cache irrelevant | [optional]
 **latitude** | **double**| The latitude coordinate of the location. | [optional]
 **longitude** | **double**| The longitude coordinate of the location. | [optional]
 **max_distance** | **double**| Use this parameter to override the \&quot;maximum distance\&quot; setting of the geo search. | [optional]
 **exclude_products_not_in_range** | **bool**| Use this parameter to override the \&quot;exclude products not in range\&quot; setting of the geo search. | [optional]
 **purchaser_id** | **string**| Use this parameter to pass the purchaser ID. This ID is only needed, if the &#39;customer specific pricing&#39; module is active. Otherwise it will be ignored. | [optional]
 **page** | **int**| If a search result contains many results these will be divided into pages. This limits the amount of data that has to be sent in one go. You can indicate which page should be returned. Page numbering starts at 1. | [optional]
 **hits_per_page** | **int**| In the FACT-Finder Management Interface you can define how many results will be returned on a page by default. If you prefer another number, you can set it with this parameter. | [optional]
 **max_count_variants** | **int**| The maximum number of variants to return for every record | [optional]
 **advisor_status** | **string**| For specifying the current campaign id and answer path; format: campaignId-answerPath | [optional]
 **follow_search** | **string**| Optional request linking param from a previous search result or search param object. Can improve request performance. | [optional]
 **search_field** | **string**| Normally FACT-Finder searches all fields defined as searchable. However it is possible to search only one specific field as well. | [optional]
 **article_number_search** | **string**| Specifies if the query should be interpreted as article number | [optional] [default to DETECT]
 **use_cache** | **bool**| If true, the search result will be returned from cache memory, if a possible matching result exists. | [optional] [default to true]
 **use_geo** | **bool**| If true geoSearch features will be active. | [optional] [default to true]
 **use_ab_test** | **bool**| If true AbTest features will be active. | [optional] [default to true]
 **use_search** | **bool**| If true, search will be executed for the query. | [optional] [default to true]
 **use_asn** | **bool**| If true, filters should be generated for the result. | [optional] [default to true]
 **use_found_words** | **bool**| If true, the words that led to the records in the search results will be determined; this may require a large amount of processing time. | [optional] [default to false]
 **use_campaigns** | **bool**| If true, campaigns corresponding to this search result will be returned. | [optional] [default to true]
 **ids_only** | **bool**| If true, the returned records will contain only record IDs. | [optional] [default to false]
 **use_personalization** | **bool**| If true, the relevant products in the result will be personalized. | [optional] [default to true]
 **use_semantic_enhancer** | **bool**| If true, the semantic enhancer will be used. | [optional] [default to true]
 **use_aso** | **bool**| If true, automatic search optimization will be used. | [optional] [default to true]
 **use_deduplication** | **bool**| If true, the configured deduplication of variants will be used. | [optional] [default to true]
 **deduplication_field** | **string**| Specifies on which field variants should be deduplicated. | [optional]
 **use_ranking** | **bool**| If true, ranking will be applied. | [optional] [default to true]
 **query_from_suggest** | **bool**| This parameter indicates that the FACT-Finder query was triggered through a selection from the suggestion list. In this case send the parameter with the value true. | [optional]

### Return type

[**\Swagger\Client\Model\Result**](../Model/Result.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **searchUsingPOST**
> \Swagger\Client\Model\Result searchUsingPOST($search_request)

Search with POST

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

$apiInstance = new Swagger\Client\Api\SearchApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$search_request = new \Swagger\Client\Model\SearchRequest(); // \Swagger\Client\Model\SearchRequest | searchRequest

try {
    $result = $apiInstance->searchUsingPOST($search_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SearchApi->searchUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **search_request** | [**\Swagger\Client\Model\SearchRequest**](../Model/SearchRequest.md)| searchRequest |

### Return type

[**\Swagger\Client\Model\Result**](../Model/Result.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

