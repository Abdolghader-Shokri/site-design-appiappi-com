/**
 * Mobile nav toggle + sticky header scroll shadow.
 * No framework/jQuery dependency — kept deliberately small.
 */
( function () {
	'use strict';

	var toggle = document.querySelector( '.mobile-nav-toggle' );
	var nav = document.getElementById( 'mobile-nav' );
	var header = document.querySelector( '.site-header' );

	if ( toggle && nav ) {
		toggle.addEventListener( 'click', function () {
			var isOpen = nav.classList.toggle( 'is-open' );
			document.body.classList.toggle( 'mobile-nav-is-open', isOpen );
			toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );

		nav.querySelectorAll( 'a' ).forEach( function ( link ) {
			link.addEventListener( 'click', function () {
				nav.classList.remove( 'is-open' );
				document.body.classList.remove( 'mobile-nav-is-open' );
				toggle.setAttribute( 'aria-expanded', 'false' );
			} );
		} );
	}

	if ( header ) {
		var onScroll = function () {
			header.classList.toggle( 'is-scrolled', window.scrollY > 4 );
		};
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}
} )();

/**
 * Hero slider: only does anything when there are 2+ slides (a single
 * slide renders no dots and this exits immediately). Auto-advances every
 * 7s, pauses on hover/focus, and skips auto-advance entirely for
 * prefers-reduced-motion.
 */
( function () {
	'use strict';

	var slides = document.querySelectorAll( '.hero-slide' );
	if ( slides.length < 2 ) {
		return;
	}

	var images = document.querySelectorAll( '[data-hero-slide-image]' );
	var dots = document.querySelectorAll( '[data-hero-dot]' );
	var hero = document.querySelector( '.hero' );
	var index = 0;
	var timer = null;
	var prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function show( next ) {
		index = next;
		slides.forEach( function ( el, i ) {
			el.classList.toggle( 'is-active', i === index );
		} );
		images.forEach( function ( el, i ) {
			el.classList.toggle( 'is-active', i === index );
		} );
		dots.forEach( function ( el, i ) {
			el.classList.toggle( 'is-active', i === index );
		} );
	}

	function advance() {
		show( ( index + 1 ) % slides.length );
	}

	function start() {
		if ( prefersReducedMotion ) {
			return;
		}
		stop();
		timer = window.setInterval( advance, 7000 );
	}

	function stop() {
		if ( timer ) {
			window.clearInterval( timer );
			timer = null;
		}
	}

	dots.forEach( function ( dot, i ) {
		dot.addEventListener( 'click', function () {
			show( i );
			start();
		} );
	} );

	if ( hero ) {
		hero.addEventListener( 'mouseenter', stop );
		hero.addEventListener( 'mouseleave', start );
		hero.addEventListener( 'focusin', stop );
		hero.addEventListener( 'focusout', start );
	}

	start();
} )();

/**
 * FAQ accordion toggle. Multiple items can be open at once (no
 * single-open enforcement) — simplest accessible pattern, no surprises
 * when a user expects two answers open side by side.
 */
( function () {
	'use strict';

	document.querySelectorAll( '.faq-item__question' ).forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			var item = button.closest( '.faq-item' );
			var isOpen = item.classList.toggle( 'is-open' );
			button.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );
	} );
} )();

/**
 * Live search for the template showcase (search box only — category,
 * price range and sort are all real page reloads via query string, see
 * appiappi_showcase_archive_query() in the appiappi-template-showcase
 * plugin, so they work correctly across pagination). Pure client-side:
 * everything on this page is already in the DOM, so no AJAX round trip
 * is needed. No-ops entirely if the grid isn't on the page.
 */
( function () {
	'use strict';

	var grid = document.getElementById( 'templates-grid' );
	if ( ! grid ) {
		return;
	}

	var searchInput = document.getElementById( 'templates-search' );
	var countEl = document.getElementById( 'templates-count' );
	var emptyEl = document.getElementById( 'templates-empty' );
	var cards = grid.querySelectorAll( '.template-card' );

	function apply() {
		var query = searchInput ? searchInput.value.trim().toLowerCase() : '';

		var visibleCount = 0;
		cards.forEach( function ( card ) {
			var visible = ! query || ( card.dataset.search || '' ).indexOf( query ) !== -1;
			card.hidden = ! visible;
			if ( visible ) {
				visibleCount++;
			}
		} );

		if ( countEl ) {
			var template = 1 === visibleCount ? countEl.dataset.singular : countEl.dataset.plural;
			countEl.textContent = template.replace( '%d', visibleCount );
		}
		if ( emptyEl ) {
			emptyEl.hidden = visibleCount !== 0;
		}
	}

	if ( searchInput ) {
		searchInput.addEventListener( 'input', apply );
	}
} )();

/**
 * Per-card image carousel for Website Designs with more than one image
 * (Featured Image + gallery — see appiappi_showcase_map_post()'s
 * `images`). Only cards with a data-carousel-interval on their
 * .template-card__media (set only when there's more than one image)
 * get prev/next arrows and an auto-advance timer; every other card is
 * untouched. Pauses auto-advance while the pointer is over the card so
 * a visitor reading it doesn't have the image change under them, and
 * is skipped entirely under prefers-reduced-motion (same convention as
 * the hero slider) — arrows still work either way.
 */
( function () {
	'use strict';

	var medias = document.querySelectorAll( '.template-card__media[data-carousel-interval]' );
	if ( ! medias.length ) {
		return;
	}

	var prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	medias.forEach( function ( media ) {
		var images = media.querySelectorAll( '.template-card__image' );
		if ( images.length < 2 ) {
			return;
		}

		var interval = parseInt( media.dataset.carouselInterval, 10 ) || 3000;
		var current = 0;
		var timer = null;

		function show( index ) {
			current = ( index + images.length ) % images.length;
			images.forEach( function ( img, i ) {
				img.classList.toggle( 'is-active', i === current );
			} );
		}

		function start() {
			if ( prefersReducedMotion ) {
				return;
			}
			stop();
			timer = window.setInterval( function () { show( current + 1 ); }, interval );
		}

		function stop() {
			if ( timer ) {
				window.clearInterval( timer );
				timer = null;
			}
		}

		var prevBtn = media.querySelector( '[data-carousel-prev]' );
		var nextBtn = media.querySelector( '[data-carousel-next]' );
		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				show( current - 1 );
				start();
			} );
		}
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				show( current + 1 );
				start();
			} );
		}

		media.addEventListener( 'mouseenter', stop );
		media.addEventListener( 'mouseleave', start );

		start();
	} );
} )();
