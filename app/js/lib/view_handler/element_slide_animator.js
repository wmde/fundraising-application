'use strict';

var objectAssign = require( 'object-assign' ),

	/**
	 * View Handler for showing and hiding elements if the update value matches a regular expression
	 * @class
	 */
	ElementSlideAnimator = {
		el: null,
		showOnValueMatchRegex: null,

		// internal fields
		slideSpeed: 600,
		isHidden: null, // Null to always trigger show/hide on first call to update

		update: function ( value ) {
			if ( this.showOnValueMatchRegex.test( value ) ) {
				this.showElement();
			} else {
				this.hideElement();
			}
		},
		showElement: function () {
			if ( this.isHidden === false ) {
				return;
			}
			this.el
				.slideDown( this.slideSpeed )
				.animate(
					{ opacity: 1 },
					{ queue: false, duration: this.slideSpeed }
				);
			this.isHidden = false;
		},
		hideElement: function () {
			if ( this.isHidden === true ) {
				return;
			}
			this.el
				.slideUp( this.slideSpeed )
				.animate(
					{ opacity: 0 },
					{ queue: false, duration: this.slideSpeed }
				);
			this.isHidden = true;
		}

	};

module.exports = {
	/**
	 *
	 * @param {jQuery} element
	 * @param {string|RegExp} showOnValue - Show element if the value is equal to the string or matches the regex
	 * @return {ElementSlideAnimator}
	 */
	createHandler: function ( element, showOnValue ) {
		if ( !( showOnValue instanceof RegExp ) ) {
			showOnValue = new RegExp( '^' + showOnValue + '$' );
		}
		return objectAssign( Object.create( ElementSlideAnimator ), {
			el: element,
			showOnValueMatchRegex: showOnValue
		} );
	}
};
