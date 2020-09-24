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
					v-if="addressTypeIsNotAnon && addressTypeSelected"
					:show-error="fieldErrors"
					:form-data="formData"
					:address-type="addressType"
					v-on:field-changed="onFieldChange"/>
			<postal
					v-if="addressTypeIsNeitherAnonNorEmail && addressTypeSelected"
					:show-error="fieldErrors"
					:form-data="formData"
					:countries="countries"
					:post-code-validation="addressValidationPatterns.postcode"
					v-on:field-changed="onFieldChange"/>
			<receipt-opt-out
					:message="$t( 'receipt_needed_donation_page' )"
					:initial-receipt-needed="receiptNeeded"
					v-if="addressTypeIsNeitherAnonNorEmail && addressTypeSelected"
					v-on:opted-out="setReceiptOptedOut( $event )"/>
			<email
					v-if="addressTypeIsNotAnon && addressTypeSelected"
					:show-error="fieldErrors.email"
					:form-data="formData"
					v-on:field-changed="onFieldChange"/>
			<newsletter-opt-in v-if="addressTypeIsNotAnon && addressTypeSelected"></newsletter-opt-in>
		</AutofillHandler>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import AutofillHandler from '@/components/shared/AutofillHandler.vue';
import ProvisionalAddressType from '@/components/pages/donation_form/ProvisionalAddressType.vue';
import AddressSwitchDonorType from '@/components/pages/donation_form/ProvisionalAddressType.vue';
import Name from '@/components/shared/Name.vue';
import Postal from '@/components/shared/Postal.vue';
import ReceiptOptOut from '@/components/shared/ReceiptOptOut.vue';
import Email from '@/components/shared/Email.vue';
import NewsletterOptIn from '@/components/pages/donation_form/NewsletterOptIn.vue';
import { mapGetters } from 'vuex';
import { AddressValidity, AddressFormData, ValidationResult } from '@/view_models/Address';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';
import { Validity } from '@/view_models/Validity';
import { NS_ADDRESS } from '@/store/namespaces';
import {
	validateAddressField,
	setAddressField,
	validateAddress,
	validateEmail,
	setReceiptOptOut,
	setAddressType,
	validateAddressType,
} from '@/store/address/actionTypes';
import { action } from '@/store/util';
import PaymentBankData from '@/components/shared/PaymentBankData.vue';
import { mergeValidationResults } from '@/merge_validation_results';
import { camelizeName } from '@/camlize_name';
import { Country } from '@/view_models/Country';
import { AddressValidation } from '@/view_models/Validation';

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
	data: function (): { formData: AddressFormData } {
		return {
			formData: {
				salutation: {
					name: 'salutation',
					value: '',
					pattern: this.$props.addressValidationPatterns.salutation,
					optionalField: false,
				},
				title: {
					name: 'title',
					value: '',
					pattern: this.$props.addressValidationPatterns.title,
					optionalField: true,
				},
				companyName: {
					name: 'companyName',
					value: '',
					pattern: this.$props.addressValidationPatterns.companyName,
					optionalField: false,
				},
				firstName: {
					name: 'firstName',
					value: '',
					pattern: this.$props.addressValidationPatterns.firstName,
					optionalField: false,
				},
				lastName: {
					name: 'lastName',
					value: '',
					pattern: this.$props.addressValidationPatterns.lastName,
					optionalField: false,
				},
				street: {
					name: 'street',
					value: '',
					pattern: this.$props.addressValidationPatterns.street,
					optionalField: false,
				},
				city: {
					name: 'city',
					value: '',
					pattern: this.$props.addressValidationPatterns.city,
					optionalField: false,
				},
				postcode: {
					name: 'postcode',
					value: '',
					pattern: this.$props.addressValidationPatterns.postcode,
					optionalField: false,
				},
				country: {
					name: 'country',
					value: 'DE',
					pattern: this.$props.addressValidationPatterns.country,
					optionalField: false,
				},
				email: {
					name: 'email',
					value: '',
					pattern: this.$props.addressValidationPatterns.email,
					optionalField: false,
				},
			},
		};
	},
	props: {
		validateAddressUrl: String,
		validateEmailUrl: String,
		validateBankDataUrl: String,
		validateLegacyBankDataUrl: String,
		countries: Array as () => Array<Country>,
		isDirectDebit: Boolean,
		addressValidationPatterns: Object as () => AddressValidation,
	},
	computed: {
		fieldErrors: {
			get: function (): AddressValidity {
				return Object.keys( this.formData ).reduce( ( validity: AddressValidity, fieldName: string ) => {
					if ( !this.formData[ fieldName ].optionalField ) {
						validity[ fieldName ] = this.$store.state.address.validity[ fieldName ] === Validity.INVALID;
					}
					return validity;
				}, ( {} as AddressValidity ) );
			},
		},
		disabledAddressTypes: {
			get: function (): Array<AddressTypeModel> {
				return this.$store.getters[ 'payment/isDirectDebitPayment' ] ? [ AddressTypeModel.ANON ] : [];
			},
		},
		...mapGetters( NS_ADDRESS, [
			'addressType',
			'addressTypeIsNotAnon',
			'addressTypeIsNeitherAnonNorEmail',
			'addressTypeIsInvalid',
		] ),
		addressTypeName(): string {
			return addressTypeName( this.$store.state.address.addressType );
		},
		receiptNeeded(): Boolean {
			return !this.$store.state.address.receiptOptOut;
		},
		addressTypeSelected(): Boolean {
			return this.$store.state.AddressType !== AddressTypeModel.UNSET;
		},
	},
	mounted() {
		Object.entries( this.$data.formData ).forEach( ( formItem ) => {
			const key: string = formItem[ 0 ];
			this.$data.formData[ key ].value = this.$store.state.address.values[ key ];
			if ( this.$store.state[ NS_ADDRESS ].validity[ key ] === Validity.RESTORED ) {
				this.$store.dispatch( action( NS_ADDRESS, validateAddressField ), this.$data.formData[ key ] );
			}
		} );
	},
	methods: {
		validateForm(): Promise<ValidationResult> {
			return Promise.all( [
				this.$store.dispatch( action( NS_ADDRESS, validateAddressType ), this.$store.state.address.addressType ),
				this.$store.dispatch( action( NS_ADDRESS, validateAddress ), this.$props.validateAddressUrl ),
				this.$store.dispatch( action( NS_ADDRESS, validateEmail ), this.$props.validateEmailUrl ),
			] ).then( mergeValidationResults );
		},
		onFieldChange( fieldName: string ): void {
			this.$store.dispatch( action( NS_ADDRESS, setAddressField ), this.$data.formData[ fieldName ] );
		},
		onAutofill( autofilledFields: { [key: string]: string; } ) {
			Object.keys( autofilledFields ).forEach( key => {
				const fieldName = camelizeName( key );
				if ( this.$data.formData[ fieldName ] ) {
					this.$store.dispatch( action( NS_ADDRESS, setAddressField ), this.$data.formData[ fieldName ] );
				}
			} );
		},
		setReceiptOptedOut( optedOut: boolean ): void {
			this.$store.dispatch( action( NS_ADDRESS, setReceiptOptOut ), optedOut );
		},
		setAddressType( addressType: AddressTypeModel ): void {
			this.$store.dispatch( action( NS_ADDRESS, setAddressType ), addressType );
		},
	},
} );
</script>
