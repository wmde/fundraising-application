'use strict';

var objectAssign = require( 'object-assign' ),

	/**
	 * View Handler for showing and hiding elements if the update value matches a regular expression
	 * @class
	 */
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
	},

	ElementHideAnimator = {
		el: null,

		showElement: function () {
			this.el.show();
		},
		hideElement: function () {
			this.el.hide();
		}
	};

function createRegexIfNeeded( showOnValue  ) {
	if ( !( showOnValue instanceof RegExp ) ) {
		showOnValue = new RegExp( '^' + showOnValue + '$' );
	}
	return showOnValue;
}

/**
 *
 * @param {string|RegExp} showOnValue - Show element if the value is equal to the string or matches the regex
 * @param {Object} animator Object with showElement and hideElement methods
 * @return {VisibilitySwitcher}
 */
function createElementCustomVisibilityHandler( showOnValue, animator ) {
	return objectAssign(
		Object.create( VisibilitySwitcher ),
		{
			showOnValueMatchRegex: createRegexIfNeeded( showOnValue ),
			animator: animator
		}
	);
}

module.exports = {
	/**
	 *
	 * @param {jQuery} element
	 * @param {string|RegExp} showOnValue - Show element if the value is equal to the string or matches the regex
	 * @return {VisibilitySwitcher}
	 */
	createElementSlideAnimationHandler: function ( element, showOnValue ) {
		return createElementCustomVisibilityHandler( showOnValue, objectAssign(
			Object.create( ElementSlideAnimator ),
			{ el: element }
		) );
	},

	/**
	 *
	 * @param {jQuery} element
	 * @param {string|RegExp} showOnValue - Show element if the value is equal to the string or matches the regex
	 * @return {VisibilitySwitcher}
	 */
	createElementVisibilityHandler: function ( element, showOnValue ) {
		return createElementCustomVisibilityHandler( showOnValue, objectAssign(
			Object.create( ElementHideAnimator ),
			{ el: element }
		) );
	},

	createElementCustomVisibilityHandler: createElementCustomVisibilityHandler
};
