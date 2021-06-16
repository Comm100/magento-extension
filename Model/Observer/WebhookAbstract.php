<?php

namespace Comm100\LiveChat\Model\Observer;

use Comm100\LiveChat\Helper\Constants;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Comm100\LiveChat\Model\Comm100LiveChatFactory;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Customer.
 */
abstract class WebhookAbstract implements ObserverInterface
{
    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * Curl Adapter.
     *
     * @var Curl
     */
    protected $_curlAdapter;

    /**
     * Json Helper.
     *
     * @var Json
     */
    protected $_jsonHelper;

    /**
     * Comm100LiveChatFactory factory.
     *
     * @var Comm100LiveChatFactory
     */
    protected $_comm100LiveChatFactory;

    protected $_storeManager;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Json $jsonHelper,
        Curl $curlAdapter,
        Comm100LiveChatFactory $comm100LiveChatFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->_logger = $logger;
        $this->_curlAdapter = $curlAdapter;
        $this->_jsonHelper = $jsonHelper;
        $this->_comm100LiveChatFactory = $comm100LiveChatFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * Set new customer group to all his quotes.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        // Check if the chat box for the store is registered and rendered otherwise return null.
        $storeId = $this->_storeManager->getStore()->getId();
        $chatBoxData = $this->getChatBoxData($storeId);
        if (
            $chatBoxData['siteId'] == null ||
            $chatBoxData['campaignId'] == null
        ) {
            return null;
        }

        $eventCode = $this->_getWebhookEvent($observer);
        $eventData = $this->_getWebhookData($observer);

        // Event data will be null when there is no data to be reutrned for example if the chat box is not shown for a particular store id.
        if ($eventData == null) {
            return;
        }

        $body = [
            'event' => $eventCode,
            'data' => $eventData,
        ];

        $this->_sendWebhook($body);
    }

    protected function _sendWebhook($body)
    {
        // Get Magnento App authorization token from the db.
        $comm100LiveChatFactoryParent = $this->_comm100LiveChatFactory->create();
        $item = $comm100LiveChatFactoryParent->getCollection()->getFirstItem();
        $data = $item->getData();
        $token = $data['Comm100AccessToken'];
        $magentoAppBaseUrl = $data["MagentoAPIBaseURL"];
        if ($magentoAppBaseUrl == null || $magentoAppBaseUrl == "") {
            $magentoAppBaseUrl = Constants::MAGENTO_API_BASE_URL;
        }
        $webHookUrl = $magentoAppBaseUrl . Constants::WEBHOOK;
        $baseUrl = $data['MagentoBaseURL'];
        $siteId = $data['Comm100SiteID'];

        $bodyJson = $this->_jsonHelper->serialize($body);
        $this->_logger->debug('Comm100 :  JSON Body : ' . $bodyJson . '.');

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'EventType: ' . $body['event'],
            'SiteId: ' . $siteId,
            'X-Magento-Shop-Domain: ' . $baseUrl,
            'Token: ' . $token,
            'BaseURL:' . $baseUrl,
            'X-Magento-Topic: ' . $body['event'],
            'X-Magento-Hmac-Sha256: blank_value'
        ];

        // curl_setopt($this->_curlAdapter, CURLOPT_TIMEOUT, 2000);
        // curl_setopt($this->_curlAdapter, CURLOPT_NOSIGNAL, 1);

        $this->_curlAdapter->write(
            'POST',
            $webHookUrl,
            '1.1',
            $headers,
            $bodyJson
        );
        $response = $this->_curlAdapter->read();
        if (!$response) {
            $this->_logger->debug(
                'Comm100 : Webhook API to magento app send fail ::' .
                    $this->_curlAdapter->getError()
            );
        } else {
            $this->_logger->debug(
                'C100 : Webhook API to magento app Success' . $response
            );
        }
        $this->_curlAdapter->close();
    }

    public function getChatBoxData($currentMagentoStoreId)
    {
        //Get comm100 site and campaign id from magento db.
        $comm100LiveChatStore = $this->_comm100LiveChatStoreCampaignsFactory->create();
        $collection = $comm100LiveChatStore->getCollection();
        $siteId = null;
        $campaignId = null;

        foreach ($collection as $item) {
            if ($currentMagentoStoreId == $item->getData()['MagentoStoreId']) {
                $siteId = $item->getData()['Comm100SiteID'];
                $campaignId = $item->getData()['Comm100CampaignID'];
            }
        }

        return ['siteId' => $siteId, 'campaignId' => $campaignId];
    }

    abstract protected function _getWebhookEvent(Observer $observer);

    abstract protected function _getWebhookData(Observer $observer);
}
