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
 * Resource Model Specialday.
 *
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class Specialday extends \Magestore\Storelocator\Model\ResourceModel\AbstractDbManageStores
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(\Magestore\Storelocator\Setup\InstallSchema::SCHEMA_SPECIALDAY, 'specialday_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreRelationTable()
    {
        return $this->getTable(\Magestore\Storelocator\Setup\InstallSchema::SCHEMA_STORE_SPECIALDAY);
    }
}
