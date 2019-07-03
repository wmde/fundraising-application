<template>
	<div id="addressForm" class="column is-full">
		<div class="has-margin-top-18">
			<h1 class="title is-size-1">{{ $t( 'donation_form_section_address_title' ) }}</h1>
			<address-type></address-type>
		</div>
		<name :show-error="fieldErrors" :form-data="formData" :address-type="addressType" v-on:field-changed="onFieldChange"></name>
		<postal :show-error="fieldErrors" :form-data="formData" :countries="countries" v-on:field-changed="onFieldChange"></postal>
		<date-of-birth/>
        <receipt-opt-out/>
        <div class="has-margin-top-36">
			<h1 class="title is-size-1">{{ $t( 'donation_form_section_email_title' ) }}</h1>
			<email></email>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { mapGetters } from 'vuex';
import AddressType from '@/components/pages/donation_form/AddressType.vue';
import Name from '@/components/pages/donation_form/Name.vue';
import Postal from '@/components/pages/donation_form/Postal.vue';
import DateOfBirth from '@/components/pages/donation_form/DateOfBirth.vue';
import ReceiptOptOut from '@/components/pages/donation_form/ReceiptOptOut.vue';
import Email from '@/components/pages/donation_form/Email.vue';
import { AddressValidity, FormData, ValidationResult } from '@/view_models/Address';
import { Validity } from '@/view_models/Validity';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { setAddressField, validateAddress } from '@/store/address/actionTypes';
import { action } from '@/store/util';

export default Vue.extend( {
	name: 'Address',
	components: {
		Name,
		Postal,
		DateOfBirth,
		ReceiptOptOut,
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
						validity[ fieldName ] = this.$store.state.membership_address.validity[ fieldName ] === Validity.INVALID;
					}
					return validity;
				}, ( {} as AddressValidity ) );
			},
		},
		...mapGetters( NS_MEMBERSHIP_ADDRESS, [
			'addressType',
		] ),
	},
	methods: {
		validateForm(): Promise<ValidationResult> {
			return this.$store.dispatch( action( NS_MEMBERSHIP_ADDRESS, validateAddress ), this.$props.validateAddressUrl );
		},
		onFieldChange( fieldName: string ): void {
			this.$store.dispatch( action( NS_MEMBERSHIP_ADDRESS, setAddressField ), this.formData[ fieldName ] );
		},
	},
} );
</script>
<style lang="scss" scoped>
    @import "../../../scss/custom";

    button.is-main {
        height: 54px;
        font-size: 1em;
        font-weight: bold;
        width: 250px;
        border-radius: 0;
    }
    @include until($tablet) {
        button.is-main {
            width: 100%;
        }
    }
</style>
