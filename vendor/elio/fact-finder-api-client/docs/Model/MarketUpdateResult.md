# MarketUpdateResult

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**error** | [**\Swagger\Client\Model\ErrorDescription**](ErrorDescription.md) | A description of the error in case of failure. The property is present if and only if success&#x3D;false. | [optional] 
**record** | **map[string,object]** | The original input of the operation (will only be transmitted when the query parameter verbose&#x3D;true was added to the request). | [optional] 
**success** | **bool** | If true, the operation succeeded. Otherwise an error occurred which will be described in the error field. | 
**warnings** | [**\Swagger\Client\Model\ErrorDescription[]**](ErrorDescription.md) | A list of all warnings. | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


