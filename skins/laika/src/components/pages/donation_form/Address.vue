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
import { useStore } from 'vuex';
import { reactive, computed, onMounted, PropType } from '@vue/composition-api';
import store from '../../../store/address';
import AutofillHandler from '@/components/shared/AutofillHandler.vue';
import AddressType from '@/components/pages/donation_form/AddressType.vue';
import Name from '@/components/shared/Name.vue';
import Postal from '@/components/shared/Postal.vue';
import ReceiptOptOut from '@/components/shared/ReceiptOptOut.vue';
import Email from '@/components/shared/Email.vue';
import NewsletterOptIn from '@/components/pages/donation_form/NewsletterOptIn.vue';
import { mapGetters } from 'vuex';
import { AddressValidity, AddressFormData, ValidationResult, InputField } from '@/view_models/Address';
import { AddressTypeModel, addressTypeName as getAddressTypeName } from '@/view_models/AddressTypeModel';
import { Validity } from '@/view_models/Validity';
import { NS_ADDRESS } from '@/store/namespaces';
import {
	validateAddressField,
	setAddressField,
	validateAddress,
	validateEmail,
	setReceiptOptOut,
	setAddressType as setAddressTypeActionType,
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
		const formData: AddressFormData = reactive(
			{
				salutation: {
					name: 'salutation',
					value: '',
					pattern: props.addressValidationPatterns.salutation,
					optionalField: false,
				},
				title: {
					name: 'title',
					value: '',
					pattern: props.addressValidationPatterns.title,
					optionalField: true,
				},
				companyName: {
					name: 'companyName',
					value: '',
					pattern: props.addressValidationPatterns.companyName,
					optionalField: false,
				},
				firstName: {
					name: 'firstName',
					value: '',
					pattern: props.addressValidationPatterns.firstName,
					optionalField: false,
				},
				lastName: {
					name: 'lastName',
					value: '',
					pattern: props.addressValidationPatterns.lastName,
					optionalField: false,
				},
				street: {
					name: 'street',
					value: '',
					pattern: props.addressValidationPatterns.street,
					optionalField: false,
				},
				city: {
					name: 'city',
					value: '',
					pattern: props.addressValidationPatterns.city,
					optionalField: false,
				},
				postcode: {
					name: 'postcode',
					value: '',
					pattern: props.addressValidationPatterns.postcode,
					optionalField: false,
				},
				country: {
					name: 'country',
					value: 'DE',
					pattern: props.addressValidationPatterns.country,
					optionalField: false,
				},
				email: {
					name: 'email',
					value: '',
					pattern: props.addressValidationPatterns.email,
					optionalField: false,
				},
			}
		);

		// computed
		const fieldErrors = computed(
			(): AddressValidity => {
				return Object.keys( formData ).reduce( ( validity: AddressValidity, fieldName: string ) => {
					if ( !formData[ fieldName ].optionalField ) {
						validity[ fieldName ] = $store.state.address.validity[ fieldName ] === Validity.INVALID;
					}
					return validity;
				}, ( {} as AddressValidity ) );
			}
		);
		const disabledAddressTypes = computed(
			(): Array<AddressTypeModel> => {
				return $store.getters[ 'payment/isDirectDebitPayment' ] ? [ AddressTypeModel.ANON ] : [];
			}
		);
		const addressType = computed( () => $store.getters[ 'address/addressType' ] );
		const addressTypeIsNotAnon = computed( () => $store.getters[ 'address/addressTypeIsNotAnon' ] );
		const addressTypeIsInvalid = computed( () => $store.getters[ 'address/addressTypeIsInvalid' ] );

		const addressTypeName = computed(
			(): string => getAddressTypeName( $store.state.address.addressType )
		);
		const receiptNeeded = computed(
			(): Boolean => !$store.state.address.receiptOptOut
		);

		// mounted
		onMounted( () => {
			Object.entries( formData ).forEach( ( formItem ) => {
				const key: string = formItem[ 0 ];
				formData[ key ].value = $store.state.address.values[ key ];
				if ( $store.state[ NS_ADDRESS ].validity[ key ] === Validity.RESTORED ) {
					$store.dispatch( action( NS_ADDRESS, validateAddressField ), formData[ key ] );
				}
			} );
		} );

		// methods
		function validateForm(): Promise<ValidationResult> {
			return Promise.all( [
				$store.dispatch( action( NS_ADDRESS, validateAddressType ), $store.state.address.addressType ),
				$store.dispatch( action( NS_ADDRESS, validateAddress ), props.validateAddressUrl ),
				$store.dispatch( action( NS_ADDRESS, validateEmail ), props.validateEmailUrl ),
			] ).then( mergeValidationResults );
		}

		function onFieldChange( fieldName: string ): void {
			$store.dispatch( action( NS_ADDRESS, setAddressField ), formData[ fieldName ] );
		}

		function onAutofill( autofilledFields: { [key: string]: string; } ): void {
			Object.keys( autofilledFields ).forEach( key => {
				const fieldName = camelizeName( key );
				if ( formData[ fieldName ] ) {
					$store.dispatch( action( NS_ADDRESS, setAddressField ), formData[ fieldName ] );
				}
			} );
		}

		function setReceiptOptedOut( optedOut: boolean ): void {
			$store.dispatch( action( NS_ADDRESS, setReceiptOptOut ), optedOut );
		}

		function setAddressType( addressType: AddressTypeModel ): void {
			$store.dispatch( action( NS_ADDRESS, setAddressTypeActionType ), addressType );
		}

		return {
			formData,
			fieldErrors,
			disabledAddressTypes,
			addressType, addressTypeIsNotAnon, addressTypeIsInvalid,
			addressTypeName,
			receiptNeeded,

			validateForm,
			onFieldChange,
			onAutofill,
			setReceiptOptedOut,
			setAddressType,
		};
	},
} );
</script>
