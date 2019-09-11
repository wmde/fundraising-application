<template>
	<div class="membership-summary">
		<div class="title is-size-5">{{ $t( 'membership_confirmation_thanks_text' ) }}</div>
		<div class="payment-summary" v-html="getSummary()"></div>
		<slot></slot>
	</div>
</template>

<script lang="js">
import Vue from 'vue';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';

class PrivateApplicantRenderer {
	static renderAddress( address, country ) {
		return address.salutation + ' ' + address.fullName + ', '
				+ address.streetAddress + ', ' + address.postalCode + ' ' + address.city + ', ' + country
				+ ' <p>E-Mail: ' + address.email + '</p>';
	}
}
class CompanyApplicantRenderer {
	static renderAddress( address, country ) {
		return address.fullName + ', '
				+ address.streetAddress + ', ' + address.postalCode + ' ' + address.city + ', ' + country
				+ ' <p>E-Mail: ' + address.email + '</p>';
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
			if ( !this.canRender( this.membershipApplication.membershipFee, this.membershipApplication.paymentIntervalInMonths ) ) {
				return this.$t( 'membership_form_review_payment_missing' );
			}
			const numericInterval = parseInt( this.membershipApplication.paymentIntervalInMonths, 10 );
			const interval = this.$t( 'donation_form_payment_interval_' + this.membershipApplication.paymentIntervalInMonths );
			const addressTypeRenderer = addressTypeRenderers[ this.address.applicantType ];
			const formattedAmountMonthly = parseFloat( this.membershipApplication.membershipFee ).toFixed( 2 ).replace( '.', ',' );
			const amountYearly = parseFloat( this.membershipApplication.membershipFee ) * 12 / numericInterval;
			const formattedAmountYearly = YearlyAmountRenderer.renderAmount(
				amountYearly,
				numericInterval,
				this.$t( 'currency_name' ),
				this.$t( 'donation_form_payment_interval_12' )
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
		canRender: function ( fee, interval ) {
			return fee !== '' && !isNaN( Number( fee ) ) && interval !== '';
		},
	},
} );
</script>

<style lang="scss">
	@import "../scss/custom";
</style>
