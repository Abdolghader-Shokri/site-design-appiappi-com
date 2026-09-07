/**
 * Header widget dropdown (click/tap to open — not hover, so it works
 * identically on touch devices — closes on an outside click, a second
 * trigger click, or Escape) and the sign-in error banner's dismiss +
 * address-bar cleanup.
 */
( function () {
	'use strict';

	function closeAllMenus( except ) {
		document.querySelectorAll( '.client-login-widget__trigger[aria-expanded="true"]' ).forEach( function ( trigger ) {
			if ( trigger === except ) {
				return;
			}
			trigger.setAttribute( 'aria-expanded', 'false' );
			var menu = trigger.parentElement.querySelector( '.client-login-widget__menu' );
			if ( menu ) {
				menu.classList.remove( 'is-open' );
			}
		} );
	}

	document.addEventListener( 'click', function ( e ) {
		var trigger = e.target.closest( '.client-login-widget__trigger' );
		if ( trigger ) {
			var menu = trigger.parentElement.querySelector( '.client-login-widget__menu' );
			var isOpen = 'true' === trigger.getAttribute( 'aria-expanded' );
			closeAllMenus( trigger );
			trigger.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
			if ( menu ) {
				menu.classList.toggle( 'is-open', ! isOpen );
			}
			return;
		}

		if ( ! e.target.closest( '.client-login-widget' ) ) {
			closeAllMenus( null );
		}
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key ) {
			closeAllMenus( null );
		}
	} );

	var banner = document.querySelector( '.client-login-error-banner' );
	if ( banner ) {
		if ( window.history && window.history.replaceState ) {
			var url = new URL( window.location.href );
			url.searchParams.delete( 'client_login_error' );
			window.history.replaceState( {}, '', url.toString() );
		}
		var closeBtn = banner.querySelector( '.client-login-error-banner__close' );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', function () {
				banner.hidden = true;
			} );
		}
	}
} )();
