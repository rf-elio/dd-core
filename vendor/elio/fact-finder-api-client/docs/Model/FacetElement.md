# FacetElement

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**absolute_max_value** | **double** | The absolute maximum value for the overall slider range. | [optional] 
**absolute_min_value** | **double** | The absolute minimum value for the overall slider range. | [optional] 
**cluster_level** | **int** | Level in the cluster hierarchy. Corresponding subcategories have a higher (deeper) level. | 
**distance** | **double** | The distance between the location of the search and the market location associated with this element. | [optional] 
**preview_image_url** | **string** | URL to the preview image to be displayed with the element. | [optional] 
**search_params** | [**\Swagger\Client\Model\SearchParams**](SearchParams.md) | Defines the search that should be executed when the element is clicked. | [optional] 
**selected** | **string** | TRUE, if the element is currently selected, IMPLICIT, if the selection is implicit, IRRELEVANT means, that the element has been selected by the user, but does not match any record of the search result, otherwise FALSE. | 
**selected_max_value** | **double** | The maximum value of the currently selected slider range. | [optional] 
**selected_min_value** | **double** | The minimum value of the currently selected slider range. | [optional] 
**show_distance** | **bool** | If &#39;true&#39;, the distance should be added to the element name label by frontend. | [optional] 
**text** | **string** | The text to be displayed to the user. | [optional] 
**total_hits** | **int** | The number of products that the search result should contain when this facet element is selected. | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


