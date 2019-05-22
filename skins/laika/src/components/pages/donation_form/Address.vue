<template>
	<div id="addressForm">
		<h1 class="title is-size-1">{{ $t( 'donation_section_address_title' ) }}</h1>
		<div>
			<address-type></address-type>
			<name :show-error="fieldErrors" :form-data="formData" :validate-input="validateInput" :address-type="addressType"></name>
			<postal v-if="addressTypeIsNotAnon" :show-error="fieldErrors" :form-data="formData" :validate-input="validateInput" :countries="countries"></postal>
			<h1 class="title is-size-1">{{ $t( 'donation_section_email_title' ) }}</h1>
			<email></email>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import AddressType from '@/components/pages/donation_form/AddressType.vue';
import Name from '@/components/pages/donation_form/Name.vue';
import Postal from '@/components/pages/donation_form/Postal.vue';
import Email from '@/components/pages/donation_form/Email.vue';
import { mapGetters } from 'vuex';
import { AddressValidity, FormData } from '@/view_models/Address';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { Validity } from '@/view_models/Validity';
import { NS_ADDRESS } from '@/store/namespaces';
import { setAddressFields, validateInput } from '@/store/address/actionTypes';
import { action } from '@/store/util';

export default Vue.extend( {
	name: 'Address',
	components: {
		Name,
		Postal,
		AddressType,
		Email,
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
			},
		};
	},
	props: {
		validateAddressUrl: String,
		countries: Array as () => Array<String>,
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
			'addressTypeIsNotAnon',
		] ),
	},
	methods: {
		validateForm() {
			return this.$store.dispatch( action( NS_ADDRESS, setAddressFields ), {
				validateAddressUrl: this.$props.validateAddressUrl,
				formData: this.$data.formData,
			} );
		},
		validateInput( formData: FormData, fieldName: string ) {
			this.$store.dispatch( action( NS_ADDRESS, validateInput ), formData[ fieldName ] );
		},
	},
} );
</script>
