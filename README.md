# Radial Magento2 Fraud Insight Connector

The Radial Fraud Insight Extension is upgraded to be **compatible with Magento 2** and enabled automatic high to low fraud transaction detection.

- Automated integration and on-boarding
- Flexible configuration


## How to get Fraud Insight API Credentials

Please contact sales@radial.com to get Fraud Insight API Hostname, API Key and Store ID in order to configure your extension.

## Installation

### Install using composer via command line (**_recommended_**)
1. Login to Magento web server with a user who has permissions to write to the Magento file system.
2. Go to Magento root directory
```
cd /var/www/magento2
```
3. Disable the cache
```
php bin/magento cache:disable
```
4. Add the reference to the `git` repository
```
composer config repositories vcs https://github.com/RadialCorp/magento2-fraud-insight.git
```
5. Update the dependency in `composer.json`
```
composer require radial/magento2-fraud-insight:dev-master
```
6. Run the setup script
```
php bin/magento setup:upgrade
```
7. Login to Magento admin panel and confirm extension is installed.

---
Copyright &copy; 2016 Radial, Inc.
