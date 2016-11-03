<?php
/**
 * Copyright © 2015 CommerceExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CommerceExtensions\ProductImportExport\Controller\Adminhtml\Data;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
#use Magento\Catalog\Model\Product;
#use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class ExportPost extends \CommerceExtensions\ProductImportExport\Controller\Adminhtml\Data
{
    /**
     * Export action from import/export data
     *
     * @return ResponseInterface
     */
	const MULTI_DELIMITER = ' , ';
	
	/* REMOVE ATTRIBUTES WE DO NOT WANT TO EXPORT */ 
    protected $_systemFields = ['price','weight','special_price','cost','msrp','sku','attribute_set_id','entity_id','type_id','has_options','required_options','name','swatch_image','image','small_image','thumbnail','url_key','meta_title','meta_description','meta_keyword','image_label','small_image_label','thumbnail_label','short_description','description','created_at','updated_at','special_from_date','special_to_date','custom_design_from','custom_design_to','news_from_date','news_to_date','custom_layout_update'];
	
    protected $_disabledAttributes = ['attribute_set_id','tier_price','entity_id','old_id','media_gallery','sku_type','weight_type','shipment_type','price_type','groupyprice'];
	
    protected $_attributes = array();
	
	 /**
     * Prepare products media gallery
     *
     * @param  int[] $productIds
     * @return array 
     */
	protected $resourceConnection;
	protected $fileFactory;
	protected $frameworkUrl;
	protected $storeManager;
	protected $categoryModel;
	protected $stockRegistry;
	protected $taxClassModel;
	protected $productModel;
	protected $configurableProduct;
	protected $downloadableLink;
	protected $downloadableSample;
	protected $bundleOption;
	protected $bundleSelection;
	protected $attributeSet;
    protected $productAttributeCollection;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
		\Magento\Framework\Url $frameworkUrl,
		\Magento\Store\Model\StoreManager $storeManager,
		\Magento\Catalog\Model\Category $categoryModel,
		\Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
		\Magento\Tax\Model\ClassModel $taxClassModel,
        \Magento\Catalog\Model\Product $productModel,
		\Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
		\Magento\Downloadable\Model\Link $downloadableLink,
		\Magento\Downloadable\Model\Sample $downloadableSample,
		\Magento\Bundle\Model\Option $bundleOption,
		\Magento\Bundle\Model\Selection $bundleSelection,
		\Magento\Eav\Model\Entity\Attribute\Set $attributeSet,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributeCollection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->fileFactory = $fileFactory;
        $this->_frameworkUrl = $frameworkUrl;
        $this->_storeManager = $storeManager;
        $this->_categoryModel = $categoryModel;
        $this->_stockRegistry = $stockRegistry;
        $this->_taxClassModel = $taxClassModel;
        $this->_productModel = $productModel;
        $this->_configurableProduct = $configurableProduct;
        $this->_downloadableLink = $downloadableLink;
        $this->_downloadableSample = $downloadableSample;
        $this->_bundleOption = $bundleOption;
		$this->_bundleSelection = $bundleSelection;
		$this->_attributeSet = $attributeSet;
        $this->_productAttributeCollection = $productAttributeCollection;
        parent::__construct($context,$fileFactory);
    }
	
    protected function getMediaGallery(array $productIds)
    {
        if (empty($productIds)) {
            return [];
        }
		$_resource = $this->resourceConnection;
		$connection = $_resource->getConnection();
        $select = $connection->select()->from(
            ['mg' => $_resource->getTableName('catalog_product_entity_media_gallery')],
            [
                'mg.value_id',
                'mg.attribute_id',
                'filename' => 'mg.value',
                'mgv.label',
                'mgv.position',
                'mgv.disabled'
            ]
        )->joinLeft(
            ['mgv' => $_resource->getTableName('catalog_product_entity_media_gallery_value')],
            '(mg.value_id = mgv.value_id AND mgv.store_id = 0)',
            []
        )->where(
            'mg.value_id IN(?)',
            $productIds
        );

        $rowMediaGallery = [];
        $stmt = $connection->query($select);
        while ($mediaRow = $stmt->fetch()) {
            $rowMediaGallery[] = [
                '_media_attribute_id' => $mediaRow['attribute_id'],
                '_media_image' => $mediaRow['filename'],
                '_media_label' => $mediaRow['label'],
                '_media_position' => $mediaRow['position'],
                '_media_is_disabled' => $mediaRow['disabled'],
            ];
        }

        return $rowMediaGallery;
    }

    public function execute()
    {
		$params = $this->getRequest()->getParams();
		
		$_resource = $this->resourceConnection;
		$catalog_product_bundle_option_value = $_resource->getTableName('catalog_product_bundle_option_value');
		$_productData = $this->_productModel;
		$_productAttributes = $this->_productAttributeCollection->load();
		$_stockData = $this->_stockRegistry;
		$connection = $_resource->getConnection();
		
		if($params['export_delimiter'] != "") {
			$delimiter = $params['export_delimiter'];
		} else {
			$delimiter = ",";
		}
		if($params['export_enclose'] != "") {
			$enclose = $params['export_enclose'];
		} else {
			$enclose = "\"";
		}
		
		/* BUILD OUT COLUMNS NOT IN DEFAULT ATTRIBUTES */
		$template = ''.$enclose.'{{store}}'.$enclose.''.$delimiter.''.$enclose.'{{websites}}'.$enclose.''.$delimiter.''.$enclose.'{{attribute_set}}'.$enclose.''.$delimiter.''.$enclose.'{{prodtype}}'.$enclose.''.$delimiter.''.$enclose.'{{related}}'.$enclose.''.$delimiter.''.$enclose.'{{upsell}}'.$enclose.''.$delimiter.''.$enclose.'{{crosssell}}'.$enclose.''.$delimiter.''.$enclose.'{{tier_prices}}'.$enclose.''.$delimiter.''.$enclose.'{{associated}}'.$enclose.''.$delimiter.''.$enclose.'{{config_attributes}}'.$enclose.''.$delimiter.''.$enclose.'{{bundle_options}}'.$enclose.''.$delimiter.''.$enclose.'{{grouped}}'.$enclose.''.$delimiter.''.$enclose.'{{group_price_price}}'.$enclose.''.$delimiter.''.$enclose.'{{downloadable_options}}'.$enclose.''.$delimiter.''.$enclose.'{{downloadable_sample_options}}'.$enclose.''.$delimiter.''.$enclose.'{{gallery_label}}'.$enclose.''.$delimiter.''.$enclose.'{{qty}}'.$enclose.''.$delimiter.''.$enclose.'{{min_qty}}'.$enclose.''.$delimiter.''.$enclose.'{{use_config_min_qty}}'.$enclose.''.$delimiter.''.$enclose.'{{is_qty_decimal}}'.$enclose.''.$delimiter.''.$enclose.'{{backorders}}'.$enclose.''.$delimiter.''.$enclose.'{{use_config_backorders}}'.$enclose.''.$delimiter.''.$enclose.'{{min_sale_qty}}'.$enclose.''.$delimiter.''.$enclose.'{{use_config_min_sale_qty}}'.$enclose.''.$delimiter.''.$enclose.'{{max_sale_qty}}'.$enclose.''.$delimiter.''.$enclose.'{{use_config_max_sale_qty}}'.$enclose.''.$delimiter.''.$enclose.'{{is_in_stock}}'.$enclose.''.$delimiter.''.$enclose.'{{low_stock_date}}'.$enclose.''.$delimiter.''.$enclose.'{{notify_stock_qty}}'.$enclose.''.$delimiter.''.$enclose.'{{use_config_notify_stock_qty}}'.$enclose.''.$delimiter.''.$enclose.'{{manage_stock}}'.$enclose.''.$delimiter.''.$enclose.'{{use_config_manage_stock}}'.$enclose.''.$delimiter.''.$enclose.'{{stock_status_changed_auto}}'.$enclose.''.$delimiter.''.$enclose.'{{use_config_qty_increments}}'.$enclose.''.$delimiter.''.$enclose.'{{qty_increments}}'.$enclose.''.$delimiter.''.$enclose.'{{enable_qty_increments}}'.$enclose.''.$delimiter.''.$enclose.'{{is_decimal_divided}}'.$enclose.''.$delimiter.''.$enclose.'{{use_config_enable_qty_increments}}'.$enclose.''.$delimiter.''.$enclose.'{{use_config_enable_qty_inc}}'.$enclose.''.$delimiter.''.$enclose.'{{stock_status_changed_automatically}}'.$enclose.''.$delimiter.''.$enclose.'{{product_id}}'.$enclose.''.$delimiter.''.$enclose.'{{store_id}}'.$enclose.''.$delimiter.''.$enclose.'{{additional_attributes}}'.$enclose.''.$delimiter.'';
		
		$attributesArray = array('store' => 'store', 'websites' => 'websites', 'attribute_set' => 'attribute_set', 'prodtype' => 'prodtype', 'related' => 'related', 'upsell' => 'upsell', 'crosssell' => 'crosssell', 'tier_prices' => 'tier_prices', 'associated' => 'associated', 'config_attributes' => 'config_attributes', 'bundle_options' => 'bundle_options', 'grouped' => 'grouped', 'group_price_price' => 'group_price_price', 'downloadable_options' => 'downloadable_options', 'downloadable_sample_options' => 'downloadable_sample_options', 'gallery_label' => 'gallery_label', 'qty' => 'qty', 'min_qty' => 'min_qty', 'use_config_min_qty' => 'use_config_min_qty', 'is_qty_decimal' => 'is_qty_decimal', 'backorders' => 'backorders', 'use_config_backorders' => 'use_config_backorders', 'min_sale_qty' => 'min_sale_qty', 'use_config_min_sale_qty' => 'use_config_min_sale_qty', 'max_sale_qty' => 'max_sale_qty', 'use_config_max_sale_qty' => 'use_config_max_sale_qty', 'is_in_stock' => 'is_in_stock', 'low_stock_date' => 'low_stock_date', 'notify_stock_qty' => 'notify_stock_qty', 'use_config_notify_stock_qty' => 'use_config_notify_stock_qty', 'manage_stock' => 'manage_stock', 'use_config_manage_stock' => 'use_config_manage_stock', 'stock_status_changed_auto' => 'stock_status_changed_auto', 'use_config_qty_increments' => 'use_config_qty_increments', 'qty_increments' => 'qty_increments', 'enable_qty_increments' => 'enable_qty_increments', 'is_decimal_divided' => 'is_decimal_divided', 'use_config_enable_qty_increments' => 'use_config_enable_qty_increments', 'use_config_enable_qty_inc' => 'use_config_enable_qty_inc', 'stock_status_changed_automatically' => 'stock_status_changed_automatically', 'product_id' => 'product_id', 'store_id' => 'store_id', 'additional_attributes' => 'additional_attributes');
		
		if($params['export_category_paths'] == "true") {
			$template .= ''.$enclose.'{{categories}}'.$enclose.''.$delimiter.'';
			$attributesArray = array_merge($attributesArray, array('categories' => 'categories'));
		}
		
		#$product_stock_attributes = $_stockData->getStockItem(2);
		
		foreach ($_productAttributes as $productAttr) {
			$col = $productAttr->getAttributeCode();
			if (!in_array($col, $this->_disabledAttributes)) {
				$attributesArray[$col] = $col;
				$template .= ''.$enclose.'{{'.$col.'}}'.$enclose.''.$delimiter.'';
				#echo "ATTRIBUTE: " . $col;
			}
		}
		
		#$product_attributes = $_productData->getAttributes();
		/* PRODUCT ATTRIBUTES 
        foreach($product_attributes as $col=>$val){
			if (!in_array($col, $this->_disabledAttributes)) {
				$attributesArray[$col] = $col;
				$template .= '"{{'.$col.'}}",';
				#echo "ATTRIBUTE: " . $col;
			}
		}
		*/
		#exit;
		
		if($params['product_id_from'] != "" && $params['product_id_to'] != "") {
			$productCollection = $_productData->getCollection()
									->addAttributeToSelect('*')
									->addAttributeToFilter ( 'entity_id' , array( "from" => $params['product_id_from'], "to" => $params['product_id_to'] ))
									->load();	
		} else {
			$productCollection = $_productData->getCollection()
									->addAttributeToSelect('*')
									->load();
		}
		$Custdata = array();
		foreach($productCollection as $product){
			if(is_array($product->getOptions())) {
				foreach ($product->getOptions() as $o) {
					if(!empty($o->getData())){
						$customoptionstitle = str_replace(" ", "_", $o->getData('title')) . "__" . $o->getData('type') . "__" . $o->getData('is_require') . "__". $o->getData('sort_order') . "," ;
						$CustInattrArrayAndTemp = substr_replace($customoptionstitle,"",-1);
						$attributesArray = array_merge($attributesArray, array($CustInattrArrayAndTemp => $CustInattrArrayAndTemp));
						$template .= ''.$enclose.'{{'.$CustInattrArrayAndTemp.'}}'.$enclose.''.$delimiter.'';
						$Custdata[] = $CustInattrArrayAndTemp;
					}
				}
			}
		}
       
		$headers = new \Magento\Framework\DataObject($attributesArray);
        $ExtendToString = $headers->toString($template);
		$content = str_replace('__' , ':' , $ExtendToString);
		$content .= "\n";
		$storeTemplate = [];
		
		#$productCollection = $_productData->getCollection();
		$productCollection->addAttributeToSelect(
                			'name'
           				  )->addAttributeToSelect(
							'price'
           				  )->addAttributeToSelect(
							'special_price'
           				  )->addAttributeToSelect(
							'special_from_date'
           				  )->addAttributeToSelect(
							'special_to_date'
           				  )->addAttributeToSelect(
							'cost'
           				  )->addAttributeToSelect(
							'msrp'
           				  )->addAttributeToSelect(
							'weight'
           				  )->addAttributeToSelect(
							'url_key'
           				  )->addAttributeToSelect(
							'meta_title'
           				  )->addAttributeToSelect(
							'meta_keyword'
           				  )->addAttributeToSelect(
							'meta_description'
           				  )->addAttributeToSelect(
							'short_description'
           				  )->addAttributeToSelect(
							'description'
           				  )->addAttributeToSelect(
							'visibility'
           				  )->addAttributeToSelect(
							'image'
           				  )->addAttributeToSelect(
							'small_image'
           				  )->addAttributeToSelect(
							'thumbnail'
           				  )->addAttributeToSelect(
							'swatch_image'
           				  )->addAttributeToSelect(
							'image_label'
           				  )->addAttributeToSelect(
							'small_image_label'
           				  )->addAttributeToSelect(
							'thumbnail_label'
           				  )->addAttributeToSelect(
							'news_from_date'
           				  )->addAttributeToSelect(
							'news_to_date'
           				  )->addAttributeToSelect(
							'options_container'
           				  )->addAttributeToSelect(
							'country_of_manufacture'
           				  )->addAttributeToSelect(
							'page_layout'
           				  )->addAttributeToSelect(
							'custom_design'
           				  )->addAttributeToSelect('*');
						  
		foreach($productCollection as $_product){
			#print_r($_product->getData());
			
			// THIS CLEANS HTML and other " qoute data
			foreach ($_product->getData() as $field => $_productElementData){
				#echo "FIELD: " . $field . "<br/>";	
				#echo "VALUE: " . $_productElementData . "<br/>";
				if(!is_array($_productElementData) && strpos($field, '__') !== '__'){
					$storeTemplate[$field] = str_replace( '"', '""', $_productElementData);
				}
				
				$option="";
				
                if (in_array($field, $this->_systemFields) || is_object($_productElementData)) {
                    continue;
                }
				
				$attribute = $this->getAttribute($field);
                if (!$attribute) {
                    continue;
                }
				#print_r($attribute);
                if ($attribute->usesSource()) {
					#echo "FIELD: " . $field . "<br/>";	
					$option = $attribute->getSource()->getOptionText($_productElementData);
					$_productElementData = $option;
					/*
					if (is_object($attribute->usesSource())) {		
						$option = $attribute->getSource()->getOptionText($_productElementData);
					} else {				
						$option = $attribute->getSource()->getOptionText($_productElementData);					
					}
					*/
				}
				if (is_array($option)) {
					$_productElementData = join(self::MULTI_DELIMITER, $option);
				}
				#echo "FIELD: " . $field . "";	
				#echo "VALUE: " . $_productElementData . "<br/><br/>";
				unset($option);
				$storeTemplate[$field] = $_productElementData;
				
			}
			#exit;
			$storeTemplate['store'] = $this->storeCodeByID($_product->getStoreId());
			$storeTemplate['websites'] = $this->websiteCodeById($_product->getWebsiteIds());
			$storeTemplate['attribute_set'] = $this->attributebyid($_product->getData('attribute_set_id'));
			$storeTemplate['prodtype'] = $_product->getData('type_id');
			$storeTemplate['msrp_enabled'] = $this->msrpriceActual($_product->getData('msrp_display_actual_price_type'));
			$storeTemplate['product_id'] = $_product->getData('entity_id');
			$storeTemplate['url_path'] = $_product->getProductUrl();
			//Retrieve accessible external product attributes
			#$storeTemplate['tax_class_id'] = $this->taxclasswitname($_product->getData('tax_class_id'));
			#$storeTemplate['msrp_display_actual_price_type'] = $this->msrpriceActual($_product->getData('msrp_display_actual_price_type'));
			#$storeTemplate['visibility'] = $this->productVisibility($_product->getData('visibility'));
			#$storeTemplate['status'] = $this->productStatus($_product->getStatus());
			#$storeTemplate['product_type_id'] = $_product->getData('type_id');
			#$storeTemplate['custom_design'] = $this->customDesign($_product->getData('custom_design'));
			#$storeTemplate['page_layout'] = $this->pagelayout($_product->getData('page_layout'));
			$storeTemplate['store_id'] = $_product->getStoreId();
			$storeTemplate['additional_attributes'] = $this->GetExternalFields($_product->getData('entity_id'),$_product->getData('type_id'));
			
			/* PRODUCT CATEGORIES EXPORT START */
			if($params['export_category_paths'] == "true") {
				$storeTemplate['category_ids'] = $this->sptidwithcoma($_product->getCategoryIds());
				$finalimportofcategories = "";
				$okforallcategoriesnow = "";
				$finalvcategoriesproductoptions1 = "";
				$finalvcategoriesproductoptions2 = "";
				$finalvcategoriesproductoptions2before = "";
				foreach(explode(',',$storeTemplate['category_ids']) as $productcategoryId)
				{
						$cat = $this->_categoryModel->load($productcategoryId);
						$finalvcategoriesproductoptions1 = $cat->getName();
						$subcatsforreverse = $cat->getParentIds();
						$subcats = array_shift($subcatsforreverse);
						$subcats1 = array_shift($subcatsforreverse);
						$finalvcategoriesproductoptions2before = "";
						foreach($subcatsforreverse as $subcatsproductcategoryId)
						{
								$subcat = $this->_categoryModel->load($subcatsproductcategoryId);
								$finalvcategoriesproductoptions2before .= $subcat->getName() . "/";
								$subsubcats = $subcat->getChildren();
						}
						$finalimportofcategories .= $finalvcategoriesproductoptions2before . 
						$finalvcategoriesproductoptions1 . " , ";
				}
				$okforallcategoriesnow = substr_replace($finalimportofcategories,"",-3);
				$storeTemplate['categories'] = $okforallcategoriesnow;
			} else {
				$storeTemplate['category_ids'] = $this->sptidwithcoma($_product->getCategoryIds());
			}
			/* PRODUCT CATEGORIES EXPORT END */
			
			/* RELATED */
			$finalrelatedproducts = "";
			$incoming_RelatedProducts = $_product->getRelatedProducts();
			foreach($incoming_RelatedProducts as $relatedproducts_str){
				if($params['export_related_position'] == "true") {
					$finalrelatedproducts .= $relatedproducts_str['position'] .":". $relatedproducts_str->getSku() . ",";
				} else {
					$finalrelatedproducts .= $relatedproducts_str->getSku() . ",";
				}
			}
			$storeTemplate['related'] = substr_replace($finalrelatedproducts,"",-1);
			/* UP SELL */
			$finalupsellproducts = "";
			$incoming_UpSellProducts = $_product->getUpSellProducts();
			foreach($incoming_UpSellProducts as $UpSellproducts_str){
				if($params['export_crossell_position'] == "true") {
					$finalupsellproducts .= $UpSellproducts_str['position'] .":". $UpSellproducts_str->getSku() . ",";
				} else {
					$finalupsellproducts .= $UpSellproducts_str->getSku() . ",";
				}
			}
			$storeTemplate['upsell'] = substr_replace($finalupsellproducts,"",-1);
			/* CROSS SELL  */
			$finalcrosssellproducts = "";
			$incoming_CrossSellProducts = $_product->getCrossSellProducts();
			foreach($incoming_CrossSellProducts as $CrossSellproducts_str){
				if($params['export_upsell_position'] == "true") {
					$finalcrosssellproducts .= $CrossSellproducts_str['position'] .":". $CrossSellproducts_str->getSku() . ",";
				} else {
					$finalcrosssellproducts .= $CrossSellproducts_str->getSku() . ",";
				}
			}
			$storeTemplate['crosssell'] = substr_replace($finalcrosssellproducts,"",-1);
			
			/* EXPORTS TIER PRICING */
			
			if(!empty($_product->getTierPrice())) {
				$tier_pricing = $this->TierPrice($_product->getTierPrice());
			} else {
				if ($attribute = $_product->getResource()->getAttribute('tier_price')) {
					$attribute->getBackend()->afterLoad($_product);
					$tier_pricing = $this->TierPrice($_product->getData('tier_price'));
				}
			}
			$storeTemplate['tier_prices'] = (string)$tier_pricing;
			
			/* EXPORTS CUSTOMER GROUP PRICING 
			$finalgrouped_prices_info="";
			$select_qry_grouped_pricing = "SELECT * FROM ".$_resource->getTableName('catalog_product_entity_group_price')." WHERE entity_id = '".$_product->getId()."'";
			$rows = $connection->query($select_qry_grouped_pricing);
			
			while ($groupRow = $rows->fetch()) {
			  if($groupRow['website_id'] !="" && $groupRow['website_id'] > 0) {
				$finalgrouped_prices_info .= $groupRow['customer_group_id'] . "=" . $groupRow['value'] . "=" . $groupRow['website_id'] . "|";
			  } else {
				$finalgrouped_prices_info .= $groupRow['customer_group_id'] . "=" . $groupRow['value'] . "|";
			  }
			}
			$storeTemplate['group_price_price'] = substr_replace($finalgrouped_prices_info,"",-1);
			*/
			
			/* EXPORTS ASSOICATED BUNDLE SKUS */
			if($_product->getTypeId() == "bundle") {
				$finalbundleoptions = "";
				$finalbundleselectionoptions = "";
				$finalbundleselectionoptionssorting = "";
				$optionModel = $this->_bundleOption->getResourceCollection()->setProductIdFilter($_product->getId());
					
				foreach($optionModel as $eachOption) {
						
						$selectOptionID = "SELECT title FROM ".$catalog_product_bundle_option_value." WHERE option_id = ".$eachOption->getData('option_id')."";
						$Optiondatarows = $connection->query($selectOptionID);
						while ($Option_row = $Optiondatarows->fetch()) {
							$finaltitle = str_replace(' ','_',$Option_row['title']);
						}
						$finalbundleoptions .=  $finaltitle . "," . $eachOption->getData('type') . "," . $eachOption->getData('required') . "," . $eachOption->getData('position') . "|";
						$selectionModel =$this->_bundleSelection->setOptionId($eachOption->getData('option_id'))->getResourceCollection();
						
						foreach($selectionModel as $eachselectionOption) {
							if($eachselectionOption->getData('option_id') == $eachOption->getData('option_id')) {
							$finalbundleselectionoptionssorting .=  $eachselectionOption->getData('sku') . ":" . $eachselectionOption->getData('selection_price_type') . ":" . $eachselectionOption->getData('selection_price_value') . ":" . $eachselectionOption->getData('is_default') . ":" . $eachselectionOption->getData('selection_qty') . ":" . $eachselectionOption->getData('selection_can_change_qty'). ":" . $eachselectionOption->getData('position') . ",";
							}
						}
						$finalbundleselectionoptionssorting = substr_replace($finalbundleselectionoptionssorting,"",-1);
						$finalbundleselectionoptionssorting .=  "|";
						$finalbundleselectionoptions = substr_replace($finalbundleselectionoptionssorting,"",-1);
				}
				$row['bundle_options'] = substr_replace($finalbundleoptions,"",-1);
				$row['bundle_selections'] = substr_replace($finalbundleselectionoptions,"",-1);
			}
			
			/* EXPORTS DOWNLOADABLE OPTIONS */
			$finaldownloabledproductoptions = "";
			$finaldownloabledsampleproductoptions = "";
			
			if($_product->getTypeId() == "downloadable") {
			$_linkCollection = $this->_downloadableLink->getCollection()
								->addProductToFilter($_product->getId())
								->addTitleToResult($_product->getStoreId())
								->addPriceToResult($_product->getStore()->getWebsiteId());

			 foreach ($_linkCollection as $link) {
			 // echo "<pre>";
			 // print_r($link->getData());
			 // echo $link->getStoreId();
			 // exit;
			    /* @var Mage_Downloadable_Model_Link $link */
				#Main file,0.00,3,file,/test.mp3,/sample.mp3
				
				/* if($link->getLinkId() == ""){ $LinkId = "null"; } else { $LinkId = $link->getLinkId(); }
				if($link->getSortOrder() == ""){ $SortOrder = "null"; } else { $SortOrder = $link->getSortOrder(); }
				if($link->getNumberOfDownloads() == ""){ $NumberOfDownloads = "null"; } else { $NumberOfDownloads = $link->getNumberOfDownloads(); }
				if($link->getIsShareable() == ""){ $IsShareable = "null"; } else { $IsShareable = $link->getIsShareable(); }
				if($link->getLinkUrl() == ""){ $LinkUrl = "null"; } else { $LinkUrl = $link->getLinkUrl(); }
				if($link->getLinkFile() == ""){ $LinkFile = "null"; } else { $LinkFile = $link->getLinkFile(); }
				if($link->getLinkType() == ""){ $LinkType = "null"; } else { $LinkType = $link->getLinkType(); }
				if($link->getSampleUrl() == ""){ $SampleUrl = "null"; } else { $SampleUrl = $link->getSampleUrl(); }
				if($link->getSampleFile() == ""){ $SampleFile = "null"; } else { $SampleFile = $link->getSampleFile(); }
				if($link->getSampleType() == ""){ $SampleType = "null"; } else { $SampleType = $link->getSampleType(); } */
				
				//if($link->getDefaultTitle() == ""){ $DefaultTitle = "null"; } else { $DefaultTitle = $link->getDefaultTitle(); }
				//if($link->getStoreTitle() == ""){ $StoreTitle = "null"; } else { $StoreTitle = $link->getStoreTitle(); }
				
				
				/* if($link->getTitle() == ""){ $Title = "null"; } else { $Title = $link->getTitle(); } */
				
				
				//if($link->getDefaultPrice() == ""){ $DefaultPrice = "null"; } else { $DefaultPrice = $link->getDefaultPrice(); }
				//if($link->getWebsitePrice() == ""){ $WebsitePrice = "null"; } else { $WebsitePrice = $link->getWebsitePrice(); }
				
				
				
				/* if($link->getPrice() == ""){ $Price = "null"; } else { $Price = $link->getPrice(); } */
				
				//$finaldownloabledproductoptions .= $LinkId . "," . $SortOrder . "," . $NumberOfDownloads . "," . $IsShareable . "," .$LinkUrl ."," .$LinkFile . "," . $LinkType . "," . $SampleUrl . "," . $SampleFile . "," . $SampleType . "," . $Title . "," . $Price  . "|";

				if($link->getLinkType() =="url" && $link->getSampleType() =="url") {
				$finaldownloabledproductoptions .= $link->getTitle() . "," . $link->getPrice() . "," . $link->getNumberOfDownloads() . "," . $link->getLinkType() . "," . $link->getLinkUrl() . "," . $link->getSampleUrl() . "|";
				} else if($link->getLinkType() =="url" && $link->getSampleType() =="file") {
				$finaldownloabledproductoptions .= $link->getTitle() . "," . $link->getPrice() . "," . $link->getNumberOfDownloads() . "," . $link->getLinkType() . "," . $link->getLinkUrl() . "," . $link->getSampleFile() . "," . $link->getSampleType() . "|";
				} else if($link->getLinkType() =="file" && $link->getSampleType() =="url") {
				$finaldownloabledproductoptions .= $link->getTitle() . "," . $link->getPrice() . "," . $link->getNumberOfDownloads() . "," . $link->getLinkType() . "," . $link->getLinkFile() . "," . $link->getSampleUrl() . "," . $link->getSampleType() . "|";
				} else if($link->getLinkType() =="file" && $link->getSampleType() =="file") {
				$finaldownloabledproductoptions .= $link->getTitle() . "," . $link->getPrice() . "," . $link->getNumberOfDownloads() . "," . $link->getLinkType() . "," . $link->getLinkFile() . "," . $link->getSampleFile() . "|";
				}else if($link->getLinkType() =="file" && $link->getSampleType() ==""){
				$finaldownloabledproductoptions .= $link->getTitle() . "," . $link->getPrice() . "," . $link->getNumberOfDownloads() . "," . $link->getLinkType() . "," . $link->getLinkFile() . "," . $link->getSampleFile() . "|";
				}else if($link->getLinkType() =="url" && $link->getSampleType() ==""){
				$finaldownloabledproductoptions .= $link->getTitle() . "," . $link->getPrice() . "," . $link->getNumberOfDownloads() . "," . $link->getLinkType() . "," . $link->getLinkUrl() . "," . $link->getSampleUrl() . "|";
				}else if($link->getLinkType() =="" && $link->getSampleType() =="file"){
				$finaldownloabledproductoptions .= $link->getTitle() . "," . $link->getPrice() . "," . $link->getNumberOfDownloads() . "," . $link->getLinkType() . "," . $link->getLinkFile() . "," . $link->getSampleFile() . "," . $link->getSampleType() . "|";
				}else if($link->getLinkType() =="" && $link->getSampleType() =="url"){
				$finaldownloabledproductoptions .= $link->getTitle() . "," . $link->getPrice() . "," . $link->getNumberOfDownloads() . "," . $link->getLinkType() . "," . $link->getLinkUrl() . "," . $link->getSampleUrl() . "," . $link->getSampleType() . "|";
				}else{
				$finaldownloabledproductoptions .= "";
				}
				
				
				
			 }
			 	$storeTemplate['downloadable_options'] = substr_replace($finaldownloabledproductoptions,"",-1);
				$_linkSampleCollection = $this->_downloadableSample->getCollection()
									->addProductToFilter($_product->getId())
									->addTitleToResult($_product->getStoreId());
			
				foreach ($_linkSampleCollection as $sample_link) {
					/* @var Mage_Downloadable_Model_Sample $sample_link */
					#Main file,file,/test.mp3,/sample.mp3
					if($sample_link->getSampleType() == "url") {
						$finaldownloabledsampleproductoptions .= $sample_link->getTitle() . "," . $sample_link->getSampleType() . "," . $sample_link->getSampleUrl() . "|";
					} else if($sample_link->getSampleType() == "file") {
						$finaldownloabledsampleproductoptions .= $sample_link->getTitle() . "," . $sample_link->getSampleType() . "," . $sample_link->getSampleFile() . "|";
					}
				}
			 $storeTemplate['downloadable_sample_options'] = substr_replace($finaldownloabledsampleproductoptions,"",-1);
			 
			} else {
					$storeTemplate['downloadable_sample_options'] = "";
					$storeTemplate['downloadable_options'] = "";
			}// end for check of downloadable type
			
			
			
			
			
			/* EXPORTS ASSOICATED CONFIGURABLE SKUS */
			$storeTemplate['associated'] = '';
			if($_product->getTypeId() == "configurable") {
				$associatedProducts = $_product->getTypeInstance()->getUsedProducts($_product, null);
				foreach($associatedProducts as $associatedProduct) {
						$storeTemplate['associated'] .= $associatedProduct->getSku() . ",";
				}
			}
			/* EXPORTS ASSOICATED GROUPED SKUS */
			$storeTemplate['grouped'] = '';
			if($_product->getTypeId() == "grouped") {
				$associatedProducts = $_product->getTypeInstance()->getAssociatedProducts($_product, null);
				foreach($associatedProducts as $associatedProduct) {
						if($params['export_grouped_position'] == "true") {
							$storeTemplate['grouped'] .= $associatedProduct->getPosition() . ":" . $associatedProduct->getSku() . ":" . $associatedProduct->getQty() . ",";
						} else {
							$storeTemplate['grouped'] .= $associatedProduct->getSku() . ",";
						}
				}
			}
			/* IMAGE EXPORT [START] */
			
			if($params['export_full_image_paths'] == "true") {
				$getBaseUrl = $this->_frameworkUrl->getBaseUrl();
				if($_product->getData('image')!="") { $storeTemplate['image'] = $getBaseUrl . "pub/media/catalog/product" . $_product->getData('image'); }
				if($_product->getData('small_image')!="") { $storeTemplate['small_image'] = $getBaseUrl . "pub/media/catalog/product" . $_product->getData('small_image'); }
				if($_product->getData('thumbnail')!="") { $storeTemplate['thumbnail'] = $getBaseUrl . "pub/media/catalog/product" . $_product->getData('thumbnail'); }
				if($_product->getData('swatch_image')!="") { $storeTemplate['swatch_image'] = $getBaseUrl . "pub/media/catalog/product" . $_product->getData('swatch_image'); }
			}
			/* IMAGE EXPORT [END] */
			
			/* GALLERY IMAGE EXPORT [START] */
			$finalgalleryimages = "";
			$galleryImagesModel = $this->getMediaGallery(array($_product->getId()));
			
			if (count($galleryImagesModel) > 0) {
				$getBaseUrl = $this->_frameworkUrl->getBaseUrl();
				foreach ($galleryImagesModel as $_image) {
					if($params['export_full_image_paths'] == "true") {
						$finalgalleryimages .= $getBaseUrl . "pub/media/catalog/product" .  $_image['_media_image'] . ",";
					} else {
						$finalgalleryimages .= $_image['_media_image'] . ",";
					}
				}
			}
			
			$storeTemplate['gallery'] = substr_replace($finalgalleryimages,"",-1);
			/* GALLERY IMAGE EXPORT [END] */
			
			/* EXPORTS CONFIGURABLE ATTRIBUTES [START] */
			$storeTemplate['config_attributes'] = '';
			$finalproductattributes = "";
			//check if product is a configurable type or not
			if($_product->getTypeId() == "configurable") {
				 //get the configurable data from the product
				 $config = $_product->getTypeInstance(true);
				 //loop through the attributes                                  
				 foreach($config->getConfigurableAttributesAsArray($_product) as $attributes)
				 {
					$finalproductattributes .= $attributes['attribute_code'] . ",";
				 }
			}
			$storeTemplate['config_attributes'] = substr_replace($finalproductattributes,"",-1);
			/* EXPORTS CONFIGURABLE ATTRIBUTES [END] */
			
			/* PRODUCT OPTIONS START*/
			
			/*  EMPTY CUSTOM OPTION TITLE  */
			foreach ($Custdata as $OptTitl){
				$storeTemplate[$OptTitl] = "";
			}
			/*  EXPORT PRODUCT OPTIONS [START]  */
				
			if(is_array($_product->getOptions())) {
				foreach ($_product->getOptions() as $CustmOptData) {
					$customoptionvalues = "";
					$CustOptnTitle = str_replace(" ", "_", $CustmOptData->getData('title')) . "__" . $CustmOptData->getData('type') . "__" . $CustmOptData->getData('is_require') . "__". $CustmOptData->getData('sort_order');	
					if($CustmOptData->getData('type')=="checkbox" || $CustmOptData->getData('type')=="drop_down" || $CustmOptData->getData('type')=="radio" || $CustmOptData->getData('type')=="multiple") {
						foreach ( $CustmOptData->getValues() as $oValues ) {
							if($oValues->getData('price_type')=="") { $price_type = "fixed"; } else { $price_type = $oValues->getData('price_type'); }
							if($oValues->getData('price')=="") { $price = "0.0000"; } else { $price = $oValues->getData('price'); }
							if($oValues->getData('sku')=="") { $sku = " "; } else { $sku = $oValues->getData('sku'); }
							if($oValues->getData('sort_order')=="") { $sort_order = "0"; } else { $sort_order = $oValues->getData('sort_order'); }
							if($oValues->getData('max_characters')=="") { $max_characters = "0"; } else { $max_characters = $oValues->getData('max_characters'); }
							$customoptionvalues .= $oValues->getData('title') . ":" . $price_type . ":" . $price . ":" . $sku . ":" . $sort_order . ":" . $max_characters . "|";
						}
						
					  }else{
						if($CustmOptData->getData('price_type')=="") { $price_type = "fixed"; } else { $price_type = $CustmOptData->getData('price_type'); }
						if($CustmOptData->getData('price')=="") { $price = "0.0000"; } else { $price = $CustmOptData->getData('price'); }
						if($CustmOptData->getData('sku')=="") { $sku = " "; } else { $sku = $CustmOptData->getData('sku'); }
						if($CustmOptData->getData('sort_order')=="") { $sort_order = "0"; } else { $sort_order = $CustmOptData->getData('sort_order'); }
						if($CustmOptData->getData('max_characters')=="") { $max_characters = "0"; } else { $max_characters = $CustmOptData->getData('max_characters'); }
						$customoptionvalues .= $CustmOptData->getData('title') . ":" . $price_type . ":" . $price . ":" . $sku . ":" . $sort_order . ":" . $max_characters . "|";
						
					  }
					  $storeTemplate[$CustOptnTitle] = substr_replace($customoptionvalues,"",-1);
				}
			}
			/*  EXPORT PRODUCT OPTIONS [END]  */
			
			/* PRODUCT OPTIONS END*/	

			$storeTemplate['qty'] = $_stockData->getStockItem($_product->getData('entity_id'))->getQty();
			$storeTemplate['min_qty'] = $_stockData->getStockItem($_product->getData('entity_id'))->getMinQty();
			$storeTemplate['use_config_min_qty'] = $_stockData->getStockItem($_product->getData('entity_id'))->getUseConfigMinQty();
			$storeTemplate['is_qty_decimal'] = $_stockData->getStockItem($_product->getData('entity_id'))->getData('is_qty_decimal');
			$storeTemplate['backorders'] = $_stockData->getStockItem($_product->getData('entity_id'))->getData('backorders');
			$storeTemplate['use_config_backorders'] = $_stockData->getStockItem($_product->getData('entity_id'))->getData('use_config_backorders');
			$storeTemplate['min_sale_qty'] = $_stockData->getStockItem($_product->getData('entity_id'))->getMinSaleQty();
			$storeTemplate['use_config_min_sale_qty'] = $_stockData->getStockItem($_product->getData('entity_id'))->getUseConfigMinSaleQty();
			$storeTemplate['max_sale_qty'] = $_stockData->getStockItem($_product->getData('entity_id'))->getMaxSaleQty();
			$storeTemplate['use_config_max_sale_qty'] = $_stockData->getStockItem($_product->getData('entity_id'))->getUseConfigMaxSaleQty();
			$storeTemplate['is_in_stock'] = $_stockData->getStockItem($_product->getData('entity_id'))->getIsInStock();
			$storeTemplate['low_stock_date'] = $_stockData->getStockItem($_product->getData('entity_id'))->getData('low_stock_date');
			$storeTemplate['notify_stock_qty'] = $_stockData->getStockItem($_product->getData('entity_id'))->getNotifyStockQty();
			$storeTemplate['use_config_notify_stock_qty'] = $_stockData->getStockItem($_product->getData('entity_id'))->getUseConfigNotifyStockQty();
			$storeTemplate['manage_stock'] = $_stockData->getStockItem($_product->getData('entity_id'))->getManageStock();
			$storeTemplate['use_config_manage_stock'] = $_stockData->getStockItem($_product->getData('entity_id'))->getUseConfigManageStock();
			$storeTemplate['stock_status_changed_auto'] = $_stockData->getStockItem($_product->getData('entity_id'))->getData('stock_status_changed_auto');
			$storeTemplate['use_config_qty_increments'] = $_stockData->getStockItem($_product->getData('entity_id'))->getData('use_config_qty_increments');
			$storeTemplate['qty_increments'] = $_stockData->getStockItem($_product->getData('entity_id'))->getData('qty_increments');
			$storeTemplate['enable_qty_increments'] = $_stockData->getStockItem($_product->getData('entity_id'))->getData('enable_qty_increments');
			$storeTemplate['is_decimal_divided'] = $_stockData->getStockItem($_product->getData('entity_id'))->getData('is_decimal_divided');
			$storeTemplate['use_config_enable_qty_increments'] = $_stockData->getStockItem($_product->getData('entity_id'))->getData('use_config_enable_qty_inc');
			$storeTemplate['use_config_enable_qty_inc'] = $_stockData->getStockItem($_product->getData('entity_id'))->getData('use_config_enable_qty_inc');
			$storeTemplate['stock_status_changed_automatically'] = $_stockData->getStockItem($_product->getData('entity_id'))->getData('stock_status_changed_auto');
			
			$_product->addData($storeTemplate);
            $content .= $_product->toString($template) . "\n";
			unset($storeTemplate);
		}
		#print_r($storeTemplate);
        #exit;
        return $this->fileFactory->create('export_products.csv', $content, DirectoryList::VAR_DIR);
    }
	
	public function GetExternalFields($ProductId,$ProductType){
		if($ProductType == 'configurable' && $ProductId !=""){
			$product = $this->_productModel->load($ProductId); 
			$config = $product->getTypeInstance(true);
			$ProductData = array();
			foreach($config->getConfigurableAttributesAsArray($product) as $attributes)
			{
				$associated_products = $this->_configurableProduct->getUsedProductCollection($product)->addAttributeToSelect('*')->addFilterByRequiredOptions();
				foreach($associated_products as $Associatedproduct){
					$ProductData[]= $Associatedproduct->getSku() .'='.$attributes['attribute_code'] .'='. $Associatedproduct->getAttributeText($attributes['attribute_code']);	
				}
			}
			return implode(',',$ProductData);
		}
	}
	

	
    public function getAttribute($code)
    {
        if (!isset($this->_attributes[$code])) {
            $this->_attributes[$code] = $this->_productModel->getResource()->getAttribute($code);
        }
        return $this->_attributes[$code];
    }
	
	public function sptidwithcoma($sdata){
		$data =	implode(",",$sdata);
		return $data;
	}
	
	public function taxclasswitname($taxid){
		$taxclassbyname = $this->_taxClassModel->load($taxid);
		$data = $taxclassbyname->getClassName();
		if($data == ""){
			return "None";
		}else{
			return $data;
		}
	}
	
	public function storenamebyid($storeid){
		return $this->_storeManager->getStore($storeid)->getName();
	}
	public function storeCodeByID($storeid){
		return $this->_storeManager->getStore($storeid)->getCode();
	}
	
	public function msrpriceActual($msrp_displayActualPrice){
		$data ="";
		if($msrp_displayActualPrice == 0){ $data .= "Use config"; }
		if($msrp_displayActualPrice == 1){ $data .= "On Gesture"; }
		if($msrp_displayActualPrice == 2){ $data .= "In Cart"; }
		if($msrp_displayActualPrice == 3){ $data .= "Before Order Confirmation"; }
		return $data;
	}
	
	public function TierPrice($incoming_tierps){
		$data="";
		if(is_array($incoming_tierps)) {
			$export_data="";
			foreach($incoming_tierps as $tier_str){
				#print_r($tier_str);
				$export_data .= $tier_str['cust_group'] . "=" . round($tier_str['price_qty']) . "=" . $tier_str['price'] . "|";
			}
			$data = substr_replace($export_data,"",-1);
		}
		
		return $data;
	}
	public function CustomerGroupPrice($incoming_custgrpprice){
		$data="";
		if(is_array($incoming_custgrpprice)) {
			$export_data="";
			foreach($incoming_custgrpprice as $cust_str){
				#print_r($tier_str);
				$export_data .= $cust_str['customer_group_id'] . "=" . $cust_str['value'] . "=" . $cust_str['website_id'] . "|";
			}
			$data = substr_replace($export_data,"",-1);
		}
		
		return $data;
	}
	
	public function websiteCodeById($webid){
		$withname = array();
		foreach($webid as $webids){
			$withname[] = $this->_storeManager->getWebsite($webids)->getCode();
		}
		$data =	implode(",",$withname);
		return $data;
	}
	
	public function attributebyid($attributeid){
		$data = $this->_attributeSet->load($attributeid)->getAttributeSetName();
		return $data;
	}
	
	public function productStatus($Pstatus){
		$data = "";
		if($Pstatus == 1){ $data .= "Enabled";	}
		if($Pstatus == 2){ $data .= "Disabled";	}
		return $data;
	}
	
	public function pagelayout($pagelayout){
		$data = "";
		if($pagelayout == ""){ 
			$data .= "No layout";
		}else{
			$data .= $pagelayout;
		}
		return $data;
	}
	
	public function customDesign($Pstatus){
		$data = "";
		if($Pstatus == ''){ $data .= "No Layout"; } else{ $data .= $Pstatus; }
		return $data;
	}
	
	public function RequiredOptions($RequiredOptions){
		$data = "";
		if($RequiredOptions == 1){ $data .= "Yes";	}
		if($RequiredOptions == 0){ $data .= "No";	}
		return $data;
	}
	
	public function productVisibility($PVstatus){
		$data = "";
		if($PVstatus == 1){ $data .= "Not Visible Individually"; }
		if($PVstatus == 2){ $data .= "Search"; }
		if($PVstatus == 3){ $data .= "Catalog"; }
		if($PVstatus == 4){ $data .= "Catalog, Search"; }
		return $data;
	}
	
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'CommerceExtensions_ProductImportExport::import_export'
        );

    }
	
}
