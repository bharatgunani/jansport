<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */


namespace Amasty\Geoip\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    CONST BLOCK_FILE = 'amgeoip/general/block_file_url';
    CONST LOCATION_FILE = 'amgeoip/general/location_file_url';

    public $geoipRequiredFiles = array(
        'block'    => 'GeoLite2-City-Blocks-IPv4.csv',
        'location' => 'GeoLite2-City-Locations-en.csv'
    );

    public $_geoipIgnoredLines = array(
        'block'    => 2,
        'location' => 2
    );

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Amasty\Geoip\Model\Import
     */
    protected $importModel;

    /**
     * Resource model of config data
     *
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $_resource;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Amasty\Geoip\Model\Import $importModel,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $_resource
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
        $this->importModel = $importModel;
        $this->_resource = $_resource;
    }

    public function getUrlBlockFile() {
        return $this->scopeConfig->getValue(self::BLOCK_FILE);
    }

    public function getUrlLocationFile() {
        return $this->scopeConfig->getValue(self::LOCATION_FILE);
    }

    public function isDone()
    {
        return ($this->scopeConfig->getValue('amgeoip/import/location') && $this->scopeConfig->getValue('amgeoip/import/block'));
    }

    public function importTableHasData() {
        return $this->importModel->importTableHasData();
    }

    public function resetDone()
    {
        $this->_resource->saveConfig('amgeoip/import/block', 0, 'default', 0);
        $this->_resource->saveConfig('amgeoip/import/location', 0, 'default', 0);
    }

    public function getDirPath($action)
    {
        $varDir = $this->directoryList->getPath('var');

        if ($action == 'download_and_import') {
            $dir = $varDir . DIRECTORY_SEPARATOR . 'amasty' . DIRECTORY_SEPARATOR . 'geoip' . DIRECTORY_SEPARATOR . 'amasty_files';
        } else {
            $dir = $varDir . DIRECTORY_SEPARATOR . 'amasty' . DIRECTORY_SEPARATOR . 'geoip';
        }
        return $dir;
    }

    public function getFilePath($type, $action)
    {
        $dir = $this->getDirPath($action);
        $file = $dir . DIRECTORY_SEPARATOR . $this->geoipRequiredFiles[$type];
        return $file;
    }

    public function isFileExist($filePath)
    {
        if (file_exists($filePath)) {
            return true;
        }
        return false;
    }

}
