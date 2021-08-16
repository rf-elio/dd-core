# DetailPage

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**campaigns** | [**\Swagger\Client\Model\Campaign[]**](Campaign.md) | Active campaigns for the product with the requested ID. | [optional] 
**field_roles** | **map[string,string]** | A field to role mapping. For example, a field role may be &#39;brand&#39;, meaning that the field contains the manufacturer&#39;s name. (key &#x3D; field role, value &#x3D; field name) | 
**recommendations** | [**\Swagger\Client\Model\RecommendationResult**](RecommendationResult.md) | Recommendations for the product with the requested ID. | [optional] 
**record** | [**\Swagger\Client\Model\RecordWithId**](RecordWithId.md) | Product record for the requested product ID. | [optional] 
**similar_products** | [**\Swagger\Client\Model\SimilarProducts**](SimilarProducts.md) | Products similar to the product with the requested ID. | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


