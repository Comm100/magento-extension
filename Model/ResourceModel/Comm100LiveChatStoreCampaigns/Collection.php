<?php

namespace Comm100\LiveChat\Model\ResourceModel\Comm100LiveChatStoreCampaigns;

class Collection extends
    \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'MagentoStoreId';
    protected $_eventPrefix = 'comm100_livechat_livechatstorecampaigns_collection';
    protected $_eventObject = 'comm100_livechat_livechatstorecampaigns_collection';

    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            'Comm100\LiveChat\Model\Comm100LiveChatStoreCampaigns',
            'Comm100\LiveChat\Model\ResourceModel\Comm100LiveChatStoreCampaigns'
        );
    }
}
