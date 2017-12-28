'use strict';

var objectAssign = require( 'object-assign' ),

	Animator = require( './animator' ),

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
	};

function createRegexIfNeeded( showOnValue ) {
	if ( !( showOnValue instanceof RegExp ) ) {
		showOnValue = new RegExp( '^' + showOnValue + '$' );
	}
	return showOnValue;
}

/**
 *
 * @param {string|RegExp} showOnValue - Show element if the value is equal to the string or matches the regex
 * @param {ElementAnimator} animator
 * @return {VisibilitySwitcher}
 */
function createCustomVisibilitySwitcher( showOnValue, animator ) {
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
	createSlidingVisibilitySwitcher: function ( element, showOnValue ) {
		return createCustomVisibilitySwitcher( showOnValue, Animator.createSlidingElementAnimator( element ) );
	},

	/**
	 *
	 * @param {jQuery} element
	 * @param {string|RegExp} showOnValue - Show element if the value is equal to the string or matches the regex
	 * @return {VisibilitySwitcher}
	 */
	createSimpleVisibilitySwitcher: function ( element, showOnValue ) {
		return createCustomVisibilitySwitcher( showOnValue, Animator.createSimpleElementAnimator( element ) );
	},

	createCustomVisibilitySwitcher: createCustomVisibilitySwitcher
};
