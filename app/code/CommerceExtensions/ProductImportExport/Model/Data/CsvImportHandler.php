<?php

/**
 * Copyright © 2015 CommerceExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CommerceExtensions\ProductImportExport\Model\Data;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Store\Model\Website;

/**
 *  CSV Import Handler
 */
 
class CsvImportHandler
{
	const MULTI_DELIMITER = ' , ';
	
    protected $_attributes = array();
	
	protected $_resource;
	
    protected $_filesystem;
	
	protected $date;
	
	protected $csvProcessor;
	
	protected $_eavConfig;
	
	protected $objectManager;
	
	protected $Product;
	
	protected $Category;
	
    protected $_imageFields = ['image','swatch_image','small_image','thumbnail','media_gallery','gallery','gallery_label'];
	
    public function __construct(
		ResourceConnection $resource,
		\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
		Filesystem $filesystem,
		\Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Eav\Model\Entity\Attribute\Set $AttributeSet,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\CommerceExtensions\ProductImportExport\Model\Data\Import\SimpleProduct $SimpleProduct,
		\CommerceExtensions\ProductImportExport\Model\Data\Import\BundleProduct $BundleProduct,
		\CommerceExtensions\ProductImportExport\Model\Data\Import\VirtualProduct $VirtualProduct,
		\CommerceExtensions\ProductImportExport\Model\Data\Import\ConfigurableProduct $ConfigurableProduct,
		\CommerceExtensions\ProductImportExport\Model\Data\Import\GroupedProduct $GroupedProduct,
		\CommerceExtensions\ProductImportExport\Model\Data\Import\DownloadableProduct $DownloadableProduct,
		\Magento\Catalog\Model\Product $Product,
		\Magento\Catalog\Model\Category $Category,
		\Magento\Store\Model\Website $Website,
        \Magento\Eav\Model\Config $eavConfig
    ) {
         // prevent admin store from loading
		 $this->_resource = $resource;
		 $this->_fileUploaderFactory = $fileUploaderFactory;
		 $this->_filesystem = $filesystem;
		 $this->csvProcessor = $csvProcessor;
		 $this->_attributeFactory = $AttributeSet;
         $this->localeFormat = $localeFormat;
		 $this->date = $date;
         $this->_objectManager = $objectManager;
		 $this->SimpleProduct = $SimpleProduct;
		 $this->BundleProduct = $BundleProduct;
		 $this->VirtualProduct = $VirtualProduct;
		 $this->ConfigurableProduct = $ConfigurableProduct;
		 $this->GroupedProduct = $GroupedProduct;
		 $this->DownloadableProduct = $DownloadableProduct;
		 $this->_ProductModel = $Product;
		 $this->_CategoryModel = $Category;
		 $this->website = $Website;
         $this->_eavConfig = $eavConfig;
		 
    }
	
	protected function getConnection($data){
		$this->connection = $this->_resource->getConnection($data);
		return $this->connection;
	}
	
	
	public function requiredDataForSaveProduct($product, $params){
		$prodtype = $product['prodtype'];
		
		#$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		foreach ( $product as $field => $value ) {
			if ( in_array( $field, $this -> _imageFields ) ) {
				continue;
			} 
			#$attribute = $objectManager->create('Magento\Eav\Model\Config')->getAttribute(ModelProduct::ENTITY, $field);
			$attribute = $this->_eavConfig->getAttribute(ModelProduct::ENTITY, $field);
			
			if ( !$attribute ) {
				continue;
			}
			
			$isArray = false;
			$setValue = $value;
			
			if ( $attribute -> getFrontendInput() == 'multiselect' ) {
				$value = explode( self :: MULTI_DELIMITER, $value );
				$isArray = true;
				$setValue = array();
			} 
			
			if ($attribute->getData('is_global') == '1') {
				$arrayOfFieldstoSkip[] = $field;
			}
			if ( $value && $attribute -> getBackendType() == 'decimal' ) {
				$setValue = $this->localeFormat->getNumber($value);
			} 
			
			if ( $attribute -> usesSource() ) {
				$options = $attribute -> getSource() -> getAllOptions( false );

                if ($isArray) {
                    foreach ($options as $item) {
                        if (in_array($item['label'], $value)) {
                            $setValue[] = $item['value'];
                        }
                    }
                } else {
                    $setValue = false;
                    foreach ($options as $item) {
                        if (is_array($item['value'])) {
                            foreach ($item['value'] as $subValue) {
                                if (isset($subValue['value']) && $subValue['value'] == $value) {
                                    $setValue = $value;
                                }
                            }
                        } else if ($item['label'] == $value) {
                            $setValue = $item['value'];
                        }
                    }
                }
			} 	
			$product[$field] = $setValue;
		}
		
		#keeps existing category_ids and adds new ones to them
		if(isset($product['category_ids'])) {
			if($params['append_categories'] == "true") { 
				$product1 = $this->_ProductModel->loadByAttribute('sku', $product['sku']);
				if (!empty($product1)) {
					$productModel = $this->_ProductModel->load($product1->getId());
					$cats = $productModel->getCategoryIds();
					$catsarray = explode(",",$product['category_ids']);
					$finalcatsimport = array_merge($cats, $catsarray);
					$product['category_ids'] = $finalcatsimport;
				} else {
					$catsarray = explode(",",$product['category_ids']);
					$product['category_ids'] = $catsarray;
				}
			} else {
				$catsarray = explode(",",$product['category_ids']);
				$product['category_ids'] = $catsarray;
			}
		}
		
