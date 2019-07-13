<template>
	<div class="donation-summary">
		<div class="payment-summary" v-html="getSummary()"></div>
		<div class="payment-email">{{ $t( 'donation_confirmation_topbox_email', { email: this.address.email } ) }}</div>
		<div class="has-margin-top-18">{{ $t( 'membership_confirmation_success_text' ) }}</div>
	</div>
</template>

<script lang="js">
import Vue from 'vue';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';

class PrivateApplicantRenderer {
	static renderAddress( address, country ) {
		return address.salutation + ' ' + address.fullName + ', '
				+ address.streetAddress + ', ' + address.postalCode + ' ' + address.city + ', ' + country;
	}
}
class CompanyApplicantRenderer {
	static renderAddress( address, country ) {
		return address.fullName + ', '
				+ address.streetAddress + ', ' + address.postalCode + ' ' + address.city + ', ' + country;
	}
}

const addressTypeRenderers = {
	[ addressTypeName( AddressTypeModel.PERSON ) ]: PrivateApplicantRenderer,
	[ addressTypeName( AddressTypeModel.COMPANY ) ]: CompanyApplicantRenderer,
};

export default Vue.extend( {
	name: 'MembershipSummary',
	props: [
		'address',
		'membershipApplication',
	],
	methods: {
		getSummary: function () {
			const addressTypeRenderer = addressTypeRenderers[ this.address.applicantType ];
			const interval = this.$t( 'donation_form_payment_interval_' + this.membershipApplication.paymentIntervalInMonths );
			const formattedAmountMonthly = parseFloat( this.membershipApplication.membershipFee ).toFixed( 2 ).replace( '.', ',' );
			const formattedAmountYearly = parseFloat( this.membershipApplication.membershipFee * 12 ).toFixed( 2 ).replace( '.', ',' );
			const membershipType = this.$t( this.membershipApplication.membershipType );
			const address = addressTypeRenderer.renderAddress( this.address, this.$t( 'donation_form_country_option_' + this.address.countryCode ) );

			return this.$t(
				'membership_confirmation_data_text',
				{
					paymentInterval: interval,
					membershipType: membershipType,
					membershipFee: formattedAmountMonthly,
					membershipFeeYearly: formattedAmountYearly,
					address: address,
				}
			);
		},
	},
} );
</script>

<style lang="scss">
	@import "../../../scss/custom";
</style>
