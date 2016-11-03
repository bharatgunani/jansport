<?php

/**
 * Copyright © 2015 CommerceExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CommerceExtensions\ProductImportExport\Model\Data\Import;

#use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;


/**
 *  CSV Import Handler Simple Product
 */
 
class SimpleProduct {
	
	protected $_filesystem;
		
	protected $_objectManager;
	
    protected $_imageCache = array();
	
    public function __construct(
		\Magento\Catalog\Model\ProductFactory $ProductFactory,
		Filesystem $filesystem,
		\Magento\Catalog\Model\Product $Product
    ) {
         // prevent admin store from loading
		 $this->_objectManager = $ProductFactory;
		 $this->_filesystem = $filesystem;
		 $this->Product = $Product;

    }
	
	public function addImage($imageName, $columnName, $imageArray = array()) {
		if($imageName=="") { return $imageArray; }
		
		if($columnName == "media_gallery") {
			$galleryData = explode(',', $imageName);
			foreach( $galleryData as $gallery_img ) {
				if (array_key_exists($gallery_img, $imageArray)) {
					array_push($imageArray[$gallery_img],$columnName);
				} else {
					$imageArray[$gallery_img] = array($columnName);
				}
			}
		} else {
			if (array_key_exists($imageName, $imageArray)) {
				array_push($imageArray[$imageName],$columnName);
			} else {
				$imageArray[$imageName] = array($columnName);
			}
		}
		return $imageArray;
	}
	
