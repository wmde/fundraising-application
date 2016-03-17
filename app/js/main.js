// main module to expose all submodules
/**
 * Uppercase keys designate namespaces, lowercase keys designate global objects/functions
 */
module.exports = {
	FormFieldAccessors: require( './lib/form_field_accessors' ),
	FormValidation: require( './lib/form_validation' ),
	ReduxValidation: require( './lib/redux_validation' ),
	Components: require( './lib/form_components' ),
	Store: require( './lib/store' ),
	StoreUpdates: require( './lib/store_update_handling' ),
	View: {
		createClearAmountHandler: require( './lib/view_handler/clear_amount' ).createHandler,
		createElementSliderHandler: require( './lib/view_handler/element_slide' ).createHandler,
		createErrorBoxHandler:  require( './lib/view_handler/error_box' ).createHandler,
		createFormPageVisibilityHandler: require( './lib/view_handler/form_page_visibility' ).createHandler
	},
	Actions: require( './lib/actions' )
};
