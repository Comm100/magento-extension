<?php

/**
 * Copyright © Comm100. All rights reserved.
 */

namespace Comm100\LiveChat\Plugin;

/**
 * Class to add custom attributes to the cart item.
 */
class CustomerDataCartSection
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_sessionManager;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager
    ) {
        $this->_customerSession = $customerSession;
        $this->_sessionManager = $sessionManager;
        $this->_cart = $cart;
    }

    /**
     * Method to get the cart id of the current cart.
     * @return string cart id.
     */
    public function getMagentoCartId()
    {
        return $this->_cart->getQuote()->getId();
    }

    /**
     * Method to get the customer id.
     * @return int customer id.
     */
    public function getCustomerId()
    {
        $customerId = null;
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->getCustomer()->getId();
        }

        return $customerId;
    }

    /**
     * Method to get the customer
     * @return mixed the current cart customer.
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
     * Add cart id and magento customer id data to result. Inherited from the parent.
     *
     * @param  \Magento\Checkout\CustomerData\Cart $subject.
     * @param array the parent array containing the default values.
     * @return array array of the cart data along with the added date.
     */
    public function afterGetSectionData(
        \Magento\Checkout\CustomerData\Cart $subject,
        $result
    ) {
        $result['cartId'] = $this->getMagentoCartId();
        $result['magentoCustomerId'] = $this->getCustomerId();

        return $result;
    }
}
