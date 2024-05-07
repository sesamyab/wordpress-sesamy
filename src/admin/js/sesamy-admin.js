(function ( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	 // Check the checkbox state on page load ( For Locked section )
    if ($('.sesamy-classic-locked').is(':checked')) {
    	var fromdate = $( "input[name=locked_from_date]" ).val();
        $('.sesamy-classic-locked-active').hide(); // Show content if checkbox is unchecked
        $('.sesamy-classic-locked-inactive').show(); // Hide content if checkbox is checked initially
    }else{
    	$('.sesamy-classic-locked-active').show(); // Show content if checkbox is unchecked
    	if( fromdate == '' ){
        	$('.sesamy-classic-locked-inactive').hide(); // Hide content if checkbox is checked initially
        }else{
        	$('.sesamy-classic-locked-inactive').show(); // Show content if from date not null
        }
    }

    // Check checkbox change event
    $( '.sesamy-classic-locked' ).change( function() {
        var fromdate = $( "input[name=locked_from_date]" ).val();
        if ( $( this ).is( ':checked' ) ) {
            $('.sesamy-classic-locked-active').hide(); // Show content if checkbox is unchecked
        	$('.sesamy-classic-locked-inactive').show(); // Hide content if checkbox is checked initially
        } else {
            $('.sesamy-classic-locked-active').show(); // Show content if checkbox is unchecked
            if( fromdate == '' ){
        		$('.sesamy-classic-locked-inactive').hide(); // Hide content if from date null
        	}else{
        		$('.sesamy-classic-locked-inactive').show(); // Show content if from date not null
        	}
        }
    });

    // On change from date
    $( 'input[name=locked_from_date]' ).change( function() {
    	if ( $( this ).val() == '' ){
    		$('.sesamy-classic-locked-inactive').hide(); // Hide content if value of from date in null
    	}else{
    		$('.sesamy-classic-locked-inactive').show(); // Show content if checkbox is checked initially
    	}
    });

    // Check the checkbox state on page load ( For Single Purchase Enable )
    if ( $( '.sesamy-classic-single-purchase').is( ':checked' )) {
        $('.sesamy-classic-locked-price').show(); // Show content if checkbox is checked initially
    }else{
    	$('.sesamy-classic-locked-price').hide(); // Hide content if checkbox is checked initially
    }

    // Check checkbox change event
    $('.sesamy-classic-single-purchase').change( function() {
        if ( $( this ).is(':checked') ) {
            $('.sesamy-classic-locked-price').show(); // Hide content if checkbox is checked
        } else {
            $('.sesamy-classic-locked-price').hide(); // Show content if checkbox is unchecked
        }
    });
	
	// Check checkbox change event
    $('.sesamy-classic-single-purchase').change( function() {
        if ( $( this ).is(':checked') ) {
            $('.sesamy-classic-locked-price').show(); // Hide content if checkbox is checked
			$('.sesamy-classic-locked-discount-codes').show(); // Hide content if checkbox is checked
        } else {
            $('.sesamy-classic-locked-price').hide(); // Show content if checkbox is unchecked
			$('.sesamy-classic-locked-discount-codes').hide(); // Show content if checkbox is unchecked
        }
    });

	// Check checkbox change event
	if ( jQuery( '#sesamy_paywall_wizard' ).is( ':checked' ) ) {
		// Show content if checkbox is checked
		jQuery('.sesamy-paywall-wizard-login-button').show();
		jQuery('.sesamy-paywall-wizard-logo').show();
		jQuery('.sesamy-paywall-wizard-color').show();
		jQuery('.sesamy-paywall-wizard-subscriptionsUrl').show();
		jQuery('.sesamy-paywall-wizard-subscriptionsUrl').find('input').attr('required', 'required');
		jQuery('.sesamy-paywall-wizard-discountCode').show();
		jQuery('.sesamy-paywall-wizard-subscriptions').show();
		jQuery('.sesamy-paywall-wizard-subscriptions').find('input').attr('required', 'required');
		jQuery('.sesamy-paywall-wizard-subscriptions').find('.po').removeAttr('required');
		jQuery('.sesamy-paywall-wizard-subscriptions').find('.discountCode').removeAttr('required');
		jQuery('.sesamy-paywall-wizard-subscriptions').find('.tag').removeAttr('required');
		jQuery('.sesamy-paywall-wizard-subscriptionFeatures').show();
		jQuery('.sesamy-paywall-wizard-subscriptionPurchaseText').show();
		jQuery('.sesamy-paywall-wizard-termsOfServiceUrl').show();
		jQuery('.sesamy-paywall-wizard-privacyPolicyUrl').show();
		jQuery('.sesamy-paywall-wizard-footer-payment-methods').show();
	} else {
		// Hide content if checkbox is not checked
		jQuery('.sesamy-paywall-wizard-login-button').hide();
		jQuery('.sesamy-paywall-wizard-logo').hide();
		jQuery('.sesamy-paywall-wizard-color').hide();
		jQuery('.sesamy-paywall-wizard-subscriptionsUrl').find('input').removeAttr('required');
		jQuery('.sesamy-paywall-wizard-subscriptionsUrl').hide();
		jQuery('.sesamy-paywall-wizard-discountCode').hide();
		jQuery('.sesamy-paywall-wizard-subscriptions').hide();
		jQuery('.sesamy-paywall-wizard-subscriptions').find('input').removeAttr('required');
		jQuery('.sesamy-paywall-wizard-subscriptionFeatures').hide();
		jQuery('.sesamy-paywall-wizard-subscriptionPurchaseText').hide();
		jQuery('.sesamy-paywall-wizard-termsOfServiceUrl').hide();
		jQuery('.sesamy-paywall-wizard-privacyPolicyUrl').hide();
		jQuery('.sesamy-paywall-wizard-footer-payment-methods').hide();
	}

	if ( jQuery( '.sesamy_paywall_wizard_show_hide' ).is( ':checked' ) ) {
		jQuery('.sesamy_paywall_wizard_show_hide_title').html("Switch to default paywall");
		jQuery('.sesamy-classic-paywall-wizard-inactive').show();
	} else {
		jQuery('.sesamy_paywall_wizard_show_hide_title').html("Switch to paywall wizard");
		jQuery('.sesamy-classic-paywall-wizard-inactive').hide();
	}

})( jQuery );


