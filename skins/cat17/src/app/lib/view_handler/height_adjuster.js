/**
 * In the desktop view the fom field elements are absolutely positioned to create a two-column layout,
 * pulling them out of their container.
 *
 * This class adjusts the min-height of form field container elements
 *
 * TODO Replace this class with better HTML Markup and CSS Flexbox.
 */

const MINIMUM_HEIGHT = 285;

export class HeightAdjuster {
	constructor( $fieldset ) {
		this.$fieldset = $fieldset;
	}
	update() {
		this.$fieldset.css( 'min-height', 0 );
		const fieldsHeight = this.$fieldset.find( '.info-text.opened' ).prop( 'scrollHeight' );
		this.$fieldset.css( 'min-height', Math.max( MINIMUM_HEIGHT, fieldsHeight ) + 'px' );
	}
}

export default HeightAdjuster;
