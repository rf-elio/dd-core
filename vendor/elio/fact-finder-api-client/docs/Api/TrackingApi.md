# Swagger\Client\TrackingApi

All URIs are relative to *https://ng-demo.fact-finder.de/fact-finder*

Method | HTTP request | Description
------------- | ------------- | -------------
[**trackCartUsingPOST**](TrackingApi.md#trackCartUsingPOST) | **POST** /rest/v4/track/{channel}/cart | Track a cart event
[**trackCheckoutUsingPOST**](TrackingApi.md#trackCheckoutUsingPOST) | **POST** /rest/v4/track/{channel}/checkout | Track a checkout event
[**trackClickUsingPOST**](TrackingApi.md#trackClickUsingPOST) | **POST** /rest/v4/track/{channel}/click | Track a click event
[**trackFeedbackUsingPOST**](TrackingApi.md#trackFeedbackUsingPOST) | **POST** /rest/v4/track/{channel}/feedback | Track a feedback event
[**trackLandingPageClickUsingPOST**](TrackingApi.md#trackLandingPageClickUsingPOST) | **POST** /rest/v4/track/{channel}/landingPageClick | Track a click event for products provided by a campaign on a landing page.
[**trackLogUsingPOST**](TrackingApi.md#trackLogUsingPOST) | **POST** /rest/v4/track/{channel}/log | Track a log event
[**trackLoginUsingPOST**](TrackingApi.md#trackLoginUsingPOST) | **POST** /rest/v4/track/{channel}/login | Track a login event
[**trackPredBasketClickUsingPOST**](TrackingApi.md#trackPredBasketClickUsingPOST) | **POST** /rest/v4/track/{channel}/predbasketClick | Track a predictive basket click event
[**trackRecommendationClickUsingPOST**](TrackingApi.md#trackRecommendationClickUsingPOST) | **POST** /rest/v4/track/{channel}/recommendationClick | Track a recommendation click event


# **trackCartUsingPOST**
> trackCartUsingPOST($channel, $events)

Track a cart event

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

$apiInstance = new Swagger\Client\Api\TrackingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$events = array(new \Swagger\Client\Model\CartOrCheckoutEvent()); // \Swagger\Client\Model\CartOrCheckoutEvent[] | events

try {
    $apiInstance->trackCartUsingPOST($channel, $events);
} catch (Exception $e) {
    echo 'Exception when calling TrackingApi->trackCartUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **events** | [**\Swagger\Client\Model\CartOrCheckoutEvent[]**](../Model/CartOrCheckoutEvent.md)| events |

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **trackCheckoutUsingPOST**
> trackCheckoutUsingPOST($channel, $events)

Track a checkout event

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

$apiInstance = new Swagger\Client\Api\TrackingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$events = array(new \Swagger\Client\Model\CartOrCheckoutEvent()); // \Swagger\Client\Model\CartOrCheckoutEvent[] | events

try {
    $apiInstance->trackCheckoutUsingPOST($channel, $events);
} catch (Exception $e) {
    echo 'Exception when calling TrackingApi->trackCheckoutUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **events** | [**\Swagger\Client\Model\CartOrCheckoutEvent[]**](../Model/CartOrCheckoutEvent.md)| events |

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **trackClickUsingPOST**
> trackClickUsingPOST($channel, $events)

Track a click event

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

$apiInstance = new Swagger\Client\Api\TrackingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$events = array(new \Swagger\Client\Model\ClickEvent()); // \Swagger\Client\Model\ClickEvent[] | events

try {
    $apiInstance->trackClickUsingPOST($channel, $events);
} catch (Exception $e) {
    echo 'Exception when calling TrackingApi->trackClickUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **events** | [**\Swagger\Client\Model\ClickEvent[]**](../Model/ClickEvent.md)| events |

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **trackFeedbackUsingPOST**
> trackFeedbackUsingPOST($channel, $events)

Track a feedback event

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

$apiInstance = new Swagger\Client\Api\TrackingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$events = array(new \Swagger\Client\Model\FeedbackEvent()); // \Swagger\Client\Model\FeedbackEvent[] | events

try {
    $apiInstance->trackFeedbackUsingPOST($channel, $events);
} catch (Exception $e) {
    echo 'Exception when calling TrackingApi->trackFeedbackUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **events** | [**\Swagger\Client\Model\FeedbackEvent[]**](../Model/FeedbackEvent.md)| events |

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **trackLandingPageClickUsingPOST**
> trackLandingPageClickUsingPOST($channel, $events)

Track a click event for products provided by a campaign on a landing page.

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

$apiInstance = new Swagger\Client\Api\TrackingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$events = array(new \Swagger\Client\Model\LandingPageClickEvent()); // \Swagger\Client\Model\LandingPageClickEvent[] | events

try {
    $apiInstance->trackLandingPageClickUsingPOST($channel, $events);
} catch (Exception $e) {
    echo 'Exception when calling TrackingApi->trackLandingPageClickUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **events** | [**\Swagger\Client\Model\LandingPageClickEvent[]**](../Model/LandingPageClickEvent.md)| events |

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **trackLogUsingPOST**
> trackLogUsingPOST($channel, $events)

Track a log event

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

$apiInstance = new Swagger\Client\Api\TrackingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$events = array(new \Swagger\Client\Model\SearchLogEvent()); // \Swagger\Client\Model\SearchLogEvent[] | events

try {
    $apiInstance->trackLogUsingPOST($channel, $events);
} catch (Exception $e) {
    echo 'Exception when calling TrackingApi->trackLogUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **events** | [**\Swagger\Client\Model\SearchLogEvent[]**](../Model/SearchLogEvent.md)| events |

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **trackLoginUsingPOST**
> trackLoginUsingPOST($channel, $events)

Track a login event

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

$apiInstance = new Swagger\Client\Api\TrackingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$events = array(new \Swagger\Client\Model\LoginEvent()); // \Swagger\Client\Model\LoginEvent[] | events

try {
    $apiInstance->trackLoginUsingPOST($channel, $events);
} catch (Exception $e) {
    echo 'Exception when calling TrackingApi->trackLoginUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **events** | [**\Swagger\Client\Model\LoginEvent[]**](../Model/LoginEvent.md)| events |

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **trackPredBasketClickUsingPOST**
> trackPredBasketClickUsingPOST($channel, $events)

Track a predictive basket click event

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

$apiInstance = new Swagger\Client\Api\TrackingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$events = array(new \Swagger\Client\Model\PredBasketClickEvent()); // \Swagger\Client\Model\PredBasketClickEvent[] | events

try {
    $apiInstance->trackPredBasketClickUsingPOST($channel, $events);
} catch (Exception $e) {
    echo 'Exception when calling TrackingApi->trackPredBasketClickUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **events** | [**\Swagger\Client\Model\PredBasketClickEvent[]**](../Model/PredBasketClickEvent.md)| events |

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **trackRecommendationClickUsingPOST**
> trackRecommendationClickUsingPOST($channel, $events)

Track a recommendation click event

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

$apiInstance = new Swagger\Client\Api\TrackingApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel = "channel_example"; // string | channel
$events = array(new \Swagger\Client\Model\RecommendationClickEvent()); // \Swagger\Client\Model\RecommendationClickEvent[] | events

try {
    $apiInstance->trackRecommendationClickUsingPOST($channel, $events);
} catch (Exception $e) {
    echo 'Exception when calling TrackingApi->trackRecommendationClickUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel** | **string**| channel |
 **events** | [**\Swagger\Client\Model\RecommendationClickEvent[]**](../Model/RecommendationClickEvent.md)| events |

### Return type

void (empty response body)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

