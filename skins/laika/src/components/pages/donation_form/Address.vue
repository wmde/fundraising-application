<template>
    <div id="addressForm">
        <h2>{{ $t( 'address_form_title' ) }}</h2>
        <h5>{{ $t( 'address_form_subtitle' ) }}</h5>
		<div>
			<address-type></address-type>
			<name :show-error="fieldErrors" :form-data="formData" :validate-input="validateInput"></name>
			<postal :show-error="fieldErrors" :form-data="formData" :validate-input="validateInput" :countries="countries"></postal>
		</div>
		<input type="hidden" name="addressType" v-model="formData.addressType.value">
		<!--
			Vue component for an overview of the donation (Zusammenfassung in the design).
			It will contain the final Donate button.
			validateForm() should be called upon clicking that button.
		-->
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import AddressType from '@/components/pages/donation_form/AddressType.vue';
import Name from '@/components/pages/donation_form/Name.vue';
import Postal from '@/components/pages/donation_form/Postal.vue';
import { AddressValidity, FormData } from '@/view_models/Address';
import { Validity } from '@/view_models/Validity';
import { NS_ADDRESS } from '@/store/namespaces';
import { storeAddressFields, validateInput } from '@/store/address/actionTypes';
import { action } from '@/store/util';

export default Vue.extend( {
	name: 'Address',
	components: {
		Name,
		Postal,
		AddressType,
	},
	data: function (): { formData: FormData } {
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
				addressType: {
					name: 'addressType',
					value: 'person',
					pattern: '',
					optionalField: false,
				},
			},
		};
	},
	props: {
		validateAddressUrl: String,
		countries: {
			type: Array,
			default: function () {
				return [ 'DE', 'AT', 'CH', 'BE', 'IT', 'LI', 'LU' ];
			},
		},
	},
	computed: {
		fieldErrors: {
			get: function (): AddressValidity {
				return Object.keys( this.formData ).reduce( ( validity: AddressValidity, fieldName: string ) => {
					if ( !this.formData[ fieldName ].optionalField ) {
						validity[ fieldName ] = this.$store.state.address.form[ fieldName ] === Validity.INVALID;
					}
					return validity;
				}, ( {} as AddressValidity ) );
			},
		},
	},
	methods: {
		validateForm() {
			this.$store.dispatch( action( NS_ADDRESS, storeAddressFields ), {
				validateAddressUrl: this.$props.validateAddressUrl,
				formData: this.$data.formData,
			} ).then( () => {
				if ( this.$store.getters[ 'address/allFieldsAreValid' ] ) {
					// TODO submit form
				}
			} );
		},
		validateInput( formData: FormData, fieldName: string ) {
			this.$store.dispatch( action( NS_ADDRESS, validateInput ), formData[ fieldName ] );
		},
	},
} );
</script>
