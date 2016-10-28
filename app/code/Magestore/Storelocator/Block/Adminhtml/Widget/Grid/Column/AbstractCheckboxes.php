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

namespace Magestore\Storelocator\Block\Adminhtml\Widget\Grid\Column;

/**
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
abstract class AbstractCheckboxes extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @var \Magestore\Storelocator\Helper\Data
     */
    protected $_storelocatorHelper;

    /**
     * @var \Magestore\Storelocator\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magestore\Storelocator\Helper\Data $storelocatorHelper,
        \Magestore\Storelocator\Model\StoreFactory $storeFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storelocatorHelper = $storelocatorHelper;
        $this->_storeFactory = $storeFactory;

        $this->_filterTypes['checkbox'] = 'Magestore\Storelocator\Block\Adminhtml\Widget\Grid\Column\Filter\Checkbox';
    }

    /**
     * values.
     *
     * @return mixed
     */
    public function getValues()
    {
        if (!$this->hasData('values')) {
            $this->setData('values', $this->getSelectedValues());
        }

        return $this->getData('values');
    }

    /**
     * get selected rows.
     *
     * @return array
     */
    abstract public function getSelectedValues();
}