		$ProductData = $this->ProductData($product, $params);
		
		
		if(isset($product['categories'])) {
			if($params['append_categories'] == "true") { 
				$product1 = $this->_ProductModel->loadByAttribute('sku', $product['sku']);
				if (!empty($product1)) {
					$productModel = $this->_ProductModel->load($product1->getId());
					$cats = $productModel->getCategoryIds();
					$catsarray = explode(",",$ProductData['categories']);
					$finalcatsimport = array_merge($cats, $catsarray);
					$product['category_ids'] = $finalcatsimport;
				} else {
					$catsarray = explode(",",$ProductData['categories']);
					$product['category_ids'] = $catsarray;
				}
			} else {
				$catsarray = explode(",",$ProductData['categories']);
				$product['category_ids'] = $catsarray;
			}
		}
		
		$ProductAttributeData = $this->ProductAttributeData($product);
		
		//Checks if it is new Product
		if($productIdupdate = $this->_ProductModel->loadByAttribute('sku', $product['sku'])) {
			$newProduct = false;
			$SetProductData = $productIdupdate;
		} else {
			$newProduct = true;
			$SetProductData = "";
		}
	
		if($newProduct || $params['reimport_images'] == "true") { 
			$ProductImageGallery = $this->ProductImageGallery($product, $params);
		} else {
			$ProductImageGallery = array();
		}
		
