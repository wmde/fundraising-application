// main module to expose all submodules
module.exports = {
	FormData: require( './lib/form_data' ),
	FormValidation: require( './lib/form_validation' ),
	FormState: require( './lib/store' ),
	View: {
		createEnabledWhenValidHandler: require( './lib/view_handler/enabled_when_valid' ).createHandler,
		createFormPageVisibilityHandler: require( './lib/view_handler/form_page_visibility' ).createHandler,
		createFormPageHighlightHandler: require( './lib/view_handler/form_page_highlight' ).createHandler
	},
	Actions: require( './lib/actions' )
};
