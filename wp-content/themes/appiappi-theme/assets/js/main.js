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
