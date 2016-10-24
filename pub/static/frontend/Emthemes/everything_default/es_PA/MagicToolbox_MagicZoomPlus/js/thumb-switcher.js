
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
            mainContainerId: 'mtImageContainer',
            magic360ContainerId: 'mt360Container',
            videoContainerId: 'mtVideoContainer',
            mainContainer: null,
            magic360Container: null,
            videoContainer: null,
            mainThumbRegExp: /(?:\s|^)(?:mz\-thumb|MagicThumb\-swap)(?:\s|$)/,
            magic360ThumbRegExp: /(?:\s|^)m360\-selector(?:\s|$)/,
            videoThumbRegExp: /(?:\s|^)video\-selector(?:\s|$)/,
            thumbs: [],
            needVideoPlayer: false,
            needVimeoJSFramework: false,
            playIfBase: 0,
            showRelated: 0,
            videoAutoRestart: 0,
            prevVideoId: null
        },

        /**
         * Gallery creation
         * @protected
         */
        _create: function () {

            var options = this.options,
                videoUrl = '',
                selectorsContainer = null,
                aTags = [];

            options.mainContainer = document.getElementById(options.mainContainerId);
            options.magic360Container = document.getElementById(options.magic360ContainerId);
            options.videoContainer = document.getElementById(options.videoContainerId);

            options.isMagicZoom = (options.tool == 'magiczoom' || options.tool == 'magiczoomplus');

            this.options.thumbs = [];
            selectorsContainer = this.element.find('.MagicToolboxSelectorsContainer');
            if (selectorsContainer.length) {
                aTags = Array.prototype.slice.call(
                    selectorsContainer.get(0).getElementsByTagName('a')
                );
            }
            for (var i = 0; i < aTags.length; i++) {
                if (aTags[i].getElementsByTagName('img').length) {
                    options.thumbs.push(aTags[i]);
                    if (aTags[i].className.match(options.videoThumbRegExp)) {
                        options.needVideoPlayer = true;
                        videoUrl = aTags[i].getAttribute('data-video');
                        if (videoUrl.match(/(?:www\.|player\.)?vimeo\.com/)) {
                            options.needVimeoJSFramework = true;
                        }
                    }
                } else {
                    //console.log('Wrong selector: ', aTags[i]);
                }
            }

            if (options.needVideoPlayer) {

                //NOTE: load vimeo js framework
                if (options.needVimeoJSFramework && typeof(window.$f) == 'undefined') {
                    var firstScriptTag = document.getElementsByTagName('script')[0],
                        newScriptTag = document.createElement('script');
                    newScriptTag.async = true;
                    newScriptTag.src = 'https://secure-a.vimeocdn.com/js/froogaloop2.min.js';
                    firstScriptTag.parentNode.insertBefore(newScriptTag, firstScriptTag);
                }

                //NOTE: load player loader
                if (!$.isFunction($.fn.productVideoLoader)) {
                    require(['jquery', 'loadPlayer'], $.proxy(function () {
                        if (this.options.videoContainer) {
                            //NOTE: init base video
                            var initVideo = $(this.options.videoContainer).find('.init-video');
                            if (initVideo.length) {
                                this._initVideo(initVideo.data('video'));
                            }
                        }
                    }, this));
                } else {
                    //NOTE: init base video
                    var initVideo = $(this.options.videoContainer).find('.init-video');
                    if (initVideo.length) {
                        this._initVideo(initVideo.data('video'));
                    }
                }
            }

            if (options.mainContainer && options.magic360Container && options.videoContainer && options.thumbs.length) {
                this._bind();
            }

            //NOTE: start MagicScroll on selectors
            var id = 'MagicToolboxSelectors'+options.productId,
                selectorsEl = document.getElementById(id);

            if ((typeof(window['MagicScroll']) != 'undefined') && selectorsEl && selectorsEl.className.match(/(?:\s|^)MagicScroll(?:\s|$)/)) {
                if (options.tool == 'magicthumb') {
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

            if (typeof(magicJS.Doc.je1) == 'undefined') {
                addMethod = 'jAddEvent';
            }

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

            var options = this.options,
                thumbs = options.thumbs,
                objThis = event.target || event.srcElement,
                toolMainId = 'MagicZoomPlusImage-product-'+options.productId,
                isMagic360Thumb,
                isVideoThumb,
                isMagic360Hidden,
                isVideoHidden;

            if (!options || !options.mainContainer) {
                return false;
            }

            if (options.isMagicZoom) {
                //NOTE: in order to magiczoom(plus) was not switching selector
                event.stopQueue && event.stopQueue();
            }

            if (objThis.tagName.toLowerCase() == 'img') {
                objThis = objThis.parentNode;
            }

            isMagic360Thumb = objThis.className.match(options.magic360ThumbRegExp);
            isVideoThumb = objThis.className.match(options.videoThumbRegExp);
            isMagic360Hidden = options.magic360Container.style.display == 'none';
            isVideoHidden = options.videoContainer.style.display == 'none';

            if (!isVideoThumb) {
                if ($(options.videoContainer).find('iframe').length) {
                    $(options.videoContainer).find('.product-video').productVideoLoader('stop');
                }
            }

            if (isMagic360Thumb && isMagic360Hidden) {
                //NOTE: the 360 container was hidden when clicking on the 360 thumbnail
                options.mainContainer.style.display = 'none';
                options.videoContainer.style.display = 'none';
                options.magic360Container.style.display = 'block';
            } else if (isVideoThumb) {
                //NOTE: clicking on the video thumbnail
                if (isVideoHidden) {
                    //NOTE: the video container was hidden
                    options.mainContainer.style.display = 'none';
                    options.magic360Container.style.display = 'none';
                    options.videoContainer.style.display = 'block';
                }
                this._initVideo($(objThis).data('video'));
            } else if (!(isMagic360Thumb || isVideoThumb)) {
                //NOTE: the main container was hidden when clicking on the main thumbnail
                options.videoContainer.style.display = 'none';
                options.magic360Container.style.display = 'none';
                options.mainContainer.style.display = 'block';
                if (options.isMagicZoom) {
                    //NOTE: hide image to skip magiczoom(plus) switching effect
                    if (!$mjs(objThis).jHasClass('mz-thumb-selected')) {
                        document.querySelector('#'+toolMainId+' .mz-figure > img').style.visibility = 'hidden';
                    }
                    //NOTE: switch image
                    MagicZoom.switchTo(toolMainId, objThis);
                }
            }

            if (options.isMagicZoom) {
                //NOTE: to highlight magic360 selector when switching thumbnails
                for (var i = 0; i < thumbs.length; i++) {
                    $mjs(thumbs[i]).jRemoveClass('active-selector');
                }
                $mjs(objThis).jAddClass('active-selector');
            }

            return false;
        },

        /**
         * Init video
         * @param videoUrl
         * @private
         */
        _initVideo: function(videoUrl) {
            var videoContainer = this.options.videoContainer,
                aTag = document.createElement('a'),
                videoCode = null,
                videoType = 'youtube';

            aTag.href = videoUrl;

            if (aTag.host.match(/youtube\.com|youtu\.be/)) {
                var regExp1 = /\bv=([^&]+)(?:&|$)/,
                    regExp2 = /^\/(?:embed\/|v\/)?([^\/\?]+)(?:\/|\?|$)/;
                if (aTag.search.match(regExp1)) {
                    videoCode = aTag.search.match(regExp1)[1];
                } else if (aTag.pathname.match(regExp2)) {
                    videoCode = aTag.pathname.match(regExp2)[1];
                }
            } else if (aTag.host.match(/(?:www\.|player\.)?vimeo\.com/)) {
                var regExp3 = /\/(?:channels\/[^\/]+\/|groups\/[^\/]+\/videos\/|album\/[^\/]+\/video\/|video\/|)(\d+)(?:\/|\?|$)/;
                videoType = 'vimeo';
                if (aTag.pathname.match(regExp3)) {
                    videoCode = aTag.pathname.match(regExp3)[1];
                }
            }

            if (videoCode && (this.options.prevVideoId != videoType+'-'+videoCode)) {
                $(videoContainer).find('.product-video').remove();
                $(videoContainer).append(
                    '<div class="product-video" ' +
                    'data-related="' + this.options.showRelated + '" ' +
                    'data-loop="' + this.options.videoAutoRestart + '" ' +
                    'data-type="' + videoType + '" ' +
                    'data-code="' + videoCode + '" ' +
                    'data-width="100%" data-height="100%"></div>'
                );
                $(videoContainer).find('.product-video').productVideoLoader();
                this.options.prevVideoId = videoType+'-'+videoCode;
            }
        },

        /**
         * Get options
         * @public
         */
        getOptions: function () {
            return {
                playIfBase: this.options.playIfBase,
                showRelated: this.options.showRelated,
                videoAutoRestart: this.options.videoAutoRestart,
                tool: this.options.tool,
                switchMethod: this.options.switchMethod,
                productId: this.options.productId
            };
        }
    });

    return $.mage.magicToolboxThumbSwitcher;
});
