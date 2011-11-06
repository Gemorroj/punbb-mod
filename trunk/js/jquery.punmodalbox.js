/**
 * @author melnikov.vyacheslav
 */


(function($) {

	var	mb	=	'#modalbox',
		hdr	=	mb+'-header',
		cnt	=	mb+'-container',
		ovr	=	mb+'-overlay'
		wrp	=	mb+'-wrapper'
		ld	=	mb+'-loading';

	$.modalBox  = {
		settings : {
			title       : "Modal Box",
			initWidth   : 420,
		    initHeight  : 200,
			overlaySpeed: 200,
			expandSpeed	: 200,
			oOpacity	: 0.5,			
			spinner		: 'style/img/spinner.gif',
			cssfile		: '<link rel="stylesheet" type="text/css" href="style/imports/jquery.punmodal.css" media="screen" />',
			cssiefix	: '<!--[if lte IE 6]><style type="text/css">#modalbox {position: absolute;}</style><![endif]-->',
			html 		: '<div id="modalbox-overlay"></div><div id="modalbox"><div id="modalbox-wrapper"><div id="modalbox-header"><span id="modalbox-close"><a class="modalbox-close" href="#"> [ X ] </a></span><h2></h2></div><div id="modalbox-loading"><img src="style/img/spinner.gif" alt="" /></div><div id="modalbox-container"></div></div></div> '
		},
		init : function () {
			if ($.modalBox.settings.inited) {
				return true
			} else {
				$.modalBox.settings.inited = true
			}
			$('head').append($.modalBox.settings.cssfile);
			$('head').append($.modalBox.settings.cssiefix);
			$('body').append($.modalBox.settings.html);
			$('#modalbox,#modalbox-overlay').hide();
			$("#modalbox-close a.modalbox-close").click(jQuery.modalBox.hideBox);
			$(window).resize(function () {
                $.modalBox.resizeBoxes();
            });
			return true;
		},

		fixIE: function () {
			var wHeight = $(window).height() + 'px', wWidth = $(window).width() + 'px';
			// add an iframe to prevent select options from bleeding through
			$.modalBox.settings.iframe = $('<iframe src="javascript:false;">')
				.css($.extend($.modalBox.settings.iframeCss, {
					opacity: 0, 
					position: 'absolute',
					height: wHeight,
					width: wWidth,
					zIndex: 80,
					width: '100%',
					top: 0,
					left: 0
				}))
				.hide()
				.appendTo('body');
		},

		resizeBoxes : function (def) {
			// Get the page size, Get page scroll
			var pageSize = $.modalBox.getPageSize(), pageScroll = $.modalBox.getPageScroll();

			// Style overlay and show it
			$('#modalbox-overlay').css({
				width:		pageSize.pageWidth,
				height:		pageSize.pageHeight
			});
   
			$('#modalbox').css({
				top:	0,
				left:	pageScroll.xScroll
			});	
		},

        getWindowSize : function () {
            wSize = {};
			if (self.innerHeight) {	// all except Explorer
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
            return wSize
        },

		getPageSize : function(){
			var xScroll, yScroll;

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

			var windowWidth, windowHeight;

			if (self.innerHeight) {	// all except Explorer
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

			return {'pageWidth':pageWidth,'pageHeight':pageHeight,'windowWidth':windowWidth,'windowHeight':windowHeight};
		},

		getPageScroll : function () {
			var xScroll, yScroll;
			if (self.pageYOffset) {
				yScroll = self.pageYOffset;
				xScroll = self.pageXOffset;
			} else if (document.documentElement && document.documentElement.scrollTop) {	 // Explorer 6 Strict
				yScroll = document.documentElement.scrollTop;
				xScroll = document.documentElement.scrollLeft;
			} else if (document.body) {// all other Explorers
				yScroll = document.body.scrollTop;
				xScroll = document.body.scrollLeft;	
			}

			return {'xScroll':xScroll,'yScroll':yScroll};
		},

		generateId : function (prefix) {
			prefix = prefix || 'jquery-gen';
			var newId = prefix + '_' + (Math.round(100000 * Math.random()));
			if ($('#' + newId)[0] !== undefined) {
				return gridView.generateId(prefix);
			} else {
				return newId;
			}
		},

		showBox : function (def) {
			if (!def) {def=$.modalBox.settings;}
			$.modalBox.resizeBoxes(def);
			$("#modalbox-header h2").text(def.title);
			$('#modalbox-overlay').css({
				opacity: $.modalBox.settings.oOpacity
			}).fadeIn($.modalBox.settings.overlaySpeed, function () {
				$('#modalbox-wrapper').css({'height':$.modalBox.settings.initHeight,'width':$.modalBox.settings.initWidth});
				$('#modalbox').slideDown(function () {
					if (def.ajax) {
						$.ajax({
							type: "GET",
							url: def.ajax,
							dataType: "html",
							cache: true,
							success: function (data) {
								$('#modalbox-loading').hide();
								$('#modalbox-container').hide().html(data);
								$.modalBox.resizeBoxes(def);

                                cHeight = $('#modalbox-container').height();
                                cWidth = $('#modalbox-container').width();
                                wSize = $.modalBox.getWindowSize();                             


								s = Math.floor(Math.sqrt(cHeight*cWidth / 12));
								rWidth = 4 * s;
								rHeight = 3 * s;

 								if (cHeight > wSize.height) {
									rHeight = wSize.height - 8;
									$('#modalbox-wrapper').css({overflow:'auto'});
								}

								$('#modalbox-wrapper').animate(
                                    {height: rHeight, width: rWidth},
									$.modalBox.settings.expandSpeed,
									function () {
										$('#modalbox-container').show();
										$('#modalbox-wrapper').css({height:'auto'});
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

		hideBox : function (def) {
			$('#modalbox-header').css({width:'auto'});
			$('#modalbox-wrapper').animate({
				height	:	$.modalBox.settings.initHeight,
				width	:	$.modalBox.settings.initWidth},
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
			width			:	600,
			height			:	480,
			boxTimer		:	300,
			dataType		:	"html",
			position		:	'top',
			ajax			:	null,
			onLoad			:	null,
			onClose			:	null
		}, options);

		if (def.position !== 'top' && def.position !== 'center') {
			def.position = 'top';
		}

		$.modalBox.init();
		return this.click(function () {
            $.modalBox.showBox(def);
        });
	};
})(jQuery);