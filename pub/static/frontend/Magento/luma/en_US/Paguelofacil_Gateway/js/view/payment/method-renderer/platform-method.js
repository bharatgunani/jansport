/**
 * Paguelofacil SA
 *
 * @copyright   Paguelofacil (http://paguelofacil.com)
 */
/*browser:true*/
/*global define*/
define(
    [
        'Paguelofacil_Gateway/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Paguelofacil_Gateway/payment/platform-form'
            },

            getCode: function() {
                return 'paguelofacil_gateway';
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            }
        });
    }
);
