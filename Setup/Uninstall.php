<?php

namespace Mageplaza\HelloWorld\Setup;

use Comm100\LiveChat\Helper\Constants;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Comm100\LiveChat\Model\Comm100LiveChatFactory;
use Magento\Framework\HTTP\Adapter\Curl;

class Uninstall implements UninstallInterface
{
    /**
     * Comm100LiveChatFactory factory.
     *
     * @var Comm100LiveChatFactory
     */
    protected $_comm100LiveChatFactory;

    public function __construct(
        Curl $curlAdapter,
        Comm100LiveChatFactory $comm100LiveChatFactory
    ) {
        $this->_curlAdapter = $curlAdapter;
        $this->_comm100LiveChatFactory = $comm100LiveChatFactory;
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

        $installer
            ->getConnection()
            ->dropTable($installer->getTable(Constants::CHILD_TABLE_NAME));

        $installer
            ->getConnection()
            ->dropTable($installer->getTable(Constants::PARENT_TABLE_NAME));
        $installer
            ->getConnection()
            ->delete('interation', 'name = ' + Constants::INTEGRATION_NAME);

        $this->uninstallFromMagentoApp();
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
     * Method to uninstalling the extension
     */
    protected function uninstallFromMagentoApp()
    {
        $comm100LiveChatData = $this->getComm100MagentoDbDetails();
        $token = $comm100LiveChatData['Comm100AccessToken'];
        $baseUrl = urlencode($comm100LiveChatData['MagentoBaseURL']);
        $magentoAppBaseUrl = $comm100LiveChatData["MagentoAPIBaseURL"];
        if($magentoAppBaseUrl == null || $magentoAppBaseUrl == ""){
            $magentoAppBaseUrl = Constants::MAGENTO_API_BASE_URL;
        }

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer' . $token,
        ];

        $this->_curlAdapter->write(
            'GET',
            sprintf($magentoAppBaseUrl.Constants::UNINSTALL, $baseUrl,$token),
            '1.1',
            $headers
        );
        $this->_curlAdapter->read();
        $this->_curlAdapter->close();
    }
}
