<?php
/**
 * *
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */

namespace Magestore\OneStepCheckout\Block\Checkout;

/**
 * Class LayoutProcessor
 * @package Magestore\OneStepCheckout\Block\Checkout
 */
class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    
    /**
     * @var \Magestore\OneStepCheckout\Helper\Config
     */
    protected $_helperConfig;

    /**
     * LayoutProcessor constructor.
     * @param \Magestore\OneStepCheckout\Helper\Config $helperConfig
     */
    public function __construct(
        \Magestore\OneStepCheckout\Helper\Config $helperConfig
    ) {
        $this->_helperConfig = $helperConfig;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if ($this->_helperConfig->isEnabledOneStep()) {
            if(isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['discount'])) {
                unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['discount']);
            }
            if(isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {
                $childs = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $this->processShippingInput($childs);
            }
            if(isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'])) {
                $childs = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];

                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] = $this->processBillingInput($childs);
            }
            if(isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['giftCardAccount'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['giftCardAccount']['component'] = "Magestore_OneStepCheckout/js/view/payment/gift-card-account";
            }
            if(isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['giftCardAccount'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['giftCardAccount']['component'] = "Magestore_OneStepCheckout/js/view/summary/gift-card-account";
            }
            if(isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['storeCredit'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['storeCredit']['component'] = "Magestore_OneStepCheckout/js/view/payment/customer-balance";
            }
            if(isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['customerbalance'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['customerbalance']['component'] = "Magestore_OneStepCheckout/js/view/summary/customer-balance";
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['customerbalance']['config']['template'] = "Magestore_OneStepCheckout/summary/customer-balance";
            }
            if(isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['reward'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['reward']['component'] = "Magestore_OneStepCheckout/js/view/payment/reward";
            }
            if(isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['before_grandtotal']['children']['reward'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['before_grandtotal']['children']['reward']['component'] = "Magestore_OneStepCheckout/js/view/summary/reward";
            }

            if(isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']['before-place-order']['children']['agreements'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']['before-place-order']['children']['agreements']['config']['template'] = "Magestore_OneStepCheckout/checkout/checkout-agreements";
            }
        }

        return $jsLayout;

    }

    /**
     * @param $childs
     * @return mixed
     */
    public function processShippingInput($childs){
        if(count($childs) > 0){
            foreach($childs as $key => $child){
                if(isset($child['config']['template']) && $child['config']['template'] == 'ui/group/group' && isset($child['children'])){
                    $childs[$key]['component'] = "Magestore_OneStepCheckout/js/view/form/components/group";
                    if (isset($childs[$key]['children'])) {
                        $children = $childs[$key]['children'];
                        $newChildren = array();
                        foreach ($children as $item) {
                            $item['config']['elementTmpl'] = "Magestore_OneStepCheckout/form/element/input";
                            $newChildren[] = $item;
                        }
                        $childs[$key]['children'] = $newChildren;
                    }
                }
                if(isset($child['config']) && isset($child['config']['elementTmpl']) && $child['config']['elementTmpl'] == "ui/form/element/input"){
                    $childs[$key]['config']['elementTmpl'] = "Magestore_OneStepCheckout/form/element/input";
                }
                if(isset($child['config']) && isset($child['config']['template']) && $child['config']['template'] == "ui/form/field"){
                    $childs[$key]['config']['template'] = "Magestore_OneStepCheckout/js/form/components/field";
                    $childs[$key]['config']['template'] = "Magestore_OneStepCheckout/form/field";
                }
                $sortOrder = $this->_helperConfig->getFieldSortOrder($key);
                if($sortOrder !== false){
                    $childs[$key]['sortOrder'] = strval($sortOrder);
                }
            }
        }
        return $childs;
    }

    /**
     * @param $payments
     * @return mixed
     */
    public function processBillingInput($payments){
        if(count($payments) > 0){
            foreach($payments as $paymentCode => $paymentComponent){
                if (isset($paymentComponent['component']) && $paymentComponent['component'] != "Magento_Checkout/js/view/billing-address") {
                    continue;
                }
                $paymentComponent['component'] = "Magestore_OneStepCheckout/js/view/billing-address";
                if(isset($paymentComponent['children']['form-fields']['children'])){
                    $childs = $paymentComponent['children']['form-fields']['children'];
                    foreach($childs as $key => $child){
                        if(isset($child['config']['template']) && $child['config']['template'] == 'ui/group/group' && isset($child['children'])){
                            $childs[$key]['component'] = "Magestore_OneStepCheckout/js/view/form/components/group";
                            if (isset($childs[$key]['children'])) {
                                $children = $childs[$key]['children'];
                                $newChildren = array();
                                foreach ($children as $item) {
                                    $item['config']['elementTmpl'] = "Magestore_OneStepCheckout/form/element/input";
                                    $newChildren[] = $item;
                                }
                                $childs[$key]['children'] = $newChildren;
                            }
                        }
                        if(isset($child['config']) && isset($child['config']['elementTmpl']) && $child['config']['elementTmpl'] == "ui/form/element/input"){
                            $childs[$key]['config']['elementTmpl'] = "Magestore_OneStepCheckout/form/element/input";
                        }
                        if(isset($child['config']) && isset($child['config']['template']) && $child['config']['template'] == "ui/form/field"){
                            $childs[$key]['config']['template'] = "Magestore_OneStepCheckout/form/field";
                        }
                        $sortOrder = $this->_helperConfig->getFieldSortOrder($key);
                        if($sortOrder !== false){
                            $childs[$key]['sortOrder'] = $sortOrder;
                        }
                    }
                    $paymentComponent['children']['form-fields']['children'] = $childs;
                    $payments[$paymentCode] = $paymentComponent;
                }
            }
        }
        return $payments;
    }
}
