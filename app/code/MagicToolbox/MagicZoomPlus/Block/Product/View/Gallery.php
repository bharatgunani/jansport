<?php

/**
 * Magic Zoom Plus view block
 *
 */
namespace MagicToolbox\MagicZoomPlus\Block\Product\View;

use Magento\Framework\Data\Collection;
use MagicToolbox\MagicZoomPlus\Helper\Data;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    /**
     * Helper
     *
     * @var \MagicToolbox\MagicZoomPlus\Helper\Data
     */
    public $magicToolboxHelper = null;

    /**
     * MagicZoomPlus module core class
     *
     * @var \MagicToolbox\MagicZoomPlus\Classes\MagicZoomPlusModuleCoreClass
     *
     */
    public $toolObj = null;

    /**
     * Rendered gallery HTML
     * @var array
     */
    protected $renderedGalleryHtml = [];

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \MagicToolbox\MagicZoomPlus\Helper\Data $magicToolboxHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \MagicToolbox\MagicZoomPlus\Helper\Data $magicToolboxHelper,
        array $data = []
    ) {
        $this->magicToolboxHelper = $magicToolboxHelper;
        $this->toolObj = $this->magicToolboxHelper->getToolObj();
        parent::__construct($context, $arrayUtils, $jsonEncoder, $data);
    }

    /**
     * Get escaper
     *
     * @return \Magento\Framework\Escaper
     */
    public function getEscaper()
    {
        return $this->_escaper;
    }

    /**
     * Retrieve collection of gallery images
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return Magento\Framework\Data\Collection
     */
    public function getGalleryImagesCollection($product = null)
    {
        static $images = [];
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $id = $product->getId();
        if (!isset($images[$id])) {
            $images[$id] = $product->getMediaGalleryImages();
            if ($images[$id] instanceof \Magento\Framework\Data\Collection) {
                foreach ($images[$id] as $image) {
                    /* @var \Magento\Framework\DataObject $image */
                    if ($image->getMediaType() != 'image') {
                        continue;
                    }

                    $img = $this->_imageHelper->init($product, 'product_page_image_large', ['width' => null, 'height' => null])
                            ->setImageFile($image->getFile())
                            ->getUrl();

                    $originalSizeArray = $this->_imageHelper->getOriginalSizeArray();

                    if ($this->toolObj->params->checkValue('square-images', 'Yes')) {
                        $bigImageSize = ($originalSizeArray[0] > $originalSizeArray[1]) ? $originalSizeArray[0] : $originalSizeArray[1];
                        $img = $this->_imageHelper->init($product, 'product_page_image_large')
                                ->setImageFile($image->getFile())
                                ->resize($bigImageSize)
                                ->getUrl();
                    }
                    $image->setData('large_image_url', $img);

                    list($w, $h) = $this->magicToolboxHelper->magicToolboxGetSizes('thumb', $originalSizeArray);
                    $medium = $this->_imageHelper->init($product, 'product_page_image_medium', ['width' => $w, 'height' => $h])
                            ->setImageFile($image->getFile())
                            ->getUrl();
                    $image->setData('medium_image_url', $medium);

                    list($w, $h) = $this->magicToolboxHelper->magicToolboxGetSizes('selector', $originalSizeArray);
                    $thumb = $this->_imageHelper->init($product, 'product_page_image_small', ['width' => $w, 'height' => $h])
                            ->setImageFile($image->getFile())
                            ->getUrl();
                    $image->setData('small_image_url', $thumb);
                }
            }
        }
        return $images[$id];
    }

    /**
     * Retrieve original gallery block
     *
     * @return mixed
     */
    public function getOriginalBlock()
    {
        $data = $this->_coreRegistry->registry('magictoolbox');
        return is_null($data) ? null : $data['blocks']['product.info.media.image'];
    }

    /**
     * Retrieve another gallery block
     *
     * @return mixed
     */
    public function getAnotherBlock()
    {
        $data = $this->_coreRegistry->registry('magictoolbox');
        if ($data) {
            $skip = true;
            foreach ($data['blocks'] as $name => $block) {
                if ($name == 'product.info.media.magiczoomplus') {
                    $skip = false;
                    continue;
                }
                if ($skip) {
                    continue;
                }
                if ($block) {
                    return $block;
                }
            }
        }
        return null;
    }

    /**
     * Check for installed modules, which can operate in cooperative mode
     *
     * @return bool
     */
    public function isCooperativeModeAllowed()
    {
        $data = $this->_coreRegistry->registry('magictoolbox');
        return is_null($data) ? false : $data['cooperative-mode'];
    }

    /**
     * Get thumb switcher initialization attribute
     *
     * @return string
     */
    public function getThumbSwitcherInitAttribute()
    {
        static $html = null;
        if ($html === null) {
            $html = '';
            if ($this->isCooperativeModeAllowed()) {
                $id = $this->getProduct()->getId();
                $tool = 'magiczoomplus';
                $switchMethod = $this->toolObj->params->getValue('selectorTrigger');
                if ($switchMethod == 'hover') {
                    $switchMethod = 'mouseover';
                }
                $html = ' data-mage-init=\'{"magicToolboxThumbSwitcher": {"tool": "'.$tool.'", "productId": "'.$id.'", "switchMethod": "'.$switchMethod.'"}}\'';
            }
        }
        return $html;
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->renderGalleryHtml();
        return parent::_beforeToHtml();
    }

    /**
     * Get rendered HTML
     *
     * @param integer $id
     * @return string
     */
    public function getRenderedHtml($id = null)
    {
        if (is_null($id)) {
            $id = $this->getProduct()->getId();
        }
        return isset($this->renderedGalleryHtml[$id]) ? $this->renderedGalleryHtml[$id] : '';
    }

    /**
     * Render gallery block HTML
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $isAssociatedProduct
     * @param array $data
     * @return $this
     */
    public function renderGalleryHtml($product = null, $isAssociatedProduct = false, $data = [])
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $id = $product->getId();
        if (!isset($this->renderedGalleryHtml[$id])) {
            $this->toolObj->params->setProfile('product');
            $name = $product->getName();
            $productImage = $product->getImage();
            $mainImageHTML = '';
            $selectorsArray = [];

            $images = $this->getGalleryImagesCollection($product);

            $originalBlock = $this->getOriginalBlock();

            if (!$images->count()) {
                $this->renderedGalleryHtml[$id] = $isAssociatedProduct ? '' : $this->getPlaceholderHtml();
                return $this;
            }

            foreach ($images as $image) {

                if ($image->getMediaType() != 'image') {
                    continue;
                }

                $label = $image->getLabel();
                if (empty($label)) {
                    $label = $name;
                }

                if (empty($mainImageHTML) || $productImage == $image->getFile()) {
                    $mainImageHTML = $this->toolObj->getMainTemplate([
                        'id' => $id,
                        'img' => $image->getData('large_image_url'),
                        'thumb' => $image->getData('medium_image_url'),
                        'title' => $name,
                        'alt' => $name,
                    ]);
                }

                $selectorsArray[] = $this->toolObj->getSelectorTemplate([
                    'id' => $id,
                    'group' => 'product-page',
                    'img' => $image->getData('large_image_url'),
                    'thumb' => $image->getData('small_image_url'),
                    'medium' => $image->getData('medium_image_url'),
                    'title' => $label,
                    'alt' => $label
                ]);

            }

            //NOTE: cooperative mode
            if (isset($data['magic360-html'])) {
                $mainImageHTML =
                    '<div id="mainImageContainer" style="display: none;"><div>'.$mainImageHTML.'</div></div>'.
                    '<div id="magic360Container">'.$data['magic360-html'].'</div>';
                if (isset($data['magic360-icon'])) {
                    $data['magic360-icon'] =
                        '<a class="m360-selector active-selector" title="360" href="#" onclick="return false;">'.
                        '<img class="" src="'.$data['magic360-icon'].'" alt="360" />'.
                        '</a>';
                    array_unshift($selectorsArray, $data['magic360-icon']);
                }
                foreach ($selectorsArray as &$selector) {
                    if (preg_match('#(<a\b[^>]*?\bclass=")([^"]*+")#i', $selector, $match)) {
                        $selector = str_replace($match[0], $match[1].'zoom-with-360 '.$match[2], $selector);
                    } else {
                        $selector = str_replace('<a ', '<a class="zoom-with-360" ', $selector);
                    }
                }
            }

            if (empty($selectorsArray)) {
                if ($originalBlock) {
                    $this->renderedGalleryHtml[$id] = $isAssociatedProduct ? '' : $this->getPlaceholderHtml();
                }
                return $this;
            }
            $additionalClasses = '';
            $scrollOptions = '';
            if ($scroll = $this->magicToolboxHelper->getScrollObj()) {
                $additionalClasses = $this->toolObj->params->getValue('scroll-extra-styles');
                if (empty($additionalClasses)) {
                    $additionalClasses = 'MagicScroll';
                } else {
                    $additionalClasses = 'MagicScroll '.trim($additionalClasses);
                }


                $scrollOptions = $scroll->params->serialize(false, '', 'magiczoomplus-magicscroll-product');

                if ($this->isCooperativeModeAllowed()) {
                    //NOTE: disable MagicScroll on page load to start manually
                    $scrollOptions = 'autostart:false;'.$scrollOptions;
                }

                if (!empty($scrollOptions)) {
                    $scrollOptions = " data-options=\"{$scrollOptions}\"";
                }
            }
            $selectorMaxWidth = (int)$this->toolObj->params->getValue('selector-max-width');
            $thumbSwitcher = '';
            if (!$isAssociatedProduct && $this->isCooperativeModeAllowed()) {
                $thumbSwitcher = $this->getThumbSwitcherInitAttribute();
            }

            $layout = $this->toolObj->params->getValue('template');
            ob_start();
            try {
                include ($this->getTemplateFile('MagicToolbox_MagicZoomPlus::product/view/layouts/'.$layout.'.phtml'));
            } catch (\Exception $exception) {
                ob_end_clean();
                throw $exception;
            }
            $this->renderedGalleryHtml[$id] = ob_get_clean();
        }
        return $this;
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
            $placeholderUrl = $this->_imageHelper->getDefaultPlaceholderUrl('image');
            list($width, $height) = $this->magicToolboxHelper->magicToolboxGetSizes('thumb');
            $html = '<div class="MagicToolboxContainer placeholder"'.$this->getThumbSwitcherInitAttribute().' style="width: '.$width.'px;height: '.$height.'px">'.
                    '<span class="align-helper"></span>'.
                    '<img src="'.$placeholderUrl.'"/>'.
                    '</div>';
        }
        return $html;
    }
}
