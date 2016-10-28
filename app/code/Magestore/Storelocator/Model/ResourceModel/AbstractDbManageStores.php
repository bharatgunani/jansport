<?php

/**
 * Magestore.
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
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
abstract class AbstractDbManageStores extends \Magestore\Storelocator\Model\ResourceModel\AbstractResource implements \Magestore\Storelocator\Model\ResourceModel\DbManageStoresInterface
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
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param array                                  $storelocatorIds
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function pickStores(\Magento\Framework\Model\AbstractModel $object, array $storelocatorIds = [])
    {
        $id = (int) $object->getId();
        $table = $this->getStoreRelationTable();

        $old = $this->getStorelocatorIds($object);
        $new = $storelocatorIds;

        /*
         * remove stores from object
         */
        $this->deleteData(
            $table,
            [
                $this->getIdFieldName() . ' = ?' => $id,
                'storelocator_id IN(?)' => array_values(array_diff($old, $new)),
            ]
        );

        /*
         * add store to object
         */
        $insert = [];
        foreach (array_values(array_diff($new, $old)) as $storelocatorId) {
            $insert[] = [$this->getIdFieldName() => $id, 'storelocator_id' => (int) $storelocatorId];
        }
        $this->insertData($table, $insert);

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
        $collection->addFieldToFilter(
            'storelocator_id',
            ['in' => $this->getStorelocatorIds($object)]
        );

        return $collection;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return mixed
     */
    public function getStorelocatorIds(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();
        $id = (int) $object->getId();

        $select = $connection->select()->from(
            $this->getStoreRelationTable(),
            'storelocator_id'
        )->where(
            $this->getIdFieldName() . ' = :object_id'
        );

        return $connection->fetchCol($select, [':object_id' => $id]);
    }

    /**
     * get table relation ship.
     *
     * @return string
     */
    abstract public function getStoreRelationTable();
}
