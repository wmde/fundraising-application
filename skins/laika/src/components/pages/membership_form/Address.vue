<template>
	<div class="address-section">
		<div class="has-margin-top-18">
			<h1 class="title is-size-1">{{ $t( 'donation_form_section_address_title' ) }}</h1>
			<address-type :initial-value="addressType" v-on:address-type="setAddressType( $event )"/>
		</div>
		<h1 class="has-margin-top-36 title is-size-5">{{ $t( 'donation_form_section_address_title' ) }}</h1>
		<AutofillHandler @autofill="onAutofill">
			<name :show-error="fieldErrors" :form-data="formData" :address-type="addressType" v-on:field-changed="onFieldChange"/>
			<postal :show-error="fieldErrors" :form-data="formData" :countries="countries" v-on:field-changed="onFieldChange"/>
			<receipt-opt-out :message="$t( 'receipt_needed_membership_page' )" v-on:opted-out="setReceiptOptedOut( $event )"/>
			<date-of-birth v-if="isPerson"/>
			<email :show-error="fieldErrors.email" :form-data="formData" v-on:field-changed="onFieldChange"/>
		</AutofillHandler>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { mapGetters } from 'vuex';
import AddressType from '@/components/pages/membership_form/AddressType.vue';
import Name from '@/components/shared/Name.vue';
import Postal from '@/components/shared/Postal.vue';
import DateOfBirth from '@/components/pages/membership_form/DateOfBirth.vue';
import ReceiptOptOut from '@/components/shared/ReceiptOptOut.vue';
import Email from '@/components/shared/Email.vue';
import AutofillHandler from '@/components/shared/AutofillHandler.vue';
import { AddressValidity, AddressFormData, ValidationResult } from '@/view_models/Address';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { Validity } from '@/view_models/Validity';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { setAddressField, validateAddress, validateEmail, setReceiptOptOut, setAddressType } from '@/store/membership_address/actionTypes';
import { action } from '@/store/util';
import { mergeValidationResults } from '@/merge_validation_results';
import { camelizeName } from '@/camlize_name';

export default Vue.extend( {
	name: 'Address',
	components: {
		Name,
		Postal,
		DateOfBirth,
		ReceiptOptOut,
		AddressType,
		Email,
		AutofillHandler,
	},
	data: function (): { formData: AddressFormData } {
		return {
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
		validateAddressUrl: String,
		validateEmailUrl: String,
		countries: Array as () => Array<String>,
		initialFormValues: [ Object, String ],
	},
	computed: {
		fieldErrors: {
			get: function (): AddressValidity {
				return Object.keys( this.formData ).reduce( ( validity: AddressValidity, fieldName: string ) => {
					if ( !this.formData[ fieldName ].optionalField ) {
						validity[ fieldName ] = this.$store.state.membership_address.validity[ fieldName ] === Validity.INVALID;
					}
					return validity;
				}, ( {} as AddressValidity ) );
			},
		},
		...mapGetters( NS_MEMBERSHIP_ADDRESS, [
			'addressType',
			'email',
			'isPerson',
		] ),
	},
	mounted() {
		Object.entries( this.$store.state[ NS_MEMBERSHIP_ADDRESS ].values ).forEach( ( entry ) => {
			const name: string = entry[ 0 ];
			const value: string = entry[ 1 ] as string;
			if ( !this.formData[ name ] ) {
				return;
			}
			this.formData[ name ].value = value;
		} );
	},
	methods: {
		validateForm(): Promise<ValidationResult> {
			return Promise.all( [
				this.$store.dispatch( action( NS_MEMBERSHIP_ADDRESS, validateAddress ), this.$props.validateAddressUrl ),
				this.$store.dispatch( action( NS_MEMBERSHIP_ADDRESS, validateEmail ), this.$props.validateEmailUrl ),
			] ).then( mergeValidationResults );

		},
		onFieldChange( fieldName: string ): void {
			this.$store.dispatch( action( NS_MEMBERSHIP_ADDRESS, setAddressField ), this.$data.formData[ fieldName ] );
		},
		onAutofill( autofilledFields: { [key: string]: string; } ) {
			Object.keys( autofilledFields ).forEach( key => {
				const fieldName = camelizeName( key );
				if ( this.$data.formData[ fieldName ] ) {
					this.$store.dispatch( action( NS_MEMBERSHIP_ADDRESS, setAddressField ), this.$data.formData[ fieldName ] );
				}
			} );
		},
		setReceiptOptedOut( optedOut: boolean ): void {
			this.$store.dispatch( action( NS_MEMBERSHIP_ADDRESS, setReceiptOptOut ), optedOut );
		},
		setAddressType( addressType: AddressTypeModel ): void {
			this.$store.dispatch( action( NS_MEMBERSHIP_ADDRESS, setAddressType ), addressType );
		},
	},
} );
</script>
