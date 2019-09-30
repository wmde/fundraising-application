<template>
	<div class="address-update-form">
		<form name="laika-address-update" ref="form" :action="updateAddressURL" method="post">
			<h1 class="title is-size-1">{{ $t( 'address_change_form_title' ) }}</h1>
			<legend class="title is-size-6">{{ $t( 'address_change_form_label' ) }}</legend>
			<div>
				<name :show-error="fieldErrors"
						:form-data="formData"
						:address-type="addressType"
						v-on:field-changed="onFieldChange">
				</name>
				<postal :show-error="fieldErrors"
						:form-data="formData"
						:countries="countries"
						v-on:field-changed="onFieldChange">
				</postal>
				<receipt-opt-out v-on:opted-out="setReceiptOptedOut( $event )"/>
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
import SubmitValues from '@/components/pages/update-address/SubmitValues.vue';
import { AddressValidity, AddressFormData, ValidationResult } from '@/view_models/Address';
import { Validity } from '@/view_models/Validity';
import { NS_ADDRESS } from '@/store/namespaces';
import { setAddressField, validateAddress, setReceiptOptOut, setAddressType } from '@/store/address/actionTypes';
import { action } from '@/store/util';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';
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
		updateAddressURL: String,
		isCompany: Boolean,
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
