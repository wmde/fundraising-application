<template>
	<span class="submit-values">

		<input type="hidden" name="zahlweise" :value="payment.type">
		<input type="hidden" name="periode" :value="payment.interval">
		<input type="hidden" name="betrag" :value="formattedAmount">
		<input type="hidden" name="iban" :value="bankdata.iban">
		<input type="hidden" name="bic" :value="bankdata.bic">

		<input type="hidden" name="addressType" :value="addressType">

		<input type="hidden" name="salutation" :value="address.salutation">
		<input type="hidden" name="title" :value="address.title">
		<input type="hidden" name="firstName" :value="address.firstName">
		<input type="hidden" name="lastName" :value="address.lastName">
		<input type="hidden" name="companyName" :value="address.companyName">
		<input type="hidden" name="street" :value="address.street">
		<input type="hidden" name="postcode" :value="address.postcode">
		<input type="hidden" name="city" :value="address.city">
		<input type="hidden" name="country" :value="address.country">
		<input type="hidden" name="email" :value="address.email">
		<input type="hidden" name="info" :value="newsletterOptIn ? '1' : ''">
		<input type="hidden" name="info" :value="receiptOptOut ? '0' : '1'">

		<input type="hidden" name="impCount" :value="trackingData.bannerImpressionCount">
		<input type="hidden" name="bImpCount" :value="trackingData.impressionCount">

	</span>
</template>

<script lang="ts">
import Vue from 'vue';
import { mapState } from 'vuex';
import { NS_ADDRESS, NS_BANKDATA, NS_PAYMENT } from '@/store/namespaces';
import { Payment } from '@/view_models/Payment';
import { AddressState } from '@/view_models/Address';
import { AddressTypeModel, addressTypeName, AddressTypeNames } from '@/view_models/AddressTypeModel';
import { BankAccount } from '@/view_models/BankAccount';

export default Vue.extend( {
	name: 'SubmitValues',
	props: [
		'trackingData',
	],
	computed: {
		...mapState( NS_PAYMENT, {
			payment: ( state: Payment ) => state.values,
			formattedAmount: ( state: Payment ) => {
				// endpoint expects a German-formatted decimal number
				const strAmount = String( state.values.amount );
				return [
					strAmount.slice( 0, -2 ),
					',',
					strAmount.slice( -2 ),
				].join( '' );
			},
		} ),
		...mapState( NS_ADDRESS, {
			address: ( state: AddressState ) => state.values,
			addressType: ( state: AddressState ) => {
				return addressTypeName( state.addressType );
			},
			newsletterOptIn: ( state: AddressState ) => state.newsletterOptIn,
			receiptOptOut: ( state: AddressState ) => state.receiptOptOut,
		} ),
		...mapState( NS_BANKDATA, {
			bankdata: ( state: BankAccount ) => state.values,
		} ),
	},
} );
</script>

<style scoped>

</style>
