(function () {
	"use strict";

	var slideMenu = $('.side-menu');

	// Toggle Sidebar
	$(document).on('click','[data-toggle="sidebar"]',function(event) {
		event.preventDefault();
		$('.app').toggleClass('sidenav-toggled');
		$('.side-menu > .slide > .slide-menu').each(function () {
			$(this).removeData('slideToken').off('transitionend.slideMenu');
			$(this).css({ maxHeight: '', transition: '', paddingTop: '', paddingBottom: '' });
		});
	});

	$(".app-sidebar").hover(function() {
		if ($('body').hasClass('sidenav-toggled')) {
			$('body').addClass('sidenav-toggled-open');
		}
	}, function() {
		if ($('body').hasClass('sidenav-toggled')) {
			$('body').removeClass('sidenav-toggled-open');
		}
	});



	// Activate sidebar slide toggle (height is animated in CSS via max-height)
	var slideDuration = 300;
	var slideInstant = false;
	var reduceSlideMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function isFlyoutSidebar() {
		return $('.app').hasClass('sidenav-toggled') && !$('.app').hasClass('sidenav-toggled-open') && window.innerWidth >= 768;
	}

	function clearSlideInline($menu) {
		$menu.css({ maxHeight: '', transition: '', paddingTop: '', paddingBottom: '' });
	}

	function refreshSidebarScroll() {
		var $pane = $('.main-sidemenu');
		if ($pane.length && $pane.data('mCS')) {
			$pane.mCustomScrollbar('update');
		}
	}

	function finishSlide($menu, token) {
		if ($menu.data('slideToken') !== token) {
			return;
		}
		$menu.removeData('slideToken');
		$menu.off('transitionend.slideMenu');
		clearSlideInline($menu);
		refreshSidebarScroll();
	}

	function bindSlideEnd($menu, token) {
		$menu.off('transitionend.slideMenu').on('transitionend.slideMenu', function (e) {
			if (e.target !== $menu.get(0) || !e.originalEvent || e.originalEvent.propertyName !== 'max-height') {
				return;
			}
			finishSlide($menu, token);
		});
		window.setTimeout(function () {
			finishSlide($menu, token);
		}, slideDuration + 40);
	}

	function openSlide($item) {
		var $menu = $item.children('ul.slide-menu').first();
		if (!$menu.length || slideInstant || reduceSlideMotion || isFlyoutSidebar()) {
			clearSlideInline($menu);
			$item.addClass('is-expanded');
			return;
		}

		var token = Date.now() + Math.random();
		var menu = $menu.get(0);
		$menu.data('slideToken', token);
		$menu.css({ transition: 'none', maxHeight: '0px', paddingTop: '0px', paddingBottom: '0px' });
		$item.addClass('is-expanded');
		$menu.css({ maxHeight: 'none', paddingTop: '10px', paddingBottom: '10px' });
		var target = menu.scrollHeight;
		$menu.css({ maxHeight: '0px', paddingTop: '0px', paddingBottom: '0px' });
		menu.offsetHeight;
		$menu.css('transition', '');
		menu.offsetHeight;
		$menu.css({ maxHeight: target + 'px', paddingTop: '', paddingBottom: '' });
		bindSlideEnd($menu, token);
	}

	function closeSlide($item) {
		var $menu = $item.children('ul.slide-menu').first();
		if (!$menu.length || !$item.hasClass('is-expanded')) {
			$item.removeClass('is-expanded');
			return;
		}
		if (slideInstant || reduceSlideMotion || isFlyoutSidebar()) {
			clearSlideInline($menu);
			$item.removeClass('is-expanded');
			return;
		}

		var token = Date.now() + Math.random();
		var menu = $menu.get(0);
		$menu.data('slideToken', token);
		$menu.css('transition', 'none');
		$menu.css('maxHeight', menu.scrollHeight + 'px');
		menu.offsetHeight;
		$menu.css('transition', '');
		menu.offsetHeight;
		$item.removeClass('is-expanded');
		$menu.css('maxHeight', '0px');
		bindSlideEnd($menu, token);
	}

	$(document).off('click.sideSlide', "[data-toggle='slide']").on('click.sideSlide', "[data-toggle='slide']", function (event) {
		event.preventDefault();
		var $parent = $(this).parent();
		if (!$parent.children('ul.slide-menu').length) {
			return;
		}
		var expand = !$parent.hasClass('is-expanded');
		$parent.siblings('.slide.is-expanded').each(function () {
			closeSlide($(this));
		});
		if (expand) {
			openSlide($parent);
		} else {
			closeSlide($parent);
		}
	});
	
	$("[data-toggle='sub-slide']").click(function(event) {
		event.preventDefault();
		if(!$(this).parent().hasClass('is-expanded')) {
			slideMenu.find("[data-toggle='sub-slide']").parent().removeClass('is-expanded');
		}
		$(this).parent().toggleClass('is-expanded');
		$('.slide.active').addClass('is-expanded');
	});
	
	// Set initial active toggle
	$("[data-toggle='slide.'].is-expanded").parent().toggleClass('is-expanded');
	$("[data-toggle='sub-slide.'].is-expanded").parent().toggleClass('is-expanded');
	

	//Activate bootstrip tooltips
	$("[data-toggle='tooltip']").tooltip();
	
	
	// ______________Active Class
	$(".app-sidebar a").each(function() {
	  var pageUrl = window.location.href.split(/[?#]/)[0];
		if (this.href == pageUrl) { 
			$(this).addClass("active");
			$(this).parent().addClass("active"); // add active to li of the current link
			$(this).parent().parent().prev().addClass("active"); // add active class to an anchor
			$(this).parent().parent().parent().parent().parent().addClass("active"); 
			slideInstant = true;
			$(this).parent().parent().prev().trigger('click'); // open the active group without animating on load
			slideInstant = false;
		}
	});
	
	var toggleSidebar = function() {
		var w = $(window);
		if(w.outerWidth() <= 1024) {
			$("body").addClass("sidebar-gone");
			$(document).off("click", "body").on("click", "body", function(e) {
				if($(e.target).hasClass('sidebar-show') || $(e.target).hasClass('search-show')) {
					$("body").removeClass("sidebar-show");
					$("body").addClass("sidebar-gone");
					$("body").removeClass("search-show");
				}
			});
		}else{
			$("body").removeClass("sidebar-gone");
		}
	}
	toggleSidebar();
	$(window).resize(toggleSidebar);
	
	
	//mCustomScrollbar
	$(".main-sidemenu").mCustomScrollbar({
		theme:"minimal",
		autoHideScrollbar: true,
		scrollbarPosition: "outside"
	});

})();