# CHANGELOG.md

## 3.0.0 - 2023-05-25
### Features (1 change)
- Replaced non-existent or deprecated classes/methods in the Shopware version 6.5

## 2.2.7 - 2022-09-21
### Fix (1 change)
- Product export filter now removes line breaks

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
