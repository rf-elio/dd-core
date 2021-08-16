# ResultSuggestion

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**attributes** | **map[string,object]** | Contains additional information for the suggestion. Keys give the names of the attributes, with corresponding values. | 
**hit_count** | **int** | The number of products that should be found when this suggestion is selected for a search. | 
**image** | **string** | The URL of the image to be displayed to the user. | [optional] 
**name** | **string** | The name for the Suggest Entry that should be displayed to the user. | 
**score** | **double** | Defines how well the suggestion matches the query. | [optional] 
**search_params** | [**\Swagger\Client\Model\SearchParams**](SearchParams.md) | Defines the search that should be executed when clicking on Suggest entry. | [optional] 
**type** | **string** | The suggestion type. | 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


