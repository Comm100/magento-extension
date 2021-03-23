<?php

namespace Comm100\LiveChat\Block\System\Config;

use Comm100\LiveChat\Helper\Constants;
use Comm100\LiveChat\Model\Comm100LiveChatFactory;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Magento\Authorization\Model\UserContextInterface;

class ChatButtonConfig extends \Magento\Framework\View\Element\Template
{
    /**
     * Path to block template.
     */
    const CHECK_TEMPLATE = 'system/config/chatButtonConfig.phtml';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $_storeManager
     */
    protected $_storeManager;

    /**
     * Comm100LiveChat factory.
     *
     * @var Comm100LiveChatFactory
     */
    protected $_comm100LiveChat;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        UserContextInterface $userContext,
        UserCollectionFactory $userCollectionFactory,
        Comm100LiveChatFactory $comm100LiveChat,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlinterface = $context->getUrlBuilder();
        $this->_storeManager = $storeManager;
        $this->_comm100LiveChat = $comm100LiveChat;
        $this->_userContext = $userContext;
        $this->_userCollectionFactory = $userCollectionFactory;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::CHECK_TEMPLATE);
        }

        return $this;
    }

    /**
     * Method to get the magento account email id.
     * @return string the email id of the magento user.
     */
    private function getMagentoAccountEmail()
    {
        $collection = $this->_userCollectionFactory->create();
        $userId = $this->_userContext->getUserId();
        $collection->addFieldToFilter('main_table.user_id', $userId);
        $userData = $collection->getFirstItem();

        return $userData->getData()['email'];
    }

    /**
     * Method to get the Comm100_LiveChat db details.
     * @return mixed the first and default comm100 live chat detail.
     */
    private function getComm100MagentoDbDetails()
    {
        $comm100LiveChatObj = $this->_comm100LiveChat->create();
        $collection = $comm100LiveChatObj->getCollection();
        $magentoDbDetails = $collection->getFirstItem();
        return $magentoDbDetails;
    }

    /**
     * Method to get the default page. This is the page which contains the url to the base page of the magento app.
     * @return string the default page of mangento app.
     */
    public function getDefaultPage()
    {
        $baseUrl = urlencode($this->_storeManager->getStore()->getBaseUrl());
        $magnetoAccountEmail = urlencode($this->getMagentoAccountEmail());
        $comm100LiveChatData = $this->getComm100MagentoDbDetails();
        $consumerKey = urlencode($comm100LiveChatData['ConsumerKey']);
        $consumerSecret = urlencode($comm100LiveChatData['ConsumerSecret']);
        $oAuthVerifier = urlencode($comm100LiveChatData['OAuthVerifier']);
        $magentoAppBaseUrl = $comm100LiveChatData["MagentoAppBaseURL"];

        if ($magentoAppBaseUrl == null || $magentoAppBaseUrl == "") {
            $magentoAppBaseUrl = Constants::MAGENTO_APP_BASE_URL;
        }
        return sprintf(
            $magentoAppBaseUrl . Constants::DEFAULT_PAGE,
            $baseUrl,
            $consumerKey,
            $consumerSecret,
            $oAuthVerifier,
            $magnetoAccountEmail
        );
    }
}
