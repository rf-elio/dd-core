# CHANGELOG.md
## 6.6.13 - 2025-06-04
### Fix (2 changes)
- Optimized `IndexUpdateCommand` performance by implementing batch reading of EntityStatus entities from the DB
- Fixed `SyncDataCommand` RAM leak by implementing batch reading of EntityStatus entities from the DB

## 6.6.12 - 2025-06-03
- Cache key generation in `CachedFilterService` passes `SalesChannelId` and `LangugeId` as separate array elements

## 6.6.11 - 2025-06-02
### Feature (8 changes)
- Filtering in the suggest is now allowed:
  - `SuggestRequest` can now hold filters
  - Added `SuggestRequestBuilder` and `SuggestRequestBuildEvent`
- Added `AbstractSuggestProductTransformer` to add products to suggest items
- Added `ProductCriteriaBaseEvent` that holds criteria for collecting products and product numbers
- Added `SuggestProductCollectCriteriaEvent` in `AbstractSuggestProductTransformer`
- Added `ProductListingCriteriaEvent` to `AbstractRecommendationProductTransformer` and injected `ProductListingLoader`
- `useLegacyLocale` configuration option to switch between old (`de`) and new (`de-DE`) locale
- Added `StringUtil` to handle encoding and decoding of property names

### Fix (10 changes)
- Fixed cache key generation in `CachedFilterService`
- Special characters are now removed from facet names and values in template element attributes
- Moved `encodePropertyName` method to `StringUtil`
- `ConfigurationResponse` now has a `collection` attribute
- `ConfigurationController` now excludes product comparison sales channels and iterates over sales channels to send requests
- Renamed Filter & Sorting configuration card labels when in category view and fixed width of sales channel select
- Added `active`, `useSearch` and `useListing` global twig variables, which are checked in cross selling and listing extension JS plugins when initializing
- Fixed auto-complete suggest feature blocking navigation via tab key
- `ProductListingCriteriaEvent` now extends from `ProductCriteriaBaseEvent` and renamed `getMainNumbers` method to `getProductNumbers`
- Fixed configuration service injection to always use the interface

## 6.6.10 - 2025-05-23
### Feature (1 change)
- `ProductDataType` now holds an array of visibilities per sales channel

## 6.6.9 - 2025-05-22
### Feature (4 changes)
- `AbstractProductTransformer`: Added new method that dispatches a `ProductExtensionsLoadedEvent`
- Added `FeatureActive` twig function to check if a plugin feature is active
- Added `CustomPriceItem` struct
- Added `encodePropertyName` in `ProductUtil` method to handle properties with special characters that could cause issues

### Fix (5 changes)
- Added constants for plugin features in base plugin
- Added checks for the `product.recommendation` feature so that certain code is only executed if it's active to prevent errors
- Remove obsolete `getProductAttribute` method in `ProductUtil`
- `cms-block-cross-selling`: Added check for recommendation feature, removed template include with empty array and renamed `crossSellings` variable to `crossSellingTypes`
- Cleaned up obsolete use statements

## 6.6.8 - 2025-03-31
### Fix (1 change)
- Update plugin logo

## 6.6.7 - 2025-03-21
### Feature (1 change)
- Compatibility with Shopware 6.6.10
- `elio-product-detail-cross-selling.plugin.js`: `_loadSliders` method now emits a `elioDataDiscoveryCrossSelling/slidersLoaded` event

## 6.6.6 - 2025-03-20
### Feature (2 changes)
- Added `syncProfile` feature flag for when the sync is handled by a connector plugin
- Sync Extension and sync related commands are now disabled in the administration if `syncProfile` feature flag is disabled

### Fix (3 changes)
- Fixed the suggest auto-complete feature by accessing innerHTML attribute instead of calling non-existent getInnerHTML() function
- Add `ProductListingCriteriaManipulationEvent` to the `AbstractProductTranformer` in case the criteria have to modified before products are loaded
- Replace settings logo in extensions with old logo

## 6.6.5 - 2025-03-05
### Fix (2 changes)
- `AbstractRecommendationProductTransformer`:
  - Replaced `ProductListingLoader` with `SalesChannelRepository` to load products in order to correctly handle variants
  - Added additional check if the product number exists in the mapped productNumber-type array while grouping products to types

