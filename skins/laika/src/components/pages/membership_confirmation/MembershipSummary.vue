<template>
	<div class="donation-summary">
		<div class="intro"><slot></slot></div>
		<div class="payment-summary" v-html="getSummary()"></div>
		<div class="payment-email" v-html="getEmail()"></div>
	</div>
</template>

<script lang="js">
import Vue from 'vue';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';

class PrivateDonorRenderer {
	static getPersonTypeMessageKey() {
		return 'donation_confirmation_topbox_donor_type_person';
	}
	static renderAddress( address, country ) {
		return address.salutation + ' ' + address.fullName + ', '
				+ address.streetAddress + ', ' + address.postalCode + ' ' + address.city + ', ' + country;
	}
	static canRender( address ) {
		return address.salutation && address.firstName && address.lastName && address.streetAddress && address.postalCode && address.city;
	}
}
class CompanyDonorRenderer {
	static getPersonTypeMessageKey() {
		return 'donation_confirmation_topbox_donor_type_company';
	}
	static renderAddress( address, country ) {
		return address.fullName + ', '
				+ address.streetAddress + ', ' + address.postalCode + ' ' + address.city + ', ' + country;
	}
	static canRender( address ) {
		return address.fullName && address.streetAddress && address.postalCode && address.city;
	}
}

const addressTypeRenderers = {
	[ addressTypeName( AddressTypeModel.PERSON ) ]: PrivateDonorRenderer,
	[ addressTypeName( AddressTypeModel.COMPANY ) ]: CompanyDonorRenderer,
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
			const interval = this.$t( 'donation_form_payment_interval_' + this.membershipApplication.interval );
			const formattedAmount = parseFloat( this.membershipApplication.membershipFee ).toFixed( 2 ).replace( '.', ',' );
			const paymentType = this.$t( this.membershipApplication.paymentType );
			const personType = this.$t( addressTypeRenderer.getPersonTypeMessageKey() );
			let address = this.$t( 'donation_confirmation_review_address_missing' );
			if ( addressTypeRenderer.canRender( this.address ) ) {
				address = addressTypeRenderer.renderAddress( this.address, this.$t( 'donation_form_country_option_' + this.address.countryCode ) );
			}

			return this.$t(
				'donation_confirmation_topbox_summary',
				{
					interval,
					formattedAmount,
					paymentType,
					personType,
					address,
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

	.donation {
		&-summary {
			.intro {
				margin-bottom: 18px;
			}
		}
	}
</style>
