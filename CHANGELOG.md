# CHANGELOG.md
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