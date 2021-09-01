# sw6-ElioFactFinder


# Components
## Exports
### Profiles

### Extensions

### Usage
Exports can be generated using the following console command. The command offers a way to automatically
generate due exports or to enforce the generation of a specific or all exports.

Commands:
*  `bin/console elio-ff:export:generate`: Generates all due exports
*  `bin/console elio-ff:export:generate {id}`: Generates a specific export on due
*  `bin/console elio-ff:export:generate -f`: Refreshes all exports (interval ignored)
*  `bin/console elio-ff:export:generate {id} -f`: Refreshes a specific export (interval ignored)

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
   
## Exceptions
### FactFinderException
All exceptions should inherit from the FactFinderException to have a marker that this plugin cause the exception. 
Further more offers our FactFinderException an easy way to generate exception messages without using sprint all the 
time.

Usage:

```php
class MyException extends FactFinderException
{
    public function __construct(object $is, string $should)
    {
        parent::__construct(
            'Message {{ placeholder }} got {{ someOtherPlaceholder }}.',
            ['placeholder' => 'value 1', 'someOtherPlaceholder' => '...']
        );
    }
}
```

### InvalidTypeException
Can be thrown if the given object type is not the excepted type.

Usage:
```php
throw new InvalidTypeException($message, TrackingMessage::class);
```

# Api
This plugin uses the ff rest api with an auto generated api client.
OpenApi documentation: https://ng-demo.fact-finder.de/fact-finder/swagger-ui.html
