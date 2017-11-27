'use strict';

module.exports = {
	mapFromSelectOptions: function( $select ) {
		var map = {};
		$( 'option', $select ).each( function( i, option ) {
			option = $( option );
			map[ option.attr( 'value' ) ] = option.text();
		} );
		return map;
	},
	mapFromLabeledRadios: function( $container ) {
		var map = {};
		$( 'input', $container ).each( function( i, input ) {
			input = $( input );
			map[ input.attr( 'value' ) ] = input.next( 'label' ).text();
		} );
		return map;
	},
	/**
	 * Seemingly excess selectors to make sure not to select extra (hidden) form fields (cp. SuboptionDisplayHandler)
	 */
	mapFromRadioInfoTexts: function( $container ) {
		var map = {};
		$( '.wrap-input input', $container ).each( function( i, input ) {
			input = $( input );
			map[ input.attr( 'value' ) ] = input.parents( '.wrap-field' ).data( 'info-text' );
		} );
		return map;
	}
};
