<template>
	<div id="addressForm" class="column is-full">
		<payment-bank-data v-if="isDirectDebit" :validateBankDataUrl="validateBankDataUrl" :validateLegacyBankDataUrl="validateLegacyBankDataUrl"></payment-bank-data>
		<address-type v-on:address-type="setAddressType( $event )"></address-type>
		<name :show-error="fieldErrors" :form-data="formData" :address-type="addressType" v-on:field-changed="onFieldChange"></name>
		<postal v-if="addressTypeIsNotAnon" :show-error="fieldErrors" :form-data="formData" :countries="countries" v-on:field-changed="onFieldChange"></postal>
		<receipt-opt-out v-on:opted-out="setReceiptOptedOut( $event )"/>
		<div class="has-margin-top-36">
			<h1 class="title is-size-1">{{ $t( 'donation_form_section_email_title' ) }}</h1>
			<email v-on:email="setEmail( $event )"></email>
		</div>
		<newsletter-opt-in></newsletter-opt-in>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import AddressType from '@/components/shared/AddressType.vue';
import Name from '@/components/shared/Name.vue';
import Postal from '@/components/shared/Postal.vue';
import ReceiptOptOut from '@/components/shared/ReceiptOptOut.vue';
import Email from '@/components/shared/Email.vue';
import NewsletterOptIn from '@/components/pages/donation_form/NewsletterOptIn.vue';
import { mapGetters } from 'vuex';
import { AddressValidity, FormData, ValidationResult } from '@/view_models/Address';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { Validity } from '@/view_models/Validity';
import { NS_ADDRESS } from '@/store/namespaces';
import { setAddressField, validateAddress, setReceiptOptOut, setAddressType, setEmail } from '@/store/address/actionTypes';
import { action } from '@/store/util';
import PaymentBankData from '@/components/pages/donation_form/PaymentBankData.vue';

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
		validateBankDataUrl: String,
		validateLegacyBankDataUrl: String,
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
		isDirectDebit: {
			get: function (): boolean {
				return this.$store.getters[ 'payment/isDirectDebitPayment' ];
			},
		},
		...mapGetters( NS_ADDRESS, [
			'addressType',
			'addressTypeIsNotAnon',
		] ),
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
		setEmail( email: string ): void {
			this.$store.dispatch( action( NS_ADDRESS, setEmail ), email );
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
