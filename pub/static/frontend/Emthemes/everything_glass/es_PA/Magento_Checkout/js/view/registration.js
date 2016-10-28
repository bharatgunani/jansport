/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['jquery', 'uiComponent'],
    function ($, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/registration',
                accountCreated: false,
                creationStarted: false
            },
            /** Initialize observable properties */
            initObservable: function () {
                this._super()
                    .observe('accountCreated')
                    .observe('creationStarted');
                return this;
            },
            getEmailAddress: function() {
                return this.email;
            },
            createAccount: function() {
				var stat_loader_url = $('#static_files_url').val();
				$('#registration form').append('<img class="regi_loader" src="'+stat_loader_url+'" />');
                this.creationStarted(true);
                $.post(this.registrationUrl).done(
                    function() {
                        this.accountCreated(true)
                    }.bind(this)
                );
            }
        });
    }
);
