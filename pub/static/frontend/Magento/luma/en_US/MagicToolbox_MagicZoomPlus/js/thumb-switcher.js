
define([
    'jquery',
], function ($) {
    'use strict';

    /**
     * Thumb switcher widget
     */
    $.widget('mage.magicToolboxThumbSwitcher', {

        options: {
            tool: null,
            productId: '',
            switchMethod: 'click',
            isMagicZoom: false,
            mainContainerId: 'mainImageContainer',
            mainContainer: null,
            magic360ContainerId: 'magic360Container',
            magic360Container: null,
            thumbs: []
        },

        /**
         * Gallery creation
         * @protected
         */
        _create: function () {

            this.options.mainContainer = document.getElementById(this.options.mainContainerId);
            this.options.magic360Container = document.getElementById(this.options.magic360ContainerId);

            if (this.options.mainContainer && this.options.magic360Container) {
                this.options.isMagicZoom = (this.options.tool == 'magiczoom' || this.options.tool == 'magiczoomplus');
                this.options.thumbs = Array.prototype.slice.call(
                    this.element.find('.MagicToolboxSelectorsContainer').get(0).getElementsByTagName('a')
                );
                this._bind();
            }

            //NOTE: start MagicScroll on selectors
            var id = 'MagicToolboxSelectors'+this.options.productId,
                selectorsEl = document.getElementById(id);

            if ((typeof(window['MagicScroll']) != 'undefined') && selectorsEl && selectorsEl.className.match(/(?:\s|^)MagicScroll(?:\s|$)/)) {
                if (this.options.tool == 'magicthumb') {
                    window.checkForThumbIsReadyIntervalID = setInterval(function() {
                        if (typeof(MagicThumb.thumbs) != 'undefined' && MagicThumb.thumbs.length) {
                            var magicThumbIsReady = true;
                            for (var i = 0; i <  MagicThumb.thumbs.length; i++) {
                                if (!MagicThumb.thumbs[i].ready) {
                                    magicThumbIsReady = false;
                                    break;
                                }
                            }
                            if (magicThumbIsReady) {
                                MagicScroll.start(id);
                                clearInterval(window.checkForThumbIsReadyIntervalID);
                                window.checkForThumbIsReadyIntervalID = null;
                            }
                        }
                    }, 100);
                } else {
                    MagicScroll.start(id);
                }
            }
        },

        /**
         * Bind handler to elements
         * @protected
         */
        _bind: function () {

            var tool = this.options.tool,
                switchMethod = this.options.switchMethod,
                thumbs = this.options.thumbs,
                isMagicZoom = this.options.isMagicZoom,
                addMethod = 'je1';

            if (typeof(magicJS.Doc.je1) == 'undefined') addMethod = 'jAddEvent';

            if (isMagicZoom) {
                //NOTE: only if MagicThumb is not present
                if (addMethod != 'je1') {
                    switchMethod = (switchMethod == 'click' ? 'btnclick' : switchMethod);
                }
            }

            var switchThumbFn = $.proxy(this._switchThumb, this);
            for (var i = 0; i < thumbs.length; i++) {
                if (isMagicZoom) {
                    //NOTE: if MagicThumb is present
                    if (addMethod == 'je1') {
                        $mjs(thumbs[i])[addMethod](switchMethod, switchThumbFn);
                        $mjs(thumbs[i])[addMethod]('touchstart', switchThumbFn);
                    } else {
                        $mjs(thumbs[i])[addMethod](switchMethod+' tap', switchThumbFn, 1);
                    }
                } else if (tool == 'magicthumb') {
                    $mjs(thumbs[i])[addMethod](switchMethod, switchThumbFn);
                    $mjs(thumbs[i])[addMethod]('touchstart', switchThumbFn);
                }
            }
        },

        /**
         * Switch thumb
         * @param {jQuery.Event} event
         * @private
         */
        _switchThumb: function(event) {

            if (!this.options || !this.options.mainContainer) {
                return false;
            }

            var options = this.options,
                magic360ThumbRegExp =  new RegExp('(?:\\s|^)m360\-selector(?:\\s|$)'),
                hiddenThumbRegExp = /(?:\s|^)hidden\-selector(?:\s|$)/,
                thumbs = options.thumbs,
                objThis = event.target || event.srcElement,
                toolMainId = 'MagicZoomPlusImage'+options.productId,
                isMagic360Thumb = false,
                isMagic360Visible = false;

            if (options.isMagicZoom) {
                //NOTE: in order to magiczoom(plus) was not switching selector
                event.stopQueue && event.stopQueue();
            }

            if (objThis.tagName.toLowerCase() == 'img') {
                objThis = objThis.parentNode;
            }

            isMagic360Thumb = objThis.className.match(magic360ThumbRegExp);
            isMagic360Visible = options.magic360Container.style.display != 'none';

            if (isMagic360Thumb && !isMagic360Visible) {
                options.mainContainer.style.display = 'none';
                options.magic360Container.style.display = 'block';
            } else if (isMagic360Visible && (!isMagic360Thumb || thumbs.length == 1 ||
                thumbs[0].className.match(magic360ThumbRegExp) &&
                thumbs[1].className.match(hiddenThumbRegExp)))
            {
                options.magic360Container.style.display = 'none';
                options.mainContainer.style.display = 'block';
                if (options.isMagicZoom) {
                    //NOTE: hide image to skip magiczoom(plus) switching effect
                    if (!$mjs(objThis).jHasClass('mz-thumb-selected')) {
                        document.querySelector('#'+toolMainId+' .mz-figure > img').style.visibility = 'hidden';
                    }
                }
            }
            if (options.isMagicZoom) {
                //NOTE: switch image
                MagicZoom.switchTo(toolMainId, objThis);
                //NOTE: to highlight magic360 selector when switching thumbnails
                for (var i = 0; i < thumbs.length; i++) {
                    $mjs(thumbs[i]).jRemoveClass('active-selector');
                }
                $mjs(objThis).jAddClass('active-selector');
            }

            return false;
        },

        /**
         * Get options
         * @public
         */
        getOptions: function () {
            return $.extend({}, this.options);
        }
    });

    return $.mage.magicToolboxThumbSwitcher;
});
