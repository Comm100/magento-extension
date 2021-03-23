<?php

namespace Comm100\LiveChat\Model\ResourceModel;

use Comm100\LiveChat\Helper\Constants;

class Comm100LiveChat extends
    \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init(Constants::PARENT_TABLE_NAME, 'Comm100SiteID');
    }
}
