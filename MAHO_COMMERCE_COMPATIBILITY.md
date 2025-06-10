# Maho Commerce Compatibility

This fork includes modifications to make the Algolia Search extension compatible with Maho Commerce.

## Changes Made

### 1. Helper Class Compatibility
- Modified `app/code/community/Algolia/Algoliasearch/Helper/Entity/Helper.php` to extend from `Mage_Core_Helper_Abstract`
- This is required because Maho Commerce enforces strict type checking for helper classes

## Installation Instructions

### 1. Install via Composer

Add this repository to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/trabulium/algoliasearch-mahocommerce"
        }
    ],
    "require": {
        "algolia/algoliasearch-magento": "dev-maho-compatibility"
    }
}
```

Then run:
```bash
composer require algolia/algoliasearch-magento:dev-maho-compatibility
```

### 2. CRITICAL: Regenerate Autoloader

After installation, you MUST regenerate the autoloader with the Maho plugin:

```bash
COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload
```

This step is essential because the Maho composer plugin needs to regenerate the autoloader with proper inclusion of bootstrap.php and all namespace mappings.

### 3. Create Required Symlink

The Algolia library needs to be accessible from the `lib` directory:

```bash
mkdir -p lib
ln -s vendor/algolia/algoliasearch-magento/lib/AlgoliaSearch lib/AlgoliaSearch
```

### 4. Copy Module Declaration

```bash
cp vendor/algolia/algoliasearch-magento/app/etc/modules/Algolia_Algoliasearch.xml app/etc/modules/
```

### 5. Clear Cache

```bash
php maho cache:flush
```

## Troubleshooting

### Error: "Failed opening required '/path/to/lib/AlgoliaSearch/loader.php'"
- Ensure you've created the symlink as described in step 3

### Error: "Return value must be of type Mage_Core_Helper_Abstract"
- This fork should resolve this issue. If you still see it, ensure you're on the `maho-compatibility` branch

### Error: "Undefined constant BP"
- Run the composer dump-autoload command from step 2