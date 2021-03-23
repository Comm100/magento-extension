<?php

namespace Comm100\LiveChat\Model\ResourceModel\Comm100LiveChat;

class Collection extends
    \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'MagentoStoreId';
    protected $_eventPrefix = 'comm100_livechat_collection';
    protected $_eventObject = 'comm100_livechat_collection';

    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            'Comm100\LiveChat\Model\Comm100LiveChat',
            'Comm100\LiveChat\Model\ResourceModel\Comm100LiveChat'
        );
    }
}