// Check checkbox change event
jQuery( '.sesamy_paywall_wizard_show_hide' ).change( function() {
	if ( jQuery( this ).is( ':checked' ) ) {
		jQuery('.sesamy_paywall_wizard_show_hide_title').html("Switch to default paywall");
		jQuery('.sesamy-classic-paywall-wizard-inactive').show();
	} else {
		jQuery('.sesamy_paywall_wizard_show_hide_title').html("Switch to paywall wizard");
		jQuery('.sesamy-classic-paywall-wizard-inactive').hide();
	}
});

// Check checkbox change event
jQuery( '#sesamy_paywall_wizard' ).change( function() {
	if ( jQuery( this ).is( ':checked' ) ) {
		// Show content if checkbox is checked
		jQuery('.sesamy-paywall-wizard-login-button').show();
		jQuery('.sesamy-paywall-wizard-logo').show();
		jQuery('.sesamy-paywall-wizard-color').show();
		jQuery('.sesamy-paywall-wizard-subscriptionsUrl').show();
		jQuery('.sesamy-paywall-wizard-subscriptionsUrl').find('input').attr('required', 'required');
		jQuery('.sesamy-paywall-wizard-discountCode').show();
		jQuery('.sesamy-paywall-wizard-subscriptions').show();
		jQuery('.sesamy-paywall-wizard-subscriptions').find('input').attr('required', 'required');
		jQuery('.sesamy-paywall-wizard-subscriptions').find('.po').removeAttr('required');
		jQuery('.sesamy-paywall-wizard-subscriptions').find('.discountCode').removeAttr('required');
		jQuery('.sesamy-paywall-wizard-subscriptions').find('.tag').removeAttr('required');
		jQuery('.sesamy-paywall-wizard-subscriptionFeatures').show();
		jQuery('.sesamy-paywall-wizard-subscriptionPurchaseText').show();
		jQuery('.sesamy-paywall-wizard-termsOfServiceUrl').show();
		jQuery('.sesamy-paywall-wizard-privacyPolicyUrl').show();
		jQuery('.sesamy-paywall-wizard-footer-payment-methods').show();
	} else {
		// Hide content if checkbox is not checked
		jQuery('.sesamy-paywall-wizard-login-button').hide();
		jQuery('.sesamy-paywall-wizard-logo').hide();
		jQuery('.sesamy-paywall-wizard-color').hide();
		jQuery('.sesamy-paywall-wizard-subscriptionsUrl').find('input').removeAttr('required');
		jQuery('.sesamy-paywall-wizard-subscriptionsUrl').hide();
		jQuery('.sesamy-paywall-wizard-discountCode').hide();
		jQuery('.sesamy-paywall-wizard-subscriptions').hide();
		jQuery('.sesamy-paywall-wizard-subscriptions').find('input').removeAttr('required');
		jQuery('.sesamy-paywall-wizard-subscriptionFeatures').hide();
		jQuery('.sesamy-paywall-wizard-subscriptionPurchaseText').hide();
		jQuery('.sesamy-paywall-wizard-termsOfServiceUrl').hide();
		jQuery('.sesamy-paywall-wizard-privacyPolicyUrl').hide();
		jQuery('.sesamy-paywall-wizard-footer-payment-methods').hide();
	}
});

