<?php

namespace Comm100\LiveChat\Helper;

/**
 * Class containing the application level constants.
 */
class Constants
{
    // Magento app  Urls related.
    const MAGENTO_APP_BASE_URL = 'https://dash11.comm100.io/magentoui';
    const MAGENTO_API_BASE_URL = 'https://api11.comm100.io/magentoapi';
    const MAGENTO_APP_INSTALL_API_URL =  Constants::MAGENTO_APP_BASE_URL.'/V1/install';

    const SAVE_VISITOR = "/api/saveVisitor";
    const WEBHOOK = "/V1/webhook/events";
    const DEFAULT_PAGE ='/magento/Index?baseURL=%1$s&consumerKey=%2$s&consumerSecret=%3$s&oAuthVerifier=%4$s&magentoAdminEmail=%5$s&token=%6$s&ip=%7$s&mver=%8$s';
    const UNINSTALL = '/api/MagentoApiIntegration/V1/uninstall?baseURL=%1$s&token=%2$s';

    // DB related.
    const PARENT_TABLE_NAME = 'Comm100_LiveChat';
    const CHILD_TABLE_NAME = 'Comm100_LiveChat_Stores_Campaigns';
    const INTEGRATION_NAME = 'Comm100 Live Chat';

    // Custom Variables related.
    const MAGENTO_CUSTOMER_ID = 'Magento_Customer_Id';
}
