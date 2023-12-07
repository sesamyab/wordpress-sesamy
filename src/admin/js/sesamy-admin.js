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

})( jQuery );
