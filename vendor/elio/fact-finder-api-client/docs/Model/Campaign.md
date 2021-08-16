# Campaign

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**active_questions** | [**\Swagger\Client\Model\Question[]**](Question.md) | The currently active questions to be shown to the user. These questions do not need to be the root questions, in case the user has already answered a question. | [optional] 
**advisor_tree** | [**\Swagger\Client\Model\Question[]**](Question.md) | The advisor root questions associated with this campaign. | 
**category** | **string** | The category of the campaign. May be empty. | 
**exclude_products_not_in_markets** | **bool** | The setting which decides whether pushed products should be excluded if they are not mapped to any selected market. | [optional] 
**exclude_products_not_in_range** | **bool** | The setting which decides whether pushed products should be excluded if they are not close enough to the search location. | [optional] 
**feedback_texts** | [**\Swagger\Client\Model\FeedbackText[]**](FeedbackText.md) | The feedback text lines that will be displayed to the user. | [optional] 
**flavour** | **string** | The kind of the campaign. | 
**hits** | [**\Swagger\Client\Model\TypedFlatRecord[]**](TypedFlatRecord.md) | The records associated with the campaign, if it should push products. | [optional] 
**id** | **string** | The ID of the campaign. | [optional] 
**name** | **string** | The name of the campaign. | [optional] 
**target** | [**\Swagger\Client\Model\Target**](Target.md) | The redirect target. | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


