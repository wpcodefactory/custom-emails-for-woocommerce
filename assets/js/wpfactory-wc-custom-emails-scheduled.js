/**
 * Custom Emails for WooCommerce - Scheduled JS
 *
 * @version 3.7.3
 * @since   3.7.3
 *
 * @author  WPFactory
 *
 * @todo    (dev) `#content`?
 */

jQuery( document ).ready( function () {
	jQuery( '.wpfactory-wc-custom-emails-unschedule' ).on( 'click', function ( e ) {
		if ( confirm( wpfactoryWCCustomEmailsScheduled.unscheduleMessage ) ) {
			var url = jQuery( this ).attr( 'href' );
			jQuery( '#content' ).load( url );
		} else {
			e.preventDefault();
		}
	} );
} );
