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

/**
 * Animated geometric network overlay for page-title header
 * backgrounds (Customizer → Page Header Backgrounds → per-page
 * "Animated Geometric Overlay"). Only pages where that toggle is on
 * actually have a .page-header__network canvas in the DOM
 * (appiappi_page_header_network_canvas() in inc/template-tags.php),
 * so this no-ops everywhere else. Nodes drift slowly and connect
 * with lines to nearby nodes; both cycle through a shifting
 * multi-hue palette so it never reads as a static, single-colour
 * grid — deliberately colourful, not just white. Draws one static
 * frame (no loop) under prefers-reduced-motion, and pauses while
 * the tab is hidden so it doesn't burn CPU in the background.
 *
 * Speed, density and the connection-line "glow" style are shared by
 * every page (Customizer → Page Header Backgrounds, global controls,
 * added 2026-09-07) rather than set per page — localized once as
 * `appiappiPageHeaderNetwork` (inc/enqueue.php).
 */
( function () {
	'use strict';

	var canvases = document.querySelectorAll( '.page-header__network' );
	if ( ! canvases.length ) {
		return;
	}

	var networkCfg = window.appiappiPageHeaderNetwork || {};
	var speedMultiplier = parseFloat( networkCfg.speed ) || 1;
	var densityMultiplier = parseFloat( networkCfg.density ) || 1;
	var glowLines = !! networkCfg.glowLines;

	var prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	canvases.forEach( function ( canvas ) {
		var header = canvas.closest( '.page-header' );
		if ( ! header ) {
			return;
		}

		var ctx = canvas.getContext( '2d' );
		var dpr = Math.min( window.devicePixelRatio || 1, 2 );
		var width = 0;
		var height = 0;
		var nodes = [];
		var raf = null;

		function buildNodes() {
			var baseCount = Math.round( ( width * height ) / 16000 * densityMultiplier );
			var count = Math.max( 8, Math.min( 140, baseCount ) );
			nodes = [];
			for ( var i = 0; i < count; i++ ) {
				nodes.push( {
					x: Math.random() * width,
					y: Math.random() * height,
					vx: ( Math.random() - 0.5 ) * 0.18 * speedMultiplier,
					vy: ( Math.random() - 0.5 ) * 0.18 * speedMultiplier,
					hue: Math.random() * 360,
					hueSpeed: ( Math.random() - 0.5 ) * 6
				} );
			}
		}

		function resize() {
			var rect = header.getBoundingClientRect();
			width = rect.width;
			height = rect.height;
			canvas.width = width * dpr;
			canvas.height = height * dpr;
			canvas.style.width = width + 'px';
			canvas.style.height = height + 'px';
			ctx.setTransform( dpr, 0, 0, dpr, 0, 0 );
			buildNodes();
		}

		function draw( timestamp ) {
			ctx.clearRect( 0, 0, width, height );

			nodes.forEach( function ( node ) {
				node.x += node.vx;
				node.y += node.vy;
				node.hue = ( node.hue + node.hueSpeed * 0.016 + 360 ) % 360;

				if ( node.x < 0 || node.x > width ) {
					node.vx *= -1;
					node.x = Math.max( 0, Math.min( width, node.x ) );
				}
				if ( node.y < 0 || node.y > height ) {
					node.vy *= -1;
					node.y = Math.max( 0, Math.min( height, node.y ) );
				}
			} );

			var maxDist = Math.max( 90, Math.min( width, height ) * 0.22 );
			// Seconds since this canvas started animating — drives the
			// glow-lines pulse below; harmless/unused when that's off.
			var t = ( timestamp || 0 ) / 1000;

			for ( var i = 0; i < nodes.length; i++ ) {
				for ( var j = i + 1; j < nodes.length; j++ ) {
					var a = nodes[ i ];
					var b = nodes[ j ];
					var dx = a.x - b.x;
					var dy = a.y - b.y;
					var dist = Math.sqrt( dx * dx + dy * dy );
					if ( dist < maxDist ) {
						var opacity = ( 1 - dist / maxDist ) * 0.35;
						var midHue = ( a.hue + b.hue ) / 2;

						if ( glowLines ) {
							// A slow shimmer travelling along each connection
							// (phase offset by position so lines don't pulse
							// in lockstep) plus an actual glow, for the
							// "floating in space" feel requested — subtler
							// than a strobe, more like drifting energy.
							var pulse = 0.55 + 0.45 * Math.sin( t * 1.6 + dist * 0.05 );
							ctx.strokeStyle = 'hsla(' + midHue + ', 90%, 72%, ' + ( opacity * pulse ) + ')';
							ctx.lineWidth = 1 + pulse * 1.4;
							ctx.shadowColor = 'hsla(' + midHue + ', 95%, 70%, ' + Math.min( 1, opacity * pulse * 1.5 ) + ')';
							ctx.shadowBlur = 6 * pulse;
						} else {
							ctx.strokeStyle = 'hsla(' + midHue + ', 85%, 70%, ' + opacity + ')';
							ctx.lineWidth = 1;
							ctx.shadowBlur = 0;
						}

						ctx.beginPath();
						ctx.moveTo( a.x, a.y );
						ctx.lineTo( b.x, b.y );
						ctx.stroke();
					}
				}
			}
			ctx.shadowBlur = 0;

			nodes.forEach( function ( node ) {
				ctx.beginPath();
				ctx.fillStyle = 'hsla(' + node.hue + ', 90%, 70%, 0.9)';
				ctx.shadowColor = 'hsla(' + node.hue + ', 90%, 65%, 0.8)';
				ctx.shadowBlur = 6;
				ctx.arc( node.x, node.y, 2.2, 0, Math.PI * 2 );
				ctx.fill();
			} );
			ctx.shadowBlur = 0;
		}

		function step( timestamp ) {
			draw( timestamp );
			if ( ! prefersReducedMotion ) {
				raf = window.requestAnimationFrame( step );
			}
		}

		function handleVisibility() {
			if ( document.hidden ) {
				if ( raf ) {
					window.cancelAnimationFrame( raf );
					raf = null;
				}
			} else if ( ! prefersReducedMotion && ! raf ) {
				step();
			}
		}

		resize();
		step();

		window.addEventListener( 'resize', resize, { passive: true } );
		document.addEventListener( 'visibilitychange', handleVisibility );
	} );
} )();
