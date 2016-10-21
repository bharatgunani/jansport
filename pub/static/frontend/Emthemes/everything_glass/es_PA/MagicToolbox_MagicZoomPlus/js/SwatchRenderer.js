
define([
    'jquery',
    'Magento_Swatches/js/SwatchRenderer'
], function($){

    $.widget('magictoolbox.SwatchRenderer', $.custom.SwatchRenderer, {

        options: {
            mtConfig: {
                enabled: false,
                simpleProductId: null,
                enabled: false,
                useOriginalGallery: true,
                currentProductId: null,
                galleryData: [],
                tools: {},
                thumbSwitcherOptions: {}
            }
        },

        /**
         * @private
         */
        _create: function () {

            this._super();

            var spConfig = this.options.jsonConfig;

            if (typeof(spConfig.magictoolbox) != 'undefined' && typeof(spConfig.productId) != 'undefined') {
                this.options.mtConfig.enabled = true;
                this.options.mtConfig.currentProductId = spConfig.productId;
                this.options.mtConfig.useOriginalGallery = spConfig.magictoolbox.useOriginalGallery;
                this.options.mtConfig.galleryData = spConfig.magictoolbox.galleryData;
                this.options.mtConfig.tools = {
                    'Magic360': {
                        'idTemplate': 'product{tool}-{id}',
                        'objName': 'Magic360',
                        'undefined': true
                    },
                    'MagicSlideshow': {
                        'idTemplate': 'product{tool}-{id}',
                        'objName': 'MagicSlideshow',
                        'undefined': true
                    },
                    'MagicScroll': {
                        'idTemplate': 'product{tool}-{id}',
                        'objName': 'MagicScroll',
                        'undefined': true
                    },
                    'MagicZoomPlus': {
                        'idTemplate': '{tool}Image{id}',
                        'objName': 'MagicZoom',
                        'undefined': true
                    },
                    'MagicZoom': {
                        'idTemplate': '{tool}Image{id}',
                        'objName': 'MagicZoom',
                        'undefined': true
                    },
                    'MagicThumb': {
                        'idTemplate': '{tool}Image{id}',
                        'objName': 'MagicThumb',
                        'undefined': true
                    }
                };
                for (var tool in this.options.mtConfig.tools) {
                    this.options.mtConfig.tools[tool].undefined = (typeof(window[tool]) == 'undefined');
                }
            }
        },

        /**
         * @private
         */
        _initThumbSwitcherOptions: function () {
            var container = $('div.product.media div.MagicToolboxContainer');
            if (container.length && container.magicToolboxThumbSwitcher) {
                //NOTE: get thumb switcher options
                this.options.mtConfig.thumbSwitcherOptions = container.magicToolboxThumbSwitcher('getOptions');
                //NOTE: cut off unnecessary options
                for (var optionName in this.options.mtConfig.thumbSwitcherOptions) {
                    if (optionName.match(/^tool|productId|switchMethod$/)) {
                        continue;
                    }
                    delete this.options.mtConfig.thumbSwitcherOptions[optionName];
                }
            }
        },

        /**
         * Callback for product media
         *
         * @param $this
         * @param response
         * @private
         */
        _ProductMediaCallback: function ($this, response) {

            //NOTE: init thumb switcher options
            if (!this.options.mtConfig.useOriginalGallery && !Object.keys(this.options.mtConfig.thumbSwitcherOptions).length) {
                this._initThumbSwitcherOptions();
            }

            if (response.variantProductId) {
                this.options.mtConfig.simpleProductId = response.variantProductId;
                delete response.variantProductId;
            } else {
                this.options.mtConfig.simpleProductId = null;
            }

            this._super($this, response);
        },

        /**
         * Update [gallery-placeholder] or [product-image-photo]
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isProductViewExist
         */
        updateBaseImage: function (images, context, isProductViewExist) {

            if (!this.options.mtConfig.enabled) {
                this._super(images, context, isProductViewExist);
                return;
            }

            var spConfig = this.options.jsonConfig,
                galleryData = [],
                tools = {};

            if (this.options.mtConfig.useOriginalGallery) {
                images = spConfig.images[this.options.mtConfig.simpleProductId];
                if (!images) {
                    images = this.options.mediaGalleryInitial;
                }
                this._super(images, context, isProductViewExist);
                return;
            }

            var productId = spConfig.productId;
            if (this.options.mtConfig.simpleProductId) {
                productId = this.options.mtConfig.simpleProductId;
            }

            galleryData = this.options.mtConfig.galleryData;

            //NOTE: associated product has no images
            if (!galleryData[productId].length) {
                productId = spConfig.productId;
            }

            //NOTE: there is no need to change gallery
            if (this.options.mtConfig.currentProductId == productId) {
                return;
            }

            tools = this.options.mtConfig.tools;

            //NOTE: stop tools
            for (var tool in tools) {
                if (tools[tool].undefined) continue;
                var id = tools[tool].idTemplate.replace('{tool}', tool).replace('{id}', this.options.mtConfig.currentProductId);
                if (document.getElementById(id)) {
                    window[tools[tool].objName].stop(id);
                }
            }

            //NOTE: stop MagiScroll on selectors
            var id = 'MagicToolboxSelectors'+this.options.mtConfig.currentProductId,
                selectorsEl = document.getElementById(id);
            if (!tools['MagicScroll'].undefined && selectorsEl && selectorsEl.className.match(/(?:\s|^)MagicScroll(?:\s|$)/)) {
                MagicScroll.stop(id);
            }

            //NOTE: replace gallery
            $('div.product.media div.MagicToolboxContainer').replaceWith(galleryData[productId]);

            //NOTE: start MagiScroll on selectors
            id = 'MagicToolboxSelectors'+productId;
            selectorsEl = document.getElementById(id);
            if (!tools['MagicScroll'].undefined && selectorsEl && selectorsEl.className.match(/(?:\s|^)MagicScroll(?:\s|$)/)) {
                MagicScroll.start(id);
            }

            //NOTE: initialize thumb switcher widget
            var container = $('div.product.media div.MagicToolboxContainer');
            if (container.length) {
                this.options.mtConfig.thumbSwitcherOptions.productId = productId;
                if (container.magicToolboxThumbSwitcher) {
                    container.magicToolboxThumbSwitcher(this.options.mtConfig.thumbSwitcherOptions);
                } else {
                    //NOTE: require thumb switcher widget
                    /*
                    require(["magicToolboxThumbSwitcher"], function ($) {
                        container.magicToolboxThumbSwitcher(this.options.mtConfig.thumbSwitcherOptions);
                    });
                    */
                }
            }

            //NOTE: update current product id
            this.options.mtConfig.currentProductId = productId;

            //NOTE: start tools
            for (var tool in tools) {
                if (tools[tool].undefined) continue;
                var id = tools[tool].idTemplate.replace('{tool}', tool).replace('{id}', this.options.mtConfig.currentProductId);
                if (document.getElementById(id)) {
                    window[tools[tool].objName].start(id);
                }
            }
        }
    });

    return $.magictoolbox.SwatchRenderer;
});
