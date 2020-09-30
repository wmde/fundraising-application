<template>
	<div class="address-section">
		<AutofillHandler @autofill="onAutofill" >
			<payment-bank-data v-if="isDirectDebit" :validateBankDataUrl="validateBankDataUrl" :validateLegacyBankDataUrl="validateLegacyBankDataUrl"></payment-bank-data>
		</AutofillHandler>
		<address-type
				v-on:address-type="setAddressType( $event )"
				:disabledAddressTypes="disabledAddressTypes"
				:is-direct-debit="isDirectDebit"
				:initial-address-type="addressTypeName">
		</address-type>
		<span
				v-if="addressTypeIsInvalid"
				class="help is-danger">{{ $t( 'donation_form_section_address_error' ) }}
		</span>
		<div
				class="has-margin-top-18"
				v-show="!addressTypeIsNotAnon">{{ $t( 'donation_addresstype_option_anonymous_disclaimer' ) }}
		</div>
		<AutofillHandler @autofill="onAutofill" >
			<name
					v-if="addressTypeIsNotAnon"
					:show-error="fieldErrors"
					:form-data="formData"
					:address-type="addressType"
					v-on:field-changed="onFieldChange"/>
			<postal
					v-if="addressTypeIsNotAnon"
					:show-error="fieldErrors"
					:form-data="formData"
					:countries="countries"
					:post-code-validation="addressValidationPatterns.postcode"
					v-on:field-changed="onFieldChange"/>
			<receipt-opt-out
					:message="$t( 'receipt_needed_donation_page' )"
					:initial-receipt-needed="receiptNeeded"
					v-if="addressTypeIsNotAnon"
					v-on:opted-out="setReceiptOptedOut( $event )"/>
			<email
					v-if="addressTypeIsNotAnon"
					:show-error="fieldErrors.email"
					:form-data="formData"
					v-on:field-changed="onFieldChange"/>
			<newsletter-opt-in v-if="addressTypeIsNotAnon"></newsletter-opt-in>
		</AutofillHandler>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { PropType, onMounted } from '@vue/composition-api';
import AutofillHandler from '@/components/shared/AutofillHandler.vue';
import AddressType from '@/components/pages/donation_form/AddressType.vue';
import Name from '@/components/shared/Name.vue';
import Postal from '@/components/shared/Postal.vue';
import ReceiptOptOut from '@/components/shared/ReceiptOptOut.vue';
import Email from '@/components/shared/Email.vue';
import NewsletterOptIn from '@/components/pages/donation_form/NewsletterOptIn.vue';
import PaymentBankData from '@/components/shared/PaymentBankData.vue';
import { Country } from '@/view_models/Country';
import { AddressValidation } from '@/view_models/Validation';
import { useAddressFunctions } from '@/components/pages/donation_form/AddressFunctions';
import { NS_ADDRESS } from '@/store/namespaces';
import { validateAddressField } from '@/store/address/actionTypes';
import { action } from '@/store/util';
import { Validity } from '@/view_models/Validity';

export default Vue.extend( {
	name: 'Address',
	components: {
		Name,
		Postal,
		AddressType,
		ReceiptOptOut,
		Email,
		NewsletterOptIn,
		PaymentBankData,
		AutofillHandler,
	},
	props: {
		validateAddressUrl: String,
		validateEmailUrl: String,
		validateBankDataUrl: String,
		validateLegacyBankDataUrl: String,
		countries: Array as PropType<Array<Country>>,
		isDirectDebit: Boolean,
		addressValidationPatterns: Object as PropType<AddressValidation>,
	},
	// TODO properly type 'props'
	setup( props : any, { root: { $store } } ) {

		const addressFunctions = useAddressFunctions( props, $store );

		onMounted( () => {
			Object.entries( addressFunctions.formData ).forEach( ( formItem ) => {
				const key: string = formItem[ 0 ];
				addressFunctions.formData[ key ].value = $store.state.address.values[ key ];
				if ( $store.state[ NS_ADDRESS ].validity[ key ] === Validity.RESTORED ) {
					$store.dispatch( action( NS_ADDRESS, validateAddressField ), addressFunctions.formData[ key ] );
				}
			} );
		} );

		return { ...addressFunctions };
	},
} );
</script>
