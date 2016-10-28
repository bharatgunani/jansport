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

namespace Magestore\Storelocator\Block\Adminhtml\Widget;

use Magestore\Storelocator\Block\Adminhtml\Widget\Grid\Column\Filter\Checkbox as FilterCheckbox;

/**
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter
     */
    protected $_converter;

    /**
     * @var \Magestore\Storelocator\Helper\Data
     */
    protected $_storelocatorHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magestore\Storelocator\Helper\Data $storelocatorHelper,
        \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_storelocatorHelper = $storelocatorHelper;
        $this->_converter = $converter;

        if ($this->hasData('serialize_grid') && count($this->getSelectedRows())) {
            $this->setDefaultFilter(
                ['checkbox_id' => FilterCheckbox::CHECKBOX_YES]
            );
        }
    }

    /**
     * get selected row values.
     *
     * @return array
     */
    public function getSelectedRows()
    {
        $selectedValues = $this->_converter->toFlatArray(
            $this->_storelocatorHelper->getTreeSelectedValues()
        );

        return array_values($selectedValues);
    }
}