		$ProductStockdata = $this->ProductStockdata($product);
		$ProductSupperAttribute = $this->ProductSupperAttribute($product, $params);
		$ProductCustomOption = $this->ProductCustomOption($product);
		$this->CreateProductWithrequiredField($newProduct,$SetProductData,$prodtype,$params,$ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption);
	}

    protected function _filterData(array $RawDataHeader, array $RawData)
    {
		$rowCount=0;
		$RawDataRows = array();
        foreach ($RawData as $rowIndex => $dataRow) {
			// skip headers
            if ($rowIndex == 0) {
                continue;
            }
            // skip empty rows
            if (count($dataRow) <= 1) {
                unset($RawData[$rowIndex]);
                continue;
            }
			/* we take rows from [0] = > value to [website] = base */
            if ($rowIndex > 0) {
				foreach ($dataRow as $rowIndex => $dataRowNew) {
					$RawDataRows[$rowCount][$RawDataHeader[$rowIndex]] = $dataRowNew;
				}
			}
			$rowCount++;
        }
        return $RawDataRows;
    }
	
	public function UploadCsvOfproduct($file){
		$uploader = $this->_fileUploaderFactory->create(['fileId' => $file]);
		$uploader->setAllowedExtensions(['csv']);
		$uploader->setAllowRenameFiles(false);
		$uploader->setFilesDispersion(false);
		$path = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath('ProductImportExport');
		$result = $uploader->save($path);
		return $result;
	}
	
	public function readCsvFile($PfilePath, $params){
		
		if($params['import_delimiter'] != "") { $this->csvProcessor->setDelimiter($params['import_delimiter']); }
		if($params['import_enclose'] != "") { $this->csvProcessor->setEnclosure($params['import_enclose']); }
		
		$RawProductData = $this->csvProcessor->getData($PfilePath);
        // first row of file represents headers
        $fileFields = $RawProductData[0];
        $productData = $this->_filterData($fileFields, $RawProductData);
		
		foreach($productData as $product){
			$this->requiredDataForSaveProduct($product, $params);
		}
	}
	
	public function CreateProductWithrequiredField($newProduct,$SetProductData,$prodtype,$params,$ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption){
	
		if($prodtype == "simple") {
		  $this->SimpleProduct->SimpleProductData($newProduct,$SetProductData,$params,$ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption);
		} elseif ($prodtype == "bundle") {
		  $this->BundleProduct->BundleProductData($newProduct,$SetProductData,$params,$ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute);
		} elseif ($prodtype == "virtual") {
		 $this->VirtualProduct->VirtualProductData($newProduct,$SetProductData,$params,$ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute);
		} elseif ($prodtype == "configurable") {
		 $this->ConfigurableProduct->ConfigurableProductData($newProduct,$SetProductData,$params,$ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute);
		} elseif ($prodtype == "grouped") {
		 $this->GroupedProduct->GroupedProductData($newProduct,$SetProductData,$params,$ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute);
		} elseif ($prodtype == "downloadable") {
		 $this->DownloadableProduct->DownloadableProductData($newProduct,$SetProductData,$params,$ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute);
		} else {
			//Not Imported Products
		}
	}
	
	public function ProductData($product, $params){
		/*
		$defaultProductData = array(
			'name'           => (isset($product['name'])) ? $product['name'] : '',
			'sku'      	     => $product['sku'],
			'url_key'        => (isset($product['url_key'])) ? $product['url_key'] : '',
			'store'          => (isset($product['store'])) ? $product['store'] : '',
			'store_id'       => (isset($product['store_id'])) ? $product['store_id'] : '0',
			'websites'       => $this->websitenamebyid($product['websites']),
			'attribute_set'  => $this->attributeSetNamebyid($product['attribute_set']),
			'prodtype'       => $product['prodtype'],
			'categories'     => (isset($product['categories'])) ? $this->addCategories($product['categories'], $product['store_id'], $params) : '',
			'category_ids'   => (isset($product['category_ids'])) ? $product['category_ids'] : '',
			'status'         => (isset($product['status'])) ? $product['status'] : 'Disabled',
			'weight'         => (isset($product['weight'])) ? $product['weight'] : '',
			'price'          => (isset($product['price'])) ? $product['price'] : '',
			'special_price'  => (isset($product['special_price'])) ? $product['special_price'] : '',
			'visibility'     => (isset($product['visibility'])) ? $product['visibility'] : '',
			'tax_class_id'   => (isset($product['tax_class_id'])) ? $product['tax_class_id'] : '',
			'description'    => (isset($product['description'])) ? $product['description'] : '',
			'short_description'    => (isset($product['short_description'])) ? $product['short_description'] : ''
		);
		*/
		if(isset($product['name'])) { $defaultProductData['name'] = $product['name']; }
		$defaultProductData['sku'] = $product['sku'];
		if(isset($product['url_key'])) { $defaultProductData['url_key'] = $product['url_key']; }
		if(isset($product['store'])) { $defaultProductData['store'] = $product['store']; }
		if(isset($product['store_id'])) { $defaultProductData['store_id'] = $product['store_id']; } else { $defaultProductData['store_id'] = "0"; }
		if(isset($product['websites'])) { $defaultProductData['websites'] = $this->websitenamebyid($product['websites']); }
		if(isset($product['attribute_set'])) { $defaultProductData['attribute_set'] = $this->attributeSetNamebyid($product['attribute_set']); }
		if(isset($product['prodtype'])) { $defaultProductData['prodtype'] = $product['prodtype']; }
		if(isset($product['categories'])) { $defaultProductData['categories'] = $this->addCategories($product['categories'], $product['store_id'], $params); }
		if(isset($product['category_ids'])) { $defaultProductData['category_ids'] = $product['category_ids']; }
		if(isset($product['status'])) { $defaultProductData['status'] = $product['status']; }
		if(isset($product['weight'])) { $defaultProductData['weight'] = $product['weight']; }
		if(isset($product['price'])) { $defaultProductData['price'] = $product['price']; }
		if(isset($product['special_price'])) { $defaultProductData['special_price'] = $product['special_price']; }
		if(isset($product['visibility'])) { $defaultProductData['visibility'] = $product['visibility']; }
		if(isset($product['tax_class_id'])) { $defaultProductData['tax_class_id'] = $product['tax_class_id']; }
		if(isset($product['description'])) { $defaultProductData['description'] = $product['description']; }
		if(isset($product['short_description'])) { $defaultProductData['short_description'] = $product['short_description']; }
		
		return $defaultProductData;
	}
	
	public function ProductAttributeData($product){
	
		$defaultAttributeData = array();
		
		foreach ( $product as $field => $value ) {
		    if(!in_array($field, $this ->_imageFields)) { 
				$defaultAttributeData[$field] = $value;
			}
		}
		/*
		$defaultAttributeData = array(
			'has_options'                     => (isset($product['has_options'])) ? $product['has_options'] : '',
			'config_attributes'               => (isset($product['config_attributes'])) ? $product['config_attributes'] : '',
			'country_of_manufacture'          => (isset($product['country_of_manufacture'])) ? $product['country_of_manufacture'] : '',
			'msrp'                            => $this->msrRetailpriceSuggested('msrp'),
			'msrp_enabled'                    => (isset($product['msrp_enabled'])) ? $product['msrp_enabled'] : null,
			'msrp_display_actual_price_type'  => (isset($product['msrp_display_actual_price_type'])) ? $product['msrp_display_actual_price_type'] : null,
			'meta_title'                      => (isset($product['meta_title'])) ? $product['meta_title'] : '',
			'meta_description'                => (isset($product['meta_description'])) ? $product['meta_description'] : '',
			'custom_design'                   => (isset($product['custom_design'])) ? $product['custom_design'] : '',
			'page_layout'                     => $this->pagelayout($product['page_layout']),
			'options_container'               => (isset($product['options_container'])) ? $product['options_container'] : '',
			'gift_message_available'          => (isset($product['gift_message_available'])) ? $product['gift_message_available'] : '',
			'url_path'                        => (isset($product['url_path'])) ? $product['url_path'] : '',
			'meta_keyword'                    => (isset($product['meta_keyword'])) ? $product['meta_keyword'] : '',
			'custom_layout_update'            => (isset($product['custom_layout_update'])) ? $product['custom_layout_update'] : '',
			'news_from_date'                  => $this->dateformat($product['news_from_date']),
			'news_to_date'                    => $this->dateformat($product['news_to_date']),
			'special_from_date'               => $this->dateformat($product['special_from_date']),
			'special_to_date'                 => $this->dateformat($product['special_to_date']),
			'custom_design_from'              => $this->dateformat($product['custom_design_from']),
			'custom_design_to'                => $this->dateformat($product['custom_design_to']),
			'product_status_changed'          => (isset($product['product_status_changed'])) ? $product['product_status_changed'] : null,
			'product_changed_websites'        => (isset($product['product_changed_websites'])) ? $product['product_changed_websites'] : null,
			'additional_attributes'           => (isset($product['additional_attributes'])) ? $product['additional_attributes'] : null,
	
		);
		*/
		return $defaultAttributeData;
	}
	
	public function ProductSupperAttribute($product, $params){
	
		$defaultSupperAttributeData = array(
			'related'                     => (isset($product['related'])) ? $product['related'] : '',
			'upsell'                      => (isset($product['upsell'])) ? $product['upsell'] : '',
			'crosssell'                   => (isset($product['crosssell'])) ? $product['crosssell'] : '',
			'tier_prices'                 => (isset($product['tier_prices'])) ? $this->TierPricedata($product['tier_prices'], $product['sku'], $params) : '',
			'associated'                  => (isset($product['associated'])) ? $product['associated'] : '',
			'bundle_options'              => (isset($product['bundle_options'])) ? $product['bundle_options'] : '',
			'grouped'                     => (isset($product['grouped'])) ? $product['grouped'] : '',
			'group_price_price'           => (isset($product['group_price_price'])) ? $product['group_price_price'] : '',
			'downloadable_options'        => (isset($product['downloadable_options'])) ? $product['downloadable_options'] : '',
			'downloadable_sample_options' => (isset($product['downloadable_sample_options'])) ? $product['downloadable_sample_options'] : '',
		);
		return $defaultSupperAttributeData;
	
	}
	public function ProductCustomOption($product){
		$custom_options = array();
		foreach ( $product as $field => $value ){
			if(strpos($field,':')!==FALSE && strlen($value)) {
			   $values=explode('|',$value);
			   if(count($values)>0) {
						$iscustomoptions = "true";
						
						foreach($values as $v) {
					 $parts = explode(':',$v);
					 $title = $parts[0];
						}
				  @list($title,$type,$is_required,$sort_order) = explode(':',$field);
				  $title2 = $title;
				  $custom_options[] = array(
					 'is_delete'=>0,
					 'title'=>$title2,
					 'previous_group'=>'',
					 'previous_type'=>'',
					 'type'=>$type,
					 'is_require'=>$is_required,
					 'sort_order'=>$sort_order,
					 'values'=>array()
				  );
				  if($is_required ==1) {
						$iscustomoptionsrequired = "true";
				  }
				  foreach($values as $v) {
					 $parts = explode(':',$v);
					 $title = $parts[0];
					 if(count($parts)>1) {
						$price_type = $parts[1];
					 } else {
						$price_type = 'fixed';
					 }
					 if(count($parts)>2) {
						$price = $parts[2];
					 } else {
						$price =0;
					 }
					 if(count($parts)>3) {
						$sku = $parts[3];
					 } else {
						$sku='';
					 }
					 if(count($parts)>4) {
						$sort_order = $parts[4];
					 } else {
						$sort_order = 0;
					 }
					 if(count($parts)>5) {
						$max_characters = $parts[5];
					 } else {
						$max_characters = '';
					 }
					 if(count($parts)>6) {
						$file_extension = $parts[6];
					 } else {
						$file_extension = '';
					 }
					 if(count($parts)>7) {
						$image_size_x = $parts[7];
					 } else {
						$image_size_x = '';
					 }
					 if(count($parts)>8) {
						$image_size_y = $parts[8];
					 } else {
						$image_size_y = '';
					 }
					 switch($type) {
						case 'file':
						   $custom_options[count($custom_options) - 1]['price_type'] = $price_type;
						   $custom_options[count($custom_options) - 1]['price'] = $price;
						   $custom_options[count($custom_options) - 1]['sku'] = $sku;
						   $custom_options[count($custom_options) - 1]['file_extension'] = $file_extension;
						   $custom_options[count($custom_options) - 1]['image_size_x'] = $image_size_x;
						   $custom_options[count($custom_options) - 1]['image_size_y'] = $image_size_y;
						   break;
						   
						case 'field':
						   $custom_options[count($custom_options) - 1]['max_characters'] = $max_characters;
						case 'area':
						   $custom_options[count($custom_options) - 1]['max_characters'] = $max_characters;
						   
						case 'date':
						case 'date_time':
						case 'time':
						   $custom_options[count($custom_options) - 1]['price_type'] = $price_type;
						   $custom_options[count($custom_options) - 1]['price'] = $price;
						   $custom_options[count($custom_options) - 1]['sku'] = $sku;
						   break;
													  
						case 'drop_down':
						case 'radio':
						case 'checkbox':
						case 'multiple':
						default:
						   $custom_options[count($custom_options) - 1]['values'][]=array(
							  'is_delete'=>0,
							  'title'=>$title,
							  'option_type_id'=>-1,
							  'price_type'=>$price_type,
							  'price'=>$price,
							  'sku'=>$sku,
							  'sort_order'=>$sort_order,
							  'max_characters'=>$max_characters,
						   );
						   break;
					 }
				  }
			   }
			}
		}
		return $custom_options;
	}
	
	public function ProductImageGallery($product, $params){
	
		if($params['deleteall_andreimport_images'] == "true") { 
			$productRepository = $this->_objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');
			#$galleryProcessor = $objectManager->create('Magento\Catalog\Model\Product\Gallery\GalleryManagement');
			$productModel = $productRepository->get($product['sku']);
			#$product1 = $objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('sku', $product['sku']);
			#$productModel = $productRepository->getById($product1->getId(), false, 0);
			#$productModel = $objectManager->create('Magento\Catalog\Model\Product')->setStoreId(0)->load($product1->getId());
			$existingMediaGalleryEntries = $productModel->getMediaGalleryEntries();
			foreach ($existingMediaGalleryEntries as $key => $entry) {
				#print_r($entry->getData());
				#echo "Entry: " . $entry;
				unset($existingMediaGalleryEntries[$key]);
				#$galleryProcessor->remove($productModel->getSku(), $entry['id']);

			}
			$productModel->setMediaGalleryEntries($existingMediaGalleryEntries);
			#$productModel->save();
			$productRepository->save($productModel);
			
			
			#$connectionRead = $this->getConnection('core_read');
			$connection = $this->_resource->getConnection();
			$_eav_attribute = $this->_resource->getTableName('eav_attribute');
			$_catalog_product_entity = $this->_resource->getTableName('catalog_product_entity');
			$_catalog_product_entity_varchar = $this->_resource->getTableName('catalog_product_entity_varchar');
			$_product_id = $productModel->getId();
			
			//DELETES EXTRA BLANK STORE IMAGES
			$attributeImage = $connection->fetchAll("SELECT DISTINCT P.entity_id, P.sku, V1.store_id, V1.value_id,
    CASE
        WHEN V1.Value IS NULL
            THEN NULL
        ELSE CONCAT('', V1.value)
    END AS Image
    FROM ".$_catalog_product_entity." AS P LEFT JOIN
    ".$_catalog_product_entity_varchar." AS V1 ON P.entity_id = V1.entity_id AND V1.attribute_id = ( SELECT attribute_id FROM ".$_eav_attribute." AS eav WHERE eav.attribute_code = 'image' and eav.entity_type_id ='4') WHERE P.entity_id = '".$_product_id."' AND V1.store_id != 0");
	
			foreach($attributeImage as $attributeName){
				if($attributeName['Image'] == "" || $attributeName['Image'] == NULL) {
					$connection->query("DELETE FROM ".$_catalog_product_entity_varchar." WHERE value_id = '".$attributeName['value_id']."'");
				}
			}
			//DELETES EXTRA BLANK STORE IMAGES LABEL
			$attributeImage = $connection->fetchAll("SELECT DISTINCT P.entity_id, P.sku, V1.store_id, V1.value_id,
    CASE
        WHEN V1.Value IS NULL
            THEN NULL
        ELSE CONCAT('', V1.value)
    END AS ImageLabel
    FROM ".$_catalog_product_entity." AS P LEFT JOIN
    ".$_catalog_product_entity_varchar." AS V1 ON P.entity_id = V1.entity_id AND V1.attribute_id = ( SELECT attribute_id FROM ".$_eav_attribute." AS eav WHERE eav.attribute_code = 'image_label' and eav.entity_type_id ='4') WHERE P.entity_id = '".$_product_id."' AND V1.store_id != 0");
	
			foreach($attributeImage as $attributeName){
				if($attributeName['ImageLabel'] == "" || $attributeName['ImageLabel'] == NULL) {
					$connection->query("DELETE FROM ".$_catalog_product_entity_varchar." WHERE value_id = '".$attributeName['value_id']."'");
				}
			}
			
			//DELETES EXTRA BLANK STORE SMALL IMAGES
			$attributeImage = $connection->fetchAll("SELECT DISTINCT P.entity_id, P.sku, V1.store_id, V1.value_id,
    CASE
        WHEN V1.Value IS NULL
            THEN NULL
        ELSE CONCAT('', V1.value)
    END AS SmallImage
    FROM ".$_catalog_product_entity." AS P LEFT JOIN
    ".$_catalog_product_entity_varchar." AS V1 ON P.entity_id = V1.entity_id AND V1.attribute_id = ( SELECT attribute_id FROM ".$_eav_attribute." AS eav WHERE eav.attribute_code = 'small_image' and eav.entity_type_id ='4') WHERE P.entity_id = '".$_product_id."' AND V1.store_id != 0");
	
			foreach($attributeImage as $attributeName){
				if($attributeName['SmallImage'] == "" || $attributeName['SmallImage'] == NULL) {
					$connection->query("DELETE FROM ".$_catalog_product_entity_varchar." WHERE value_id = '".$attributeName['value_id']."'");
				}
			}
			
			//DELETES EXTRA BLANK STORE SMALL IMAGES LABEL
			$attributeImage = $connection->fetchAll("SELECT DISTINCT P.entity_id, P.sku, V1.store_id, V1.value_id,
    CASE
        WHEN V1.Value IS NULL
            THEN NULL
        ELSE CONCAT('', V1.value)
    END AS SmallImageLabel
    FROM ".$_catalog_product_entity." AS P LEFT JOIN
    ".$_catalog_product_entity_varchar." AS V1 ON P.entity_id = V1.entity_id AND V1.attribute_id = ( SELECT attribute_id FROM ".$_eav_attribute." AS eav WHERE eav.attribute_code = 'small_image_label' and eav.entity_type_id ='4') WHERE P.entity_id = '".$_product_id."' AND V1.store_id != 0");
	
			foreach($attributeImage as $attributeName){
				if($attributeName['SmallImageLabel'] == "" || $attributeName['SmallImageLabel'] == NULL) {
					$connection->query("DELETE FROM ".$_catalog_product_entity_varchar." WHERE value_id = '".$attributeName['value_id']."'");
				}
			}
			
			//DELETES EXTRA BLANK STORE THUMBNAIL
			$attributeImage = $connection->fetchAll("SELECT DISTINCT P.entity_id, P.sku, V1.store_id, V1.value_id,
    CASE
        WHEN V1.Value IS NULL
            THEN NULL
        ELSE CONCAT('', V1.value)
    END AS Thumbnail
    FROM ".$_catalog_product_entity." AS P LEFT JOIN
    ".$_catalog_product_entity_varchar." AS V1 ON P.entity_id = V1.entity_id AND V1.attribute_id = ( SELECT attribute_id FROM ".$_eav_attribute." AS eav WHERE eav.attribute_code = 'thumbnail' and eav.entity_type_id ='4') WHERE P.entity_id = '".$_product_id."' AND V1.store_id != 0");
	
			foreach($attributeImage as $attributeName){
				if($attributeName['Thumbnail'] == "" || $attributeName['Thumbnail'] == NULL) {
					$connection->query("DELETE FROM ".$_catalog_product_entity_varchar." WHERE value_id = '".$attributeName['value_id']."'");
				}
			}
			
			//DELETES EXTRA BLANK STORE THUMBNAIL LABEL
			$attributeImage = $connection->fetchAll("SELECT DISTINCT P.entity_id, P.sku, V1.store_id, V1.value_id,
    CASE
        WHEN V1.Value IS NULL
            THEN NULL
        ELSE CONCAT('', V1.value)
    END AS ThumbnailLabel
    FROM ".$_catalog_product_entity." AS P LEFT JOIN
    ".$_catalog_product_entity_varchar." AS V1 ON P.entity_id = V1.entity_id AND V1.attribute_id = ( SELECT attribute_id FROM ".$_eav_attribute." AS eav WHERE eav.attribute_code = 'thumbnail_label' and eav.entity_type_id ='4') WHERE P.entity_id = '".$_product_id."' AND V1.store_id != 0");
	
			foreach($attributeImage as $attributeName){
				if($attributeName['ThumbnailLabel'] == "" || $attributeName['ThumbnailLabel'] == NULL) {
					$connection->query("DELETE FROM ".$_catalog_product_entity_varchar." WHERE value_id = '".$attributeName['value_id']."'");
				}
			}
			
			//DELETES EXTRA BLANK STORE SWATCH
			$attributeImage = $connection->fetchAll("SELECT DISTINCT P.entity_id, P.sku, V1.store_id, V1.value_id,
    CASE
        WHEN V1.Value IS NULL
            THEN NULL
        ELSE CONCAT('', V1.value)
    END AS Swatch
    FROM ".$_catalog_product_entity." AS P LEFT JOIN
    ".$_catalog_product_entity_varchar." AS V1 ON P.entity_id = V1.entity_id AND V1.attribute_id = ( SELECT attribute_id FROM ".$_eav_attribute." AS eav WHERE eav.attribute_code = 'swatch' and eav.entity_type_id ='4') WHERE P.entity_id = '".$_product_id."' AND V1.store_id != 0");
	
			foreach($attributeImage as $attributeName){
				if($attributeName['Swatch'] == "" || $attributeName['Swatch'] == NULL) {
					$connection->query("DELETE FROM ".$_catalog_product_entity_varchar." WHERE value_id = '".$attributeName['value_id']."'");
				}
			}
			
			
			
			
	
		}
		#exit;
		if($params['import_images_by_url'] == "true") { 	
			$_itemCounter=0;
			$arr = array("image", "small_image", "thumbnail", "gallery", "swatch_image");
			foreach ($arr as $mediaAttributeCode) {
				if(isset($product[$mediaAttributeCode])) {
					if($product[$mediaAttributeCode] != "") {
						
						if($mediaAttributeCode == "gallery") { 
							$finalgalleryfiles="";
							$eachImageUrls = explode(',',$product[$mediaAttributeCode]);
							foreach($eachImageUrls as $_imageUrl){
									if($_imageUrl !="") {
										$orgfile = $_imageUrl;
										$fields = $mediaAttributeCode;
										$path_parts = pathinfo($orgfile);
										$file = '/'. $path_parts['basename'];
										#$fullpath = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('catalog').'/product'. $file;
										$fullpath = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('import'). $file;
										try {
											#$filewithspacesreplaced = str_replace(" ","%20", $orgfile); //fix for urls with spaces in the Url
											$ch = curl_init ($orgfile);
												  curl_setopt($ch, CURLOPT_HEADER, 0);
												  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
												  curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
												  $rawdata=curl_exec($ch);
												  curl_close ($ch);
												  if(file_exists($fullpath)) {
													#$file = '/'. $path_parts['filename']."_".$_itemCounter.".".$path_parts['extension'];
													#$fullpath = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('catalog').'/product'. $file;
													#if(file_exists($fullpath)) {
														unlink($fullpath);
													#}
												  }
												  $fp = fopen($fullpath,'x');
														fwrite($fp, $rawdata);
														fclose($fp);
										}
										catch (\Exception $e) { 
           									throw new \Magento\Framework\Exception\LocalizedException(__("IMAGE IMPORT ERROR: " . $e->getMessage()), $e);
										}
										
										if($file!="") {
											$finalgalleryfiles .= $file .",";
										}
									}
							}	
							$ProductImageGallery[$mediaAttributeCode] = substr_replace($finalgalleryfiles,"",-1);
						
						} else {
							$orgfile = $product[$mediaAttributeCode];
							$fields = $mediaAttributeCode;
							$path_parts = pathinfo($orgfile);
							$file = '/'. $path_parts['basename'];
							#$fullpath = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('catalog').'/product'. $file;
							$fullpath = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('import'). $file;
							try {
								#$filewithspacesreplaced = str_replace(" ","%20", $orgfile); //fix for urls with spaces in the Url
								$ch = curl_init ($orgfile);
									  curl_setopt($ch, CURLOPT_HEADER, 0);
									  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
									  curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
									  $rawdata=curl_exec($ch);
									  curl_close ($ch);
									  if(file_exists($fullpath)) {
										#$file = '/'. $path_parts['filename']."_".$_itemCounter.".".$path_parts['extension'];
										#$fullpath = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('catalog').'/product'. $file;
										#if(file_exists($fullpath)) {
											unlink($fullpath);
										#}
									  }
									  $fp = fopen($fullpath,'x');
											fwrite($fp, $rawdata);
											fclose($fp);
							}
							catch (\Exception $e) { 
           									throw new \Magento\Framework\Exception\LocalizedException(__("IMAGE IMPORT ERROR: " . $e->getMessage()), $e);
							}
							
							if($file!="") {
								$ProductImageGallery[$mediaAttributeCode] = $file;
							} 
						}
						
					} else {
						$ProductImageGallery[$mediaAttributeCode] = '';
					}
					$_itemCounter++;
				}
			}
			
		} else {
			$ProductImageGallery = array(
				'gallery'       => (isset($product['gallery'])) ? $product['gallery'] : '',
				'image'       => (isset($product['image'])) ? $product['image'] : '',
				'small_image'       => (isset($product['small_image'])) ? $product['small_image'] : '',
				'thumbnail'       => (isset($product['thumbnail'])) ? $product['thumbnail'] : '',
				'swatch_image'       => (isset($product['swatch_image'])) ? $product['swatch_image'] : '',
				'gallery_label' => (isset($product['gallery_label'])) ? $product['gallery_label'] : ''
		
			);
		}
			
		return $ProductImageGallery;
	}
	
	public function ProductStockdata($product){

		$defaultStockData = array(
			'manage_stock'                  => (isset($product['manage_stock'])) ? $product['manage_stock'] : null,
			'use_config_manage_stock'       => (isset($product['use_config_manage_stock'])) ? $product['use_config_manage_stock'] : null,
			'qty'                           => (isset($product['qty'])) ? $product['qty'] : null,
			'min_qty'                       => (isset($product['min_qty'])) ? $product['min_qty'] : null,
			'use_config_min_qty'            => (isset($product['use_config_min_qty'])) ? $product['use_config_min_qty'] : null,
			'min_sale_qty'                  => (isset($product['min_sale_qty'])) ? $product['min_sale_qty'] : null,
			'use_config_min_sale_qty'       => (isset($product['use_config_min_sale_qty'])) ? $product['use_config_min_sale_qty'] : null,
			'max_sale_qty'                  => (isset($product['max_sale_qty'])) ? $product['max_sale_qty'] : null,
			'use_config_max_sale_qty'       => (isset($product['use_config_max_sale_qty'])) ? $product['use_config_max_sale_qty'] : null,
			'is_qty_decimal'                => (isset($product['is_qty_decimal'])) ? $product['is_qty_decimal'] : null,
			'backorders'                    => (isset($product['backorders'])) ? $product['backorders'] : null,
			'use_config_backorders'         => (isset($product['use_config_backorders'])) ? $product['use_config_backorders'] : null,
			'notify_stock_qty'              => (isset($product['notify_stock_qty'])) ? $product['notify_stock_qty'] : null,
			'use_config_notify_stock_qty'   => (isset($product['use_config_notify_stock_qty'])) ? $product['use_config_notify_stock_qty'] : null,
			'enable_qty_increments'         => (isset($product['enable_qty_increments'])) ? $product['enable_qty_increments'] : null,
			'use_config_enable_qty_inc'     => (isset($product['use_config_enable_qty_inc'])) ? $product['use_config_enable_qty_inc'] : null,
			'qty_increments'                => (isset($product['qty_increments'])) ? $product['qty_increments'] : null,
			'use_config_qty_increments'     => (isset($product['use_config_qty_increments'])) ? $product['use_config_qty_increments'] : null,
			'is_in_stock'                   => (isset($product['is_in_stock'])) ? $product['is_in_stock'] : null,
			'low_stock_date'                => (isset($product['low_stock_date'])) ? $product['low_stock_date'] : null,
			'stock_status_changed_auto'     => (isset($product['stock_status_changed_auto'])) ? $product['stock_status_changed_auto'] : null
		);
		return $defaultStockData;
	}

	
	public function pagelayout($pagelayout){
		$data = "";
		if($pagelayout == "No layout"){ 
			$data = "";
		}else{
			$data = $pagelayout;
		}
		return $data;
	}
	
	Public function msrRetailpriceSuggested($msrp){
		$data ="";
		if($msrp == "None"){
			$data = "";
		}else{
			$data = $msrp;
		}
		return $data;
	}
	
	public function TierPricedata($TPData, $product_sku, $params){
	
 		if($params['append_tier_prices'] != "true") { 
			 //get current product tier prices
			#$productModel = $this->_ProductModel->loadByAttribute('sku', $product_sku);
			#$existing_tps = $productModel->getTierPrice();
			$existing_tps = array();
		} else {
			$existing_tps = array();
		}
		
		$etp_lookup = array();
		//make a lookup array to prevent dup tiers by qty
		foreach($existing_tps as $key => $etp){
			$etp_lookup[intval($etp['price_qty'])] = $key;
		}
		
		//parse incoming tier prices string
		$incoming_tierps = explode('|',$TPData);
		$tps_toAdd = array();  
		$tierpricecount=0;   
		  
		foreach($incoming_tierps as $tier_str){
		
			if (empty($tier_str)) continue;
			
			$tmp = array();
			$tmp = explode('=',$tier_str);
			
			if ($tmp[1] == 0 && $tmp[2] == 0) continue;
								//echo ('adding tier');
			//print_r($tmp);
			$tps_toAdd[$tierpricecount] = array(
								'website_id' => 0, // !!!! this is hard-coded for now
								#'website_id' => $tmp[0], // !!!! this is hard-coded for now
								#'website_id' => $store->getWebsiteId(),
								'cust_group' => $tmp[0], // !!! so is this
								'price_qty' => $tmp[1],
								'price' => $tmp[2],
								'delete' => ''
							);
							
			//drop any existing tier values by qty
			if(isset($etp_lookup[intval($tmp[1])])){
				unset($existing_tps[$etp_lookup[intval($tmp[1])]]);
				$tps_toAdd[$tierpricecount] = array();
			}
			$tierpricecount++;
		}
		
		//combine array
		$tps_toAdd =  array_merge($existing_tps, $tps_toAdd);
		
		//save it
		#$product->setTierPrice($tps_toAdd);
		return $tps_toAdd;
	}
	
	
   protected $_categoryCache = array();
   
   protected function addCategories($categories, $storeId, $params)
    {
		// $rootId = $store->getRootCategoryId();
		// $rootId = Mage::app()->getStore()->getRootCategoryId();
        //$rootId = 2; // our store's root category id
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$delimitertouse = "/";
		if($params['root_catalog_id'] != "") { 
			$rootId = $params['root_catalog_id'];
		} else {
		  $rootId = 2; 
		}
        if (!$rootId) {
            return array();
        }
        $rootPath = '1/'.$rootId;
        if (empty($this->_categoryCache[$storeId])) {
		
			$collection = $objectManager->create('Magento\Catalog\Model\Category')->getCollection()
                ->setStoreId($storeId)
                ->addAttributeToSelect('name');
			/*
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->setStore($store)
                ->addAttributeToSelect('name');
			*/
            $collection->getSelect()->where("path like '".$rootPath."/%'");

            foreach ($collection as $cat) {
                $pathArr = explode('/', $cat->getPath());
                $namePath = '';
                for ($i=2, $l=sizeof($pathArr); $i<$l; $i++) {
					//if(!is_null($collection->getItemById($pathArr[$i]))) { }
                    $name = $collection->getItemById($pathArr[$i])->getName();
                    $namePath .= (empty($namePath) ? '' : '/').trim($name);
                }
                $cat->setNamePath($namePath);
            }
            
            $cache = array();
            foreach ($collection as $cat) {
                $cache[$cat->getNamePath()] = $cat;
                $cat->unsNamePath();
            }
            $this->_categoryCache[$storeId] = $cache;
        }
        $cache =& $this->_categoryCache[$storeId];
        
        $catIds = array();
		  //->setIsAnchor(1)
	      //Delimiter is ' , ' so people can use ', ' in multiple categorynames
        foreach (explode(' , ', $categories) as $categoryPathStr) {
			//Remove this line if your using ^ vs / as delimiter for categories.. fix for cat names with / in them
           $categoryPathStr = preg_replace('#\s*/\s*#', '/', trim($categoryPathStr));
            if (!empty($cache[$categoryPathStr])) {
                $catIds[] = $cache[$categoryPathStr]->getId();
                continue;
            }
            $path = $rootPath;
            $namePath = '';
             #foreach (explode($delimitertouse, $categoryPathStr) as $catName) {
             foreach (explode('/', $categoryPathStr) as $catName) {
                $namePath .= (empty($namePath) ? '' : '/').$catName;
                if (empty($cache[$namePath])) {
                    $cat = $objectManager->create('Magento\Catalog\Model\Category')
                        ->setStoreId($storeId)
                        ->setPath($path)
                        ->setName($catName)
						->setIsActive(1)
                        ->save();
                    $cache[$namePath] = $cat;
                }
                $catId = $cache[$namePath]->getId();
                $path .= '/'.$catId;
				if ($catId) {
					$catIds[] = $catId;
				}
            }
        }
        return join(',', $catIds);
    }
	
	public function attributeSetNamebyid($attributeSetName){
		$connectionRead = $this->getConnection('core_read');
		$_eav_attribute_set = $this->_resource->getTableName('eav_attribute_set');
		# Load a single product attributes
		$attributeSetdata = $connectionRead->fetchAll('SELECT attribute_set_id FROM '.$_eav_attribute_set.' WHERE attribute_set_name = "'.$attributeSetName.'" and entity_type_id = 4');
		foreach($attributeSetdata as $attributeName){
			return $attributeName['attribute_set_id'];
		}
	}
	
	public function websitenamebyid($webid){
		//addFieldToFilter( 'code', $webids )
		
		$webidX = explode(',', $webid);
		$WebsiteId = array();
		foreach($webidX as $webids){
			$WebsiteId[] = $this->website->load($webids)->getId();
		}
		return $WebsiteId;
	}
	
	public function dateformat($date){
		$data = $this->date->gmtDate('Y-m-d',$date);
		return $data;
	}
}