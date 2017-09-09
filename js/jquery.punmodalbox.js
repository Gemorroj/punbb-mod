/**
 * @author melnikov.vyacheslav
 */

(function($) {
    $.modalBox = {
        settings: {
            title       : "Modal Box",
            initWidth   : 420,
            initHeight  : 200,
            overlaySpeed: 200,
            expandSpeed : 200,
            oOpacity    : 0.5,
            cssFile     : '<link rel="stylesheet" type="text/css" href="style/imports/jquery.punmodal.css" media="screen" />',
            html        : '<div id="modalbox-overlay"></div><div id="modalbox"><div id="modalbox-wrapper"><div id="modalbox-header"><span id="modalbox-close"><a class="modalbox-close" href="#"> [ X ] </a></span><h2></h2></div><div id="modalbox-loading"><img src="style/img/busy.gif" alt="" /></div><div id="modalbox-container"></div></div></div> '
        },
        init: function () {
            if ($.modalBox.settings.inited) {
                return true;
            } else {
                $.modalBox.settings.inited = true;
            }
            $('head').append($.modalBox.settings.cssFile);
            $(document.body).append($.modalBox.settings.html);
            $('#modalbox,#modalbox-overlay').hide();
            $("#modalbox-close").find("a.modalbox-close").click($.modalBox.hideBox);
            $(window).resize(function () {
                $.modalBox.resizeBoxes();
            });
            return true;
        },

        resizeBoxes: function () {
            // Get the page size, Get page scroll
            var pageSize = $.modalBox.getPageSize(), pageScroll = $.modalBox.getPageScroll();

            // Style overlay and show it
            $('#modalbox-overlay').css({
                'width':  pageSize.pageWidth + 'px',
                'height': pageSize.pageHeight + 'px'
            });
   
            $('#modalbox').css({
                'top':    0,
                'left':   pageScroll.xScroll + 'px'
            });    
        },

        getWindowSize: function () {
            var wSize = {'width': 0, 'height': 0};

            if (self.innerHeight) {    // all except Explorer
                if (document.documentElement.clientWidth) {
                    wSize.width = document.documentElement.clientWidth; 
                } else {
                    wSize.width = self.innerWidth;
                }
                wSize.height = self.innerHeight;
            } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
                wSize.width = document.documentElement.clientWidth;
                wSize.height = document.documentElement.clientHeight;
            } else if (document.body) { // other Explorers
                wSize.width = document.body.clientWidth;
                wSize.height = document.body.clientHeight;
            }

            return wSize;
        },

        getPageSize: function () {
            var xScroll = 0, yScroll = 0, pageHeight = 0, pageWidth = 0, windowWidth = 0, windowHeight = 0;

            if (window.innerHeight && window.scrollMaxY) {
                xScroll = window.innerWidth + window.scrollMaxX;
                yScroll = window.innerHeight + window.scrollMaxY;
            } else if (document.body.scrollHeight > document.body.offsetHeight) { // all but Explorer Mac
                xScroll = document.body.scrollWidth;
                yScroll = document.body.scrollHeight;
            } else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
                xScroll = document.body.offsetWidth;
                yScroll = document.body.offsetHeight;
            }

            if (self.innerHeight) {    // all except Explorer
                if (document.documentElement.clientWidth){
                    windowWidth = document.documentElement.clientWidth; 
                } else {
                    windowWidth = self.innerWidth;
                }
                windowHeight = self.innerHeight;
            } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
                windowWidth = document.documentElement.clientWidth;
                windowHeight = document.documentElement.clientHeight;
            } else if (document.body) { // other Explorers
                windowWidth = document.body.clientWidth;
                windowHeight = document.body.clientHeight;
            }    

            // for small pages with total height less then height of the viewport
            if (yScroll < windowHeight) {
                pageHeight = windowHeight;
            } else { 
                pageHeight = yScroll;
            }

            // for small pages with total width less then width of the viewport
            if (xScroll < windowWidth) {
                pageWidth = xScroll;
            } else {
                pageWidth = windowWidth;
            }

            return {'pageWidth': pageWidth, 'pageHeight': pageHeight, 'windowWidth': windowWidth, 'windowHeight': windowHeight};
        },

        getPageScroll: function () {
            var xScroll = 0, yScroll = 0;
            if (self.pageYOffset) {
                yScroll = self.pageYOffset;
                xScroll = self.pageXOffset;
            } else if (document.documentElement && document.documentElement.scrollTop) {     // Explorer 6 Strict
                yScroll = document.documentElement.scrollTop;
                xScroll = document.documentElement.scrollLeft;
            } else if (document.body) {// all other Explorers
                yScroll = document.body.scrollTop;
                xScroll = document.body.scrollLeft;
            }

            return {'xScroll': xScroll,'yScroll': yScroll};
        },

        generateId: function (prefix) {
            prefix = prefix || 'jquery-gen';
            var newId = prefix + '_' + (Math.round(100000 * Math.random()));
            if ($('#' + newId).length > 0) {
                return this.generateId(prefix);
            } else {
                return newId;
            }
        },

        showBox: function (def) {
            if (!def) {
                def = $.modalBox.settings;
            }
            $.modalBox.resizeBoxes();
            $("#modalbox-header").find("h2").text(def.title);
            $('#modalbox-overlay').css({
                opacity: $.modalBox.settings.oOpacity
            }).fadeIn($.modalBox.settings.overlaySpeed, function () {
                $('#modalbox-wrapper').css({'height': $.modalBox.settings.initHeight + 'px', 'width': $.modalBox.settings.initWidth + 'px'});
                $('#modalbox').slideDown(function () {
                    if (def.ajax) {
                        $.ajax({
                            type: "GET",
                            url: def.ajax,
                            dataType: "html",
                            cache: true,
                            success: function (data) {
                                var $container = $('#modalbox-container');
                                var $wrapper = $('#modalbox-wrapper');

                                $('#modalbox-loading').hide();
                                $container.hide().html(data);
                                $.modalBox.resizeBoxes();

                                var cHeight = $container.height();
                                var cWidth = $container.width();
                                var wSize = $.modalBox.getWindowSize();


                                var s = Math.floor(Math.sqrt(cHeight * cWidth / 12));
                                var rWidth = 4 * s;
                                var rHeight = 3 * s;

                                if (cHeight > wSize.height) {
                                    rHeight = wSize.height - 8;
                                    $wrapper.css({'overflow': 'auto'});
                                }

                                $wrapper.animate(
                                    {'height': rHeight + 'px', 'width': rWidth + 'px'},
                                    $.modalBox.settings.expandSpeed,
                                    function () {
                                        $container.show();
                                        $wrapper.css({'height': 'auto'});
                                    }
                                );

                                if ($.isFunction(def.onLoad)) {
                                    def.onLoad();
                                }
                            }
                        });
                    }
                });
            });
        },

        hideBox: function (def) {
            $('#modalbox-header').css('width', 'auto');
            $('#modalbox-wrapper').animate({
                    'height': $.modalBox.settings.initHeight + 'px',
                    'width': $.modalBox.settings.initWidth + 'px'
                },
                $.modalBox.settings.expandSpeed,
                function () {
                    $('#modalbox-loading').show();
                    $('#modalbox-container').hide();
                    $('#modalbox').slideUp(function () {
                        $('#modalbox-overlay').fadeOut($.modalBox.settings.overlaySpeed);
                        if ($.isFunction(def)) {
                            def();
                        }    
                    });
                }
            );
        }
    };

    $.fn.modalBox = function (options) {
        var def = $.extend({
            width           :    600,
            height          :    480,
            boxTimer        :    300,
            dataType        :    'html',
            position        :    'top',
            ajax            :    null,
            onLoad          :    null,
            onClose         :    null
        }, options);

        if (def.position !== 'top' && def.position !== 'center') {
            def.position = 'top';
        }

        $.modalBox.init();
        return this.click(function () {
            $.modalBox.showBox(def);
        });
    };
})($);
