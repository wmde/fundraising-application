<template>
	<span class="submit-values">
		<input type="hidden" name="addressType" :value="addressType">

		<input type="hidden" name="salutation" :value="address.salutation">
		<input type="hidden" name="title" :value="address.title">
		<input type="hidden" name="firstName" :value="address.firstName">
		<input type="hidden" name="lastName" :value="address.lastName">
		<input type="hidden" name="company" :value="address.companyName">
		<input type="hidden" name="street" :value="address.street">
		<input type="hidden" name="postcode" :value="address.postcode">
		<input type="hidden" name="city" :value="address.city">
		<input type="hidden" name="country" :value="address.country">
		<input type="hidden" name="receiptOptOut" :value="receiptOptOut">
	</span>
</template>

<script lang="ts">
import Vue from 'vue';
import { mapState } from 'vuex';
import { NS_ADDRESS, NS_BANKDATA, NS_PAYMENT } from '@/store/namespaces';
import { Payment } from '@/view_models/Payment';
import { AddressState } from '@/view_models/Address';
import { addressTypeName } from '@/view_models/AddressTypeModel';
import { BankAccount } from '@/view_models/BankAccount';

export default Vue.extend( {
	name: 'SubmitValues',
	props: [
		'trackingData',
	],
	computed: {
		...mapState( NS_ADDRESS, {
			address: ( state: AddressState ) => state.values,
			addressType: ( state: AddressState ) => {
				return addressTypeName( state.addressType );
			},
			receiptOptOut: ( state: AddressState ) => state.receiptOptOut ? '1' : '',
		} ),
	},
} );
</script>
