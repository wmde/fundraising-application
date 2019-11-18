<template>
	<form id="address-update-form" name="address-update-form" v-on:submit.prevent="submit" method="post" ref="form" class="modal-card">
		<div v-if="hasErrored" class="help is-danger has-margin-top-18">
			{{ $t( 'donation_confirmation_address_update_error' ) }}
		</div>
		<div v-if="hasSucceeded" class="has-margin-top-18">
			{{ $t( 'donation_confirmation_address_update_success' ) }}
		</div>
		<div v-if="!hasErrored && !hasSucceeded">
			<address-type v-on:address-type="setAddressType( $event )" :disabled-anonymous-type="true"></address-type>
			<name :show-error="fieldErrors" :form-data="formData" :address-type="addressType" v-on:field-changed="onFieldChange"></name>
			<postal :show-error="fieldErrors" :form-data="formData" :countries="countries" v-on:field-changed="onFieldChange"></postal>
			<email :show-error="fieldErrors.email" :form-data="formData" v-on:field-changed="onFieldChange"></email>
			<newsletter-opt-in></newsletter-opt-in>
			<div class="columns has-margin-top-18 has-padding-bottom-18">
				<div class="column">
					<b-button type="is-primary is-main has-margin-top-18 level-item" @click="$parent.close()" outlined>
						{{ $t( 'donation_confirmation_address_update_cancel' ) }}
					</b-button>
				</div>
				<div class="column">
					<b-button type="is-primary is-main has-margin-top-18 level-item"
								:class="isValidating ? 'is-loading' : ''"
								native-type="submit">
						{{ $t( 'donation_confirmation_address_update_confirm' ) }}
					</b-button>
				</div>
			</div>
		</div>
		<div v-else class="columns has-margin-top-18 has-padding-bottom-18">
			<div class="column">
				<b-button type="is-primary is-main has-margin-top-18" @click="$parent.close()" outlined>
					{{ $t( 'back_to_donation_summary' ) }}
				</b-button>
			</div>
		</div>
	</form>
</template>

<script lang="ts">
import Vue from 'vue';
import AddressType from '@/components/pages/donation_form/AddressType.vue';
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
import { setAddressField, validateAddress, setReceiptOptOut, setAddressType } from '@/store/address/actionTypes';
import { action } from '@/store/util';
import PaymentBankData from '@/components/shared/PaymentBankData.vue';
import TwoStepAddressType from '@/components/pages/donation_form/TwoStepAddressType.vue';
import SubmitValues from '@/components/pages/update_address/SubmitValues.vue';
import axios, { AxiosResponse } from 'axios';
import { trackFormSubmission } from '@/tracking';

export interface SubmittedAddress {
	addressData: AddressFormData,
	addressType: string
}

export default Vue.extend( {
	name: 'AddressModal',
	components: {
		Name,
		Postal,
		AddressType,
		TwoStepAddressType,
		ReceiptOptOut,
		Email,
		NewsletterOptIn,
		PaymentBankData,
		SubmitValues,
	},
	data: function (): { formData: AddressFormData, isValidating: boolean } {
		return {
			isValidating: false,
			formData: {
				salutation: {
					name: 'salutation',
					value: '',
					pattern: '^(Herr|Frau)$',
					optionalField: false,
				},
				title: {
					name: 'title',
					value: '',
					pattern: '',
					optionalField: true,
				},
				companyName: {
					name: 'companyName',
					value: '',
					pattern: '^.+$',
					optionalField: false,
				},
				firstName: {
					name: 'firstName',
					value: '',
					pattern: '^.+$',
					optionalField: false,
				},
				lastName: {
					name: 'lastName',
					value: '',
					pattern: '^.+$',
					optionalField: false,
				},
				street: {
					name: 'street',
					value: '',
					pattern: '^.+$',
					optionalField: false,
				},
				city: {
					name: 'city',
					value: '',
					pattern: '^.+$',
					optionalField: false,
				},
				postcode: {
					name: 'postcode',
					value: '',
					pattern: '^[0-9]{4,5}$',
					optionalField: false,
				},
				country: {
					name: 'country',
					value: 'DE',
					pattern: '',
					optionalField: false,
				},
				email: {
					name: 'email',
					value: '',
					pattern: '^(.+)@(.+)\\.(.+)$',
					optionalField: false,
				},
			},
		};
	},
	props: {
		donation: Object,
		updateDonorUrl: String,
		validateAddressUrl: String,
		countries: Array as () => Array<String>,
		hasErrored: Boolean,
		hasSucceeded: Boolean,
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
		...mapGetters( NS_ADDRESS, [
			'addressType',
		] ),
	},
	methods: {
		validateForm(): Promise<ValidationResult> {
			return this.$store.dispatch( action( NS_ADDRESS, validateAddress ), this.$props.validateAddressUrl );
		},
		onFieldChange( fieldName: string ): void {
			this.$store.dispatch( action( NS_ADDRESS, setAddressField ), this.$data.formData[ fieldName ] );
		},
		setAddressType( addressType: AddressTypeModel ): void {
			this.$store.dispatch( action( NS_ADDRESS, setAddressType ), addressType );
		},
		submit() {
			this.$data.isValidating = true;
			this.validateForm().then( ( validationResult: ValidationResult ) => {
				if ( validationResult.status === 'OK' ) {
					let form = this.$refs.form as HTMLFormElement;
					trackFormSubmission( form );
					const jsonForm = new FormData();
					Object.keys( this.$data.formData ).forEach( fieldName => {
						jsonForm.set( fieldName, this.$data.formData[ fieldName ].value );
					} );
					jsonForm.set( 'updateToken', this.$props.donation.updateToken );
					jsonForm.set( 'donation_id', this.$props.donation.id );
					jsonForm.set( 'addressType', addressTypeName( this.$store.getters[ NS_ADDRESS + '/addressType' ] ) );
					axios.post(
						this.$props.updateDonorUrl,
						jsonForm,
						{ headers: { 'Content-Type': 'multipart/form-data' } }
					).then( ( validationResult: AxiosResponse<any> ) => {
						this.$data.isValidating = false;
						if ( validationResult.data.state === 'OK' ) {
							const address = this.$data.formData;
							let addressData = {
								streetAddress: address.street.value,
								postalCode: address.postcode.value,
								city: address.city.value,
								country: address.country.value,
								email: address.email.value,
							} as any;
							if ( this.$store.getters[ NS_ADDRESS + '/addressType' ] === AddressTypeModel.COMPANY ) {
								addressData.fullName = address.companyName.value;
							} else {
								addressData.salutation = address.salutation.value;
								addressData.firstName = address.firstName.value;
								addressData.lastName = address.lastName.value;
								addressData.fullName = `${address.title.value} ${address.firstName.value} ${address.lastName.value}`;
							}
							this.$emit( 'address-updated', {
								addressData,
								addressType: addressTypeName( this.$store.getters[ NS_ADDRESS + '/addressType' ] ),
							} );
						} else {
							this.$emit( 'address-update-failed' );
						}
					} );
				}
			} );
		},
	},
} );
</script>
