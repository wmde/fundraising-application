'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	/**
	 * Create a widget instance with all properties set-up
	 *
	 * @param {string} type
	 * @param {jQuery} widgetNode A HTML node representing a widget
	 * @param {Object} valueIconMap Mapping of value to icon
	 * @param {Object} valueTextMap Mapping of value to text
	 * @param {Object} valueLongTextMap Mapping of value to longText
	 * @param {Object} additionalDependencies Additional properties that will be merged into the instance of type
	 * @return {SectionInfo} or a child
	 */
	createInstance = function ( type, widgetNode, valueIconMap, valueTextMap, valueLongTextMap, additionalDependencies ) {
		return objectAssign(
			Object.create( type ),
			{
				container: widgetNode,

				// calculate and cache elements
				icon: widgetNode.find( 'i:not(".link")' ),
				text: widgetNode.find( '.text' ),
				longText: widgetNode.find( '.info-detail' ),

				valueIconMap: valueIconMap,
				valueTextMap: valueTextMap,
				valueLongTextMap: valueLongTextMap
			},
			additionalDependencies
		);
	},

	/**
	 * Proxy that can take DOM `containers` describing widgets, maps them to one widget instance each, forward calls to them
	 *
	 * We still use jQuery as the selector engine for sub-elements. Possible todo
	 *
	 * @param {string} type
	 * @param {jQuery} containers A list of HTML node representing a widget (matched by the same selector)
	 * @param {Object} valueIconMap Mapping of value to icon
	 * @param {Object} valueTextMap Mapping of value to text
	 * @param {Object} valueLongTextMap Mapping of value to longText
	 * @param {Object} additionalDependencies Additional properties that will be merged into the instance of type
	 * @return {SectionInfo} or a child
	 */
	createProxy = function ( type, containers, valueIconMap, valueTextMap, valueLongTextMap, additionalDependencies ) {
		var widgets = [];
		_.each( containers.get(), function ( container ) {
			widgets.push( createInstance( type, jQuery( container ), valueIconMap, valueTextMap, valueLongTextMap, additionalDependencies ) );
		} );

		return objectAssign( {
			widgets: widgets,
			update: function () {
				var originalArgs = arguments;
				// There is no _.apply unfortunately and _.invoke can't pass `arguments`
				_.each( this.widgets, function ( widget ) {
					widget.update.apply( widget, originalArgs );
				} );
			}
		} );
	}
;

module.exports = {
	createInstance: createInstance,
	createProxy: createProxy
};