jQuery(document).ready(function($){

	// color picker
	var myOptions = {
		// you can declare a default color here,
		// or in the data-default-color attribute on the input
		defaultColor: false,
		// a callback to fire whenever the color changes to a valid color
		change: function(event, ui){},
		// a callback to fire when the input is emptied or an invalid color
		clear: function() {},
		// hide the color picker controls on load
		hide: true,
		// show a group of common colors beneath the square
		// or, supply an array of colors to customize further
		palettes: true
	};
	$('.sesamy-color-field').wpColorPicker(myOptions);

});

jQuery('#container').on('click','.add_more', function () {
	var newthing = jQuery('div.addNew:first').clone().find('.add_more').removeClass('add_more').addClass('remove').val('Remove Feature').end();
	jQuery('#container').append(newthing).find('.addNew').last().find('input[type=text]').val('');
});

jQuery('#container').on('click','.remove', function () {
	jQuery(this).parent().parent().remove();
});

jQuery('#container_div').on('click','.add_more', function () {
	var newIndex = (jQuery('div.addNewSection').length - 1) + 1;
	var newthing = jQuery('div.addNewSection:first').clone().find('.add_more').removeClass('add_more').addClass('remove').val('Remove Subscription').end();
	newthing.find("input").attr('name', function(idx, attrVal) {
		var oldNameArray = attrVal.split('[');
		var newName = attrVal;
		if(oldNameArray.length >= 3) {
			newName = oldNameArray[0] + '[' + newIndex + ']' + '[' + oldNameArray[2].slice(0,-1) + ']'; // change the name
		}
		return newName; // change the name
	});

	newthing.find("textarea").attr('name', function(idx, attrVal) {
		var oldNameArray = attrVal.split('[');
		var newName = attrVal;
		if(oldNameArray.length >= 3) {
			newName = oldNameArray[0] + '[' + newIndex + ']' + '[' + oldNameArray[2].slice(0,-1) + ']'; // change the name
		}
		return newName; // change the name
	});

	newthing.find("select").attr('name', function(idx, attrVal) {
		var oldNameArray = attrVal.split('[');
		var newName = attrVal;
		if(oldNameArray.length >= 3) {
			newName = oldNameArray[0] + '[' + newIndex + ']' + '[' + oldNameArray[2].slice(0,-1) + ']'; // change the name
		}
		return newName; // change the name
	});

	jQuery('#container_div').append(newthing).find('.addNewSection').last().find('input[type=text],input[type=number],textarea').not('.recurringPaymentText').val('');
});

jQuery('#container_div').on('click','.remove', function () {
	jQuery(this).parent().parent().parent().remove();
});