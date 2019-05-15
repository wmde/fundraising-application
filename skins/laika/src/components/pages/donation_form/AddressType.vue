<template>
	<fieldset>
		<legend class="subtitle has-margin-top-18">{{ $t( 'donation_section_address_header_type' ) }}</legend>
		<div>
			<b-radio type="radio"
					id="personal"
					name="addressType"
					v-model="type"
					:native-value="AddressTypeModel.PERSON"
					@change.native="setAddressType()">{{ $t( 'donation_addresstype_option_private' ) }}
			</b-radio>
		</div>
		<div>
			<b-radio type="radio"
					id="company"
					name="addressType"
					v-model="type"
					:native-value="AddressTypeModel.COMPANY"
					@change.native="setAddressType()">
				{{ $t( 'donation_addresstype_option_company' ) }}
			</b-radio>
		</div>
		<div>
			<b-radio type="radio"
					id="anonymous"
					name="addressType"
					v-model="type"
					:native-value="AddressTypeModel.ANON"
					@change.native="setAddressType()">
				{{ $t( 'donation_addresstype_option_anonymous' ) }}
			</b-radio>
		</div>
    </fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { NS_ADDRESS } from '@/store/namespaces';
import { action } from '@/store/util';
import { setAddressType } from '@/store/address/actionTypes';

export default Vue.extend( {
	name: 'AddressType',
	data: function () {
		return {
			type: AddressTypeModel.PERSON,
		};
	},
	computed: {
		AddressTypeModel: {
			get: function () {
				return AddressTypeModel;
			},
		},
	},
	methods: {
		setAddressType: function () {
			this.$store.dispatch( action( NS_ADDRESS, setAddressType ), this.type );
		},
	},
} );
</script>
