<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storelocator
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Storelocator\Model\ResourceModel;

/**
 * Resource Model Schedule.
 *
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class Schedule extends \Magestore\Storelocator\Model\ResourceModel\AbstractResource implements \Magestore\Storelocator\Model\ResourceModel\DbManageStoresInterface
{
    /**
     * @var \Magestore\Storelocator\Model\ResourceModel\Store\CollectionFactory
     */
    protected $_storeCollectionFactory;

    /**
     * Class constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null                                  $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magestore\Storelocator\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_storeCollectionFactory = $storeCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(\Magestore\Storelocator\Setup\InstallSchema::SCHEMA_SCHEDULE, 'schedule_id');
    }

    /**
     * pick stores for model.
     *
     * @param array $storelocatorIds
     *
     * @return mixed
     */
    public function pickStores(\Magento\Framework\Model\AbstractModel $object, array $storelocatorIds = [])
    {
        $id = (int) $object->getId();

        $table = $this->getTable(\Magestore\Storelocator\Setup\InstallSchema::SCHEMA_STORE);
        $old = $this->getStorelocatorIds($object);
        $new = $storelocatorIds;

        /*
         * remove stores from schedule
         */
        $bind = [$this->getIdFieldName() => new \Zend_Db_Expr('NULL')];
        $where = ['storelocator_id IN(?)' => array_values(array_diff($old, $new))];
        $this->updateData($table, $bind, $where);

        /*
         * add stores to schedule
         */
        $bind = [$this->getIdFieldName() => new \Zend_Db_Expr($id)];
        $where = ['storelocator_id IN(?)' => array_values(array_diff($new, $old))];
        $this->updateData($table, $bind, $where);

        return $this;
    }

    /**
     * get collection store of model.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getStores(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magestore\Storelocator\Model\ResourceModel\Store\Collection $collection */
        $collection = $this->_storeCollectionFactory->create();

        $collection->addFieldToSelect('storelocator_id')
            ->addFieldToFilter($this->getIdFieldName(), (int) $object->getId());

        return $collection;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return mixed
     */
    public function getStorelocatorIds(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magestore\Storelocator\Model\ResourceModel\Store\Collection $collection */
        $collection = $this->getStores($object);

        return $collection->getAllIds();
    }
}
