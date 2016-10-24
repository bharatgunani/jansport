<?php

namespace MagicToolbox\MagicZoomPlus\Helper;

/**
 * Data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Model factory
     * @var \MagicToolbox\MagicZoomPlus\Model\ConfigFactory
     */
    protected $_modelConfigFactory = null;

    /**
     * MagicZoomPlus module core class
     *
     * @var \MagicToolbox\MagicZoomPlus\Classes\MagicZoomPlusModuleCoreClass
     *
     */
    protected $magiczoomplus = null;

    /**
     * MagicScroll module core class
     *
     * @var \MagicToolbox\MagicZoomPlus\Classes\MagicScrollModuleCoreClass
     *
     */
    protected $magicscroll = null;

    /**
     * @var \MagicToolbox\MagicZoomPlus\Helper\Image
     */
    protected $_imageHelper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Product list block
     *
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    protected $listProductBlock = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \MagicToolbox\MagicZoomPlus\Model\ConfigFactory $modelConfigFactory
     * @param \stdClass $magicscroll
     * @param \MagicToolbox\MagicZoomPlus\Helper\ImageFactory $imageHelperFactory
     * @param \MagicToolbox\MagicZoomPlus\Classes\MagicZoomPlusModuleCoreClass $magiczoomplus
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \MagicToolbox\MagicZoomPlus\Model\ConfigFactory $modelConfigFactory,
        \stdClass $magicscroll,
        \MagicToolbox\MagicZoomPlus\Helper\ImageFactory $imageHelperFactory,
        \MagicToolbox\MagicZoomPlus\Classes\MagicZoomPlusModuleCoreClass $magiczoomplus,
        \Magento\Framework\Registry $registry
    ) {
        $this->_modelConfigFactory = $modelConfigFactory;
        $this->magicscroll = $magicscroll;
        $this->magiczoomplus = $magiczoomplus;
        $this->_imageHelper = $imageHelperFactory->create();
        $this->coreRegistry = $registry;
        parent::__construct($context);
    }

    public function getToolObj()
    {
        static $doInit = true;
        if ($doInit) {
            $model = $this->_modelConfigFactory->create();
            $collection = $model->getCollection();
            $collection->addFieldToFilter('platform', 0);
            $collection->addFieldToFilter('status', ['neq' => 0]);
            $data = $collection->getData();
            foreach ($data as $key => $param) {
                $this->magiczoomplus->params->setValue($param['name'], $param['value'], $param['profile']);
            }
            $doInit = false;
        }
        return $this->magiczoomplus;
    }

    public function getScrollObj()
    {
        static $doInit = true;
        if ($doInit) {
            //NOTE: init main tool
            $this->getToolObj();
            if ($this->magiczoomplus->params->checkValue('magicscroll', 'Yes', 'product')) {
                //NOTE: load params in a separate profile, in order not to overwrite the options of MagicScroll module
                $this->magicscroll->params->appendParams($this->magiczoomplus->params->getParams('product'), 'magiczoomplus-magicscroll-product');
                $this->magicscroll->params->setValue('orientation', ($this->magiczoomplus->params->checkValue('template', ['left', 'right'], 'product') ? 'vertical' : 'horizontal'), 'magiczoomplus-magicscroll-product');
            } else {
                $this->magicscroll = null;
            }
            $doInit = false;
        }
        return $this->magicscroll;
    }

    /**
     * Retrieve video settings data
     *
     * @return array
     */
    public function getVideoSettings()
    {
        static $videoSettingData = null;
        if ($videoSettingData === null) {
            $videoSettingData = [
                'playIfBase' => (int)$this->scopeConfig->getValue(\Magento\ProductVideo\Helper\Media::XML_PATH_PLAY_IF_BASE),
                'showRelated' => (int)$this->scopeConfig->getValue(\Magento\ProductVideo\Helper\Media::XML_PATH_SHOW_RELATED),
                'videoAutoRestart' => (int)$this->scopeConfig->getValue(\Magento\ProductVideo\Helper\Media::XML_PATH_VIDEO_AUTO_RESTART),
            ];
        }
        return $videoSettingData;
    }

    /**
     * Public method to get image sizes
     *
     * @return array
     */
    public function magicToolboxGetSizes($sizeType, $originalSizes = [])
    {
        $w = $this->magiczoomplus->params->getValue($sizeType.'-max-width');
        $h = $this->magiczoomplus->params->getValue($sizeType.'-max-height');
        if (empty($w)) {
            $w = 0;
        }
        if (empty($h)) {
            $h = 0;
        }
        if ($this->magiczoomplus->params->checkValue('square-images', 'No')) {
            //NOTE: fix for bad images
            if (empty($originalSizes[0]) || empty($originalSizes[1])) {
                return [$w, $h];
            }
            list($w, $h) = $this->calculateSize($originalSizes[0], $originalSizes[1], $w, $h);
        } else {
            $h = $w = $h ? ($w ? min($w, $h) : $h) : $w;
        }
        return [$w, $h];
    }

    /**
     * Public method to calculate sizes
     *
     * @return array
     */
    private function calculateSize($originalW, $originalH, $maxW = 0, $maxH = 0)
    {
        if (!$maxW && !$maxH) {
            return [$originalW, $originalH];
        } elseif (!$maxW) {
            $maxW = ($maxH * $originalW) / $originalH;
        } elseif (!$maxH) {
            $maxH = ($maxW * $originalH) / $originalW;
        }
        $sizeDepends = $originalW/$originalH;
        $placeHolderDepends = $maxW/$maxH;
        if ($sizeDepends > $placeHolderDepends) {
            $newW = $maxW;
            $newH = $originalH * ($maxW / $originalW);
        } else {
            $newW = $originalW * ($maxH / $originalH);
            $newH = $maxH;
        }
        return [round($newW), round($newH)];
    }

    /**
     * Get HTML data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $isAssociatedProduct
     * @param array $mediaAttributeCodes
     * @return string
     */
    public function getHtmlData($product, $isAssociatedProduct = false, $mediaAttributeCodes = ['small_image'])
    {
        static $_html = [];
        $id = $product->getId();
        $key = implode('_', $mediaAttributeCodes);
        if (!isset($_html[$key])) {
            $_html[$key] = [];
        }
        $html = & $_html[$key];
        if (!isset($html[$id])) {
            $this->magiczoomplus->params->setProfile('category');

            /** @var $listProductBlock \Magento\Catalog\Block\Product\ListProduct */
            $listProductBlock = $this->getListProductBlock();
            if ($listProductBlock) {
                $productImage = $listProductBlock->getImage($product, $listProductBlock->getMode() == 'grid' ? 'category_page_grid' : 'category_page_list');
                $productImageWidth = $productImage->getWidth();
            } else {
                list($productImageWidth, ) = $this->magicToolboxGetSizes('thumb');
            }


            $image = 'no_selection';
            foreach ($mediaAttributeCodes as $mediaAttributeCode) {
                $image = $product->getData($mediaAttributeCode);
                if ($image && $image != 'no_selection') {
                    break;
                }
            }
            if (!$image || $image == 'no_selection') {
                $html[$id] = $isAssociatedProduct ? '' : $this->getPlaceholderHtml();
                return $html[$id];
            }

            $img = $this->_imageHelper->init($product, 'product_page_image_large', ['width' => null, 'height' => null])
                     ->setImageFile($image)
                     ->getUrl();
            $originalSizeArray = $this->_imageHelper->getOriginalSizeArray();
            if ($this->magiczoomplus->params->checkValue('square-images', 'Yes')) {
                $bigImageSize = ($originalSizeArray[0] > $originalSizeArray[1]) ? $originalSizeArray[0] : $originalSizeArray[1];
                $img = $this->_imageHelper->init($product, 'product_page_image_large')
                        ->setImageFile($image)
                        ->resize($bigImageSize)
                        ->getUrl();
            }
            list($w, $h) = $this->magicToolboxGetSizes('thumb', $originalSizeArray);
            $medium = $this->_imageHelper->init($product, 'product_page_image_medium', ['width' => $w, 'height' => $h])
                    ->setImageFile($image)
                    ->getUrl();

            $name = $product->getName();
            $link = $this->magiczoomplus->params->checkValue('link-to-product-page', 'Yes');
            //TODO: get parent product link for associated products
            $link = $link && !$isAssociatedProduct ? $product->getProductUrl() : false;
            $html[$id] = $this->magiczoomplus->getMainTemplate([
                'id' => '-category-'.$id,
                'group' => 'category',
                'img' => $img,
                'thumb' => $medium,
                'title' => $name,
                //'alt' => $name,
                'link' => $link,
            ]);

            $html[$id] = '<div class="MagicToolboxContainer" style="width: '.$productImageWidth.'px;">'.$html[$id].'</div>';
        }

        return $html[$id];
    }

    /**
     * Retrieve another renderer
     *
     * @return mixed
     */
    public function getAnotherRenderer()
    {
        $data = $this->coreRegistry->registry('magictoolbox_category');
        if ($data) {
            $skip = true;
            foreach ($data['renderers'] as $name => $renderer) {
                if ($name == 'configurable.magiczoomplus') {
                    $skip = false;
                    continue;
                }
                if ($skip) {
                    continue;
                }
                if ($renderer) {
                    return $renderer;
                }
            }
        }
        return null;
    }

    /**
     * Get placeholder HTML
     *
     * @return string
     */
    public function getPlaceholderHtml()
    {
        static $html = null;
        if ($html === null) {
            $placeholderUrl = $this->_imageHelper->getDefaultPlaceholderUrl('small_image');
            list($width, $height) = $this->magicToolboxGetSizes('thumb');
            $html = '<div class="MagicToolboxContainer placeholder" style="width: '.$width.'px;height: '.$height.'px">'.
                    '<span class="align-helper"></span>'.
                    '<img src="'.$placeholderUrl.'"/>'.
                    '</div>';
        }
        return $html;
    }

    /**
     * Set product list block
     *
     */
    public function setListProductBlock(\Magento\Catalog\Block\Product\ListProduct $block)
    {
        $this->listProductBlock = $block;
    }

    /**
     * Get product list block
     *
     * @return \Magento\Catalog\Block\Product\ListProduct
     */
    public function getListProductBlock()
    {
        return $this->listProductBlock;
    }

    /**
     * Public method for retrieve statuses
     *
     * @return array
     */
    public function getStatuses($profile = false, $force = false)
    {
        static $result = null;
        if (is_null($result) || $force) {
            $result = [];
            $model = $this->_modelConfigFactory->create();
            $collection = $model->getCollection();
            $collection->addFieldToFilter('platform', 0);
            $data = $collection->getData();
            foreach ($data as $key => $param) {
                if (!isset($result[$param['profile']])) {
                    $result[$param['profile']] = [];
                }
                $result[$param['profile']][$param['name']] = $param['status'];
            }
        }
        return isset($result[$profile]) ? $result[$profile] : $result;
    }

    /**
     * Public method for retrieve config map
     *
     * @return array
     */
    public function getConfigMap()
    {
        return unserialize('a:3:{s:7:"default";a:8:{s:7:"General";a:1:{i:0;s:28:"include-headers-on-all-pages";}s:24:"Positioning and Geometry";a:7:{i:0;s:15:"thumb-max-width";i:1;s:16:"thumb-max-height";i:2;s:9:"zoomWidth";i:3;s:10:"zoomHeight";i:4;s:12:"zoomPosition";i:5;s:13:"square-images";i:6;s:12:"zoomDistance";}s:15:"Multiple images";a:4:{i:0;s:15:"selectorTrigger";i:1;s:18:"selector-max-width";i:2;s:19:"selector-max-height";i:3;s:16:"transitionEffect";}s:13:"Miscellaneous";a:5:{i:0;s:8:"lazyZoom";i:1;s:10:"rightClick";i:2;s:20:"link-to-product-page";i:3;s:12:"show-message";i:4;s:7:"message";}s:9:"Zoom mode";a:6:{i:0;s:8:"zoomMode";i:1;s:6:"zoomOn";i:2;s:7:"upscale";i:3;s:9:"smoothing";i:4;s:12:"variableZoom";i:5;s:11:"zoomCaption";}s:11:"Expand mode";a:6:{i:0;s:6:"expand";i:1;s:14:"expandZoomMode";i:2;s:12:"expandZoomOn";i:3;s:13:"expandCaption";i:4;s:19:"closeOnClickOutside";i:5;s:8:"cssClass";}s:4:"Hint";a:7:{i:0;s:4:"hint";i:1;s:17:"textHoverZoomHint";i:2;s:17:"textClickZoomHint";i:3;s:14:"textExpandHint";i:4;s:12:"textBtnClose";i:5;s:11:"textBtnNext";i:6;s:11:"textBtnPrev";}s:6:"Mobile";a:4:{i:0;s:17:"zoomModeForMobile";i:1;s:26:"textHoverZoomHintForMobile";i:2;s:26:"textClickZoomHintForMobile";i:3;s:23:"textExpandHintForMobile";}}s:7:"product";a:9:{s:7:"General";a:3:{i:0;s:13:"enable-effect";i:1;s:8:"template";i:2;s:11:"magicscroll";}s:24:"Positioning and Geometry";a:7:{i:0;s:15:"thumb-max-width";i:1;s:16:"thumb-max-height";i:2;s:9:"zoomWidth";i:3;s:10:"zoomHeight";i:4;s:12:"zoomPosition";i:5;s:13:"square-images";i:6;s:12:"zoomDistance";}s:15:"Multiple images";a:4:{i:0;s:15:"selectorTrigger";i:1;s:18:"selector-max-width";i:2;s:19:"selector-max-height";i:3;s:16:"transitionEffect";}s:13:"Miscellaneous";a:4:{i:0;s:8:"lazyZoom";i:1;s:10:"rightClick";i:2;s:12:"show-message";i:3;s:7:"message";}s:9:"Zoom mode";a:6:{i:0;s:8:"zoomMode";i:1;s:6:"zoomOn";i:2;s:7:"upscale";i:3;s:9:"smoothing";i:4;s:12:"variableZoom";i:5;s:11:"zoomCaption";}s:11:"Expand mode";a:6:{i:0;s:6:"expand";i:1;s:14:"expandZoomMode";i:2;s:12:"expandZoomOn";i:3;s:13:"expandCaption";i:4;s:19:"closeOnClickOutside";i:5;s:8:"cssClass";}s:4:"Hint";a:7:{i:0;s:4:"hint";i:1;s:17:"textHoverZoomHint";i:2;s:17:"textClickZoomHint";i:3;s:14:"textExpandHint";i:4;s:12:"textBtnClose";i:5;s:11:"textBtnNext";i:6;s:11:"textBtnPrev";}s:6:"Mobile";a:4:{i:0;s:17:"zoomModeForMobile";i:1;s:26:"textHoverZoomHintForMobile";i:2;s:26:"textClickZoomHintForMobile";i:3;s:23:"textExpandHintForMobile";}s:6:"Scroll";a:14:{i:0;s:5:"width";i:1;s:6:"height";i:2;s:4:"mode";i:3;s:5:"items";i:4;s:5:"speed";i:5;s:8:"autoplay";i:6;s:4:"loop";i:7;s:4:"step";i:8;s:6:"arrows";i:9;s:10:"pagination";i:10;s:6:"easing";i:11;s:13:"scrollOnWheel";i:12;s:9:"lazy-load";i:13;s:19:"scroll-extra-styles";}}s:8:"category";a:7:{s:7:"General";a:1:{i:0;s:13:"enable-effect";}s:24:"Positioning and Geometry";a:7:{i:0;s:15:"thumb-max-width";i:1;s:16:"thumb-max-height";i:2;s:9:"zoomWidth";i:3;s:10:"zoomHeight";i:4;s:12:"zoomPosition";i:5;s:13:"square-images";i:6;s:12:"zoomDistance";}s:13:"Miscellaneous";a:5:{i:0;s:8:"lazyZoom";i:1;s:10:"rightClick";i:2;s:20:"link-to-product-page";i:3;s:12:"show-message";i:4;s:7:"message";}s:9:"Zoom mode";a:6:{i:0;s:8:"zoomMode";i:1;s:6:"zoomOn";i:2;s:7:"upscale";i:3;s:9:"smoothing";i:4;s:12:"variableZoom";i:5;s:11:"zoomCaption";}s:11:"Expand mode";a:6:{i:0;s:6:"expand";i:1;s:14:"expandZoomMode";i:2;s:12:"expandZoomOn";i:3;s:13:"expandCaption";i:4;s:19:"closeOnClickOutside";i:5;s:8:"cssClass";}s:4:"Hint";a:7:{i:0;s:4:"hint";i:1;s:17:"textHoverZoomHint";i:2;s:17:"textClickZoomHint";i:3;s:14:"textExpandHint";i:4;s:12:"textBtnClose";i:5;s:11:"textBtnNext";i:6;s:11:"textBtnPrev";}s:6:"Mobile";a:4:{i:0;s:17:"zoomModeForMobile";i:1;s:26:"textHoverZoomHintForMobile";i:2;s:26:"textClickZoomHintForMobile";i:3;s:23:"textExpandHintForMobile";}}}');
    }
}
