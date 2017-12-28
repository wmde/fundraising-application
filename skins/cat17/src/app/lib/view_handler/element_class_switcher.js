'use strict';

var elementVisibilitySwitcher = require( './element_visibility_switcher' );

module.exports = {
	/**
	 * @param {jQuery} element
	 * @param {string|RegExp} showOnValue - Show element if the value is equal to the string or matches the regex
	 * @param {string} className - Class name(s) to apply when the condition holds true
	 * @return {VisibilitySwitcher}
	 */
	createElementClassSwitcher: function ( element, showOnValue, className ) {
		return elementVisibilitySwitcher.createCustomVisibilitySwitcher(
			showOnValue,
			{
				showElement: function () {
					element.addClass( className );
				},
				hideElement: function () {
					element.removeClass( className );
				}
			}
		);
	}
};
