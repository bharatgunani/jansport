<?php
namespace Magento\Review\Model\ResourceModel\Review;

/**
 * Interceptor class for @see \Magento\Review\Model\ResourceModel\Review
 */
class Interceptor extends \Magento\Review\Model\ResourceModel\Review implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context, \Magento\Framework\Stdlib\DateTime\DateTime $date, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Review\Model\RatingFactory $ratingFactory, \Magento\Review\Model\ResourceModel\Rating\Option $ratingOptions, $connectionName = null)
    {
        $this->___init();
        parent::__construct($context, $date, $storeManager, $ratingFactory, $ratingOptions, $connectionName);
    }

    /**
     * {@inheritdoc}
     */
    public function afterDeleteCommit(\Magento\Framework\Model\AbstractModel $object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'afterDeleteCommit');
        if (!$pluginInfo) {
            return parent::afterDeleteCommit($object);
        } else {
            return $this->___callPlugins('afterDeleteCommit', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalReviews($entityPkValue, $approvedOnly = false, $storeId = 0)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getTotalReviews');
        if (!$pluginInfo) {
            return parent::getTotalReviews($entityPkValue, $approvedOnly, $storeId);
        } else {
            return $this->___callPlugins('getTotalReviews', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function aggregate($object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'aggregate');
        if (!$pluginInfo) {
            return parent::aggregate($object);
        } else {
            return $this->___callPlugins('aggregate', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reAggregateReview($reviewId, $entityPkValue)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'reAggregateReview');
        if (!$pluginInfo) {
            return parent::reAggregateReview($reviewId, $entityPkValue);
        } else {
            return $this->___callPlugins('reAggregateReview', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityIdByCode($entityCode)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getEntityIdByCode');
        if (!$pluginInfo) {
            return parent::getEntityIdByCode($entityCode);
        } else {
            return $this->___callPlugins('getEntityIdByCode', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteReviewsByProductId($productId)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'deleteReviewsByProductId');
        if (!$pluginInfo) {
            return parent::deleteReviewsByProductId($productId);
        } else {
            return $this->___callPlugins('deleteReviewsByProductId', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdFieldName()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getIdFieldName');
        if (!$pluginInfo) {
            return parent::getIdFieldName();
        } else {
            return $this->___callPlugins('getIdFieldName', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMainTable()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getMainTable');
        if (!$pluginInfo) {
            return parent::getMainTable();
        } else {
            return $this->___callPlugins('getMainTable', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTable($tableName)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getTable');
        if (!$pluginInfo) {
            return parent::getTable($tableName);
        } else {
            return $this->___callPlugins('getTable', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getConnection');
        if (!$pluginInfo) {
            return parent::getConnection();
        } else {
            return $this->___callPlugins('getConnection', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'load');
        if (!$pluginInfo) {
            return parent::load($object, $value, $field);
        } else {
            return $this->___callPlugins('load', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'save');
        if (!$pluginInfo) {
            return parent::save($object);
        } else {
            return $this->___callPlugins('save', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Framework\Model\AbstractModel $object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'delete');
        if (!$pluginInfo) {
            return parent::delete($object);
        } else {
            return $this->___callPlugins('delete', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addUniqueField($field)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'addUniqueField');
        if (!$pluginInfo) {
            return parent::addUniqueField($field);
        } else {
            return $this->___callPlugins('addUniqueField', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resetUniqueField()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'resetUniqueField');
        if (!$pluginInfo) {
            return parent::resetUniqueField();
        } else {
            return $this->___callPlugins('resetUniqueField', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unserializeFields(\Magento\Framework\Model\AbstractModel $object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'unserializeFields');
        if (!$pluginInfo) {
            return parent::unserializeFields($object);
        } else {
            return $this->___callPlugins('unserializeFields', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqueFields()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getUniqueFields');
        if (!$pluginInfo) {
            return parent::getUniqueFields();
        } else {
            return $this->___callPlugins('getUniqueFields', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasDataChanged($object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'hasDataChanged');
        if (!$pluginInfo) {
            return parent::hasDataChanged($object);
        } else {
            return $this->___callPlugins('hasDataChanged', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChecksum($table)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getChecksum');
        if (!$pluginInfo) {
            return parent::getChecksum($table);
        } else {
            return $this->___callPlugins('getChecksum', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterLoad(\Magento\Framework\DataObject $object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'afterLoad');
        if (!$pluginInfo) {
            return parent::afterLoad($object);
        } else {
            return $this->___callPlugins('afterLoad', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(\Magento\Framework\DataObject $object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'beforeSave');
        if (!$pluginInfo) {
            return parent::beforeSave($object);
        } else {
            return $this->___callPlugins('beforeSave', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave(\Magento\Framework\DataObject $object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'afterSave');
        if (!$pluginInfo) {
            return parent::afterSave($object);
        } else {
            return $this->___callPlugins('afterSave', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete(\Magento\Framework\DataObject $object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'beforeDelete');
        if (!$pluginInfo) {
            return parent::beforeDelete($object);
        } else {
            return $this->___callPlugins('beforeDelete', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete(\Magento\Framework\DataObject $object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'afterDelete');
        if (!$pluginInfo) {
            return parent::afterDelete($object);
        } else {
            return $this->___callPlugins('afterDelete', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function serializeFields(\Magento\Framework\Model\AbstractModel $object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'serializeFields');
        if (!$pluginInfo) {
            return parent::serializeFields($object);
        } else {
            return $this->___callPlugins('serializeFields', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'beginTransaction');
        if (!$pluginInfo) {
            return parent::beginTransaction();
        } else {
            return $this->___callPlugins('beginTransaction', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCommitCallback($callback)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'addCommitCallback');
        if (!$pluginInfo) {
            return parent::addCommitCallback($callback);
        } else {
            return $this->___callPlugins('addCommitCallback', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'commit');
        if (!$pluginInfo) {
            return parent::commit();
        } else {
            return $this->___callPlugins('commit', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'rollBack');
        if (!$pluginInfo) {
            return parent::rollBack();
        } else {
            return $this->___callPlugins('rollBack', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRulesBeforeSave()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getValidationRulesBeforeSave');
        if (!$pluginInfo) {
            return parent::getValidationRulesBeforeSave();
        } else {
            return $this->___callPlugins('getValidationRulesBeforeSave', func_get_args(), $pluginInfo);
        }
    }
}
