<template>
	<div class="donation-summary">
		<slot></slot>
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

class YearlyAmountRenderer {
	static renderAmount( amount, interval, currencyTranslation, intervalTranslation ) {
		if ( interval === 12 ) {
			return '';
		}
		const formattedAmount = amount.toFixed( 2 ).replace( '.', ',' );
		return `(${formattedAmount} ${currencyTranslation} ${intervalTranslation})`;
	}
}

export default Vue.extend( {
	name: 'MembershipSummary',
	props: [
		'address',
		'membershipApplication',
	],
	methods: {
		getSummary: function () {
			const numericInterval = parseInt( this.membershipApplication.paymentIntervalInMonths, 10 );
			const addressTypeRenderer = addressTypeRenderers[ this.address.applicantType ];
			const interval = this.$t( 'donation_form_payment_interval_' + this.membershipApplication.paymentIntervalInMonths );
			const formattedAmountMonthly = parseFloat( this.membershipApplication.membershipFee ).toFixed( 2 ).replace( '.', ',' );
			const amountYearly = parseFloat( this.membershipApplication.membershipFee ) * 12 / numericInterval;
			const formattedAmountYearly = YearlyAmountRenderer.renderAmount(
				amountYearly,
				numericInterval,
				this.$t( 'currency_name' ),
				this.$t( 'donation_form_payment_interval_yearly' )
			);
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
