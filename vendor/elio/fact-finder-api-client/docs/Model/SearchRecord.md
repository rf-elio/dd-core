# SearchRecord

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**found_words** | **string[]** | The words that caused this record to be part of the result. | 
**id** | **string** | The ID of the record. | 
**master_values** | **map[string,object]** | Contains all fields in the record, with a string representation of the respective values. | 
**position** | **int** | The position of the record in the search results (starting with 0). | 
**score** | **float** | Defines how well the record matches the search term. | 
**variant_values** | [**\Swagger\Client\Model\VariantValues[]**](VariantValues.md) | Contains variants. The values are mapped from field names to the field value. | 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


