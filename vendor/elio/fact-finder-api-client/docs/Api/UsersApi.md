# Swagger\Client\UsersApi

All URIs are relative to *https://ng-demo.fact-finder.de/fact-finder*

Method | HTTP request | Description
------------- | ------------- | -------------
[**createGroupUsingPOST**](UsersApi.md#createGroupUsingPOST) | **POST** /rest/v4/groups | Create a new group. Non-admin user are not allowed to create groups containing api roles.
[**createUserUsingPOST**](UsersApi.md#createUserUsingPOST) | **POST** /rest/v4/users | Create a new local user. Non-admin user are not allowed to create user with api roles or admin user. Only channels known by the creating user can be added to the new user.
[**deleteGroupUsingDELETE**](UsersApi.md#deleteGroupUsingDELETE) | **DELETE** /rest/v4/groups/{name} | Delete the group with the given name.
[**deleteGroupsUsingDELETE**](UsersApi.md#deleteGroupsUsingDELETE) | **DELETE** /rest/v4/groups | Delete multiple groups.
[**deleteUserUsingDELETE**](UsersApi.md#deleteUserUsingDELETE) | **DELETE** /rest/v4/users/{userName} | Delete user.
[**deleteUsersUsingDELETE**](UsersApi.md#deleteUsersUsingDELETE) | **DELETE** /rest/v4/users | Delete multiple users.
[**getGroupsUsingGET**](UsersApi.md#getGroupsUsingGET) | **GET** /rest/v4/groups | Get groups matching the given filters.
[**getUsersUsingGET**](UsersApi.md#getUsersUsingGET) | **GET** /rest/v4/users | Get the users, which fulfil the given filters.
[**getVisibleChannelsUsingGET**](UsersApi.md#getVisibleChannelsUsingGET) | **GET** /rest/v4/user/channel | Get visible channels
[**updateGroupUsingPUT**](UsersApi.md#updateGroupUsingPUT) | **PUT** /rest/v4/groups | Update group. Non-admin user are not allowed to add new api roles to the group.
[**updatePasswordUsingPUT**](UsersApi.md#updatePasswordUsingPUT) | **PUT** /rest/v4/users/{userName}/password | Update user password. The password is expected to be plain text. Only passwords for local users can be changed.
[**updateUserUsingPUT**](UsersApi.md#updateUserUsingPUT) | **PUT** /rest/v4/users | Update user ui settings and permissions. Non-admin user are not allowed to add api roles or channels unknown to the creating user. Use dedicated api, if you want to change the password.


# **createGroupUsingPOST**
> \Swagger\Client\Model\Group createGroupUsingPOST($group)

Create a new group. Non-admin user are not allowed to create groups containing api roles.

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

$apiInstance = new Swagger\Client\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$group = new \Swagger\Client\Model\Group(); // \Swagger\Client\Model\Group | group

try {
    $result = $apiInstance->createGroupUsingPOST($group);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->createGroupUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **group** | [**\Swagger\Client\Model\Group**](../Model/Group.md)| group |

### Return type

[**\Swagger\Client\Model\Group**](../Model/Group.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **createUserUsingPOST**
> \Swagger\Client\Model\UserInfo createUserUsingPOST($user)

Create a new local user. Non-admin user are not allowed to create user with api roles or admin user. Only channels known by the creating user can be added to the new user.

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

$apiInstance = new Swagger\Client\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$user = new \Swagger\Client\Model\User(); // \Swagger\Client\Model\User | user

try {
    $result = $apiInstance->createUserUsingPOST($user);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->createUserUsingPOST: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **user** | [**\Swagger\Client\Model\User**](../Model/User.md)| user |

### Return type

[**\Swagger\Client\Model\UserInfo**](../Model/UserInfo.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **deleteGroupUsingDELETE**
> \Swagger\Client\Model\Group deleteGroupUsingDELETE($name)

Delete the group with the given name.

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

$apiInstance = new Swagger\Client\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$name = "name_example"; // string | Name of the group which should be deleted.

try {
    $result = $apiInstance->deleteGroupUsingDELETE($name);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->deleteGroupUsingDELETE: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **name** | **string**| Name of the group which should be deleted. |

### Return type

[**\Swagger\Client\Model\Group**](../Model/Group.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **deleteGroupsUsingDELETE**
> \Swagger\Client\Model\Group[] deleteGroupsUsingDELETE($name)

Delete multiple groups.

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

$apiInstance = new Swagger\Client\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$name = array("name_example"); // string[] | List with names of the groups that should be deleted.

try {
    $result = $apiInstance->deleteGroupsUsingDELETE($name);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->deleteGroupsUsingDELETE: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **name** | [**string[]**](../Model/string.md)| List with names of the groups that should be deleted. |

### Return type

[**\Swagger\Client\Model\Group[]**](../Model/Group.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **deleteUserUsingDELETE**
> \Swagger\Client\Model\UserInfo deleteUserUsingDELETE($user_name)

Delete user.

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

$apiInstance = new Swagger\Client\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$user_name = "user_name_example"; // string | The name of user that will be deleted.

try {
    $result = $apiInstance->deleteUserUsingDELETE($user_name);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->deleteUserUsingDELETE: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **user_name** | **string**| The name of user that will be deleted. |

### Return type

[**\Swagger\Client\Model\UserInfo**](../Model/UserInfo.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **deleteUsersUsingDELETE**
> \Swagger\Client\Model\UserInfo[] deleteUsersUsingDELETE($name)

Delete multiple users.

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

$apiInstance = new Swagger\Client\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$name = array("name_example"); // string[] | The names of users that will be deleted.

try {
    $result = $apiInstance->deleteUsersUsingDELETE($name);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->deleteUsersUsingDELETE: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **name** | [**string[]**](../Model/string.md)| The names of users that will be deleted. |

### Return type

[**\Swagger\Client\Model\UserInfo[]**](../Model/UserInfo.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getGroupsUsingGET**
> \Swagger\Client\Model\Group[] getGroupsUsingGET($name, $role)

Get groups matching the given filters.

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

$apiInstance = new Swagger\Client\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$name = "name_example"; // string | Filter groups whose name contains a specific string.
$role = "role_example"; // string | Filter groups with specific role.

try {
    $result = $apiInstance->getGroupsUsingGET($name, $role);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->getGroupsUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **name** | **string**| Filter groups whose name contains a specific string. | [optional]
 **role** | **string**| Filter groups with specific role. | [optional]

### Return type

[**\Swagger\Client\Model\Group[]**](../Model/Group.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getUsersUsingGET**
> \Swagger\Client\Model\UserInfo[] getUsersUsingGET($name, $role, $channel, $group)

Get the users, which fulfil the given filters.

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

$apiInstance = new Swagger\Client\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$name = "name_example"; // string | Filter users whose name contains a specific string.
$role = "role_example"; // string | Filter users with a specific role.
$channel = "channel_example"; // string | Filter users assigned a specific channel.
$group = "group_example"; // string | Filter users who are part of a specific group.

try {
    $result = $apiInstance->getUsersUsingGET($name, $role, $channel, $group);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->getUsersUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **name** | **string**| Filter users whose name contains a specific string. | [optional]
 **role** | **string**| Filter users with a specific role. | [optional]
 **channel** | **string**| Filter users assigned a specific channel. | [optional]
 **group** | **string**| Filter users who are part of a specific group. | [optional]

### Return type

[**\Swagger\Client\Model\UserInfo[]**](../Model/UserInfo.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getVisibleChannelsUsingGET**
> string[] getVisibleChannelsUsingGET()

Get visible channels

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

$apiInstance = new Swagger\Client\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->getVisibleChannelsUsingGET();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->getVisibleChannelsUsingGET: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters
This endpoint does not need any parameter.

### Return type

**string[]**

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **updateGroupUsingPUT**
> \Swagger\Client\Model\Group updateGroupUsingPUT($group)

Update group. Non-admin user are not allowed to add new api roles to the group.

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

$apiInstance = new Swagger\Client\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$group = new \Swagger\Client\Model\Group(); // \Swagger\Client\Model\Group | group

try {
    $result = $apiInstance->updateGroupUsingPUT($group);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->updateGroupUsingPUT: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **group** | [**\Swagger\Client\Model\Group**](../Model/Group.md)| group |

### Return type

[**\Swagger\Client\Model\Group**](../Model/Group.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **updatePasswordUsingPUT**
> \Swagger\Client\Model\UserInfo updatePasswordUsingPUT($user_name, $password)

Update user password. The password is expected to be plain text. Only passwords for local users can be changed.

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

$apiInstance = new Swagger\Client\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$user_name = "user_name_example"; // string | The name of the user, whose password should be changed.
$password = "password_example"; // string | password

try {
    $result = $apiInstance->updatePasswordUsingPUT($user_name, $password);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->updatePasswordUsingPUT: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **user_name** | **string**| The name of the user, whose password should be changed. |
 **password** | **string**| password |

### Return type

[**\Swagger\Client\Model\UserInfo**](../Model/UserInfo.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **updateUserUsingPUT**
> \Swagger\Client\Model\UserInfo updateUserUsingPUT($user)

Update user ui settings and permissions. Non-admin user are not allowed to add api roles or channels unknown to the creating user. Use dedicated api, if you want to change the password.

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

$apiInstance = new Swagger\Client\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$user = new \Swagger\Client\Model\UserUpdate(); // \Swagger\Client\Model\UserUpdate | user

try {
    $result = $apiInstance->updateUserUsingPUT($user);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->updateUserUsingPUT: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **user** | [**\Swagger\Client\Model\UserUpdate**](../Model/UserUpdate.md)| user |

### Return type

[**\Swagger\Client\Model\UserInfo**](../Model/UserInfo.md)

### Authorization

[basicAuth](../../README.md#basicAuth), [oAuth2](../../README.md#oAuth2)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