	public function SimpleProductData($newProduct,$SetProductData,$params,$ProcuctData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption){
	
	//UPDATE PRODUCT ONLY [START]
	$allowUpdateOnly = false;
	if(!$SetProductData || empty($SetProductData)) {
		$SetProductData = $this->_objectManager->create();
	}
	if($params['update_products_only'] == "true") {
		$allowUpdateOnly = true;
	} 
	
	//UPDATE PRODUCT ONLY [END]
	
	if ($allowUpdateOnly == false) {
		
		#$imagePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('import');
		$imagePath = "/import";
		#$imagePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('catalog').'/product';
	
		if(empty($ProductAttributeData['url_key'])) {
			unset($ProductAttributeData['url_key']);
		}
		if(empty($ProductAttributeData['url_path'])) {
			unset($ProductAttributeData['url_path']);
		}
		$SetProductData->setSku($ProcuctData['sku']);
		$SetProductData->setStoreId($ProcuctData['store_id']);
		if(isset($ProcuctData['name'])) { $SetProductData->setName($ProcuctData['name']); }
		if(isset($ProcuctData['websites'])) { $SetProductData->setWebsiteIds($ProcuctData['websites']); }
		if(isset($ProcuctData['attribute_set'])) { $SetProductData->setAttributeSetId($ProcuctData['attribute_set']); }
		if(isset($ProcuctData['prodtype'])) { $SetProductData->setTypeId($ProcuctData['prodtype']); }
		if(isset($ProcuctData['category_ids'])) { 
			if($ProcuctData['category_ids'] == "remove") { 
				$SetProductData->setCategoryIds(array()); 
			} else {
				$SetProductData->setCategoryIds($ProcuctData['category_ids']);
			}
		}
		if(isset($ProcuctData['status'])) { $SetProductData->setStatus($ProcuctData['status']); }
		if(isset($ProcuctData['weight'])) { $SetProductData->setWeight($ProcuctData['weight']); }
		if(isset($ProcuctData['price'])) { $SetProductData->setPrice($ProcuctData['price']); }
		if(isset($ProcuctData['visibility'])) { $SetProductData->setVisibility($ProcuctData['visibility']); }
		if(isset($ProcuctData['tax_class_id'])) { $SetProductData->setTaxClassId($ProcuctData['tax_class_id']); }
		if(isset($ProcuctData['special_price'])) { $SetProductData->setSpecialPrice($ProcuctData['special_price']); }
		if(isset($ProcuctData['description'])) { $SetProductData->setDescription($ProcuctData['description']); }
		if(isset($ProcuctData['short_description'])) { $SetProductData->setShortDescription($ProcuctData['short_description']); }
		
		// Sets the Start Date
		if(isset($ProductAttributeData['special_from_date'])) { $SetProductData->setSpecialFromDate($ProductAttributeData['special_from_date']); }
		if(isset($ProductAttributeData['news_from_date'])) { $SetProductData->setNewsFromDate($ProductAttributeData['news_from_date']); }
		
		// Sets the End Date
		if(isset($ProductAttributeData['special_to_date'])) { $SetProductData->setSpecialToDate($ProductAttributeData['special_to_date']); }
		if(isset($ProductAttributeData['news_to_date'])) { $SetProductData->setNewsToDate($ProductAttributeData['news_to_date']); }
		
		$SetProductData->addData($ProductAttributeData);
			
		/*
		$SetProductData->setCountryOfManufacture($ProductAttributeData['country_of_manufacture']);
		$SetProductData->setMetaTitle($ProductAttributeData['meta_title']);
		$SetProductData->setMetaDescription($ProductAttributeData['meta_description']);
		$SetProductData->setMetaKeyword($ProductAttributeData['meta_keyword']);
		$SetProductData->setData('msrp_enabled', $ProductAttributeData['msrp_enabled']);
		$SetProductData->setData('msrp_display_actual_price_type', $ProductAttributeData['msrp_display_actual_price_type']);
		$SetProductData->setData('msrp', $ProductAttributeData['msrp']);
		$SetProductData->setData('custom_design', $ProductAttributeData['custom_design']);
		$SetProductData->setData('page_layout', $ProductAttributeData['page_layout']);
		$SetProductData->setData('options_container', $ProductAttributeData['options_container']);
		$SetProductData->setData('gift_message_available', $ProductAttributeData['gift_message_available']);
		$SetProductData->setData('custom_layout_update', $ProductAttributeData['custom_layout_update']);
		$SetProductData->setData('custom_design_from', $ProductAttributeData['custom_design_from']);
		$SetProductData->setData('custom_design_to', $ProductAttributeData['custom_design_to']);
		$SetProductData->setData('product_status_changed', $ProductAttributeData['product_status_changed']);
		$SetProductData->setData('product_changed_websites', $ProductAttributeData['product_changed_websites']);
		*/
		
		if($newProduct || $params['reimport_images'] == "true") { 
			//media images
			$_productImages = array(
				'media_gallery'       => (isset($ProductImageGallery['gallery'])) ? $ProductImageGallery['gallery'] : '',
				'image'       => (isset($ProductImageGallery['image'])) ? $ProductImageGallery['image'] : '',
				'small_image'       => (isset($ProductImageGallery['small_image'])) ? $ProductImageGallery['small_image'] : '',
				'thumbnail'       => (isset($ProductImageGallery['thumbnail'])) ? $ProductImageGallery['thumbnail'] : '',
				'swatch_image'       => (isset($ProductImageGallery['swatch_image'])) ? $ProductImageGallery['swatch_image'] : ''
		
			);
			//create array of images with duplicates combind
			$imageArray = array();
			foreach ($_productImages as $columnName => $imageName) {
				$imageArray = $this->addImage($imageName, $columnName, $imageArray);
			}
			#echo "<pre>";
			#print_r($imageArray);
			#echo "</pre>";
			foreach ($imageArray as $ImageFile => $imageColumns) {
				$possibleGalleryData = explode( ',', $ImageFile );
				foreach( $possibleGalleryData as $_imageForImport ) {
					$SetProductData->addImageToMediaGallery($imagePath . $_imageForImport, $imageColumns, false, false);
				}
			}
		}
		#exit;
		$SetProductData->setStockData($ProductStockdata);	
		//Set Product Custom Option 
		$SetProductData->setHasOptions(true);
		$SetProductData->setProductOptions($ProductCustomOption);
		$SetProductData->setCanSaveCustomOptions(true);
		
		if(isset($ProductSupperAttribute['tier_prices'])) { 
			if($ProductSupperAttribute['tier_prices']!=""){ $SetProductData->setTierPrice($ProductSupperAttribute['tier_prices']); }
		}
		
		$SetProductData->save(); 
		
		if(isset($ProductSupperAttribute['related'])){
			if($ProductSupperAttribute['related']!=""){ $this->AppendReProduct($ProductSupperAttribute['related'] ,$ProcuctData['sku']); }
		}

		if(isset($ProductSupperAttribute['upsell'])){
			if($ProductSupperAttribute['upsell']!=""){ $this->AppendUpProduct($ProductSupperAttribute['upsell'] ,$ProcuctData['sku']); }
		}

		if(isset($ProductSupperAttribute['crosssell'])){
			if($ProductSupperAttribute['crosssell']!=""){ $this->AppendCsProduct($ProductSupperAttribute['crosssell'] , $ProcuctData['sku']); }
		}
	  }//END UPDATE ONLY CHECK
	}
	
	public function AppendReProduct($ReProduct , $sku){
		$URCProducts = explode(',',$ReProduct);
		$data = array();
		$i = 0;
		foreach($URCProducts as $linkdata){
			if($linkdata!="") {
				$id = $this->Product->getIdBySku($linkdata);
				$data[$id] = array('position' => $i);
				$i++;
			}
		}
		$this->Product->loadByAttribute('sku', $sku)->setRelatedLinkData($data)->Save();
		
	}
	public function AppendUpProduct($UpProduct , $sku){
		$URCProducts = explode(',',$UpProduct);
		$data = array();
		$i = 0;
		foreach($URCProducts as $linkdata){
			if($linkdata!="") {
				$id = $this->Product->getIdBySku($linkdata);
				$data[$id] = array('position' => $i);
				$i++;
			}
		}
		
		$this->Product->loadByAttribute('sku', $sku)->setUpSellLinkData($data)->Save();
		
	}
	public function AppendCsProduct($CsProduct , $sku){
		$URCProducts = explode(',',$CsProduct);
		$data = array();
		$i = 0;
		foreach($URCProducts as $linkdata){
			if($linkdata!="") {
				$id = $this->Product->getIdBySku($linkdata);
				$data[$id] = array('position' => $i);
				$i++;
			}
		}
		
		$this->Product->loadByAttribute('sku', $sku)->setCrossSellLinkData($data)->Save();
		
	}
}