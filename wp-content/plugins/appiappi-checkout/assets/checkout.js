/**
 * Pricing page checkout modal: open on a plan's "Choose Plan" click,
 * show a themed invoice (plan + optional Website Design line, minus any
 * plan design-credit + optional Hosting line + a monthly/annual toggle
 * for recurring plans + a pay-now/pay-later choice for plans that
 * include free hosting), collect contact details, hand off to Stripe's
 * Payment Element for the actual card/wallet details, and confirm the
 * payment. All totals shown here are for *display* —
 * appiappi-checkout's AJAX handler independently recomputes the real
 * amount server-side (and re-validates hosting eligibility) before ever
 * creating anything in Stripe, so nothing in this file is a trust
 * boundary.
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
		paymentTiming: 'now',
		hosting: { matchedPackage: null },
		orderId: 0,
		clientSecret: '',
		stripe: null,
		elements: null
	};

	function formatMoney( amount ) {
		return '$' + Number( amount ).toLocaleString( undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 } ) + ' ' + cfg.currency;
	}

	function storageLabel( pkg ) {
		return pkg.storageUnlimited ? cfg.i18n.unlimited : pkg.storageAmount;
	}

	function trafficLabel( pkg ) {
		return pkg.trafficUnlimited ? cfg.i18n.unlimited : pkg.trafficAmount;
	}

	function computeTotals() {
		var plan = state.plan;
		var design = ( cfg.selectedDesign && Number( cfg.selectedDesign.id ) === Number( state.designId ) ) ? cfg.selectedDesign : null;
		var designPrice = design ? Number( design.price ) : 0;

		// A plan without free hosting always requires full payment today —
		// mirrors the server-side enforcement in pricing.php exactly.
		var paymentTiming = plan.includesFreeHosting ? state.paymentTiming : 'now';
		var hostingRequired = ! plan.includesFreeHosting || 'later' === paymentTiming;
		var hostingPkg = hostingRequired ? state.hosting.matchedPackage : null;
		var hostingPrice = hostingPkg ? Number( hostingPkg.annualPrice ) : 0;

		var discountPercent = 0;
		var planAmount = Number( plan.price );
		if ( 'yearly' === state.billingFrequency && 'monthly' === plan.billingFrequency ) {
			discountPercent = Number( cfg.annualDiscount ) || 0;
			planAmount = Math.round( ( plan.price * 12 ) * ( 1 - discountPercent / 100 ) * 100 ) / 100;
		}

		var creditApplied = 0;
		var designAfterCredit = designPrice;
		if ( 'now' === paymentTiming && plan.designCredit > 0 && designPrice > 0 ) {
			creditApplied = Math.min( plan.designCredit, designPrice );
			designAfterCredit = Math.round( ( designPrice - creditApplied ) * 100 ) / 100;
		}

		var chargedTodayExtra = designAfterCredit + hostingPrice;
		var chargedToday = 'now' === paymentTiming ? planAmount + chargedTodayExtra : chargedTodayExtra;
		var deferredAmount = 'later' === paymentTiming ? planAmount : 0;

		return {
			design: design,
			designPrice: designPrice,
			creditApplied: creditApplied,
			designAfterCredit: designAfterCredit,
			hostingRequired: hostingRequired,
			hostingPkg: hostingPkg,
			hostingPrice: hostingPrice,
			discountPercent: discountPercent,
			planAmount: planAmount,
			paymentTiming: paymentTiming,
			chargedToday: chargedToday,
			deferredAmount: deferredAmount
		};
	}

	function populateHostingLocationSelect() {
		var select = modal.querySelector( '[data-checkout-hosting-location]' );
		var locations = [];
		cfg.hostingPackages.forEach( function ( pkg ) {
			if ( locations.indexOf( pkg.location ) === -1 ) {
				locations.push( pkg.location );
			}
		} );
		select.innerHTML = '<option value="">' + cfg.i18n.chooseLocation + '</option>' +
			locations.map( function ( loc ) { return '<option value="' + loc + '">' + loc + '</option>'; } ).join( '' );
		modal.querySelector( '[data-checkout-hosting-location-label]' ).textContent = cfg.i18n.location;
		modal.querySelector( '[data-checkout-hosting-storage-label]' ).textContent = cfg.i18n.storage;
		modal.querySelector( '[data-checkout-hosting-traffic-label]' ).textContent = cfg.i18n.traffic;
	}

	function populateHostingStorageSelect( location ) {
		var select = modal.querySelector( '[data-checkout-hosting-storage]' );
		var row = modal.querySelector( '[data-checkout-hosting-storage-row]' );
		var trafficRow = modal.querySelector( '[data-checkout-hosting-traffic-row]' );

		var labels = [];
		cfg.hostingPackages.filter( function ( p ) { return p.location === location; } ).forEach( function ( pkg ) {
			var label = storageLabel( pkg );
			if ( labels.indexOf( label ) === -1 ) {
				labels.push( label );
			}
		} );

		state.hosting.matchedPackage = null;
		trafficRow.hidden = true;

		if ( ! location || labels.length === 0 ) {
			row.hidden = true;
			return;
		}
		row.hidden = false;
		select.innerHTML = '<option value="">' + cfg.i18n.chooseStorage + '</option>' +
			labels.map( function ( l ) { return '<option value="' + l + '">' + l + '</option>'; } ).join( '' );
	}

	function populateHostingTrafficSelect( location, storage ) {
		var matches = cfg.hostingPackages.filter( function ( p ) { return p.location === location && storageLabel( p ) === storage; } );
		var labels = [];
		matches.forEach( function ( pkg ) {
			var label = trafficLabel( pkg );
			if ( labels.indexOf( label ) === -1 ) {
				labels.push( label );
			}
		} );

		var row = modal.querySelector( '[data-checkout-hosting-traffic-row]' );
		var select = modal.querySelector( '[data-checkout-hosting-traffic]' );

		if ( labels.length <= 1 ) {
			row.hidden = true;
			state.hosting.matchedPackage = matches[ 0 ] || null;
			return;
		}

		row.hidden = false;
		state.hosting.matchedPackage = null;
		select.innerHTML = '<option value="">' + cfg.i18n.chooseTraffic + '</option>' +
			labels.map( function ( l ) { return '<option value="' + l + '">' + l + '</option>'; } ).join( '' );
	}

	function resetHostingSelector() {
		state.hosting.matchedPackage = null;
		populateHostingLocationSelect();
		modal.querySelector( '[data-checkout-hosting-storage-row]' ).hidden = true;
		modal.querySelector( '[data-checkout-hosting-traffic-row]' ).hidden = true;
	}

	modal.querySelector( '[data-checkout-hosting-location]' ).addEventListener( 'change', function () {
		populateHostingStorageSelect( this.value );
		renderInvoice();
	} );
	modal.querySelector( '[data-checkout-hosting-storage]' ).addEventListener( 'change', function () {
		var location = modal.querySelector( '[data-checkout-hosting-location]' ).value;
		populateHostingTrafficSelect( location, this.value );
		renderInvoice();
	} );
	modal.querySelector( '[data-checkout-hosting-traffic]' ).addEventListener( 'change', function () {
		var location = modal.querySelector( '[data-checkout-hosting-location]' ).value;
		var storage = modal.querySelector( '[data-checkout-hosting-storage]' ).value;
		var traffic = this.value;
		state.hosting.matchedPackage = cfg.hostingPackages.filter( function ( p ) {
			return p.location === location && storageLabel( p ) === storage && trafficLabel( p ) === traffic;
		} )[ 0 ] || null;
		renderInvoice();
	} );

	modal.querySelectorAll( 'input[name="appiappi_payment_timing"]' ).forEach( function ( radio ) {
		radio.addEventListener( 'change', function () {
			state.paymentTiming = radio.value;
			resetHostingSelector();
			renderInvoice();
		} );
	} );

	function updateContinueButtonState( totals ) {
		var btn = modal.querySelector( '[data-checkout-continue]' );
		btn.disabled = ! cfg.configured || ( totals.hostingRequired && ! totals.hostingPkg );
	}

	function renderInvoice() {
		var plan = state.plan;
		var totals = computeTotals();

		modal.style.setProperty( '--checkout-plan-color', plan.color );
		modal.querySelectorAll( '[data-checkout-plan-name]' ).forEach( function ( el ) { el.textContent = plan.name; } );

		var timingBox = modal.querySelector( '[data-checkout-payment-timing]' );
		var hostingNote = modal.querySelector( '[data-checkout-hosting-required-note]' );
		if ( plan.includesFreeHosting ) {
			timingBox.hidden = false;
			hostingNote.hidden = true;
		} else {
			timingBox.hidden = true;
			hostingNote.hidden = false;
			hostingNote.textContent = cfg.i18n.hostingRequiredNote;
		}
		modal.querySelector( '[data-checkout-payment-timing-hint]' ).textContent = cfg.i18n.payLaterHint;

		modal.querySelector( '[data-checkout-hosting-selector]' ).hidden = ! totals.hostingRequired;

		var periodLabel = 'one_time' === plan.billingFrequency
			? cfg.i18n.oneTime
			: ( 'yearly' === state.billingFrequency ? '/ ' + cfg.i18n.annual.toLowerCase() : '/ ' + cfg.i18n.monthly.toLowerCase() );
		modal.querySelectorAll( '[data-checkout-plan-price]' ).forEach( function ( el ) {
			el.textContent = formatMoney( plan.price ) + ' ' + periodLabel;
		} );

		var designRow = modal.querySelector( '[data-checkout-design-row]' );
		var creditRow = modal.querySelector( '[data-checkout-credit-row]' );
		if ( totals.design ) {
			designRow.hidden = false;
			modal.querySelector( '[data-checkout-design-price]' ).textContent = totals.design.name + ' — ' + formatMoney( totals.designPrice );
			if ( totals.creditApplied > 0 ) {
				creditRow.hidden = false;
				modal.querySelector( '[data-checkout-credit-label]' ).textContent = cfg.i18n.designCredit;
				modal.querySelector( '[data-checkout-credit-amount]' ).textContent = '−' + formatMoney( totals.creditApplied );
			} else {
				creditRow.hidden = true;
			}
		} else {
			designRow.hidden = true;
			creditRow.hidden = true;
		}

		var hostingRow = modal.querySelector( '[data-checkout-hosting-row]' );
		if ( totals.hostingRequired && totals.hostingPkg ) {
			hostingRow.hidden = false;
			var pkg = totals.hostingPkg;
			modal.querySelector( '[data-checkout-hosting-label]' ).textContent = cfg.i18n.hosting + ': ' + pkg.location + ' (' + storageLabel( pkg ) + ')';
			modal.querySelector( '[data-checkout-hosting-price]' ).textContent = formatMoney( totals.hostingPrice ) + ' / yr';
		} else {
			hostingRow.hidden = true;
		}

		var toggle = modal.querySelector( '[data-checkout-billing-toggle]' );
		if ( 'monthly' === plan.billingFrequency ) {
			toggle.hidden = false;
			modal.querySelector( '[data-checkout-discount-badge]' ).textContent = cfg.i18n.save + ' ' + ( Number( cfg.annualDiscount ) || 0 ) + '%';
		} else {
			toggle.hidden = true;
		}

		modal.querySelectorAll( '[data-checkout-total], [data-checkout-total-repeat]' ).forEach( function ( el ) {
			el.textContent = formatMoney( totals.chargedToday );
		} );

		var recurringNote = modal.querySelector( '[data-checkout-recurring-note]' );
		if ( 'now' === totals.paymentTiming && 'one_time' !== plan.billingFrequency ) {
			var intervalLabel = 'yearly' === state.billingFrequency ? cfg.i18n.annual.toLowerCase() : cfg.i18n.monthly.toLowerCase();
			recurringNote.hidden = false;
			recurringNote.textContent = cfg.i18n.thenBilled.replace( '%s', formatMoney( totals.planAmount ) ).replace( '%s', intervalLabel );
		} else {
			recurringNote.hidden = true;
		}

		var deferredNote = modal.querySelector( '[data-checkout-deferred-note]' );
		if ( 'later' === totals.paymentTiming ) {
			deferredNote.hidden = false;
			deferredNote.textContent = cfg.i18n.dueOnCompletion.replace( '%s', formatMoney( totals.planAmount ) );
		} else {
			deferredNote.hidden = true;
		}

		updateContinueButtonState( totals );
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
		state.paymentTiming = 'now';
		resetHostingSelector();
		showError( '[data-checkout-error]', '' );
		showStep( 'invoice' );
		renderInvoice();
		modal.hidden = false;
		document.body.classList.add( 'appiappi-checkout-open' );

		if ( ! cfg.configured ) {
			showError( '[data-checkout-error]', cfg.i18n.notConfigured );
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
		var totals = computeTotals();
		if ( totals.hostingRequired && ! totals.hostingPkg ) {
			showError( '[data-checkout-error]', cfg.i18n.selectHosting );
			return;
		}

		showError( '[data-checkout-error]', '' );
		var submitBtn = modal.querySelector( '[data-checkout-continue]' );
		submitBtn.disabled = true;
		var originalLabel = submitBtn.textContent;
		submitBtn.textContent = cfg.i18n.processing;

		ajax( 'appiappi_checkout_create', {
			plan_id: state.plan.id,
			billing_frequency: state.billingFrequency,
			design_post_id: totals.design ? state.designId : 0,
			payment_timing: totals.paymentTiming,
			hosting_id: totals.hostingPkg ? totals.hostingPkg.id : 0,
			customer_name: form.customer_name.value,
			customer_email: form.customer_email.value,
			customer_phone: form.customer_phone.value
		} ).then( function ( res ) {
			submitBtn.disabled = false;
			submitBtn.textContent = originalLabel;

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
			submitBtn.textContent = originalLabel;
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
