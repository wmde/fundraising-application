'use strict';

var objectAssign = require( 'object-assign' ),

	/**
	 * View Handler for switching to a default value if the current value is that of a hidden/disabled element
	 * @class
	 */
	DefaultValueSwitcher = {
		update: function () {
			if ( this.elementGroup.filter( ':checked' ).is( ':visible' ) === false ) {
				this.defaultElement.click();
			}
		}
	};

function createDefaultValueSwitcher( elementGroup, defaultElement ) {
	return objectAssign(
		Object.create( DefaultValueSwitcher ),
		{
			elementGroup: elementGroup,
			defaultElement: defaultElement
		}
	);
}

module.exports = {
	createDefaultValueSwitcher: createDefaultValueSwitcher
};
