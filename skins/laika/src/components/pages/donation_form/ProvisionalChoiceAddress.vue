<template>
	<div class="address-section">
		<AutofillHandler @autofill="onAutofill" >
			<payment-bank-data v-if="isDirectDebit" :validateBankDataUrl="validateBankDataUrl" :validateLegacyBankDataUrl="validateLegacyBankDataUrl"></payment-bank-data>
		</AutofillHandler>
		<provisional-address-type
				v-on:address-type="setAddressType( $event )"
				:disabledAddressTypes="disabledAddressTypes"
				:is-direct-debit="isDirectDebit"
				:initial-address-type="addressTypeName"/>
		<span
				v-if="addressTypeIsInvalid"
				class="help is-danger">{{ $t( 'donation_form_section_address_error' ) }}
		</span>
		<div
				class="has-margin-top-18"
				v-show="!addressTypeIsNotAnon">{{ $t( 'donation_addresstype_option_anonymous_disclaimer' ) }}
		</div>
		<AutofillHandler @autofill="onAutofill">
			<name
					v-if="showName"
					:show-error="fieldErrors"
					:form-data="formData"
					:address-type="addressType"
					v-on:field-changed="onFieldChange"/>
			<postal
					v-if="showPostal"
					:show-error="fieldErrors"
					:form-data="formData"
					:countries="countries"
					:post-code-validation="addressValidationPatterns.postcode"
					v-on:field-changed="onFieldChange"/>
			<receipt-opt-out
					:message="$t( 'receipt_needed_donation_page' )"
					:initial-receipt-needed="receiptNeeded"
					v-if="showPostal"
					v-on:opted-out="setReceiptOptedOut( $event )"/>
			<email
					v-if="showEmail"
					:show-error="fieldErrors.email"
					:form-data="formData"
					v-on:field-changed="onFieldChange"/>
			<newsletter-opt-in v-if="showPostal"></newsletter-opt-in>
		</AutofillHandler>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { computed, onMounted, PropType } from '@vue/composition-api';
import AutofillHandler from '@/components/shared/AutofillHandler.vue';
import ProvisionalAddressType from '@/components/pages/donation_form/ProvisionalAddressType.vue';
import AddressSwitchDonorType from '@/components/pages/donation_form/ProvisionalAddressType.vue';
import Name from '@/components/shared/Name.vue';
import Postal from '@/components/shared/Postal.vue';
import ReceiptOptOut from '@/components/shared/ReceiptOptOut.vue';
import Email from '@/components/shared/Email.vue';
import NewsletterOptIn from '@/components/pages/donation_form/NewsletterOptIn.vue';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { Validity } from '@/view_models/Validity';
import { NS_ADDRESS } from '@/store/namespaces';
import { validateAddressField } from '@/store/address/actionTypes';
import { action } from '@/store/util';
import PaymentBankData from '@/components/shared/PaymentBankData.vue';
import { Country } from '@/view_models/Country';
import { AddressValidation } from '@/view_models/Validation';
import { useAddressFunctions } from './AddressFunctions';

export default Vue.extend( {
	name: 'Address',
	components: {
		Name,
		Postal,
		ProvisionalAddressType,
		AddressSwitchDonorType,
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
	setup( props : any, { root: { $store } } ) {
		const addressFunctions = useAddressFunctions( props, $store );

		const showPostal = computed( () => [ AddressTypeModel.COMPANY, AddressTypeModel.PERSON ].includes( $store.state.address.addressType ) );
		const showEmail = computed( () => [ AddressTypeModel.EMAIL, AddressTypeModel.COMPANY, AddressTypeModel.PERSON ].includes( $store.state.address.addressType ) );
		const showName = computed( () => [ AddressTypeModel.EMAIL, AddressTypeModel.COMPANY, AddressTypeModel.PERSON ].includes( $store.state.address.addressType ) );

		onMounted( () => {
			Object.entries( addressFunctions.formData ).forEach( ( formItem ) => {
				const key: string = formItem[ 0 ];
				addressFunctions.formData[ key ].value = $store.state.address.values[ key ];
				if ( $store.state[ NS_ADDRESS ].validity[ key ] === Validity.RESTORED ) {
					$store.dispatch( action( NS_ADDRESS, validateAddressField ), addressFunctions.formData[ key ] );
				}
			} );
		} );

		return { ...addressFunctions,
			showName,
			showPostal,
			showEmail,
		};
	},
} );
</script>
