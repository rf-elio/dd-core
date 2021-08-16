# NavigationParams

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**active_ab_tests** | **map[string,string]** | The active ab tests variants. | [optional] 
**advisor_status** | [**\Swagger\Client\Model\AdvisorCampaignStatusHolder**](AdvisorCampaignStatusHolder.md) | Describes the advisor campaign that is currently active. | [optional] 
**channel** | **string** | The channel in which the search should be performed. | 
**custom_parameters** | [**\Swagger\Client\Model\CustomParameter[]**](CustomParameter.md) | May be used to provide custom parameters, such as for custom classes. | [optional] 
**exclude_products_not_in_range** | **bool** | Overrides the excludeProductsNotInRange setting for the geo search. | [optional] 
**filters** | [**\Swagger\Client\Model\Filter[]**](Filter.md) | The filters to limit the search result. | [optional] 
**follow_search** | **string** | Optional request linking param from a previous search result or search param object. Can improve request performance. | [optional] 
**hits_per_page** | **int** | Number of products on a single page. | [optional] 
**location** | [**\Swagger\Client\Model\Location**](Location.md) | The location of the search user. | [optional] 
**market_ids** | **string[]** | Only show products that have values for these market IDs. | [optional] 
**max_count_variants** | **int** | Defines the maximum number of variants to be returned in the result. | [optional] 
**max_distance** | **double** | Overrides the maximum distance setting for the geo search. | [optional] 
**page** | **int** | The page to be requested within the search result. | [optional] 
**purchaser_id** | **string** | The ID of the purchaser. This ID is only needed if the &#39;customer specific pricing&#39; module is activated. Otherwise it will be ignored. | [optional] 
**sort_items** | [**\Swagger\Client\Model\SortItem[]**](SortItem.md) | Specifies the sort order for the search result. | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


