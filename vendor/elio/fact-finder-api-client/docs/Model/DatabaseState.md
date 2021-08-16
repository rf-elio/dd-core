# DatabaseState

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**database_version** | **int** | The version of the current worldmatch database. If the databaseVersion of a worker is less than the databaseVersion of the director, the worker needs to reload the whole worldmatch database in order to synchronize itself with the director. | [optional] 
**delta_error_count** | **int** | The number of errors (rejected delta updates) which occurred while trying to synchronize worker and director. Reloading the worldmatch database resets this counter to zero. | [optional] 
**delta_version** | **int** | The number of delta updates applied to the current worldmatch database. If the deltaVersion of a worker is less than the deltaVersion of the directory, but the databaseVersions are equal, applying the missing delta updates to the worker is sufficient to  synchronize worker and director. | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


