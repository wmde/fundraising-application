<template>
	<span class="submit-values">

		<input type="hidden" name="membership_type" :value="membershipType">

		<input type="hidden" name="payment_type" value="BEZ">
		<input type="hidden" name="membership_fee_interval" :value="fee.interval">
		<input type="hidden" name="membership_fee" :value="fee.fee">
		<input type="hidden" name="iban" :value="bankdata.iban">
		<input type="hidden" name="bic" :value="bankdata.bic">

		<input type="hidden" name="adresstyp" :value="addressType">

		<input type="hidden" name="anrede" :value="address.salutation">
		<input type="hidden" name="titel" :value="address.title">
		<input type="hidden" name="vorname" :value="address.firstName">
		<input type="hidden" name="nachname" :value="address.lastName">
		<input type="hidden" name="firma" :value="address.companyName">
		<input type="hidden" name="strasse" :value="address.street">
		<input type="hidden" name="postcode" :value="address.postcode">
		<input type="hidden" name="ort" :value="address.city">
		<input type="hidden" name="country" :value="address.country">
		<input type="hidden" name="email" :value="address.email">
		<input type="hidden" name="donationReceipt" :value="receiptOptIn">
		<input type="hidden" name="dob" :value="address.date">
		<input type="hidden" name="incentives[]" :value="incentives">

	</span>
</template>

<script lang="ts">
import Vue from 'vue';
import { mapState } from 'vuex';
import { NS_BANKDATA, NS_MEMBERSHIP_ADDRESS, NS_MEMBERSHIP_FEE } from '@/store/namespaces';
import { Payment } from '@/view_models/Payment';
import { MembershipAddressState } from '@/view_models/Address';
import { addressTypeName } from '@/view_models/AddressTypeModel';
import { BankAccount } from '@/view_models/BankAccount';
import { membershipTypeName } from '@/view_models/MembershipTypeModel';

export default Vue.extend( {
	name: 'SubmitValues',
	computed: {
		...mapState( NS_MEMBERSHIP_FEE, {
			fee: state => ( state as Payment ).values,
		} ),
		...mapState( NS_MEMBERSHIP_ADDRESS, {
			address: state => ( state as MembershipAddressState ).values,
			addressType: state => {
				return addressTypeName( ( state as MembershipAddressState ).addressType );
			},
			receiptOptIn: state => ( state as MembershipAddressState ).receiptOptOut ? '0' : '1',
			incentives: state => ( state as MembershipAddressState ).incentives,
			membershipType: state => membershipTypeName( ( state as MembershipAddressState ).membershipType ),
		} ),
		...mapState( NS_BANKDATA, {
			bankdata: state => ( state as BankAccount ).values,
		} ),
	},
} );
</script>
