/**
 * Pricing page checkout modal: open on a plan's "Choose Plan" click,
 * show a themed invoice (plan + optional Website Design line + a
 * monthly/annual toggle for recurring plans), collect contact details,
 * hand off to Stripe's Payment Element for the actual card/wallet
 * details, and confirm the payment. All totals shown here are for
 * *display* — appiappi-checkout's AJAX handler independently recomputes
 * the real amount server-side before ever creating anything in Stripe,
 * so nothing in this file is a trust boundary.
 */
( function () {
	'use strict';

	if ( typeof appiappiCheckout === 'undefined' ) {
		return;
	}

	var cfg = appiappiCheckout;
	var modal = document.getElementById( 'appiappi-checkout-modal' );
	if ( ! modal ) {
		return;
	}

	var steps = {
		invoice: modal.querySelector( '[data-checkout-step="invoice"]' ),
		payment: modal.querySelector( '[data-checkout-step="payment"]' ),
		status:  modal.querySelector( '[data-checkout-step="status"]' )
	};

	var state = {
		plan: null,
		designId: 0,
		billingFrequency: 'monthly',
		orderId: 0,
		clientSecret: '',
		stripe: null,
		elements: null
	};

	function formatMoney( amount ) {
		return '$' + Number( amount ).toLocaleString( undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 } ) + ' ' + cfg.currency;
	}

	function computeTotals() {
		var plan = state.plan;
		var design = ( cfg.selectedDesign && Number( cfg.selectedDesign.id ) === Number( state.designId ) ) ? cfg.selectedDesign : null;
		var designPrice = design ? Number( design.price ) : 0;
		var discountPercent = 0;
		var recurringAmount = Number( plan.price );

		if ( 'yearly' === state.billingFrequency && 'monthly' === plan.billingFrequency ) {
			discountPercent = Number( cfg.annualDiscount ) || 0;
			recurringAmount = Math.round( ( plan.price * 12 ) * ( 1 - discountPercent / 100 ) * 100 ) / 100;
		}

		var oneTimeAmount = 'one_time' === state.billingFrequency ? recurringAmount + designPrice : designPrice;
		var totalToday = 'one_time' === state.billingFrequency ? oneTimeAmount : recurringAmount + oneTimeAmount;

		return {
			design: design,
			designPrice: designPrice,
			discountPercent: discountPercent,
			recurringAmount: 'one_time' === state.billingFrequency ? 0 : recurringAmount,
			totalToday: totalToday
		};
	}

	function renderInvoice() {
		var plan = state.plan;
		var totals = computeTotals();

		modal.style.setProperty( '--checkout-plan-color', plan.color );
		modal.querySelectorAll( '[data-checkout-plan-name]' ).forEach( function ( el ) { el.textContent = plan.name; } );

		var periodLabel = 'one_time' === plan.billingFrequency
			? cfg.i18n.oneTime
			: ( 'yearly' === state.billingFrequency ? '/ ' + cfg.i18n.annual.toLowerCase() : '/ ' + cfg.i18n.monthly.toLowerCase() );
		modal.querySelectorAll( '[data-checkout-plan-price]' ).forEach( function ( el ) {
			el.textContent = formatMoney( plan.price ) + ' ' + periodLabel;
		} );

		var designRow = modal.querySelector( '[data-checkout-design-row]' );
		if ( totals.design ) {
			designRow.hidden = false;
			modal.querySelector( '[data-checkout-design-price]' ).textContent = totals.design.name + ' — ' + formatMoney( totals.designPrice );
		} else {
			designRow.hidden = true;
		}

		var toggle = modal.querySelector( '[data-checkout-billing-toggle]' );
		if ( 'monthly' === plan.billingFrequency ) {
			toggle.hidden = false;
			modal.querySelector( '[data-checkout-discount-badge]' ).textContent = cfg.i18n.save + ' ' + ( Number( cfg.annualDiscount ) || 0 ) + '%';
		} else {
			toggle.hidden = true;
		}

		modal.querySelectorAll( '[data-checkout-total], [data-checkout-total-repeat]' ).forEach( function ( el ) {
			el.textContent = formatMoney( totals.totalToday );
		} );

		var note = modal.querySelector( '[data-checkout-recurring-note]' );
		if ( 'one_time' !== state.billingFrequency ) {
			var intervalLabel = 'yearly' === state.billingFrequency ? cfg.i18n.annual.toLowerCase() : cfg.i18n.monthly.toLowerCase();
			note.hidden = false;
			note.textContent = cfg.i18n.thenBilled.replace( '%s', formatMoney( totals.recurringAmount ) ).replace( '%s', intervalLabel );
		} else {
			note.hidden = true;
		}
	}

	function showStep( name ) {
		Object.keys( steps ).forEach( function ( key ) {
			steps[ key ].hidden = key !== name;
		} );
	}

	function showError( selector, message ) {
		var el = modal.querySelector( selector );
		el.textContent = message;
		el.hidden = ! message;
	}

	function openModal( plan, designId ) {
		state.plan = plan;
		state.designId = designId;
		state.billingFrequency = plan.billingFrequency;
		showError( '[data-checkout-error]', '' );
		showStep( 'invoice' );
		renderInvoice();
		modal.hidden = false;
		document.body.classList.add( 'appiappi-checkout-open' );

		if ( ! cfg.configured ) {
			showError( '[data-checkout-error]', cfg.i18n.notConfigured );
			modal.querySelector( '[data-checkout-continue]' ).disabled = true;
		}
	}

	function closeModal() {
		modal.hidden = true;
		document.body.classList.remove( 'appiappi-checkout-open' );
	}

	document.querySelectorAll( '.js-appiappi-checkout-open' ).forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			var planId = button.getAttribute( 'data-plan-id' );
			var plan = cfg.plans[ planId ];
			if ( ! plan ) {
				return;
			}
			openModal( plan, parseInt( button.getAttribute( 'data-design-id' ), 10 ) || 0 );
		} );
	} );

	modal.querySelectorAll( '[data-checkout-close]' ).forEach( function ( el ) {
		el.addEventListener( 'click', closeModal );
	} );
	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key && ! modal.hidden ) {
			closeModal();
		}
	} );

	modal.querySelectorAll( 'input[name="appiappi_billing_frequency"]' ).forEach( function ( radio ) {
		radio.addEventListener( 'change', function () {
			state.billingFrequency = radio.value;
			renderInvoice();
		} );
	} );

	function ajax( action, data ) {
		var body = new URLSearchParams( Object.assign( { action: action, nonce: cfg.nonce }, data ) );
		return fetch( cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() } )
			.then( function ( r ) { return r.json(); } );
	}

	var form = modal.querySelector( '[data-checkout-contact-form]' );
	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		if ( ! cfg.configured ) {
			return;
		}
		showError( '[data-checkout-error]', '' );
		var submitBtn = modal.querySelector( '[data-checkout-continue]' );
		submitBtn.disabled = true;
		submitBtn.textContent = cfg.i18n.processing;

		ajax( 'appiappi_checkout_create', {
			plan_id: state.plan.id,
			billing_frequency: state.billingFrequency,
			design_post_id: computeTotals().design ? state.designId : 0,
			customer_name: form.customer_name.value,
			customer_email: form.customer_email.value,
			customer_phone: form.customer_phone.value
		} ).then( function ( res ) {
			submitBtn.disabled = false;
			submitBtn.textContent = cfg.i18n.continueToPayment;

			if ( ! res.success ) {
				showError( '[data-checkout-error]', ( res.data && res.data.message ) || cfg.i18n.genericError );
				return;
			}

			state.orderId = res.data.order_id;
			state.clientSecret = res.data.client_secret;

			if ( ! state.stripe ) {
				state.stripe = Stripe( res.data.publishable_key );
			}
			state.elements = state.stripe.elements( { clientSecret: state.clientSecret } );
			var paymentElement = state.elements.create( 'payment' );
			paymentElement.mount( '#appiappi-payment-element' );

			renderInvoice();
			showStep( 'payment' );
		} ).catch( function () {
			submitBtn.disabled = false;
			submitBtn.textContent = cfg.i18n.continueToPayment;
			showError( '[data-checkout-error]', cfg.i18n.genericError );
		} );
	} );

	function renderStatus( status ) {
		var body = modal.querySelector( '[data-checkout-status-body]' );
		if ( 'paid' === status ) {
			body.innerHTML = '<h2>' + cfg.i18n.successTitle + '</h2><p>' + cfg.i18n.successBody + '</p>';
		} else if ( 'failed' === status ) {
			body.innerHTML = '<h2>' + cfg.i18n.failedTitle + '</h2><p>' + cfg.i18n.failedBody + '</p>';
		} else {
			body.innerHTML = '<h2>' + cfg.i18n.checkingStatus + '</h2>';
		}
		showStep( 'status' );
		modal.hidden = false;
		document.body.classList.add( 'appiappi-checkout-open' );
	}

	function pollOrderStatus( orderId, attemptsLeft ) {
		ajax( 'appiappi_checkout_order_status', { order_id: orderId } ).then( function ( res ) {
			var status = res.success ? res.data.status : 'pending';
			if ( 'pending' === status && attemptsLeft > 0 ) {
				window.setTimeout( function () { pollOrderStatus( orderId, attemptsLeft - 1 ); }, 2000 );
				return;
			}
			renderStatus( status );
		} );
	}

	var payBtn = modal.querySelector( '[data-checkout-pay]' );
	payBtn.addEventListener( 'click', function () {
		showError( '[data-checkout-payment-error]', '' );
		payBtn.disabled = true;
		payBtn.textContent = cfg.i18n.processing;

		state.stripe.confirmPayment( {
			elements: state.elements,
			confirmParams: {
				return_url: cfg.returnUrl + ( cfg.returnUrl.indexOf( '?' ) === -1 ? '?' : '&' ) + 'checkout=return&order_id=' + state.orderId
			},
			redirect: 'if_required'
		} ).then( function ( result ) {
			payBtn.disabled = false;
			payBtn.textContent = cfg.i18n.payNow;

			if ( result.error ) {
				showError( '[data-checkout-payment-error]', result.error.message || cfg.i18n.genericError );
				return;
			}
			// No redirect happened (e.g. a plain card with no 3-D Secure step) —
			// the PaymentIntent may already show "succeeded" here, but the
			// webhook is still the source of truth for the Order record, so
			// poll briefly rather than assuming success from the client alone.
			renderStatus( 'pending' );
			pollOrderStatus( state.orderId, 5 );
		} );
	} );

	// Returning from a redirect-based payment method (3-D Secure, certain
	// wallets) — Stripe appended ?checkout=return&order_id=… to returnUrl.
	var params = new URLSearchParams( window.location.search );
	if ( 'return' === params.get( 'checkout' ) && params.get( 'order_id' ) ) {
		state.orderId = params.get( 'order_id' );
		renderStatus( 'pending' );
		pollOrderStatus( state.orderId, 5 );
	}
} )();
