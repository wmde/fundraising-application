<template>
	<fieldset>
		<legend class="subtitle">{{ $t( 'donation_form_section_address_header_type' ) }}</legend>
		<div>
			<b-radio id="personal"
					name="addressTypeInternal"
					v-model="type"
					:native-value="AddressTypeModel.PERSON"
					@change.native="setAddressType()">{{ $t( 'donation_form_addresstype_option_private' ) }}
			</b-radio>
		</div>
		<div>
			<b-radio id="company"
					name="addressTypeInternal"
					v-model="type"
					:native-value="AddressTypeModel.COMPANY"
					@change.native="setAddressType()">
				{{ $t( 'donation_form_addresstype_option_company' ) }}
			</b-radio>
		</div>
		<div>
			<b-radio id="anonymous"
					name="addressTypeInternal"
					v-model="type"
					:disabled="this.isDirectDebit"
					:native-value="AddressTypeModel.ANON"
					@change.native="setAddressType()">
				{{ $t( 'donation_form_addresstype_option_anonymous' ) }}
			</b-radio>
		</div>
    </fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

export default Vue.extend( {
	name: 'AddressType',
	data: function () {
		return {
			type: AddressTypeModel.PERSON,
		};
	},
	props: {
		isDirectDebit: Boolean,
	},
	computed: {
		AddressTypeModel: {
			get: function () {
				return AddressTypeModel;
			},
		},
	},
	watch: {
		isDirectDebit:
		{
			handler: function ( isDirectDebit ) {
				if ( isDirectDebit && this.$data.type === AddressTypeModel.ANON ) {
					this.$data.type = AddressTypeModel.PERSON;
					this.setAddressType();
				}
			},
			deep: true,
		},
	},
	methods: {
		setAddressType: function () {
			this.$emit( 'address-type', this.$data.type );
		},
	},
} );
</script>
