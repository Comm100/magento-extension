<?php

namespace Comm100\LiveChat\Setup;

use Comm100\LiveChat\Helper\Constants;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Comm100\LiveChat\Model\Comm100LiveChatFactory;
use Magento\Framework\HTTP\Adapter\Curl;
use Psr\Log\LoggerInterface;

class Uninstall implements UninstallInterface
{
    /**
     * Comm100LiveChatFactory factory.
     *
     * @var Comm100LiveChatFactory
     */
    protected $_comm100LiveChatFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    public function __construct(
        Curl $curlAdapter,
        LoggerInterface $logger,
        Comm100LiveChatFactory $comm100LiveChatFactory
    ) {
        $this->_curlAdapter = $curlAdapter;
        $this->_comm100LiveChatFactory = $comm100LiveChatFactory;
        $this->_logger = $logger;
    }

    /**
     * Un installs DB schema for a module.
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $this->uninstallFromMagentoApp();

        $installer
            ->getConnection()
            ->dropTable($installer->getTable(Constants::CHILD_TABLE_NAME));
        $installer
            ->getConnection()
            ->dropTable($installer->getTable(Constants::PARENT_TABLE_NAME));
        $installer
            ->getConnection()
            ->delete($installer->getTable('integration'), 'name = "' . Constants::INTEGRATION_NAME . '"');
        $installer
            ->getConnection()
            ->delete($installer->getTable('setup_module'), 'module = "Comm100_LiveChat"');

        $installer->endSetup();
    }

    /**
     * Method to get the Comm100_LiveChat db details.
     * @return mixed
     */
    private function getComm100MagentoDbDetails()
    {
        $comm100LiveChatObj = $this->_comm100LiveChatFactory->create();
        $collection = $comm100LiveChatObj->getCollection();
        $magentoDbDetails = $collection->getFirstItem();
        return $magentoDbDetails;
    }

    /**
     * Method to uninstalling the user from the magento app while uninstalling extension.
     */
    protected function uninstallFromMagentoApp()
    {
        $comm100LiveChatData = $this->getComm100MagentoDbDetails();
        $comm100AccessToken = $comm100LiveChatData['Comm100AccessToken'];
        $baseUrl = urlencode($comm100LiveChatData['MagentoBaseURL']);
        $magentoAppBaseUrl = $comm100LiveChatData["MagentoAppBaseURL"];
        if ($magentoAppBaseUrl == null || $magentoAppBaseUrl == "") {
            $magentoAppBaseUrl = Constants::MAGENTO_APP_BASE_URL;
        }


        $this->_logger->debug('C100 : comm100AccessToken: ' . $comm100AccessToken);
        $headers = [
            'Content-Type: application/json',
            'token: ' . $comm100AccessToken,
        ];

        $this->_curlAdapter->write(
            'GET',
            sprintf($magentoAppBaseUrl . Constants::UNINSTALL, $baseUrl),
            '1.1',
            $headers
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
