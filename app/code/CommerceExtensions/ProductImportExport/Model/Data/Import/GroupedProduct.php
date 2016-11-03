<?php

/**
 * Copyright © 2015 CommerceExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CommerceExtensions\ProductImportExport\Model\Data\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;

/**
 *  CSV Import Handler Grouped Product
 */
 
class GroupedProduct{

	protected $_filesystem;
		
	protected $_objectManager;
	
	
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
	
	public function GroupedProductData($newProduct,$SetProductData,$params,$ProcuctData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute){
	
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
			
		#$imagePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('catalog').'/product';
		$imagePath = "/import";
		
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
		if(isset($ProcuctData['category_ids'])) { $SetProductData->setCategoryIds($ProcuctData['category_ids']); }
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
			
			//add each set of images to related magento field
			foreach ($imageArray as $ImageFile => $imageColumns) {
				$possibleGalleryData = explode( ',', $ImageFile );
				foreach( $possibleGalleryData as $_imageForImport ) {
					$SetProductData->addImageToMediaGallery($imagePath . $_imageForImport, $imageColumns, false, false);
				}
			}
		}
		
		$SetProductData->setStockData($ProductStockdata);	
		
		if(isset($ProductSupperAttribute['tier_prices'])) { 
			if($ProductSupperAttribute['tier_prices']!=""){ $SetProductData->setTierPrice($ProductSupperAttribute['tier_prices']); }
		}
		
		/* MODDED TO ALLOW FOR GROUP POSITION AS WELL AND SHOULD WORK IF NO POSITION IS SET AS WELL CAN COMBO */
		$groupedpositionproducts = false;
		$finalIDssthatneedtobeconvertedto=array();
		
		if ( isset( $ProductSupperAttribute['grouped'] ) && $ProductSupperAttribute['grouped'] != "" ) {
			
			$finalskusthatneedtobeconvertedtoID="";
			$groupedpositioncounter=0;
			$finalskusforarraytoexplode = explode(",",$ProductSupperAttribute['grouped']);
			foreach($finalskusforarraytoexplode as $productskuexploded)
			{
					$pos = strpos($productskuexploded, ":");
					if ($pos !== false) {
					//if( isset($finalidsforarraytoexplode[1]) ) {	
						$groupedpositionproducts = true;
						$finalidsforarraytoexplode = explode(":",$productskuexploded);
						$finalIDssthatneedtobeconvertedto[$groupedpositioncounter]['position'] = $finalidsforarraytoexplode[0];
						$finalIDssthatneedtobeconvertedto[$groupedpositioncounter]['sku'] = $finalidsforarraytoexplode[1];
						if (isset($finalidsforarraytoexplode[2])) {
						$finalIDssthatneedtobeconvertedto[$groupedpositioncounter]['qty'] = $finalidsforarraytoexplode[2];
						}
						$finalskusthatneedtobeconvertedtoID .= $finalidsforarraytoexplode[1] . ",";
					} else {
						$groupedpositionproducts = false;
						$finalskusthatneedtobeconvertedtoID .= $productskuexploded . ",";
					}	
					$groupedpositioncounter++;
			}		
			$linkIds = $this -> skusToIds( $finalskusthatneedtobeconvertedtoID);
			if ( !empty( $linkIds ) ) {
				$SetProductData -> setGroupedLinkData( $linkIds );
			} 
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
	
	protected function userCSVDataAsArray( $data )
	{
		return explode( ',', str_replace( " ", " ", $data ) );
	} 
	
	protected function skusToIds( $userData)
	{
		$productIds = array();
		foreach ( $this -> userCSVDataAsArray( $userData ) as $oneSku ) {
			if ( ( $a_sku = ( int )$this->Product->getIdBySku( $oneSku ) ) > 0 ) {
				parse_str( "position=", $productIds[$a_sku] );
			} 
		} 
		return $productIds;
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