import { Validity } from '../validation/validation_states';

export class ErrorStateClass {
	constructor( $element ) {
		this.$element = $element;
	}
	update( value ) {
		if ( value === Validity.INVALID ) {
			this.$element.addClass( 'invalid' );
			return;
		}
		this.$element.removeClass( 'invalid' );
	}
}

export function createErrorStateClassHandler( $element ) {
	return new ErrorStateClass( $element );
}
