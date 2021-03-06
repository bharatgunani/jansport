<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */

namespace Amasty\Geoip\Model;


class Import extends \Magento\Framework\Model\AbstractModel
{

    protected static $_sessionKey = 'am_geoip_import_process_%key%';

    static protected function getSessionKey($table)
    {
        return strtr(self::$_sessionKey, array(
            '%key%' => $table
        ));
    }

    protected $_modelsCols = array(
        'block'    => array(
            'start_ip_num', 'end_ip_num', 'geoip_loc_id', 'postal_code', 'latitude', 'longitude'
        ),
        'location' => array(
            'geoip_loc_id', 'country', 'city'
        )
    );

    protected $_rowsPerTransaction = 10000;

    /**
     * Resource model of config data
     *
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $configInterface;

    /**
     * Database write connection
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Core Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $coreDate;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->configInterface = $configInterface;
        $this->resource = $resource;
        $this->scopeConfig = $scopeConfig;
        $this->coreDate = $coreDate;
        $this->backendSession = $backendSession;
    }

    public function startProcess($table, $filePath, $ignoredLines = 0)
    {
        $ret = array();

        $write = $this->resource->getConnection();
        $query = 'SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME LIKE \'%amasty_geoip_'. $table .'_%\'';
        $columns = $write->fetchCol($query);
        $oldTemporary = implode(', ', $columns);
        if (!empty($oldTemporary)) {
            $delete = "DROP TABLE IF EXISTS $oldTemporary";
            $write->query($delete);
        }


        $importProcess = array(
            'position'    => 0,
            'tmp_table'   => NULL,
            'rows_count'  => $this->_getRowsCount($filePath) - $ignoredLines,
            'current_row' => 0
        );

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $tmpTableName = $this->_prepareImport($table);

            while ($ignoredLines > 0 && ($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $ignoredLines--;
            }


            $importProcess['position'] = ftell($handle);
            $importProcess['tmp_table'] = $tmpTableName;
            $ret = $importProcess;
        }


        $this->backendSession->setData(self::getSessionKey($table), $importProcess);

            //$this->_saveInDb($table, $importProcess);

        return $ret;
    }

    public function importTableHasData() {
        $write = $this->resource->getConnection();
        $blockTable = $this->resource->getTableName('amasty_geoip_block');
        $locationTable = $this->resource->getTableName('amasty_geoip_location');
        $countBlock = "SELECT count(block_id) as count_block_id FROM $blockTable";
        $countLocation = "SELECT count(location_id) as count_location_id FROM $locationTable";
        $block = $write->fetchCol($countBlock);
        $location = $write->fetchCol($countLocation);
        if (array_key_exists(0, $block) && array_key_exists(0, $location)) {
            if ($block[0] && $location[0]) {
                return true;
            }
        }
        return false;
    }

    protected function _getRowsCount($filePath)
    {
        $linecount = 0;
        $handle = fopen($filePath, "r");
        while(!feof($handle)){
            $line = fgets($handle);
            $linecount++;
        }
        return $linecount;

        $a = sizeof(file($filePath));
        return sizeof(file($filePath));
    }

    protected function _prepareImport($table)
    {
        $write = $this->resource->getConnection();

        $targetTable = $this->resource
            ->getTableName('amasty_geoip_' . $table)
        ;

        $tmpTableName = uniqid($targetTable . '_');

        $query = 'create table ' . $tmpTableName . ' like ' . $targetTable;
        $write->query($query);

        $query = 'alter table ' . $tmpTableName . ' engine innodb';
        $write->query($query);

        return $tmpTableName;
    }

    public function doProcess($table, $filePath)
    {
        $ret = array();
        if (($handle = fopen($filePath, "r")) !== FALSE) {

            $importProcess =  $this->backendSession->getData(self::getSessionKey($table));

           //$importProcess = $this->_getFromDb($table);

            $write = $this->resource->getConnection();

            if ($importProcess) {
                $tmpTableName = $importProcess['tmp_table'];

                try {
                    $position = $importProcess['position'];

                    fseek($handle, $position);

                    $transactionIterator = 0;

                    $write->beginTransaction();

                    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {

                        $this->_importItem($table, $tmpTableName, $data);

                        $transactionIterator++;

                        if ($transactionIterator >= $this->_rowsPerTransaction) {
                            break;
                        }
                    }

                    $write->commit();

                    $importProcess['current_row'] += $transactionIterator;

                    $importProcess['position'] = ftell($handle);


                    $this->backendSession->setData(self::getSessionKey($table), $importProcess);
                    //$this->_saveInDb($table, $importProcess);

                    $ret = $importProcess;

                } catch (\Exception $e) {
                    $write->rollback();

                    $this->_destroyImport($table, $tmpTableName);

                    throw new \Exception($e->getMessage());
                }
            } else
                throw new \Exception('run start before');
        }

        return $ret;
    }

    public function commitProcess($table, $isDownload = false)
    {
        $ret = FALSE;
        $importProcess =  $this->backendSession->getData(self::getSessionKey($table));
        //$importProcess = $this->_getFromDb($table);

        if ($importProcess) {
            $tmpTableName = $importProcess['tmp_table'];

            if ($isDownload) {
                $configDate = 'date_download';
            } else {
                $configDate = 'date';
            }

            try {
                $this->configInterface
                    ->saveConfig('amgeoip/import/' . $table, 1, 'default', 0)
                    ->saveConfig('amgeoip/import/' . $configDate, $this->coreDate->gmtDate(), 'default', 0)
                    //->reinit()
                ;//clean cache

                $this->_doneImport($table, $tmpTableName);

            } catch (\Exception $e) {
                $this->_destroyImport($table, $tmpTableName);

                throw new \Exception($e->getMessage());
            }

            $this->_destroyImport($table, $tmpTableName);

            $ret = TRUE;
        } else
            throw new \Exception('run start before');

        return $ret;
    }

    protected function _doneImport($table, $tmpTableName)
    {
        $write = $this->resource->getConnection();

        $targetTable = $this->resource
            ->getTableName('amasty_geoip_' . $table)
        ;

        $query = 'delete from ' . $targetTable;
        $write->query($query);

        $query = 'insert into ' . $targetTable . ' select * from ' . $tmpTableName;
        $write->query($query);

    }

    protected function _importItem($table, $tmpTableName, &$data)
    {
        if ($table == 'block' && is_array($data) && isset($data[0])) {
            list($ip, $mask) = explode('/', $data[0]);
            $ip2long = ip2long($ip);
            $min = ($ip2long >> (32 - $mask))  << (32 - $mask);
            $max = $ip2long | ~(-1 << (32 - $mask));
            $newData = array($min, $max, $data[1], $data[6], $data[7], $data[8]);
            $data = $newData;
        } elseif($table == 'location' && is_array($data)) {
            $newData = array($data[0], $data[4], $data[10]);
            $data = $newData;
        }
        $write = $this->resource->getConnection();

        $query = 'insert into `' . $tmpTableName . '`' .
            '(`' . implode('`, `', $this->_modelsCols[$table]) . '`) VALUES ' .
            '(?)';

        $query = $write->quoteInto($query, $data);

        $write->query($query);
    }

    protected function _destroyImport($table, $tmpTableName)
    {
        $write = $this->resource->getConnection();

        $query = 'drop table ' . $tmpTableName;
        $write->query($query);

        $this->backendSession->setData(self::getSessionKey($table), NULL);
        //$this->_clearDb();
    }

    protected function _saveInDb($table, $importProcess)
    {
        $this->configInterface->saveConfig('amgeoip/import/position/' . $table, $importProcess['position'], 'default', 0);
        $this->configInterface->saveConfig('amgeoip/import/tmp_table/' . $table, $importProcess['tmp_table'], 'default', 0);
        $this->configInterface->saveConfig('amgeoip/import/rows_count/' . $table, $importProcess['rows_count'], 'default', 0);
        $this->configInterface->saveConfig('amgeoip/import/current_row/' . $table, $importProcess['current_row'], 'default', 0);
    }

    protected function _getFromDb($table)
    {
        $importProcess = NULL;
        $importProcess['position'] = $this->scopeConfig->getValue('amgeoip/import/position/' . $table, 'default', 0);
        $importProcess['tmp_table'] = $this->scopeConfig->getValue('amgeoip/import/tmp_table/' . $table, 'default', 0);
        $importProcess['rows_count'] = $this->scopeConfig->getValue('amgeoip/import/rows_count/' . $table, 'default', 0);
        $importProcess['current_row'] = $this->scopeConfig->getValue('amgeoip/import/current_row/' . $table, 'default', 0);
        return $importProcess;
    }

    protected function _clearDb()
    {
        $this->configInterface->deleteConfig('amgeoip/import/position/location', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/position/block', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/tmp_table/location', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/tmp_table/block', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/rows_count/location', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/rows_count/block', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/current_row/location', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/current_row/block', 'default', 0);
    }
}
