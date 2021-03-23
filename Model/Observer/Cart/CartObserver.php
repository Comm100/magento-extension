<?php

namespace Comm100\LiveChat\Model\Observer\Cart;

use Comm100\LiveChat\Model\Comm100LiveChatFactory;
use Comm100\LiveChat\Model\Comm100LiveChatStoreCampaignsFactory;
use Comm100\LiveChat\Model\Observer\WebhookAbstract;
use Magento\Framework\Event\Observer;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CartObserver.
 */
class CartObserver extends WebhookAbstract
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var CheckoutSession
     * */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface @sessionManager
     */
    protected $_sessionManager;


    /**
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $_customerSession;

    /**
     * @var Comm100LiveChatStoreCampaignsFactory _$comm100LiveChatStoreCampaignsFactory
     * */
    protected $_comm100LiveChatStoreCampaignsFactory;

    /**
     * @var \Magento\Checkout\Model\Cart $_cart
     */
    protected $_cart;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $_productRepositoryFactory
     */
    protected $_productRepositoryFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\Total $_shippingTotal
     */
    protected $_shippingTotal;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        CheckoutSession $checkoutSession,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        Comm100LiveChatFactory $comm100LiveChatFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Quote\Model\Quote\Address\Total $shippingTotal,
        //Others to be transferred to the parent class.
        \Psr\Log\LoggerInterface $logger,
        Json $jsonHelper,
        Curl $curlAdapter,
        Comm100LiveChatStoreCampaignsFactory $comm100LiveChatStoreCampaignsFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->_customerSession = $customerSession;
        $this->_sessionManager = $sessionManager;
        $this->_logger = $logger;
        $this->_checkoutSession = $checkoutSession;
        $this->_cart = $cart;
        $this->_comm100LiveChatStoreCampaignsFactory = $comm100LiveChatStoreCampaignsFactory;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->_shippingTotal = $shippingTotal;

        return parent::__construct(
            $logger,
            $jsonHelper,
            $curlAdapter,
            $comm100LiveChatFactory,
            $storeManager
        );
    }

    /**
     * Overrided method.
     * @return string return event name.
     */
    protected function _getWebhookEvent(Observer $observer)
    {
        $this->_logger->debug(
            'Comm100 : Cart Observer Event Name: ' .
                $observer->getEvent()->getName()
        );
        return 'CART'; // $observer->getEvent()->getName();
    }

    /**
     * Method to get the visitor id. The person who is not logged in.
     * @return int visitor id.
     */
    public function getVisitorId()
    {
        $visitorId = null;
        if ($this->_customerSession->isLoggedIn() == false) {
            $visitor = $this->_sessionManager->getVisitorData();

            return $visitor['visitor_id'];
        }

        return $visitorId;
    }

    /**
     * Method to get the customer id. The person who is logged in.
     * @return int customer id.
     */
    public function getCustomerId()
    {
        $customerId = null;
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getCustomer()->getId();
        }

        return $customerId;
    }

    /**
     * Overridden method to get the webhook data.
     */
    protected function _getWebhookData(Observer $observer)
    {
        $currentStore = $this->_storeManager->getStore();
        $baseUrl = $currentStore->getBaseUrl();
        $currentCartItem = $observer['cart']['quote']->getData();
        $currentCartItem['quote_id'] = $baseUrl . "@" . $currentCartItem['entity_id'];
        $currentCartItem['created_at'] = $currentCartItem['created_at'] ?? date('Y-m-d H:i:s');
        $currentCartItem['actual_created_at'] = date('Y-m-d H:i:s');

        $quoteObject = $this->_checkoutSession->getQuote();
        $cart = $quoteObject->getAllVisibleItems();
        $subTotal = $quoteObject->getSubtotal();
        $grandTotal = $quoteObject->getGrandTotal();

        $shippingTotal = $this->_shippingTotal->getShippingAmount();

        $currency = $currentStore->getCurrentCurrencyCode();
        $medialUrl =
            $currentStore->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . 'catalog/product';

        $cartItems = [];
        foreach ($cart as $item) {
            $itemData = $item->getData();
            $product = $this->_productRepositoryFactory
                ->create()
                ->getById($item->getProductId());
            $itemData['quote_id'] = $baseUrl . "@" . $itemData['quote_id'];
            $itemData['thumbnailUrl'] =
                $medialUrl . $product->getData('thumbnail');
            $itemData['productUrl'] = $product->getProductUrl();
            array_push($cartItems, $itemData);
        }

        $visitorId = $this->getVisitorId();
        $customerId = $this->getCustomerId();

        return  [
            'visitorId' => $visitorId,
            'customerId' => $customerId,
            'cartItems' => $cartItems,
            'currency' => $currency,
            'currentCartItem' => $currentCartItem,
            'subTotal' => $subTotal,
            'grandTotal' => $grandTotal,
            'shippingTotal' => $shippingTotal,
        ];
    }
}
