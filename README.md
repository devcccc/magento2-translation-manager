# devcccc/magento2-translation-manager
Extension for Magento to do translation within the backend. You can translate text in the backend for all 
languages - no need to use CSV files or the database any longer. Any backend user will now be able to edit
translations.

## Installation

    composer require devcccc/magento2-translation-manager

    bin/magento setup:upgrade
    bin/magento setup:di:compile
    bin/magento setup:static-content:deploy
    bin/magento cache:clear


## User Access
Then set up users to be allowed to access the Translation Manager in backend:
assign "Magento_Backend::content_translation" ACL-Role
