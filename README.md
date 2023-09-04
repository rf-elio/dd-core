# sw6-ElioSearch
# Installation
## Commands
The following commands must be configured to execute required background tasks
*  `bin/console elio-search:export:generate`: Executes the exports. This should be executed every 5 Minutes.

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
### Usage
Exports can be generated using the following console command. The command offers a way to automatically
generate due exports or to enforce the generation of a specific or all exports.

Admin:
New Export profiles can be configured in the shopware administration. To execute these exports the following commands
must be used.

Commands:
*  `bin/console elio-search:export:generate`: Generates all due exports
*  `bin/console elio-search:export:generate {id}`: Generates a specific export on due
*  `bin/console elio-search:export:generate -f`: Refreshes all exports (interval ignored)
*  `bin/console elio-search:export:generate {id} -f`: Refreshes a specific export (interval ignored)

### Events
*  **FilterProductModelEvent**: Can be used to register additional fields that should be added to the product export.
*  **FilterProductExportItemPrepareEvent**: Can be used to add additional fields to the product export item


### Extensions
#### Generator
To add contents for suggest and content search provided by an additional plugin the generator must be registered with
the tag "elio-search.export.generator" to be executed during the export generation.

The generator must implement the **ExportGeneratorInterface**. The suggested structure can be found below.

```php

class MyGenerator implements ExportGeneratorInterface
{
    public const TYPE = 'product';
    public function supports(ExportEntity $export): bool
    {
        return $export->getType() === self::TYPE;
    }
    ...
```

Export generators will create for each item that should be added to the export a new **ExportItem** instance.
The **OutputStream** must be used to write the **ExportItem** to the export file.

```php
public function generate(ExportEntity $export, OutputStream $output, SalesChannelContext $context): void
{
    $item = new ExportItem();
    $item->set('MyExportField', ...);
    $output->write($item);
}
```

To resolve rewrite urls **SeoRoute** can be used. A seo resolver is present in the writer chain. Don't resolve the paths
in your own generator, the OutputStream is buffered and will resolve 100 path at once.

```php
$item->set('ProductURL', new SeoRoute(
    ProductPageSeoUrlRoute::ROUTE_NAME, $product->getId(), ['productId' => $product->getId()]
));
```


## Configuration Service
The elioSearch configuration service **ElioSearchConfigService** should be used to access the elioSearch plugin configuration at all
places. ElioSearchConfigService enforces to provide a sales channel id to make the plugin able to work with multiple
sales channels.

This service provides in addition an object orientated way to access any config field to make it easier to find usages
of our configuration. The service includes a runtime cache to avoid excessive calls of shopware's
configuration service.

### Events
*  **ConfigurationLoadedEvent**: Provides the ability to manipulate or extend the configuration.

### Service decoration
*  **ElioSearchConfigService**: ElioSearchConfigService can be decorated to provide support for third party credential
   providers.

## Exceptions
### ElioSearchException
All exceptions should inherit from the ElioSearchException to have a marker that this plugin cause the exception.
Further more offers our ElioSearchException an easy way to generate exception messages without using sprint all the
time.

Usage:

```php
class MyException extends ElioSearchException
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
This plugin uses the search rest api with an auto generated api client.
OpenApi documentation: https://ng-demo.fact-finder.de/fact-finder/swagger-ui.html
