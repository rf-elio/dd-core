# SearchLogEvent

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**additional_info** | **string** | Additional information that should be logged. | [optional] 
**custom_sorting** | **bool** | Set to true, if the search result was sorted using a custom sorting order, otherwise false. | [optional] 
**filters** | [**\Swagger\Client\Model\Filter[]**](Filter.md) | The filters active in the search result. | 
**hit_count** | **int** | The total number of products in the search result. | 
**id** | **string** | The ID of the product. | 
**master_id** | **string** | Contains the master ID, if the article is a variant and &#39;ID&#39; refers to the variant. | [optional] 
**max_score** | **int** | The score of the first product in the result. | 
**min_score** | **int** | The score of the last product in the result. | 
**page** | **int** | The page number delivered by the search result. | 
**page_size** | **int** | The maximum number of products on a page. | 
**purchaser_id** | **string** | The ID for customer specific pricing. | [optional] 
**query** | **string** | The search term that produced the search result. | 
**search_field** | **string** | Contains the name of the search field, if the search was performed on a specific field. | [optional] 
**search_time** | **int** | The time required to produce the results (in ms). | 
**sid** | **string** | The session ID. | 
**title** | **string** | The title of the product. | [optional] 
**user_id** | **string** | The ID of the user who issued the request. | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


