# sw6-ElioFactFinder


# Components
## Bot protection
*  IP-Address: Blocked ip addresses can be configured in the plugin settings. Request from those addresses will be blocked.
*  User-Agents: Blocked user agents can be configured in the plugin settings. Request that contain those agents will be blocked.
*  Search-Terms: Blocked search terms can be configured in the plugin settings. Requests with a not allowed search term will be blocked.
*  Bad-User-Agent-List: The bot protection is using a predefined list of possible bad bot 
   (https://github.com/mitchellkrogza/apache-ultimate-bad-bot-blocker/blob/master/_generator_lists/bad-user-agents.list).
   If the user agent matches one entry in the list, the request will be blocked.
   
### Update the "Bad-User-Agent-List"
*  The latest list can be downloaded from: https://github.com/mitchellkrogza/apache-ultimate-bad-bot-blocker/blob/master/_generator_lists/bad-user-agents.list
*  The file must be places in Resources/files/bot-list.txt
*  The file should not contain empty lines

### Events
*  **BotDetectionEvent**: This event can be used to inject custom detection components.
```php
function onBotDetectionEvent(BotDetectionEvent $event) {
    $event->setDetected();
}
```
*  **BotDetectedEvent**: This event is dispatched after all detections have been executed. Extensions can use this to
change the detection state.

## Exports
### Profiles
todo

### Extensions
todo

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