# CHANGELOG.md
## 6.6.3 - 2025-01-24
### Features (3 changes)
- Compatibility with Shopware 6.6.8 and 6.6.9
- Resolving Categories from Product Streams:
  - Added `resolveCategoriesFromProductStream` config setting to allow resolving categories from product streams for both sync and listing, when products are assigned to categories via streams
  - Updated `ProductCollector` and `ElioDataDiscoveryProductListingRoute` to implement the config setting

### Fix (7 changes)
- Replaced Language selection in Sync Profile with Sales Channel Domain selection:
  - Added Migration for new `elio_data_discovery_sync_profile_domain` table
  - Updated `SyncProfileEntity` and `SyncProfileDefinition` to replace language references with SalesChannelDomain
  - Replaced `LanguageExtension`, `NoLanguagesInSyncConfiguredException` and `SyncProfileLanguageMapping` with respective `SalesChannelDomain` files and updated references
  - `SyncService`: Updated to use SalesChannelDomain instead of Languages
  - Updated Sync Profile detail/list administration components and detail page template
  - `SeoRouteOutput`: Builds base url from SalesChannelDomain now
- Removed `getSyncProfileEntity` function and replaced references with `getSyncProfileConfiguration`

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