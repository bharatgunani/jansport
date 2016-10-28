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

namespace Magestore\Storelocator\Model;

/**
 * Model Specialday.
 *
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class Specialday extends \Magestore\Storelocator\Model\AbstractModelManageStores
{
    /**
     * Model construct that should be used for object initialization.
     */
    public function _construct()
    {
        $this->_init('Magestore\Storelocator\Model\ResourceModel\Specialday');
    }

    /**
     * Processing object before save data.
     */
    public function beforeSave()
    {
        $this->_prepareSaveWorkingTime();

        return parent::beforeSave();
    }

    /*
     * prepare save working time of specialday
     */
    protected function _prepareSaveWorkingTime()
    {
        if (is_array($this->getData('time_open'))) {
            $this->setData('time_open', implode(':', $this->getData('time_open')));
        }

        if (is_array($this->getData('time_close'))) {
            $this->setData('time_close', implode(':', $this->getData('time_close')));
        }
    }
}
