/**
 * Custom Emails for WooCommerce - Content Template JS
 *
 * @version 3.7.3
 * @since   3.7.3
 *
 * @author  WPFactory
 *
 * @todo    (dev) more templates
 */

jQuery( document ).ready( function ( $ ) {

	// Add button to each element with the class 'wpfactory-wc-ce-shortcode-field'
	$( '.wpfactory-wc-ce-shortcode-field' ).each( function () {
		// Find the closest ancestor element and add a class to it
		const shortcode_closest_element = $( this ).parents().first().addClass( 'wpfactory-wc-ce-shortcode-wrap' );

		// Create the link element
		let link = $( '<a>', {
			href: '#',
			title: wpfactoryWCCustomEmailsContentTemplate.linkTitle,
			class: 'wpfactory-wc-ce-shortcode-button button button-secondary',
			text: wpfactoryWCCustomEmailsContentTemplate.linkText,
		} );

		// Create the span element for the icon
		let icon = $( '<span>', {
			class: 'dashicons dashicons-arrow-down-alt2',
		} );

		// Append the icon to the link
		link.append( icon );

		// Append the link to the selected element
		shortcode_closest_element.append( link );
	} );

	// Define the content to append
	let shortcodes_list = wpfactoryWCCustomEmailsContentTemplate.shortcodeList;

	const shortcode_list_class = '.wpfactory-wc-ce-shortcode-list';

	$( document ).on( 'click', '.wpfactory-wc-ce-shortcode-button', function ( e ) {
		e.preventDefault();

		let container = $( this ).closest( '.wpfactory-wc-ce-shortcode-wrap' );

		$( shortcode_list_class ).not( container.find( shortcode_list_class ) ).hide();

		if ( container.find( shortcode_list_class ).length ) {
			container.find( shortcode_list_class ).toggle();
		} else {
			container.append( `${shortcodes_list}` );
			container.find( shortcode_list_class ).toggle();
		}

		e.stopPropagation();
	} );

	// Click event for hiding shortcodes list when clicking outside
	$( document ).on( 'click', function ( e ) {
		const shortcode_lists = $( shortcode_list_class );

		if ( ! shortcode_lists.is( e.target ) && 0 === shortcode_lists.has( e.target ).length ) {
			shortcode_lists.hide();
		}
	} );

	// Click and append shortcodes to the field or TinyMCE editor
	$( document ).on( 'click', '.wpfactory-wc-ce-shortcode-list li', function () {
		const shortcode = $( this ).data( 'shortcode' );
		const field_container = $( this ).closest( '.wpfactory-wc-ce-shortcode-wrap' );
		const field_id = field_container.find( '.wpfactory-wc-ce-shortcode-field' ).attr( 'id' );
		const field = $( `#${field_id}` );

		if ( ! field.length ) {
			return;
		} // Ensure the field exists

		field.focus();

		// Get current cursor position
		const cursor_pos = field.prop( 'selectionStart' );

		// Use execCommand to insert text
		try {
			document.execCommand( 'insertText', false, shortcode );
		} catch ( error ) {
			// Fallback method if execCommand fails
			const field_value = field.val();
			field.val( field_value.substring( 0, cursor_pos ) + shortcode + field_value.substring( cursor_pos ) );
		}

		// Update cursor position after inserting the shortcode
		field.prop( 'selectionStart', cursor_pos + shortcode.length );
		field.prop( 'selectionEnd', cursor_pos + shortcode.length );

		// For TinyMCE editor (Visual Editor)
		if ( typeof tinyMCE !== "undefined" ) {
			const editor = tinyMCE.get( field_id );

			if ( editor ) {
				// Insert the shortcode into the TinyMCE editor
				editor.execCommand( 'mceInsertContent', false, shortcode );
			}
		}
	} );

	// Default template content
	let templates = [
		"[order_details]\n" +
		"<table>\n" +
		"    <tbody>\n" +
		"        <tr><th>Billing address</th><th>Shipping address</th></tr>\n" +
		"        <tr><td>[order_billing_address]</td><td>[order_shipping_address]</td></tr>\n" +
		"    </tbody>\n" +
		"</table>",
	];

	// Reset default template content
	$( '#wpfactory_wc_custom_emails_content_template_0' ).on( 'click', function ( event ) {
		event.preventDefault();

		const editor_container = $( this ).closest( '.wpfactory-wc-ce-editor' );
		const editor_id = editor_container.find( '.wp-editor-area' ).attr( 'id' );

		if ( typeof tinyMCE !== "undefined" ) {
			const editor = tinyMCE.get( editor_id );

			if ( editor ) {
				editor.setContent( templates[0] );
				$( `#${editor_id}` ).trigger( 'input' );
			}
		}

		const text_area = $( `#${editor_id}` );
		text_area.val( templates[0] );

		return false;
	} );

	// Listen for changes in TinyMCE editor (Visual Editor)
	if ( typeof tinyMCE !== "undefined" ) {
		const editor_container = $( '.wpfactory-wc-ce-editor' );
		const editor_id = editor_container.find( '.wp-editor-area' ).attr( 'id' );
		const editor = tinyMCE.get( editor_id );

		if ( editor ) {
			editor.on( 'Change', function () {
				$( `#${editor_id}` ).trigger( 'input' );
			} );
		}
	}

	// Filter items in the dropdown shortcode list.
	$( document ).on( 'keyup', '.wpfactory-wc-ce-shortcode-search', function () {
		let filter = $( this ).val().toLowerCase();
		$( this ).closest( '.wpfactory-wc-ce-shortcode-list' ).find( 'li' ).filter( function () {
			$( this ).toggle( $( this ).text().toLowerCase().indexOf( filter ) > - 1 );
		} );
	} );

} );
