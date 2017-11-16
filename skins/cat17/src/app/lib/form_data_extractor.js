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
		$( 'input:not(.hidden)', $container ).each( function( i, input ) {
			input = $( input );
			map[ input.attr( 'value' ) ] = input.next( 'label' ).text();
		} );
		return map;
	},
	mapFromRadioInfoTexts: function( $container ) {
		var map = {};
		$( 'input:not(.hidden)', $container ).each( function( i, input ) {
			input = $( input );
			map[ input.attr( 'value' ) ] = input.parents( '.wrap-field' ).find( '.info-text' ).text().trim();
		} );
		return map;
	}
};
