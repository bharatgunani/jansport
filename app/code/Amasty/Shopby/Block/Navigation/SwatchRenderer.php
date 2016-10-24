<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
namespace Amasty\Shopby\Block\Navigation;



use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;

class SwatchRenderer extends \Magento\Swatches\Block\LayeredNavigation\RenderLayered
{
    protected $urlBuilderHelper;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, Attribute $eavAttribute,
        AttributeFactory $layerAttribute,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Swatches\Helper\Media $mediaHelper,
        \Amasty\Shopby\Helper\UrlBuilder $urlBuilderHelper,
        array $data = []
    ) {
        parent::__construct(
            $context, $eavAttribute, $layerAttribute, $swatchHelper,
            $mediaHelper, $data
        );
        $this->urlBuilderHelper = $urlBuilderHelper;
    }

    /**
     * @param string $attributeCode
     * @param int $optionId
     * @return string
     */
    public function buildUrl($attributeCode, $optionId)
    {
        return $this->urlBuilderHelper->buildUrl($this->filter, $optionId);
    }
}
