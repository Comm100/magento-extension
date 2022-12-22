<?php

namespace Comm100\LiveChat\Model;

/**
 * Webhook model.
 *
 * @method \Comm100\LiveChat\Model\Resource\Webhook _getResource()
 * @method \Comm100\LiveChat\Model\Resource\Webhook getResource()
 * @method string                                   getCode()
 * @method \Comm100\LiveChat\Model\Webhook          setCode(string $value)
 * @method string                                   getName()
 * @method \Comm100\LiveChat\Model\Webhook          setName(string $value)
 */
class Webhook extends \Magento\Framework\Model\AbstractModel
{
    const TYPE_TEXT = 'text';

    const TYPE_HTML = 'html';

    /**
     * @var int
     */
    protected $_storeId = 0;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_escaper = $escaper;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Internal Constructor.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Comm100\LiveChat\Model\Webhook');
    }

    /**
     * Setter.
     *
     * @param int $storeId
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }


    /**
     * Load webhook by code.
     *
     * @param string $code
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function loadByCode($code)
    {
        $this->getResource()->loadByCode($this, $code);

        return $this;
    }

    /**
     * Return webhook value depend on given type.
     *
     * @param string $type
     *
     * @return string
     */
    public function getValue($type = null)
    {
        if ($type === null) {
            $type = self::TYPE_HTML;
        }
        if (
            $type == self::TYPE_TEXT ||
            !strlen((string) $this->getData('html_value'))
        ) {
            $value = $this->getData('plain_value');
            //escape html if type is html, but html value is not defined
            if ($type == self::TYPE_HTML) {
                $value = nl2br($this->_escaper->escapeHtml($value));
            }

            return $value;
        }

        return $this->getData('html_value');
    }

    /**
     * Validation of object data.
     *
     * @return \Magento\Framework\Phrase|bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * Retrieve webhooks option array.
     *
     * @todo: extract method as separate class
     *
     * @param bool $withGroup
     *
     * @return array
     */
    public function getWebhooksOptionArray($withGroup = false)
    {
        /* @var $collection \Comm100\LiveChat\Model\Resource\Webhook\Collection */
        $collection = $this->getCollection();
        $webhooks = [];
        foreach ($collection->toOptionArray() as $webhook) {
            $webhooks[] = [
                'value' => '{{customVar code=' . $webhook['value'] . '}}',
                'label' => __('%1', $webhook['label']),
            ];
        }
        if ($withGroup && $webhooks) {
            $webhooks = ['label' => __('Webhooks'), 'value' => $webhooks];
        }

        return $webhooks;
    }
}
