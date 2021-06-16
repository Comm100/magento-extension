<?php

namespace Comm100\LiveChat\Block;

use Comm100\LiveChat\Helper\Constants;
use Comm100\LiveChat\Model\Comm100LiveChatFactory;
use Comm100\LiveChat\Model\Comm100LiveChatStoreCampaignsFactory;
use Magento\Framework\View\Element\Template;
use Magento\Integration\Model\Oauth\TokenFactory;

/**
 * SnippetBlock block class for the snippetblock.phtml.
 */
class SnippetBlock extends Template
{
    /**
     * @var \Magento\Customer\Model\Session $_customerSession
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface $_storeManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Quote\Model\Quote _quote
     */
    protected $_quote;

    /**
     * @var \Magento\Checkout\Model\Cart $_cart
     */
    protected $_cart;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterfac $_sessionManager
     */
    protected $_sessionManager;

    /**
     * @var Comm100LiveChatStoreCampaignsFactory $_comm100LiveChatStoreCampaigns
     */
    protected $_comm100LiveChatStoreCampaigns;

    /**
     * @var TokenFactory $_tokenModelFactory
     */
    protected $_tokenModelFactory;

    /**
     * @var \Magento\Backend\Helper\Data $_helperBackend
     */
    protected $_helperBackend;

    /**
     * @var Comm100LiveChatFactory $_comm100LiveChatFactory
     */
    protected $_comm100LiveChatFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        Comm100LiveChatFactory $comm100LiveChat,
        Comm100LiveChatStoreCampaignsFactory $comm100LiveChatStoreCampaigns,
        TokenFactory $tokenModelFactory,
        \Magento\Backend\Helper\Data $helperBackend,
        Comm100LiveChatFactory $comm100LiveChatFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_quote = $quote;
        $this->_cart = $cart;
        $this->_sessionManager = $sessionManager;
        $this->_comm100LiveChatStoreCampaigns = $comm100LiveChatStoreCampaigns;
        $this->_tokenModelFactory = $tokenModelFactory;
        $this->_helperBackend = $helperBackend;
        $this->_comm100LiveChatFactory = $comm100LiveChatFactory;
    }

    /**
     * Method to get the admin url.
     *
     * @return string the admin url.
     */
    public function getAdminUrl()
    {
        return str_replace("/admin/", "/", $this->_helperBackend->getHomePageUrl());
    }

    /**
     * Method to get the logged in customer id.
     *
     * @return string the customer id.
     */
    public function getCustomerId()
    {
        $customerId = 0;
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->getCustomer()->getId();
        }

        return $customerId;
    }


    /**
     * Method to get the add order url.
     *
     * @return string the add order url.
     */
    public function getOrderAddUrl()
    {
        return $returnVal = "'" .
            $this->getAdminUrl() . "sales/order" .
            "'";
        $returnVal = 'null';
        if ($this->_customerSession->isLoggedIn()) {
            $returnVal =
                "'" .
                $this->_helperBackend->getUrl('*/sales_order_create', [
                    'customer_id' => $this->getCustomerId(),
                ]) .
                "'";
        } else {
            $returnVal = "'" .
                $this->getAdminUrl() . "sales/order" .
                "'";
        }

        return $returnVal;
    }

    /**
     * Method to get the magento cart id.
     * @return string the cart id.
     */
    public function getMagentoCartId()
    {
        $cartId = $this->_cart->getQuote()->getId();
        return $cartId ? $this->getBaseUrl() . "@" . $cartId : '';
    }

    /**
     * Method to get the store code.
     * @return string the store code.
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * Method to get the customer email.
     */
    public function getCustomerEmail()
    {
        $customerId = null;
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->getCustomer()->getEmail();
        }

        return $customerId;
    }

    /**
     * Method to get the current customer in the session.
     * @return mixed the custormer object.
     */
    public function getCustomer()
    {
        $customer = null;
        if ($this->_customerSession->isLoggedIn()) {
            $customer = $this->_customerSession->getCustomer();
        }

        return $customer;
    }

    /**
     * Method to get the customer token.
     * @return string the token of the customer.
     */
    public function getCustomerToken()
    {
        $customerToken = null;
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getCustomer()->getId();
            $tokenFactory = $this->_tokenModelFactory->create();
            $customerToken = $tokenFactory
                ->createCustomerToken($customerId)
                ->getToken();
        }

        return "'" . $customerToken . "'";
    }

    /**
     * Method to generate the code script of the custom variables.
     * @return string the div element with the custom variable.
     */
    public function getCustomVariables()
    {
        return '<div id="' .
            Constants::MAGENTO_CUSTOMER_ID .
            '">' .
            $this->getCustomerId() .
            '</div>';
    }

    /**
     * Method to get the Comm100_LiveChat db details.
     * @return mixed the db default first row.
     */
    private function getComm100MagentoDbDetails()
    {
        $comm100LiveChatObj = $this->_comm100LiveChatFactory->create();
        $collection = $comm100LiveChatObj->getCollection();
        $magentoDbDetails = $collection->getFirstItem();
        return $magentoDbDetails;
    }

    /**
     * Method to get api/saveVisitor url.
     * @return string the complete url.
     */
    public function getSaveVisitorUrl()
    {
        $comm100LiveChatData = $this->getComm100MagentoDbDetails();
        $magentoAppBaseUrl = $comm100LiveChatData["MagentoAPIBaseURL"];
        if ($magentoAppBaseUrl == null || $magentoAppBaseUrl == "") {
            $magentoAppBaseUrl = Constants::MAGENTO_API_BASE_URL;
        }
        return $magentoAppBaseUrl . Constants::SAVE_VISITOR;
    }

    /**
     * Method to get the store base url.
     * @return string the base url of the store.
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Method to get the chatbox data.
     * @return mixes[] the Comm100 site id and campaign id.
     */
    public function getChatBoxData()
    {
        $currentMagentoStoreId = $this->_storeManager->getStore()->getId();

        //Get comm100 site and campaign id from magento db.
        $comm100LiveChatStoreCampaigns = $this->_comm100LiveChatStoreCampaigns->create();
        $collection = $comm100LiveChatStoreCampaigns->getCollection();
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
}
