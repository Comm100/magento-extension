<?php

namespace Comm100\LiveChat\Model\Api;

use Comm100\LiveChat\Helper\Constants;
use Comm100\LiveChat\Helper\CustomApiResponse;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Comm100\LiveChat\Model\Comm100LiveChatFactory;

class CustomApi
{
    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var ResourceConnection $_resource
     */
    protected $_resource;

    /**
     * Comm100LiveChat factory.
     *
     * @var Comm100LiveChatFactory
     */
    protected $_comm100LiveChat;

    public function __construct(
        LoggerInterface $logger,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreFactory $storeFactory,
        Comm100LiveChatFactory $comm100LiveChat
    ) {
        $this->_logger = $logger;
        $this->_resource = $resource;
        $this->_storeManager = $storeManager;
        $this->_storeFactory = $storeFactory;
        $this->_comm100LiveChat = $comm100LiveChat;
    }

    /**
     * {@inheritdoc}
     */
    public function activate($params)
    {
        $testUrl="https://postman-echo.com/delay/2";
        
        $accessToken = $params['accessToken'];
        $consumerKey = $params['consumerKey'];
        $consumerSecret = $params['consumerSecret'];
        $oAuthVerifier = $params['oAuthVerifier'];

        $customApiResponse = new CustomApiResponse();
        try {
            $connection = $this->_resource->getConnection();
            $tableName = $this->_resource->getTableName(
                Constants::PARENT_TABLE_NAME
            );
            $updateData = [
                "AccessToken" => $accessToken,
                "ConsumerKey" => $consumerKey,
                "ConsumerSecret" => $consumerSecret,
                "OAuthVerifier" => $oAuthVerifier
            ];
            $connection->update($tableName, $updateData);
            $customApiResponse->setApiResponse(true, 'Activation data saved.');
        } catch (\Exception $e) {
            $customApiResponse->setApiResponse(false, $e->getMessage());
            $this->_logger->info($e->getMessage());
        }

        header('Content-Type: application/json; charset=utf-8');
        $returnArray = json_encode($customApiResponse->getApiResponse());
        print_r($returnArray, false);
    }

    /**
     * {@inheritdoc}
     */
    public function connect($params)
    {
        $magentoAppBaseUrl = $params['magentoAppBaseURL'];
		$magentoApiBaseUrl = $params['magentoApiBaseURL'];
        $comm100AccessToken = $params['comm100AccessToken'];
        $comm100SiteId = $params['siteId'];
        $comm100EmailId = $params['comm100EmailId'];

        $customApiResponse = new CustomApiResponse();
        try {
            $connection = $this->_resource->getConnection();
            $tableName = $this->_resource->getTableName(
                Constants::PARENT_TABLE_NAME
            );
            $updateData = [
                "MagentoAppBaseURL" => $magentoAppBaseUrl,
				"MagentoAPIBaseURL" => $magentoApiBaseUrl,
                "Comm100AccessToken" => $comm100AccessToken,
                "Comm100SiteID" => $comm100SiteId,
                "Comm100AgentEmail" => $comm100EmailId,
            ];
            $connection->update($tableName, $updateData);
            $customApiResponse->setApiResponse(true, 'Connect Successfull.');
        } catch (\Exception $e) {
            $customApiResponse->setApiResponse(false, $e->getMessage());
            $this->_logger->info($e->getMessage());
        }

        header('Content-Type: application/json; charset=utf-8');
        $returnArray = json_encode($customApiResponse->getApiResponse());
        print_r($returnArray, false);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteStores()
    {
        $websites = $this->_storeManager->getWebsites();
        $customApiResponse = new CustomApiResponse();
        $storeCollection = $this->_storeFactory->create()->getCollection();
        $responseWebsites = [];
        foreach ($websites as $website) {
            $website = $website->getData();
            $website['stores'] = [];
            foreach ($storeCollection as $store) {
                if ($store['website_id'] == $website['website_id']) {
                    array_push($website['stores'], $store->getData());
                }
            }
            array_push($responseWebsites, $website);
        }

        $customApiResponse->setApiResponse(true, $responseWebsites);
        header('Content-Type: application/json; charset=utf-8');
        $returnArray = json_encode($customApiResponse->getApiResponse());
        print_r($returnArray, false);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoresAndCampaigns($params)
    {
        $customApiResponse = new CustomApiResponse();
        $connection = $this->_resource->getConnection();
        $connection->beginTransaction();
        try {
            $tableName = $this->_resource->getTableName(
                Constants::CHILD_TABLE_NAME
            );
            $connection->delete($tableName);
            // Insert all the new rows
            foreach ($params as $key => $value) {
                $comm100ChildTableData = [
                    "Comm100SiteID" => $value['comm100SiteId'],
                    "Comm100CampaignID" => $value['comm100CampaignId'],
                    "MagentoStoreId" => $value['magentoStoreId']
                ];
                $connection->insert($tableName, $comm100ChildTableData);
            }
            $connection->commit();
            $customApiResponse->setApiResponse(
                true,
                'Store added successfully.'
            );
        } catch (\Exception $e) {
            $connection->rollback();
            $customApiResponse->setApiResponse(false, $e->getMessage());
            $this->_logger->info('setStoresAndCampaigns' . $e->getMessage());
        }
        header('Content-Type: application/json; charset=utf-8');
        $returnArray = json_encode($customApiResponse->getApiResponse());
        print_r($returnArray, false);
    }

    /**
     * {@inheritdoc}
     */
    public function health()
    {
        // Webhook status.
        // Integration status.
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        //Clear the table till like install.
        $customApiResponse = new CustomApiResponse();
        try {
            $connection = $this->_resource->getConnection();
            $tableName = $this->_resource->getTableName(
                Constants::PARENT_TABLE_NAME
            );
            $tableNameChild = $this->_resource->getTableName(
                Constants::CHILD_TABLE_NAME
            );
            $disconnectData = [
                "Comm100AgentEmail" => null,
                "magentoAppBaseUrl" => null,
                "Comm100AccessToken" => null,
                "Comm100SiteID" => null,
                "WebhookConnectionStatus" => false
            ];
            $connection->beginTransaction();
            $connection->update($tableName, $disconnectData);
            $connection->delete($tableNameChild);
            $connection->commit();
            $customApiResponse->setApiResponse(
                true,
                'Disconnected successful.'
            );
        } catch (\Exception $e) {
            $customApiResponse->setApiResponse(false, $e->getMessage());
            $this->_logger->info('Comm100 Disconnect Error: ' . $e->getMessage());
        }

        header('Content-Type: application/json; charset=utf-8');
        $returnArray = json_encode($customApiResponse->getApiResponse());
        $this->_logger->info("Comm100 Diconnect response : " . $returnArray);
        print_r($returnArray, false);
    }

    /**
     * Method to get the Comm100_LiveChat db data.
     * @return mixed
     */
    private function getComm100MagentoDbDetails()
    {
        $comm100LiveChatObj = $this->_comm100LiveChat->create();
        $collection = $comm100LiveChatObj->getCollection();
        $magentoDbDetails = $collection->getFirstItem();
        return $magentoDbDetails;
    }
}
