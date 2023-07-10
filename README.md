# CyberSource OroCommerce Integration

OroCyberSourceBundle adds the [CyberSource](https://www.cybersource.com) integration to OroCommerce applications.

Installation
---------
To install extension for OroCommerce 5.0.x version need to add forked Cybersource REST client repository into composer:
```
composer config repositories.cybersource-rest-client-php vcs https://github.com/oroinc/cybersource-rest-client-php
```

Cybersource REST client repository was forked because new versions (>0.0.37) do not support Flex API v.1 which is using by this extension, and old versions (<=0.0.37) have conflicts with OroCommerce 5.0 dependencies.

Extension can be installed using the following composer command:
```
composer require oro/commerce-cybersource:5.0
```


Additional details about installation and configuration instructions, please see [online documentation on CyberSource extension](https://doc.oroinc.com/backend/extend-commerce/payment/cybersource/).

Resources
---------

* [CyberSource Developer Center](https://developer.cybersource.com/)
* [OroCommerce Documentation](https://doc.oroinc.com)
* [Contributing](https://doc.oroinc.com/community/contribute/)



