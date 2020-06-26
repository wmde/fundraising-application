<template>
	<div class="address-update-form">
		<form name="laika-address-update" ref="form" :action="updateAddressURL" method="post">
			<h1 class="title is-size-1">{{ $t( 'address_change_form_title' ) }}</h1>
			<legend class="title is-size-6">{{ $t( 'address_change_form_label' ) }}</legend>
			<div>
				<receipt-opt-out :message="$t( 'receipt_needed_donation_page' )" v-on:opted-out="setReceiptOptedOut( $event )"/>
				<div> {{ $t( 'address_change_opt_out_hint') }}</div>
				<name :show-error="fieldErrors"
						:form-data="formData"
						:address-type="addressType"
						v-on:field-changed="onFieldChange">
				</name>
				<postal :show-error="fieldErrors"
						:form-data="formData"
						:countries="countries"
						:post-code-validation="addressValidationPatterns.postcode"
						v-on:field-changed="onFieldChange">
				</postal>
				<submit-values :tracking-data="{}"></submit-values>
			</div>
			<div class="level has-margin-top-36">
				<div class="level-right">
					<b-button id="next" :class="[ 'is-form-input-width', $store.getters.isValidating ? 'is-loading' : '', 'level-item' ]"
							@click.prevent="submit()"
							type="is-primary is-main">
						{{ $t( 'address_change_form_submit' ) }}
					</b-button>
				</div>
			</div>
		</form>
	</div>
</template>
<script lang="ts">
import Vue from 'vue';
import Name from '@/components/shared/Name.vue';
import Postal from '@/components/shared/Postal.vue';
import ReceiptOptOut from '@/components/shared/ReceiptOptOut.vue';
import SubmitValues from '@/components/pages/update_address/SubmitValues.vue';
import { AddressValidity, AddressFormData, ValidationResult } from '@/view_models/Address';
import { Validity } from '@/view_models/Validity';
import { Country } from '@/view_models/Country';
import { NS_ADDRESS } from '@/store/namespaces';
import { setAddressField, validateAddress, setReceiptOptOut, setAddressType } from '@/store/address/actionTypes';
import { action } from '@/store/util';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';
import { AddressValidation } from '@/view_models/Validation';
import { mapGetters } from 'vuex';
import { trackFormSubmission } from '@/tracking';

export default Vue.extend( {
	name: 'UpdateAddress',
	components: {
		Name,
		Postal,
		ReceiptOptOut,
		SubmitValues,
	},
	beforeMount() {
		this.setAddressType( this.isCompany ? AddressTypeModel.COMPANY : AddressTypeModel.PERSON );
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
			},
		};
	},
	props: {
		validateAddressUrl: String,
		updateAddressURL: String,
		isCompany: Boolean,
		countries: Array as () => Array<Country>,
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
		...mapGetters( NS_ADDRESS, [
			'addressType',
		] ),
		addressTypeString: {
			get: function (): string {
				return addressTypeName( ( this as any ).addressType );
			},
		},
	},
	methods: {
		validateForm(): Promise<ValidationResult> {
			return this.$store.dispatch( action( NS_ADDRESS, validateAddress ), this.$props.validateAddressUrl );
		},
		onFieldChange( fieldName: string ): void {
			this.$store.dispatch( action( NS_ADDRESS, setAddressField ), this.$data.formData[ fieldName ] );
		},
		setReceiptOptedOut( optedOut: boolean ): void {
			this.$store.dispatch( action( NS_ADDRESS, setReceiptOptOut ), optedOut );
		},
		setAddressType( addressType: AddressTypeModel ): void {
			this.$store.dispatch( action( NS_ADDRESS, setAddressType ), addressType );
		},
		submit() {
			if ( this.$store.state.address.receiptOptOut && this.$store.getters[ NS_ADDRESS + '/allRequiredFieldsEmpty' ] ) {
				const form = this.$refs.form as HTMLFormElement;
				trackFormSubmission( form );
				form.submit();
			}
			this.validateForm().then( ( validationResult: ValidationResult ) => {
				if ( validationResult.status === 'OK' ) {
					const form = this.$refs.form as HTMLFormElement;
					trackFormSubmission( form );
					form.submit();
				}
			} );
		},
	},
} );
</script>
