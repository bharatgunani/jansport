/**
 * @author    Amasty Team
 * @copyright Copyright (c) Amasty Ltd. ( http://www.amasty.com/ )
 * @package   Amasty_Shopby
 */
define([
    "jquery",
    "jquery/ui"
], function ($) {
    'use strict';

    $.widget('mage.amShopbyFilterAbstract',{
        options: {
            isAjax: 0
        },
        apply: function(link){
            window.location = link;
        }
    });

    $.widget('mage.amShopbyFilterItemDefault', $.mage.amShopbyFilterAbstract, {
        options: {
        },
        _create: function () {
            var self = this;
            $(function(){

                var link = self.element;
                var parent = link.parents('.item');
                var checkbox = parent.find('input[type=checkbox]');

                var params = {
                    parent:parent,
                    checkbox:checkbox,
                    link:link
                };

                checkbox.bind('click',params,function(e){
                    var link = e.data.link;
                    self.apply(link.prop('href'));
                    e.stopPropagation();
                });

                link.bind('click',params,function(e){
                    var element = e.data.checkbox;
                    element.prop('checked', !element.prop('checked'));
                    self.apply(e.data.link.prop('href'));
                    e.stopPropagation();
                    e.preventDefault();
                });

                parent.bind('click',params,function(e){
                    var element = e.data.checkbox;
                    var link = e.data.link;
                    element.prop('checked', !element.prop('checked'));
                    self.apply(link.prop('href'));
                    e.stopPropagation();
                });
            })
        }
    });

    $.widget('mage.amShopbyFilterDropdown', $.mage.amShopbyFilterAbstract, {
        options: {
        },
        _create: function () {
            var self = this;
            $(function(){
                var $select = $(self.element[0]);
                $select.change(function() {
                    self.apply($select.val());
                });
            })
        }
    });

    $.widget('mage.amShopbyFilterSlider', $.mage.amShopbyFilterAbstract, {
        options: {
        },
        _create: function () {
            var self = this;
            $(function(){

                var elementID = self.element[0].id;
                $( "#" + elementID + "_display" ).html( self.renderLabel(self.options.from)+ " - " + self.renderLabel(self.options.to) );
                var $slider = $("#" + elementID + "_slider");
                $slider.slider({
                    step: self.options.step,
                    range: true,
                    min: self.options.min,
                    max: self.options.max,
                    values: [ self.options.from, (self.options.to)],
                    slide: function( event, ui ) {
                        $( "#" + elementID + "_display" ).html( self.renderLabel(ui.values[ 0 ]) + " - " + self.renderLabel(ui.values[ 1 ]) );
                    },
                    change: function( event, ui ) {
                        var linkHref = self.options.url.replace('amshopby_slider_from', ui.values[ 0 ]).replace('amshopby_slider_to', ui.values[1]);
                        self.apply(linkHref);
                    }
                });
            });
        },
        renderLabel:function(value) {
            return this.options.template.replace('{amount}', value);
        }
    });
});
