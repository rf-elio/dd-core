# CHANGELOG.md


## 3.0.0 - 2023-07-26
### Features (1 change)
- Replaced non-existent or deprecated classes/methods in the Shopware version 6.5

## 2.3.0 - 2023-07-20
### Features (1 change)
- FactFinder main variant mapping for product listing
- Filter restrictions are applied in facet transformer
- Filter restrictions in administration: js error is fixed
- Filter panel is fixed: filters are replaced with filters from response, for opened filters only dropdown is replaced
- Cagegory tree filter is fixed
- Mapped properties in product export generator are fixed

## 2.2.8 - 2022-10-12
### Features (1 change)
- Ranking percentage values for products based on order count and value added
- Tracking parameter "pos" in products listing is corrected

## 2.2.7 - 2022-09-28
### Fix (7 change)
- Product export filter now removes line breaks
- Disabled sort options are removed from sorting collection
- Export date format is now in iso format instead of custom format
- Default filter type uses now the AssociatedFieldName for filter requests
- Listing count on search result page is now updated after filter select
- Tracking parameters changed (product number and master product number).
- Tracking session id keeps now the same after login

## 2.2.6 - 2022-09-16
### Fix (1 change)
- API filter prepare method for category path is no longer using the prepareFilterValue method to escape spaces.

## 2.2.5 - 2022-08-31
### Bugfixes (1 change)
- Category version id added to filter restriction table

### Fix (1 change)
- PHPStan corrections in ProductExportGenerator and CSVFileWriter

## 2.2.4 - 2022-08-10
### Features (3 changes)
- Export now supports basic auth
- Export cleanup for line break and backspace added
- Export date format changed to Y-m-d\TH:i:sp

## 2.2.3 - 2022-08-01
### Fix (1 changes)
- Export file name shortened to be able to use azure blob storage

## 2.2.2 - 2022-07-29
### Features (1 changes)
- New fields to export added to optimize rating

## 2.2.1 - 2022-06-30
### Bugfixes (1 changes)
- Manufacturer was missing in product listings. Association was added in ProductTransformer.

## 2.2.0 - 2022-06-09
### Features (1 changes)
- Dependencies updated to add variant support and to use same guzzle client version as shopware

## 2.1.1 - 2022-05-13
### Features (2 changes)
- Top elements in product and content search result
- Custom search query in categories

### Fix (1 change)
- Categories with product stream aren't longer loaded via ff
- Category names can now have spaces


## Template
### Security (x changes)
- changed...

### Features (x changes)
- changed...

### Fix (x changes)
- changed...

### Bugfixes (x changes)
- changed...
