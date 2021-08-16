# Swagger\Client\JobsApi

All URIs are relative to *https://ng-demo.fact-finder.de/fact-finder*

Method | HTTP request | Description
------------- | ------------- | -------------
[**startJobUsingPOST**](JobsApi.md#startJobUsingPOST) | **POST** /rest/v4/jobs/startJob | Start the job with the given name and group name. The job will only be started, if it is not already running.
[**startJobsWithGroupNameUsingPOST**](JobsApi.md#startJobsWithGroupNameUsingPOST) | **POST** /rest/v4/jobs/startJobsWithGroupName | Start the jobs with the given group name. A job will only be started, if it is not already running.
[**startJobsWithNameUsingPOST**](JobsApi.md#startJobsWithNameUsingPOST) | **POST** /rest/v4/jobs/startJobsWithName | Start the jobs with the given name. A job will only be started, if it is not already running.


# **startJobUsingPOST**
> \Swagger\Client\Model\JobTriggerResult[] startJobUsingPOST($job_name, $job_group)

Start the job with the given name and group name. The job will only be started, if it is not already running.

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

$apiInstance = new Swagger\Client\Api\JobsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$job_name = "job_name_example"; // string | jobName
$job_group = "job_group_example"; // string | jobGroup

try {
    $result = $apiInstance->startJobUsingPOST($job_name, $job_group);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling JobsApi->startJobUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **job_name** | **string**| jobName |
 **job_group** | **string**| jobGroup |

### Return type

[**\Swagger\Client\Model\JobTriggerResult[]**](../Model/JobTriggerResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **startJobsWithGroupNameUsingPOST**
> \Swagger\Client\Model\JobTriggerResult[] startJobsWithGroupNameUsingPOST($job_group)

Start the jobs with the given group name. A job will only be started, if it is not already running.

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

$apiInstance = new Swagger\Client\Api\JobsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$job_group = "job_group_example"; // string | jobGroup

try {
    $result = $apiInstance->startJobsWithGroupNameUsingPOST($job_group);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling JobsApi->startJobsWithGroupNameUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **job_group** | **string**| jobGroup |

### Return type

[**\Swagger\Client\Model\JobTriggerResult[]**](../Model/JobTriggerResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **startJobsWithNameUsingPOST**
> \Swagger\Client\Model\JobTriggerResult[] startJobsWithNameUsingPOST($job_name)

Start the jobs with the given name. A job will only be started, if it is not already running.

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

$apiInstance = new Swagger\Client\Api\JobsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$job_name = "job_name_example"; // string | jobName

try {
    $result = $apiInstance->startJobsWithNameUsingPOST($job_name);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling JobsApi->startJobsWithNameUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **job_name** | **string**| jobName |

### Return type

[**\Swagger\Client\Model\JobTriggerResult[]**](../Model/JobTriggerResult.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

