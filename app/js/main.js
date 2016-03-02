// main module to expose all submodules
/**
 * Uppercase keys designate namespaces, lowercase keys designate global objects
 */
module.exports = {
	FormFieldAccessors: require( './lib/form_field_accessors' ),
	FormValidation: require( './lib/form_validation' ),
	ReduxValidation: require( './lib/redux_validation' ),
	Store: require( './lib/store' ),
	View: {
		createClearAmountHandler: require( './lib/view_handler/clear_amount' ).createHandler,
		createErrorBoxHandler:  require( './lib/view_handler/error_box' ).createHandler,
		createFormPageVisibilityHandler: require( './lib/view_handler/form_page_visibility' ).createHandler,
		createFormPageHighlightHandler: require( './lib/view_handler/form_page_highlight' ).createHandler
	},
	Actions: require( './lib/actions' )
};
