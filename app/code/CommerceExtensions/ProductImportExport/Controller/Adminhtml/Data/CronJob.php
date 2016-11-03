<?php
/**
 * Copyright © 2015 CommerceExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CommerceExtensions\ProductImportExport\Controller\Adminhtml\Data;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

use Magento\Config\Model\Config\Backend\Image as SourceImage;


class CronJob extends \CommerceExtensions\ProductImportExport\Controller\Adminhtml\Data
{	
	/**
     * import action from import/export data
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
	public function execute()
	{
	
	 	if ($this->getRequest()->isPost()) {
				$Profile_type = $this->getRequest()->getPost('Profile_type');
				$Schedule = $this->getRequest()->getPost('Schedule');
				
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
						$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
						$connection = $resource->getConnection();
						$core_config_data = $resource->getTableName('core_config_data') ;
						
						$request = $resource->getConnection()->query("SELECT ".$core_config_data.".config_id, ".$core_config_data.".path FROM ".$core_config_data.";");
						$request_list = $request->fetchAll();
		
						$import_result = array(); 
						
						foreach($request_list as $key => $value){
							if(in_array('crontab/default/jobs/CommerceExtensions/ProductImportExport/import_products/schedule/cron_expr', $request_list[$key]) || in_array('crontab/default/jobs/CommerceExtensions/ProductImportExport/export_products/schedule/cron_expr', $request_list[$key])){
								$import_result[] = array(
															'config_id' => $value['config_id'],	
															'path' => $value['path']
															);
							}							
						}
				
			if(!empty(array_key_exists('0', $import_result)) && empty(array_key_exists('1', $import_result))){
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
							$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
							$connection = $resource->getConnection();
							$core_config_data = $resource->getTableName('core_config_data') ;	
				
				if(in_array('crontab/default/jobs/CommerceExtensions/ProductImportExport/import_products/schedule/cron_expr', $import_result['0']) && empty(array_key_exists('1', $import_result))){
						
						if($Profile_type == 'Import_Products'){
								if($Schedule == 'Hourly'){
									$connection = $resource->getConnection()->update(''.$core_config_data.'', array('value'=> '0 * * * *'), 'config_id=' . $import_result['0']['config_id'] .'');
								}
								elseif($Schedule == 'Every_24hrs'){
									$connection = $resource->getConnection()->update(''.$core_config_data.'', array(
										'value'=> '0 23 * * *'), 'config_id=' . $import_result['0']['config_id'] .'');
								}
								else{
									$connection = $resource->getConnection()->update(''.$core_config_data.'', array(
										'value'=> '0 0 * * 0'), 'config_id=' . $import_result['0']['config_id'] .''); 	
								}
						}
						if($Profile_type == 'Export_Products'){
							if($Schedule == 'Hourly'){
								$hourly_array = array(
												'scope' => 'default',
												'scope_id' => '0',
												'path' => 'crontab/default/jobs/CommerceExtensions/ProductImportExport/export_products/schedule/cron_expr',
												'value' => '0 * * * *'
												);
								$connection = $resource->getConnection()->insert(''.$core_config_data.'', $hourly_array);					
							}  
							elseif($Schedule == 'Every_24hrs'){
								$hourly_array = array(
												'scope' => 'default',
												'scope_id' => '0',
												'path' => 'crontab/default/jobs/CommerceExtensions/ProductImportExport/export_products/schedule/cron_expr',
												'value' => '0 23 * * *'
												);
									$connection = $resource->getConnection()->insert(''.$core_config_data.'', $hourly_array);
							}
							else{
								$hourly_array = array(
												'scope' => 'default',
												'scope_id' => '0',
												'path' => 'crontab/default/jobs/CommerceExtensions/ProductImportExport/export_products/schedule/cron_expr',
												'value' => '0 0 * * 0'
												);
								$connection = $resource->getConnection()->insert(''.$core_config_data.'', $hourly_array);
							}
						}	
				}		
				
				if(in_array('crontab/default/jobs/CommerceExtensions/ProductImportExport/export_products/schedule/cron_expr', $import_result['0']) && empty(array_key_exists('1', $import_result))){
						if($Profile_type == 'Export_Products'){
							if($Schedule == 'Hourly'){	
									$connection = $resource->getConnection()->update(''.$core_config_data.'', array(
									'value'=> '0 * * * *'), 'config_id=' . $import_result['0']['config_id'] .'');
								}
								elseif($Schedule == 'Every_24hrs'){
									$connection = $resource->getConnection()->update(''.$core_config_data.'', array(
										'value'=> '0 23 * * *'), 'config_id=' . $import_result['0']['config_id'] .''); 
								}
								else{
									$connection = $resource->getConnection()->update(''.$core_config_data.'', array(
										'value'=> '0 0 * * 0'), 'config_id=' . $import_result['0']['config_id'] .''); 	
								}
				
						}
						
						if($Profile_type == 'Import_Products'){  
							if($Schedule == 'Hourly'){
								$hourly_array = array(
												'scope' => 'default',
												'scope_id' => '0',
												'path' => 'crontab/default/jobs/CommerceExtensions/ProductImportExport/import_products/schedule/cron_expr',
												'value' => '0 * * * *'
													);
								$connection = $resource->getConnection()->insert(''.$core_config_data.'', $hourly_array);					
							}  
							elseif($Schedule == 'Every_24hrs'){
								$hourly_array = array(
												'scope' => 'default',
												'scope_id' => '0',
												'path' => 'crontab/default/jobs/CommerceExtensions/ProductImportExport/import_products/schedule/cron_expr',
												'value' => '0 23 * * *'
													);
								$connection = $resource->getConnection()->insert(''.$core_config_data.'', $hourly_array);
							}
							else{
								$hourly_array = array(
												'scope' => 'default',
												'scope_id' => '0',
												'path' => 'crontab/default/jobs/CommerceExtensions/ProductImportExport/import_products/schedule/cron_expr',
												'value' => '0 0 * * 0'
													);
								$connection = $resource->getConnection()->insert(''.$core_config_data.'', $hourly_array);
							}		
						}
				}
						
			}
			if(count($import_result) == '0'){
			
				if($Profile_type == 'Export_Products'){
						if($Schedule == 'Hourly'){
								$hourly_array = array(
												'scope' => 'default',
												'scope_id' => '0',
												'path' => 'crontab/default/jobs/CommerceExtensions/ProductImportExport/export_products/schedule/cron_expr',
												'value' => '0 * * * *'
													);
									$connection = $resource->getConnection()->insert(''.$core_config_data.'', $hourly_array);					
						}  
						elseif($Schedule == 'Every_24hrs'){
							$hourly_array = array(
											'scope' => 'default',
											'scope_id' => '0',
											'path' => 'crontab/default/jobs/CommerceExtensions/ProductImportExport/export_products/schedule/cron_expr',
											'value' => '0 23 * * *'
												);
								$connection = $resource->getConnection()->insert(''.$core_config_data.'', $hourly_array);
						}
						else{
							$hourly_array = array(
											'scope' => 'default',
											'scope_id' => '0',
											'path' => 'crontab/default/jobs/CommerceExtensions/ProductImportExport/export_products/schedule/cron_expr',
											'value' => '0 0 * * 0'
												);
							$connection = $resource->getConnection()->insert(''.$core_config_data.'', $hourly_array);
						}
				}
				if($Profile_type == 'Import_Products'){  
					if($Schedule == 'Hourly'){
						$hourly_array = array(
								'scope' => 'default',
								'scope_id' => '0',
								'path' => 'crontab/default/jobs/CommerceExtensions/ProductImportExport/import_products/schedule/cron_expr',
								'value' => '0 * * * *'
									);
							$connection = $resource->getConnection()->insert(''.$core_config_data.'', $hourly_array);					
							}  
					elseif($Schedule == 'Every_24hrs'){
								$hourly_array = array(
												'scope' => 'default',
												'scope_id' => '0',
												'path' => 'crontab/default/jobs/CommerceExtensions/ProductImportExport/import_products/schedule/cron_expr',
												'value' => '0 23 * * *'
													);
								$connection = $resource->getConnection()->insert(''.$core_config_data.'', $hourly_array);
					}
					else{
						$hourly_array = array(
										'scope' => 'default',
										'scope_id' => '0',
										'path' => 'crontab/default/jobs/CommerceExtensions/ProductImportExport/import_products/schedule/cron_expr',
										'value' => '0 0 * * 0'
											);
								$connection = $resource->getConnection()->insert(''.$core_config_data.'', $hourly_array);
					}		
				}
			}
			
			if(!empty(array_key_exists('0', $import_result)) && !empty(array_key_exists('1', $import_result))){
					 if($Profile_type == 'Import_Products'){
								if($Schedule == 'Hourly'){
									$connection = $resource->getConnection()->update(''.$core_config_data.'', array('value'=> '0 * * * *'), 'config_id=' . $import_result['1']['config_id'] .'');
								}
								elseif($Schedule == 'Every_24hrs'){
									$connection = $resource->getConnection()->update(''.$core_config_data.'', array(
										'value'=> '0 23 * * *'), 'config_id=' . $import_result['1']['config_id'] .'');
								}
								else{
									$connection = $resource->getConnection()->update(''.$core_config_data.'', array(
										'value'=> '0 0 * * 0'), 'config_id=' . $import_result['1']['config_id'] .''); 	
								}
						}
					if($Profile_type == 'Export_Products'){
							if($Schedule == 'Hourly'){	
									$connection = $resource->getConnection()->update(''.$core_config_data.'', array(
									'value'=> '0 * * * *'), 'config_id=' . $import_result['0']['config_id'] .'');
								}
								elseif($Schedule == 'Every_24hrs'){
									$connection = $resource->getConnection()->update(''.$core_config_data.'', array(
										'value'=> '0 23 * * *'), 'config_id=' . $import_result['0']['config_id'] .''); 
								}
								else{
									$connection = $resource->getConnection()->update(''.$core_config_data.'', array(
										'value'=> '0 0 * * 0'), 'config_id=' . $import_result['0']['config_id'] .''); 	
								}
					} 
					
			}
						$params = $this->getRequest()->getParams();
						$File_name = $this->getRequest()->getPost('File_name');
						$File_path = $this->getRequest()->getPost('File_path'); 
						$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
						$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
						$connection = $resource->getConnection();
						$prefix = $resource->getTableName('productimportexport_cronjobdata') ;
						$insData = array();
						$insData = array(
						'root_catalog_id'=> $params['root_catalog_id'],
						'update_products_only'=> $params['update_products_only'],
						'import_images_by_url'=> $params['import_images_by_url'],
						'reimport_images'=> $params['reimport_images'],
						'deleteall_andreimport_images'=> $params['deleteall_andreimport_images'],
						'append_tier_prices'=> $params['append_tier_prices'],
						'append_categories'=> $params['append_categories'],
						'import_rates_file'=> $params['import_rates_file'],
						'product_id_from'=> $params['product_id_from'],
						'product_id_to'=> $params['product_id_to'],
						'export_grouped_position'=> $params['export_grouped_position'],
						'export_related_position'=> $params['export_related_position'],
						'export_crossell_position'=> $params['export_crossell_position'], //by_percent || cart_fixed
						'export_upsell_position'=> $params['export_upsell_position'],
						'export_category_paths'=> $params['export_category_paths'],
						'export_full_image_paths'=> $params['export_full_image_paths'],
						'File_name'=> $params['File_name'], //0 1 or 2
						'content'=> $params['File_path'],
						'title'=> $params['Export_File_Location'],
						'import_enclose'=> $params['import_enclose'],
						'import_delimiter'=> $params['import_delimiter'],
						'export_enclose'=> $params['export_enclose'],
						'export_delimiter'=> $params['export_delimiter'],
						'Profile_type'=> $params['Profile_type'],
						'Schedule'=> $params['Schedule'],
						);
					
						$rs = $resource->getConnection()->query("SELECT ".$prefix.".root_catalog_id, ".$prefix.".update_products_only, ".$prefix.".import_images_by_url, ".$prefix.".reimport_images, ".$prefix.".deleteall_andreimport_images, ".$prefix.".append_tier_prices, ".$prefix.".append_categories, ".$prefix.".import_rates_file, ".$prefix.".product_id_from, ".$prefix.".product_id_to, ".$prefix.".export_grouped_position, ".$prefix.".export_related_position, ".$prefix.".export_crossell_position, ".$prefix.".export_upsell_position, ".$prefix.".export_category_paths, ".$prefix.".export_full_image_paths, ".$prefix.".import_enclose, ".$prefix.".import_delimiter, ".$prefix.".export_enclose, ".$prefix.".export_delimiter, ".$prefix.".Profile_type, ".$prefix.".Schedule, ".$prefix.".File_name, ".$prefix.".File_path, ".$prefix.".Export_File_Location FROM ".$prefix.";");
						$rows = $rs->fetchAll();
						
						if(count($rows)){
							$connection = $resource->getConnection()->update(''.$prefix.'', array(
							'root_catalog_id'=> $params['root_catalog_id'],
							'update_products_only'=> $params['update_products_only'],
							'import_images_by_url'=> $params['import_images_by_url'],
							'reimport_images'=> $params['reimport_images'],
							'deleteall_andreimport_images'=> $params['deleteall_andreimport_images'],
							'append_tier_prices'=> $params['append_tier_prices'],
							'append_categories'=> $params['append_categories'],
							'import_rates_file'=> $params['import_rates_file'],
							'product_id_from'=> $params['product_id_from'],
							'product_id_to'=> $params['product_id_to'],
							'export_grouped_position'=> $params['export_grouped_position'],
							'export_related_position'=> $params['export_related_position'],
							'export_crossell_position'=> $params['export_crossell_position'], //by_percent || cart_fixed
							'export_upsell_position'=> $params['export_upsell_position'],
							'export_category_paths'=> $params['export_category_paths'],
							'export_full_image_paths'=> $params['export_full_image_paths'],
							'File_name'=> $params['File_name'], //0 1 or 2
							'content'=> $params['File_path'],
							'title'=> $params['Export_File_Location'],
							'import_enclose'=> $params['import_enclose'],
							'import_delimiter'=> $params['import_delimiter'],
							'export_enclose'=> $params['export_enclose'],
							'export_delimiter'=> $params['export_delimiter'],
							'Profile_type'=> $params['Profile_type'],
							'Schedule'=> $params['Schedule']
							));
							// TODO: Implement execute() method.
						$this->_redirect($this->_redirect->getRefererUrl());
						$this->messageManager->addSuccess(__('Your_Provided_Data_is_successfully_updated'));
						}
					else{
						
						$connection = $resource->getConnection()->insert(''.$prefix.'', $insData);
						$rs = $resource->getConnection()->query("SELECT ".$prefix.".root_catalog_id, ".$prefix.".update_products_only, ".$prefix.".import_images_by_url, ".$prefix.".reimport_images, ".$prefix.".deleteall_andreimport_images, ".$prefix.".append_tier_prices, ".$prefix.".append_categories, ".$prefix.".import_rates_file, ".$prefix.".product_id_from, ".$prefix.".product_id_to, ".$prefix.".export_grouped_position, ".$prefix.".export_related_position, ".$prefix.".export_crossell_position, ".$prefix.".export_upsell_position, ".$prefix.".export_category_paths, ".$prefix.".export_full_image_paths, ".$prefix.".File_name, ".$prefix.".content, ".$prefix.".Export_File_Location FROM ".$prefix.";");	
						
						// TODO: Implement execute() method.
						$this->_redirect($this->_redirect->getRefererUrl());
						$this->messageManager->addSuccess(__('Your_Provided_Data_is_successfully_Saved'));
					} 
		}
		else{
 			$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$prefix = $resource->getTableName('productimportexport_cronjobdata') ;
			$core_config_data = $resource->getTableName('core_config_data') ;
						
						$rs = $resource->getConnection()->query("SELECT ".$prefix.".root_catalog_id, ".$prefix.".update_products_only, ".$prefix.".import_images_by_url, ".$prefix.".reimport_images, ".$prefix.".deleteall_andreimport_images, ".$prefix.".append_tier_prices, ".$prefix.".append_categories, ".$prefix.".import_rates_file, ".$prefix.".import_delimiter, ".$prefix.".import_enclose, ".$prefix.".export_delimiter, ".$prefix.".export_enclose, ".$prefix.".product_id_from, ".$prefix.".product_id_to, ".$prefix.".export_grouped_position, ".$prefix.".export_related_position, ".$prefix.".export_crossell_position, ".$prefix.".export_upsell_position, ".$prefix.".export_category_paths, ".$prefix.".export_full_image_paths, ".$prefix.".File_name, ".$prefix.".content, ".$prefix.".Export_File_Location FROM ".$prefix.";");	
						
							$rows = $rs->fetchAll();
							
					$request = $resource->getConnection()->query("SELECT ".$core_config_data.".config_id, ".$core_config_data.".path FROM ".$core_config_data.";");
				$request_list = $request->fetchAll();
				
						foreach($request_list as $key => $value){
							if(in_array('crontab/default/jobs/CommerceExtensions/ProductImportExport/import_products/schedule/cron_expr', $request_list[$key]) || in_array('crontab/default/jobs/CommerceExtensions/ProductImportExport/export_products/schedule/cron_expr', $request_list[$key])){
								$import_result[] = array(
															'config_id' => $value['config_id'],	
															'path' => $value['path']
															);
							}							
						}
						 
						 // Send the fetched array for import functionality 
						
						foreach($import_result as $key => $value){
							if(in_array('crontab/default/jobs/CommerceExtensions/ProductImportExport/import_products/schedule/cron_expr', $import_result[$key])){	
								$this->Cronjob_import($rows) ;
					
							}	
						}	 
		}				
	}
		
	// For Import 
	
	public function Cronjob_import($params){
	
			try{
			
				$importHandler = $this->_objectManager->create('CommerceExtensions\ProductImportExport\Model\Data\CsvImportHandler');  
				
				$File_path = $params['0']['content'] ;
				$File_name = $params['0']['File_name'] ;
				$url = $File_path.'/'.$File_name;
				$name = $File_name;
				
				$fetched_file_name = array();
				
				$tempName = tempnam('/tmp', 'php_files');
				
				$imgRawData = file_get_contents($url);
				
				file_put_contents($tempName, $imgRawData);
				
				$fetched_file_name = array(
					'name' => $name,
					'type' => 'application/vnd.ms-excel',
					'tmp_name' => $tempName,
					'error' => 0,
					'size' => strlen($imgRawData),
				); 
				
				$readData = $importHandler->UploadCsvOfproduct($fetched_file_name);
				
				$filepath = $readData['path'].'/'.$readData['file'];
				
				$param1 = array() ;
				$param1 = array(
				'import_delimiter' => $params['0']['import_delimiter'],
				'import_enclose' => $params['0']['import_enclose'],
				'root_catalog_id'=> $params['0']['root_catalog_id'],
				'update_products_only'=> $params['0']['update_products_only'],
				'import_images_by_url'=> $params['0']['import_images_by_url'],
				'reimport_images'=> $params['0']['reimport_images'],
				'deleteall_andreimport_images'=> $params['0']['deleteall_andreimport_images'],
				'append_tier_prices'=> $params['0']['append_tier_prices'],
				'append_categories'=> $params['0']['append_categories']
				);
							
				$importHandler->readCsvFile($filepath, $param1);
				$success = $this->messageManager->addSuccess(__('The Products have been imported Successfully.'));
				if($success){
					$this->reindexdata();
				}
				$this->_redirect($this->_redirect->getRefererUrl()); 
							
				// /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
/*				$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
				$resultRedirect->setUrl($this->_redirect->getRedirectUrl());
				return $resultRedirect; */
			}
			catch (\Magento\Framework\Exception\LocalizedException $e) {
						$this->messageManager->addError($e->getMessage());
					} catch (\Exception $e) {
						$this->messageManager->addError(__('Invalid file upload attempt' . $e->getMessage()));
					} 
					
	}
	
	public function reindexdata(){
		$Indexer = $this->_objectManager->create('Magento\Indexer\Model\Processor');
		$Indexer->reindexAll();
	}

}	