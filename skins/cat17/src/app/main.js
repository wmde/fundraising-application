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
	StateAggregation: {
		Donation: {
			allValiditySectionsAreValid: require( './lib/state_aggregation/donation/all_validation_sections_are_valid' ),
			amountAndFrequencyAreValid: require( './lib/state_aggregation/donation/amount_and_frequency_are_valid' ),
			donorTypeAndAddressAreValid: require( './lib/state_aggregation/donation/donor_type_and_address_are_valid' ),
			paymentAndBankDataAreValid: require( './lib/state_aggregation/donation/payment_and_bank_data_are_valid' ),
			formIsPrefilled: require( './lib/state_aggregation/donation/form_is_prefilled' ),
			salutationIsValid: require( './lib/state_aggregation/donation/salutation_is_valid' )
		},
		Membership: {
			allValiditySectionsAreValid: require( './lib/state_aggregation/membership/all_validation_sections_are_valid' ),
			membershipTypeIsValid: require( './lib/state_aggregation/membership/membership_type_is_valid' ),
			amountAndFrequencyAreValid: require( './lib/state_aggregation/membership/amount_and_frequency_are_valid' ),
			donorTypeAndAddressAreValid: require( './lib/state_aggregation/membership/donor_type_and_address_are_valid' ),
			paymentAndBankDataAreValid: require( './lib/state_aggregation/membership/payment_and_bank_data_are_valid' ),
			salutationIsValid: require( './lib/state_aggregation/membership/salutation_is_valid' )
		},
		DonorUpdate: {
			donorTypeAndAddressAreValid: require( './lib/state_aggregation/donorUpdate/donor_type_and_address_are_valid' ),
			salutationIsValid: require( './lib/state_aggregation/donorUpdate/salutation_is_valid' )
		}
	},
	View: {
		createSlidingVisibilitySwitcher: require( './lib/view_handler/element_visibility_switcher' ).createSlidingVisibilitySwitcher,
		createSimpleVisibilitySwitcher: require( './lib/view_handler/element_visibility_switcher' ).createSimpleVisibilitySwitcher,
		createElementClassSwitcher: require( './lib/view_handler/element_class_switcher' ).createElementClassSwitcher,
		createFeeOptionSwitcher: require( './lib/view_handler/fee_option_switcher' ).createFeeOptionSwitcher,
		createFieldValueValidityIndicator: require( './lib/view_handler/field_value_validity_indicator' ).createFieldValueValidityIndicator,
		createCountrySpecificAttributesHandler: require( './lib/view_handler/country_specific_attributes' ).createCountrySpecificAttributesHandler,
		createSuboptionDisplayHandler: require( './lib/view_handler/display_field_suboptions' ).createSuboptionDisplayHandler,
		createCustomAmountField: require( './lib/view_handler/custom_amount_field' ).createCustomAmountField,
		createShySubmitButtonHandler: require( './lib/view_handler/shy_submit_button' ).createShySubmitButtonHandler,
		SectionInfo: require( './lib/view_handler/section_info/main' ),
		Animator: require( './lib/view_handler/animator' )
	},
	Actions: require( './lib/actions' ),
	FormDataExtractor: require( './lib/form_data_extractor' ),
	IntegerCurrency: require( './lib/integer_currency' ),
	Scrolling: require( './lib/scrolling' ),
	BankData: require( './components/BankData.vue' ).default,
	Vue: require( 'vue' ).default,
	VueTranslate: require( 'vue-translate-plugin' ),
	VueRedux: require( 'redux-vue' )
};
