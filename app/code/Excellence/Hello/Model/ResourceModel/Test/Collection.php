<?php
namespace Excellence\Hello\Model\ResourceModel\Test;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Excellence\Hello\Model\Test','Excellence\Hello\Model\ResourceModel\Test');
    }
}
