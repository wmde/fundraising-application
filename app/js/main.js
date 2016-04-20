// main module to expose all submodules
/**
 * Uppercase keys designate namespaces, lowercase keys designate global objects/functions
 */
module.exports = {
	FormValidation: require( './lib/form_validation' ),
	ReduxValidation: require( './lib/redux_validation' ),
	Components: require( './lib/form_components' ),
	Store: require( './lib/store' ),
	StoreUpdates: require( './lib/store_update_handling' ),
	View: {
		createClearAmountHandler: require( './lib/view_handler/clear_amount' ).createHandler,
		createElementSlideAnimationHandler: require( './lib/view_handler/element_visibility_switcher' ).createElementSlideAnimationHandler,
		createElementVisibilityHandler: require( './lib/view_handler/element_visibility_switcher' ).createElementVisibilityHandler,
		createErrorBoxHandler:  require( './lib/view_handler/error_box' ).createHandler,
		createFormPageVisibilityHandler: require( './lib/view_handler/form_page_visibility' ).createHandler
	},
	Actions: require( './lib/actions' )
};
