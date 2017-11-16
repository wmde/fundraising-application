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
	/**
	 * Seemingly excess selectors to make sure
	 * - to select correct inputs of mix of different radio buttons for payment interval (interval type vs period)
	 */
	mapFromLabeledRadios: function( $container ) {
		var map = {};
		$( 'input:not(.hidden)', $container ).each( function( i, input ) {
			input = $( input );
			map[ input.attr( 'value' ) ] = input.next( 'label' ).text();
		} );
		return map;
	},
	/**
	 * Seemingly excess selectors to make sure
	 * - not to select extra (hidden) form fields (cp. SuboptionDisplayHandler)
	 * - to select correct inputs of mix of different radio buttons for payment interval (interval type vs period)
	 */
	mapFromRadioInfoTexts: function( $container ) {
		var map = {};
		$( '.wrap-input input:not(.hidden)', $container ).each( function( i, input ) {
			input = $( input );
			map[ input.attr( 'value' ) ] = input.parents( '.wrap-field' ).data( 'info-text' );
		} );
		return map;
	}
};
