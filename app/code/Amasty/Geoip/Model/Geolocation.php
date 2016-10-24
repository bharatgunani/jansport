<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */

namespace Amasty\Geoip\Model;


class Geolocation extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Amasty\Geoip\Helper\Data
     */
    public $geoipHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    public function __construct(
        \Amasty\Geoip\Helper\Data $geoipHelper,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->geoipHelper = $geoipHelper;
        $this->resource = $resource;
    }

    public function locate($ip)
    {
//        $ip = '213.184.225.37';//Minsk
        if ($this->geoipHelper->isDone()) {
            $longIP = sprintf("%u", ip2long($ip));

            if (!empty($longIP)) {
                $db =  $this->resource->getConnection();
                $select = $db->select()
                    ->from(array('l' => $this->resource->getTableName('amasty_geoip_location')))
                    ->join(
                        array('b' => $this->resource->getTableName('amasty_geoip_block')),
                        'b.geoip_loc_id = l.geoip_loc_id',
                        array()
                    )
                    ->where("$longIP between b.start_ip_num and b.end_ip_num")
                    ->limit(1)
                ;


                if ($res = $db->fetchRow($select))
                    $this->setData($res);
            }
        }

        return $this;
    }
}
