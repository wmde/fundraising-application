// main module to expose all submodules
/**
 * Uppercase keys designate namespaces, lowercase keys designate global objects/functions
 */
module.exports = {
	FormValidation: require( './lib/form_validation' ),
	ValidationDispatchers: require( './lib/validation_dispatchers' ),
	Components: require( './lib/form_components' ),
	Store: require( './lib/store' ),
	StoreUpdates: require( './lib/store_update_handling' ),
	View: {
		createDisplayAddressHandler: require( './lib/view_handler/display_address' ).createDisplayAddressHandler,
		createBankDataDisplayHandler: require( './lib/view_handler/display_bank_data' ).createBankDataDisplayHandler,
		createSlidingVisibilitySwitcher: require( './lib/view_handler/element_visibility_switcher' ).createSlidingVisibilitySwitcher,
		createSimpleVisibilitySwitcher: require( './lib/view_handler/element_visibility_switcher' ).createSimpleVisibilitySwitcher,
		createErrorBoxHandler:  require( './lib/view_handler/error_box' ).createHandler,
		createFormPageVisibilityHandler: require( './lib/view_handler/form_page_visibility' ).createHandler,
		createPaymentSummaryDisplayHandler: require( './lib/view_handler/display_payment_summary' ).createPaymentSummaryDisplayHandler,
		createFeeOptionSwitcher: require( './lib/view_handler/fee_option_switcher' ).createFeeOptionSwitcher,
		createFieldValueValidityIndicator: require( './lib/view_handler/field_value_validity_indicator' ).createFieldValueValidityIndicator,
		createCountrySpecificAttributesHandler: require( './lib/view_handler/country_specific_attributes' ).createCountrySpecificAttributesHandler,
		createRecurrentPaypalNoticeHandler: require( './lib/view_handler/recurrent_paypal_notice' ).createRecurrentPaypalNoticeHandler,
		createSuboptionDisplayHandler: require( './lib/view_handler/display_field_suboptions' ).createSuboptionDisplayHandler,
		Animator: require( './lib/view_handler/animator' )
	},
	Actions: require( './lib/actions' ),
	CurrencyFormatter: require( './lib/simple_currency_formatter' ),
	createInitialStateFromViolatedFields: require( './lib/validation_conversion' ).createInitialStateFromViolatedFields
};

