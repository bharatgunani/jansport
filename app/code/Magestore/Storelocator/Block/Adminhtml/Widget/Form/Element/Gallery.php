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

namespace Magestore\Storelocator\Block\Adminhtml\Widget\Form\Element;

/**
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */

class Gallery extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * Registry object.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Model Url instance.
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $_fileConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Magestore\Storelocator\Model\ResourceModel\Image\CollectionFactory
     */
    protected $_imageCollectionFactory;

    /**
     * @var \Magestore\Storelocator\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magestore\Storelocator\Model\SystemConfig
     */
    protected $_systemConfig;

    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\UrlFactory $backendUrlFactory,
        \Magento\Framework\File\Size $fileConfig,
        \Magestore\Storelocator\Helper\Image $imageHelper,
        \Magestore\Storelocator\Model\ResourceModel\Image\CollectionFactory $imageCollectionFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magestore\Storelocator\Model\SystemConfig $systemConfig,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->_backendUrl = $backendUrlFactory->create();
        $this->_fileConfig = $fileConfig;
        $this->_coreRegistry = $coreRegistry;
        $this->_jsonHelper = $jsonHelper;
        $this->_imageCollectionFactory = $imageCollectionFactory;
        $this->_imageHelper = $imageHelper;
        $this->_systemConfig = $systemConfig;
    }

    /**
     * Get label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Images');
    }

    /**
     * get images json data of store.
     *
     * @return string
     */
    public function getImageJsonData()
    {
        /** @var \Magestore\Storelocator\Model\Store $store */
        $store = $this->_coreRegistry->registry('storelocator_store');

        $imageArray = [];
        foreach ($store->getImages() as $image) {
            $imageData = [
                'file' => $image->getPath(),
                'url' => $this->_imageHelper->getMediaUrlImage($image->getPath()),
                'image_id' => $image->getId(),
            ];

            if ($store->getBaseimageId() == $image->getId()) {
                $imageData['base'] = 1;
            }

            $imageArray[] = $imageData;
        }

        return $this->_jsonHelper->jsonEncode($imageArray);
    }

    /**
     * Get url to upload files.
     *
     * @return string
     */
    public function getUploadUrl()
    {
        return $this->_backendUrl->getUrl('storelocatoradmin/store_gallery/upload');
    }

    /**
     * Get maximum file size to upload in bytes.
     *
     * @return int
     */
    public function getFileMaxSize()
    {
        return $this->_fileConfig->getMaxFileSize();
    }

    /**
     * get maximum image count.
     *
     * @return mixed
     */
    public function getMaximumImageCount()
    {
        return $this->_systemConfig->getMaxImageGallery();
    }
}
