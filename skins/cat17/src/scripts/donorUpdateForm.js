$( function() {
	/** global: WMDE */

	var initData = $( '#init-form' ),
		store = WMDE.Store.createDonorUpdateStore(),
		actions = WMDE.Actions
	;

	WMDE.StoreUpdates.connectComponentsToStore(
		[
			WMDE.Components.createRadioComponent( store, $( 'input[name="addressType"]' ), 'addressType' ),
			WMDE.Components.createSelectMenuComponent( store, $( 'select[name="salutation"]' ), 'salutation' ),
			WMDE.Components.createSelectMenuComponent( store, $( '#title' ), 'title' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#first-name' ), 'firstName' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#last-name' ), 'lastName' ),
			WMDE.Components.addEagerChangeBehavior( WMDE.Components.createValidatingTextComponent( store, $( '#company-name' ), 'companyName' ) ),
			WMDE.Components.createValidatingTextComponent( store, $( '#street' ), 'street' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#adress-company' ), 'street' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#post-code' ), 'postcode' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#post-code-company' ), 'postcode' ),
			WMDE.Components.addEagerChangeBehavior( WMDE.Components.createValidatingTextComponent( store, $( '#city' ), 'city' ) ),
			WMDE.Components.addEagerChangeBehavior( WMDE.Components.createValidatingTextComponent( store, $( '#city-company' ), 'city' ) ),
			WMDE.Components.createSelectMenuComponent( store, $( '#country' ), 'country' ),
			WMDE.Components.createSelectMenuComponent( store, $( '#country-company' ), 'country' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#email' ), 'email' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#email-company' ), 'email' ),
			WMDE.Components.createCheckboxComponent( store, $( '#newsletter' ), 'confirmNewsletter' ),
			WMDE.Components.createCheckboxComponent( store, $( '#newsletter-company' ), 'confirmNewsletter' ),
			WMDE.Components.createCheckboxComponent( store, $( '#donation-receipt' ), 'donationReceipt' ),
			WMDE.Components.createCheckboxComponent( store, $( '#donation-receipt-company' ), 'donationReceipt' )
		],
		store,
		'donorUpdateFormContent'
	);

	WMDE.StoreUpdates.connectValidatorsToStore(
		function( initialValues ) {
			return [
				WMDE.ValidationDispatchers.createAddressValidationDispatcher(
					WMDE.FormValidation.createAddressValidator(
						initData.data( 'validate-address-url' ),
						WMDE.FormValidation.DefaultRequiredFieldsForAddressType
					),
					initialValues
				),
				WMDE.ValidationDispatchers.createEmailValidationDispatcher(
					WMDE.FormValidation.createEmailAddressValidator( initData.data( 'validate-email-address-url' ) ),
					initialValues
				)
			];
		},
		store,
		initData.data( 'initial-form-values' ),
		'donorUpdateFormContent'
	);

	// Connect view handlers to changes in specific parts in the global state, designated by 'stateKey'
	WMDE.StoreUpdates.connectViewHandlersToStore(
		[
			{
				viewHandler: WMDE.View.createSuboptionDisplayHandler(
					$( '#type-donor' )
				),
				stateKey: 'donorUpdateFormContent.addressType'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-salutation' ) ),
				stateKey: [WMDE.StateAggregation.DonorUpdate.salutationIsValid]
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-firstname' ) ),
				stateKey: 'donationInputValidation.firstName'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-lastname' ) ),
				stateKey: 'donationInputValidation.lastName'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-company' ) ),
				stateKey: 'donationInputValidation.companyName'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-street' ) ),
				stateKey: 'donationInputValidation.street'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-postcode' ) ),
				stateKey: 'donationInputValidation.postcode'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-city' ) ),
				stateKey: 'donationInputValidation.city'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-email' ) ),
				stateKey: 'donationInputValidation.email'
			},
			// TODO Remove this button and implement submission logic check
			{
				viewHandler: WMDE.View.createShySubmitButtonHandler( $( 'form input[type="submit"]' ) ),
				stateKey: 'validity.address'
			},
			{
				viewHandler: WMDE.View.SectionInfo.createDonorTypeSectionInfo(
					$( '.donor-type' ),
					{
						'person': 'icon-account_circle',
						'firma': 'icon-work'
					},
					WMDE.FormDataExtractor.mapFromRadioLabels( $( '#type-donor .wrap-input' ) ),
					WMDE.FormDataExtractor.mapFromSelectOptions( $( '#country' ) )
				),
				stateKey: [
					'donorUpdateFormContent.addressType',
					'donorUpdateFormContent.salutation',
					'donorUpdateFormContent.title',
					'donorUpdateFormContent.firstName',
					'donorUpdateFormContent.lastName',
					'donorUpdateFormContent.companyName',
					'donorUpdateFormContent.street',
					'donorUpdateFormContent.postcode',
					'donorUpdateFormContent.city',
					'donorUpdateFormContent.country',
					'donorUpdateFormContent.email',
					WMDE.StateAggregation.DonorUpdate.donorTypeAndAddressAreValid
				]
			},
			// Show house number warning
			{
				viewHandler: WMDE.View.createSimpleVisibilitySwitcher(
					$( '#street, #adress-company' ).nextAll( '.warning-text' ),
					/^\D+$/
				),
				stateKey: 'donorUpdateFormContent.street'
			},
			// Adjust height of address form field set
			{
				viewHandler: new WMDE.View.HeightAdjuster( $( '#type-donor' )  ),
				stateKey: [
					'donorUpdateFormContent.addressType',
					'donorUpdateFormContent.salutation',
					'donorUpdateFormContent.title',
					'donorUpdateFormContent.firstName',
					'donorUpdateFormContent.lastName',
					'donorUpdateFormContent.companyName',
					'donorUpdateFormContent.street',
					'donorUpdateFormContent.postcode',
					'donorUpdateFormContent.city',
					'donorUpdateFormContent.country',
					'donorUpdateFormContent.email',
					WMDE.StateAggregation.DonorUpdate.donorTypeAndAddressAreValid
				]
			}
		],
		store
	);

	// TODO Force-check address fields, see donationForm for reference
	$( 'form' ).on( 'submit', function() {
		return true;
	} );

	// Set initial form values
	var initSetup = initData.data( 'initial-form-values' );
	store.dispatch( actions.newInitializeContentAction( initSetup ) );

	// Set initial validation state
	store.dispatch( actions.newInitializeValidationStateAction(
		initData.data( 'violatedFields' ),
		initSetup,
		initData.data( 'initial-validation-result' )
	) );

	$( '#receipt-button' ).on( 'click', function() {
		$('#personal').click();
		var form = $( '.main-form' );
		form.show();
		$('html, body').animate({
			scrollTop: form.offset().top - $('.header').height()
		}, 1000);
	} );
} );
