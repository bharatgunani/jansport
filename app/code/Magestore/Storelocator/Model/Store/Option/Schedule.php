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

namespace Magestore\Storelocator\Model\Store\Option;

/**
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class Schedule implements \Magento\Framework\Data\OptionSourceInterface, \Magestore\Storelocator\Model\Data\Option\OptionHashInterface
{
    /**
     * @var \Magestore\Storelocator\Model\ResourceModel\Schedule\CollectionFactory
     */
    protected $_scheduleCollectionFactory;

    public function __construct(
        \Magestore\Storelocator\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory
    ) {
        $this->_scheduleCollectionFactory = $scheduleCollectionFactory;
    }

    /**
     * Return array of options as value-label pairs.
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $option = [];
        /** @var \Magestore\Storelocator\Model\ResourceModel\Schedule\Collection $collection */
        $collection = $this->_scheduleCollectionFactory->create();

        foreach ($collection as $schedule) {
            $option[] = ['label' => $schedule->getScheduleName(), 'value' => $schedule->getId()];
        }

        return $option;
    }

    /**
     * Return array of options as key-value pairs.
     *
     * @return array Format: array('<key>' => '<value>', '<key>' => '<value>', ...)
     */
    public function toOptionHash()
    {
        $option = [];
        /** @var \Magestore\Storelocator\Model\ResourceModel\Schedule\Collection $collection */
        $collection = $this->_scheduleCollectionFactory->create();

        foreach ($collection as $schedule) {
            $option[$schedule->getId()] = $schedule->getScheduleName();
        }

        return $option;
    }
}
