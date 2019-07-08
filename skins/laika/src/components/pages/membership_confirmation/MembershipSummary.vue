<template>
	<div class="donation-summary">
		<div class="payment-summary" v-html="getSummary()"></div>
		<div class="payment-email" v-html="getEmail()"></div>
		<div class="has-margin-top-18">{{ $t( 'membership_confirmation_success_text' ) }}</div>
	</div>
</template>

<script lang="js">
import Vue from 'vue';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

class PrivateDonorRenderer {
	static renderAddress( address, country ) {
		return address.salutation + ' ' + address.fullName + ', '
				+ address.streetAddress + ', ' + address.postalCode + ' ' + address.city + ', ' + country;
	}
}
class CompanyDonorRenderer {
	static renderAddress( address, country ) {
		return address.fullName + ', '
				+ address.streetAddress + ', ' + address.postalCode + ' ' + address.city + ', ' + country;
	}
}

const addressTypeRenderers = {
	[ AddressTypeModel.PERSON ]: PrivateDonorRenderer,
	[ AddressTypeModel.COMPANY ]: CompanyDonorRenderer,
};

export default Vue.extend( {
	name: 'MembershipSummary',
	props: [
		'address',
		'addressType',
		'membershipApplication',
	],
	methods: {
		getSummary: function () {
			const addressTypeRenderer = addressTypeRenderers[ this.addressType ];
			const interval = this.$t( 'donation_form_payment_interval_' + this.membershipApplication.paymentIntervalInMonths );
			const formattedAmountMonthly = parseFloat( this.membershipApplication.membershipFee ).toFixed( 2 ).replace( '.', ',' );
			const formattedAmountYearly = parseFloat( this.membershipApplication.membershipFee * 12 ).toFixed( 2 ).replace( '.', ',' );
			const personType = this.$t( this.membershipApplication.membershipType );
			const address = addressTypeRenderer.renderAddress( this.address, this.$t( 'donation_form_country_option_' + this.address.countryCode ) );

			return this.$t(
				'membership_confirmation_data_text',
				{
					paymentInterval: interval,
					membershipType: personType,
					membershipFee: formattedAmountMonthly,
					membershipFeeYearly: formattedAmountYearly,
					address: address,
				}
			);
		},
		getEmail: function () {
			if ( this.address.email ) {
				return this.$t( 'donation_confirmation_topbox_email', { email: this.address.email } );
			}
			return this.$t( 'donation_confirmation_review_email_missing' );
		},
	},
} );
</script>

<style lang="scss">
	@import "../../../scss/custom";
</style>
