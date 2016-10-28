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

namespace Magestore\Storelocator\Block\Adminhtml\Store\Edit\Tab\GmapTab\Renderer;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\UrlInterface;

/**
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class Map extends \Magento\Backend\Block\Widget implements RendererInterface
{
    protected $_template = 'Magestore_Storelocator::store/map.phtml';

    /**
     * @var array
     */
    protected $_locationInputIds = [
        'address',
        'zoom_level',
        'city',
        'zipcode',
        'country_id',
        'latitude',
        'longitude',
        'zoom_level',
    ];

    /**
     * @var array
     */
    protected $_jsonKeys = [
        'latitude',
        'longitude',
        'zoom_level',
        'marker_icon',
    ];

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
    }

    /**
     * Render form element as HTML.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);

        return $this->toHtml();
    }

    /**
     * @return \Magestore\Storelocator\Model\Store
     */
    public function getRegistryModel()
    {
        return $this->_coreRegistry->registry('storelocator_store');
    }

    /**
     * @return mixed
     */
    public function getHtmlIdPrefix()
    {
        return $this->getElement()->getForm()->getHtmlIdPrefix();
    }

    /**
     * @param string $elementId
     *
     * @return string
     */
    public function getSelectorElement($elementId = '')
    {
        return '#'.$this->getHtmlIdPrefix().$elementId;
    }

    /**
     * @return string
     */
    public function getOptionMapJson()
    {
        $store = $this->getRegistryModel();

        if ($store->getData('marker_icon')) {
            $markerIcon = $this->_storeManager->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA).$store->getData('marker_icon');
            $store->setData('marker_icon', $markerIcon);
        }

        foreach ($this->_locationInputIds as $input) {
            $store->setData('input_'.$input, $this->getSelectorElement($input));
            $this->_jsonKeys[] = 'input_'.$input;
        }

        return $store->toJson($this->_jsonKeys);
    }
}
