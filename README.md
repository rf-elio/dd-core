# sw6-ElioFactFinder

# Components
## Configuration Service
The ff configuration service **FactFinderConfigService** should be used to access the ff plugin configuration at all
places. FactFinderConfigService enforces to provide a sales channel id to make the plugin able to work with multiple 
sales channels.

This service provides in addition an object orientated way to access any config field to make it easier to find usages
of our configuration. The service includes a runtime cache to avoid excessive calls of shopware's
configuration service.

### Events
*  **ConfigurationLoadedEvent**: Provides the ability to manipulate or extend the configuration.

### Service decoration
*  **FactFinderConfigService**: FactFinderConfigService can be decorated to provide support for third party credential
providers.
   
## Api
This plugin uses the ff rest api with an auto generated api client.

OpenApi documentation: https://ng-demo.fact-finder.de/fact-finder/swagger-ui.html