## 6.6.4 - 2025-02-13
### Fix (3 changes)
- Added missing return in `ProductListingLoaderDecorator` which prevented falling back to the original service
- Replaced `form-check` class selector for multi-select filter items with `edd-filter-form-check` selector for styling
- `search.html.twig`: Added `searchWidgetMinChars` option to use Shopware's `Minimal search term length` setting in the suggest

## 6.6.3 - 2025-01-24
### Features (3 changes)
- Compatibility with Shopware 6.6.8 and 6.6.9
- Resolving Categories from Product Streams:
  - Added `resolveCategoriesFromProductStream` config setting to allow resolving categories from product streams for both sync and listing, when products are assigned to categories via streams
  - Updated `ProductCollector` and `ElioDataDiscoveryProductListingRoute` to implement the config setting

### Fix (8 changes)
- Replaced Language selection in Sync Profile with Sales Channel Domain selection:
  - Added Migration for new `elio_data_discovery_sync_profile_domain` table
  - Updated `SyncProfileEntity` and `SyncProfileDefinition` to replace language references with SalesChannelDomain
  - Replaced `LanguageExtension`, `NoLanguagesInSyncConfiguredException` and `SyncProfileLanguageMapping` with respective `SalesChannelDomain` files and updated references
  - `SyncService`: Updated to use SalesChannelDomain instead of Languages
  - Updated Sync Profile detail/list administration components and detail page template
  - `SeoRouteOutput`: Builds base url from SalesChannelDomain now
- Removed `getSyncProfileEntity` function and replaced references with `getSyncProfileConfiguration`
- Added `IndexUpdateSubscriber`, which disables other subscribers that listen to the `sales_channel.product.loaded` and `product.loaded` events during execution of the `index:update` command

## 6.6.2 - 2025-01-15
### Features (4 changes)
- Added Interrupters:
  - `InterrupterItem`: holds the interrupter data
  - `InterrupterResponse`: holds the `InterrupterItems`
  - `SeoResolver`: replaces the itemId of product interrupters with the actual product ID
  - Updates dependency injection, `listing.html.twig` template and snippets

### Fix (3 changes)
- `SuggestRequest`: initialized `type` with null to fix broken suggest if `suggestToggleProductType` config is `false`
- Added `navigationStartLevelFilter` config setting to determine the level from which category paths are filtered in listing route
- `ProductUtil`: Added `getProductProperty` function to support multi value properties

## 6.6.1 - 2024-11-20
### Fix (3 changes)
- `ProductCollector`: ratingCount is now set directly from the product's `translated` custom field
- `AvailableStockAware`: fixed injection of `ProductCloseoutFilterFactory` to use the abstract class instead
- `ProductRedirectSearchApi`: fixed PHPDoc and use statement

## 6.6.0 - 2024-11-14
### Fix (10 changes)
- ProductListingLoaderDecorator: Compatibility with Shopware restored after sw 6.4.0 update.
- Custom field "content_export_type_inherited" is now correctly inherited from parent categories including cleanup.
- Not used plugin configuration "useCategoryFilterLists" removed
- Deprecation warnings in indexing removed
    - ContentDataType base class changed from Struct to Entity to avoid the generation dynamic fields (_uniqueIdentifier)
    - ProductDataType base class changed from ProductEntity to SalesChannelProductEntity to avoid the generation dynamic fields (cheapestPrice, ...)
- Full update in command "elio-data-discovery:profiles:sync" is not forcing an execution anymore
- Fixed `elio-listing-extension` plugin not being registered correctly
- Unchecking an option in a multiselect filter will not uncheck all selected options anymore
- Suggest count is calculated from a new `found` attribute instead of counting all visible suggest items

## 6.0.4 - 2024-10-31
### Features (1 change)
- Added `listingExclusionExpression` config setting: this setting allows developers to enter an expression. This expression can be used to disable the Data Discovery listing on specific pages, e.g. to ensure compatibility with third party plugins

### Fix (1 change)
- The listing template now checks if the sidebar-filter ID is empty before adding to the slots

## 2.2.0 - 2024-03-18
### Fix (1 change)
- Naming adjusted to match the new plugin name.


## Template
### Security (x changes)
- changed...

### Features (x changes)
- changed...

### Fix (x changes)
- changed...

### Bugfixes (x changes)
- changed...
