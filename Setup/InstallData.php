<?php

// For creating integration

namespace Comm100\LiveChat\Setup;

use Comm100\LiveChat\Helper\Constants;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ResourceConnection;

class InstallData implements InstallDataInterface
{
    /**
     * Curl Adapter.
     *
     * @var Curl
     */
    protected $_curlAdapter;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ConfigBasedIntegrationManager
     */
    private $integrationManager;

    /**
     * @var ProductMetadataInterface
     */
    private $_productMetadata;

    /**
     * @param StoreManagerInterface $_storeManager
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Json Helper.
     *
     * @var Json
     */
    protected $_jsonHelper;

    /**
     * Resource Connection.
     *
     * @var ResourceConnection
     */
    protected $_resource;

    public function __construct(
        ConfigBasedIntegrationManager $integrationManager,
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetadata,
        LoggerInterface $logger,
        Curl $curlAdapter,
        Json $jsonHelper,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resource
    ) {
        $this->_logger = $logger;
        $this->integrationManager = $integrationManager;
        $this->_storeManager = $storeManager;
        $this->_productMetadata = $productMetadata;
        $this->_curlAdapter = $curlAdapter;
        $this->_scopeConfig = $scopeConfig;
        $this->_jsonHelper = $jsonHelper;
        $this->_resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $access_token = null;
        $oauth_token_secret = null;

        $this->integrationManager->processIntegrationConfig([
            Constants::INTEGRATION_NAME,
        ]);

        // This is the MagentoBaseURL
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();

        // Get Email From the admin db to get the first admin user with email id.
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('admin_user');
        $select = $connection
            ->select()
            ->from($tableName, 'email')
            ->where('email != "" AND email IS NOT NULL');
        $parentTableName = $this->_resource->getTableName(
            Constants::PARENT_TABLE_NAME
        );
        $magentoAccountEmail = $connection->fetchOne($select) ?? '';
        $comm100TableData = [
            "MagentoBaseURL" => $baseUrl,
            "MagentoAccountEmail" => $magentoAccountEmail,
            "MagentoAppBaseURL" => Constants::MAGENTO_APP_BASE_URL,
			"MagentoAPIBaseURL" =>Constants::MAGENTO_API_BASE_URL,
            "MagentoVersion" => $this->_productMetadata->getVersion()
        ];
        $setup
            ->getConnection()
            ->insert($parentTableName, $comm100TableData);

        // Call the Magento App API for sending the base url and installation success status.
      //  $this->sendInstallationSuccess($baseUrl, $magentoAccountEmail);
        $setup->endSetup();
    }

    /**
     * Method to send the installation success to the magento app.
     * @param string $baseUrl
     * @param string $magentoAccountEmail
     */
    protected function sendInstallationSuccess($baseUrl, $magentoAccountEmail)
    {
        $headers = ['Content-Type: application/json'];
        $body = [
            'baseUrl' => $baseUrl,
            'magentoAccountEmail' => $magentoAccountEmail,
            'isInstalled' => true,
            'magentoVersion' => $this->_productMetadata->getVersion(),
        ];
        $bodyJson = $this->_jsonHelper->serialize($body);
        $this->_curlAdapter->write(
            'POST',
            Constants::MAGENTO_APP_INSTALL_API_URL,
            '1.1',
            $headers,
            $bodyJson
        );
        $response = $this->_curlAdapter->read();
        if (!$response) {
            $this->_logger->debug(
                'C100 : Install API to magento app send fail ::' .
                    $this->_curlAdapter->getError()
            );
        } else {
            $this->_logger->debug(
                'C100 : Install API to magento app Success' . $response
            );
        }
        $this->_curlAdapter->close();
    }
}
