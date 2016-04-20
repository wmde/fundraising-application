'use strict';

var objectAssign = require( 'object-assign' ),

	VisibilitySwitcher = {
		showOnValueMatchRegex: null,
		animator: null,

		isHidden: null, // Null to always trigger show/hide on first call to update

		update: function ( value ) {
			if ( this.showOnValueMatchRegex.test( value ) ) {
				if ( this.isHidden === false ) {
					return;
				}
				this.animator.showElement();
				this.isHidden = false;
			} else {
				if ( this.isHidden === true ) {
					return;
				}
				this.animator.hideElement();
				this.isHidden = true;
			}
		}
	},
	
	/**
	 * View Handler for showing and hiding elements if the update value matches a regular expression
	 * @class
	 */
	ElementSlideAnimator = {
		el: null,

		// internal fields
		slideSpeed: 600,

		showElement: function () {
			this.el
				.slideDown( this.slideSpeed )
				.animate(
					{ opacity: 1 },
					{ queue: false, duration: this.slideSpeed }
				);
		},
		hideElement: function () {
			this.el
				.slideUp( this.slideSpeed )
				.animate(
					{ opacity: 0 },
					{ queue: false, duration: this.slideSpeed }
				);
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
		return objectAssign(
			Object.create( VisibilitySwitcher ),
			{
				showOnValueMatchRegex: showOnValue,
				animator:  objectAssign(
					Object.create( ElementSlideAnimator ),
					{ el: element }
				)
			}
		);
	}
};
