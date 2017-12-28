'use strict';

var _ = require( 'underscore' );

module.exports = {
	mapFromSelectOptions: function ( $select ) {
		var map = {};
		_.each( $select.find( 'option' ).get(), function ( option ) {
			option = $( option );
			map[ option.attr( 'value' ) ] = option.text().trim();
		} );
		return map;
	},
	mapFromRadioLabels: function ( $container ) {
		var map = {};
		_.each( $container.find( 'input[type="radio"]' ).get(), function ( input ) {
			input = $( input );
			map[ input.attr( 'value' ) ] = input.next( 'label' ).text().trim();
		} );
		return map;
	},
	mapFromRadioLabelsShort: function ( $container ) {
		var map = {};
		_.each( $container.find( 'input[type="radio"]' ).get(), function ( input ) {
			input = $( input );
			map[ input.attr( 'value' ) ] = input.next( 'label' ).data( 'short-text' ).trim();
		} );
		return map;
	},
	/**
	 * Seemingly excess selectors to make sure not to select extra (hidden) form fields (cp. SuboptionDisplayHandler)
	 */
	mapFromRadioInfoTexts: function ( $container ) {
		var map = {};
		_.each( $container.find( '.wrap-input input[type="radio"]' ).get(), function ( input ) {
			input = $( input );
			map[ input.attr( 'value' ) ] = input.parents( '.wrap-field' ).data( 'info-text' ).trim();
		} );
		return map;
	}
};
