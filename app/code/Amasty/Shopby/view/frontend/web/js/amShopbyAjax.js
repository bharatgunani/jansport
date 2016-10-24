/**
 * @author    Amasty Team
 * @copyright Copyright (c) Amasty Ltd. ( http://www.amasty.com/ )
 * @package   Amasty_Shopby
 */
define([
    "jquery",
    "jquery/ui",
    "Amasty_Shopby/js/amShopby",
    "productListToolbarForm",
			'Magento_Ui/js/modal/modal'
], function ($, $ui, shopby, toolbar, modal) {
    'use strict';

	$.widget('mage.amShopbyAjax',{
        options:{
            _isAmshopbyAjaxProcessed: false
        },
        _create: function (){
            var self = this;
            $(function(){
                self.initAjax();
                if (typeof window.history.replaceState === "function") {
                    window.history.replaceState({url: document.URL}, document.title);

                    setTimeout(function() {
                        /*
                         Timeout is a workaround for iPhone
                         Reproduce scenario is following:
                         1. Open category
                         2. Use pagination
                         3. Click on product
                         4. Press "Back"
                         Result: Ajax loads the same content right after regular page load
                         */
                        window.onpopstate = function(e){
                            if(e.state){
                                self.updateContent(e.state.url, false);
                            }
                        };
                    }, 0)
                }
            });

        },

        updateContent: function(link, isPushState){
            var self = this;
            $("#amasty-shopby-overlay").show();
            if (typeof window.history.pushState === 'function' && isPushState) {
                window.history.pushState({url: link}, '', link);
            }
            $.getJSON(link, {isAjax: 1}, function(data){
                $('#layered-filter-block').html(data.navigation);
                $('#layered-filter-block').trigger('contentUpdated');
                //$('body').trigger('contentUpdated');
/*var contentModal = '' +
'<div class="quickshop-modal">'+
'	<div id="quickshop_amshopby" class="quickshop-modal">'+
'		<div class="content-wrap">'+
'    			<div class="qs-loading-wrap" style="display:none;">' +
'        			<div class="qs-loader"><img src="http://macrew.info/jansport/dev/magento/pub/static/frontend/Emthemes/everything_glass/en_US/images/loader-1.gif" alt="Loading..."></div>' +
'			</div>'+
'        		<div class="qs-content" style="display:none;"></div>' +
'    		</div>'+
'	</div>' +
'</div>';*/
var quickShop = '<div class="quickshop-modal"><div id="quickshop" class="quickshop-modal">'+
    '<div class="content-wrap">'+
    '<div class="qs-loading-wrap" style="display:none;">'+
    '<div class="qs-loader"><img src="http://macrew.info/jansport/dev/magento/pub/static/frontend/Emthemes/everything_glass/en_US/images/loader-1.gif" alt="Loading..."></div>'+
    '</div>'+
    '<div class="qs-content" style="display:none;"></div>'+
    '</div>'+
    '</div>'+
    '<script type="text/javascript">'+
'var qsUrl = \'\';'+
'</script>'+
'<script type="text/x-magento-init">'+
    '{'+
        '"*":{'+
            '"Emthemes_QuickShop/js/quickshop":{'+
                '"baseUrl": "http://macrew.info/jansport/dev/magento/",'+
                '"qsLabel": "",'+
                '"itemClass": ".product-item",' +
                '"target": ".product-item-info",'+
                '"autoAddButtons":true			}'+
        '}' +
    '}' +
    '</script>' +
    '</div>    ';
                $('#amasty-shopby-product-list').html(data.categoryProducts + quickShop);
                $('#amasty-shopby-product-list').trigger('contentUpdated');
                $("#amasty-shopby-overlay").hide();
                self.initAjax();
		

var config = {
			itemClass: '.products.grid .item.product-item, .products.list .item.product-item',
			qsLabel: 'Quick Shop',
			handlerClass: 'qs-button',
			baseUrl: '/',
			modalId: 'quickshop',
			autoAddButtons: true,
			target: '.product-item-info'
		};

/*$('.'+config.handlerClass).bind('mouseover',function(){
				var $button = $(this);
				qsUrl = $button.data('href');
			});
			var $modal = modal({
				innerScroll: true,
				title: config.qsLabel,
				trigger: '.'+config.handlerClass,
				content: $('#'+config.modalId).html(),
				wrapperClass: 'qs-modal',
				buttons: [],
				opened: function(){
					var $loader = $modal.find('.qs-loading-wrap');
					var $content = $modal.find('.qs-content');
					$loader.show(); $content.hide();
					$.ajax({
						url:qsUrl,
						type: 'POST',
						cache:false,
						success: function(res){
							$content.html(res).trigger('contentUpdated');
							$content.show();
							//If product type is bundle
							if($content.find('#bundle-slide').length > 0){
								var $bundleBtn = $content.find('#bundle-slide');
								var $bundleTabLink = $('#tab-label-quickshop-product-bundle-title');
								$bundleTabLink.parent().hide();
								$bundleBtn.unbind('click').click(function(e){
									e.preventDefault();
									$bundleTabLink.parent().show();
									$bundleTabLink.click();
									return false;
								});
							}
							//If use swatches
							if($content.find('.swatch-opt').length > 0){
								var $swatchOpt = $content.find('.swatch-opt');
								$content.find('.field.configurable').hide();
								setTimeout(function(){
									$swatchOpt.find('.swatch-option').each(function(){
										var $this = $(this);
										$this.bind('mouseup',function(){
											$content.find('#product-addtocart-button').attr('disabled','disabled');
											var opId = $this.attr('option-id');
											var $curOpt = $content.find('select.super-attribute-select option[value="'+opId+'"]').first();
											if($this.hasClass('selected')){
												$curOpt.parent().val('').trigger('change');
											}else{
												$curOpt.parent().val(opId).trigger('change');
											}
											$content.find('#product-addtocart-button').removeAttr('disabled');
										});
									});
								},100);
							}
							//If use reviews
							if($content.find('#tab-label-quickshop-reviews-title').length > 0){
								var $reviewsTabLink = $content.find('#tab-label-quickshop-reviews-title');
								$content.find('.reviews-actions .action.view').click(function(){
									$reviewsTabLink.click();
								});
								$content.find('.reviews-actions .action.add').click(function(){
									$reviewsTabLink.click();
									$content.find('#nickname_field').focus();	
								})
							}
						}
					}).always(function(){$loader.hide();});
				},
				closed: function(){
					$modal.find('.qs-content').html('');
				}
			}); */









            });
        },

        initAjax: function()
        {
            var self = this;
            $.mage.amShopbyFilterAbstract.prototype.apply = function(link){
                self.updateContent(link, true);
            }
            this.options._isAmshopbyAjaxProcessed = false;
            $.mage.productListToolbarForm.prototype.changeUrl = function (paramName, paramValue, defaultValue) {
                if(self.options._isAmshopbyAjaxProcessed) {
                    return;
                }
                self.options._isAmshopbyAjaxProcessed = true;
                var urlPaths = this.options.url.split('?'),
                    baseUrl = urlPaths[0],
                    urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                    paramData = {},
                    parameters;
                for (var i = 0; i < urlParams.length; i++) {
                    parameters = urlParams[i].split('=');
                    paramData[parameters[0]] = parameters[1] !== undefined
                        ? window.decodeURIComponent(parameters[1].replace(/\+/g, '%20'))
                        : '';
                }
                paramData[paramName] = paramValue;
                if (paramValue == defaultValue) {
                    delete paramData[paramName];
                }
                paramData = $.param(paramData);

                //location.href = baseUrl + (paramData.length ? '?' + paramData : '');
                self.updateContent(baseUrl + (paramData.length ? '?' + paramData : ''), true);
            }
            var changeFunction = function(e){
                self.updateContent($(this).prop('href'), true);
                e.stopPropagation();
                e.preventDefault();
            };
            $(".swatch-option-link-layered").bind('click', changeFunction);
            $(".filter-current a").bind('click',changeFunction);
            $(".filter-actions a").bind('click', changeFunction);
            $(".toolbar .pages a").bind('click', changeFunction);
        }
    });

});
