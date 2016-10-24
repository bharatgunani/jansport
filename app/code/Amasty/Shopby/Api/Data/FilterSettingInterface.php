<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Api\Data;

interface FilterSettingInterface
{
    const FILTER_SETTING_ID = 'setting_id';
    const FILTER_CODE = 'filter_code';
    const DISPLAY_MODE = 'display_mode';
    const IS_MULTISELECT = 'is_multiselect';
    const IS_SEO_SIGNIFICANT = 'is_seo_significant';
    const INDEX_MODE = 'index_mode';
    const FOLLOW_MODE = 'follow_mode';
    const HIDE_ONE_OPTION = 'hide_one_option';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getDisplayMode();

    /**
     * @return int
     */
    public function getFollowMode();

    /**
     * @return string|null
     */
    public function getFilterCode();

    /**
     * @return int
     */
    public function getHideOneOption();

    /**
     * @return int
     */
    public function getIndexMode();

    /**
     * @return bool|null
     */
    public function isMultiselect();

    /**
     * @return bool|null
     */
    public function isSeoSignificant();

    /**
     * @param int $id
     * @return FilterSettingInterface
     */
    public function setId($id);

    /**
     * @param int $displayMode
     * @return FilterSettingInterface
     */
    public function setDisplayMode($displayMode);

    /**
     * @param int $indexMode
     * @return FilterSettingInterface
     */
    public function setIndexMode($indexMode);

    /**
     * @param int $followMode
     * @return FilterSettingInterface
     */
    public function setFollowMode($followMode);

    /**
     * @param int $hideOneOption
     * @return FilterSettingInterface
     */
    public function setHideOneOption($hideOneOption);

    /**
     * @param bool $isMultiselect
     * @return FilterSettingInterface
     */
    public function setIsMultiselect($isMultiselect);

    /**
     * @param bool $isSeoSignificant
     * @return FilterSettingInterface
     */
    public function setIsSeoSignificant($isSeoSignificant);

    /**
     * @param string $filterCode
     * @return FilterSettingInterface
     */
    public function setFilterCode($filterCode);
}
