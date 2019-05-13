<template>
    <fieldset>
        <div>
			<input type="radio"
					id="personal"
					name="addressType"
					v-model="type"
					:value="AddressTypeModel.PERSON"
					@change="setAddressType()">
			<label for="personal">
				<span>{{ $t( 'donation_addresstype_option_private' ) }}</span>
			</label>
		</div>
		<div>
			<input type="radio"
					id="company"
					name="addressType"
					v-model="type"
					:value="AddressTypeModel.COMPANY"
					@change="setAddressType()">
			<label for="company">
				<span>{{ $t( 'donation_addresstype_option_company' ) }}</span>
			</label>
		</div>
		<div>
			<input type="radio"
					id="anonymous"
					name="addressType"
					v-model="type"
					:value="AddressTypeModel.ANON"
					@change="setAddressType()">
			<label for="anonymous">
				<span>{{ $t( 'donation_addresstype_option_anonymous' ) }}</span>
			</label>
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
		} 
	},
	computed: {
		AddressTypeModel: {
			get: function() {
				return AddressTypeModel;
			}
		}
	},
	methods: {
		setAddressType: function() {
			this.$store.dispatch( action( NS_ADDRESS, setAddressType ), this.type );
		}
	}
} )
</script>