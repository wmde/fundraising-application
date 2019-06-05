<template>
	<div class="donation-summary-wrapper has-background-bright columns has-padding-18">
		<div class="column is-half">
			<div class="donation-summary">
				<span class="payment-summary" v-html="getSummary()"></span>
				<span class="payment-email" v-html="getEmail()"></span>
				<span class="payment-notice" v-html="getPaymentNotice()"></span>
				<BankData v-if="isBankDataVisible" :bank-transfer-code="confirmationData.donation.bankTransferCode"></BankData>
			</div>
		</div>
		<div class="column is-half donation-links">
			<a href="javascript:window.print()">{{ $t( 'donation_confirmation_print_confirmation' ) }}</a>
		</div>
	</div>
</template>

<script lang="js">
import Vue from 'vue';
import BankData from '@/components/BankData.vue';

class PrivateDonorRenderer {
	static getPersonTypeMessageKey() {
		return 'donation_confirmation_topbox_donor_type_person';
	}
	static getAddressString( address ) {
		return address.salutation + ' ' + address.fullName + ', '
				+ address.streetAddress + ', ' + address.postalCode + ' ' + address.city;
	}
}
class CompanyDonorRenderer {
	static getPersonTypeMessageKey() {
		return 'donation_confirmation_topbox_donor_type_company';
	}
	static getAddressString( address ) {
		return address.salutation + ' ' + address.fullName + ', '
				+ address.streetAddress + ', ' + address.postalCode + ' ' + address.city;
	}
}
class AnonymousDonorRenderer {
	static getPersonTypeMessageKey() {
		return 'donation_confirmation_topbox_donor_type_anonymous';
	}
	static getAddressString() {
		return '';
	}
}

const addressTypeRenderers = {
	'person': PrivateDonorRenderer,
	'firma': CompanyDonorRenderer,
	'anonym': AnonymousDonorRenderer,
};

class BankTransferRenderer {
	static getPaymentString() {
		return 'donation_confirmation_subhead_bank_transfer';
	}
}

class DirectDebitRenderer {
	static getPaymentString() {
		return 'donation_confirmation_subhead_direct_debit';
	}
}

class EmptyRenderer {
	static getPaymentString() {
		return '';
	}
}

const paymentTypeRenderers = {
	'UEB': BankTransferRenderer,
	'BEZ': DirectDebitRenderer,
	'PPL': EmptyRenderer,
	'MCP': EmptyRenderer,
	'SUB': EmptyRenderer,
};

export default Vue.extend( {
	name: 'DonationSummary',
	components: { BankData },
	props: [
		'confirmationData',
	],
	methods: {
		getSummary: function () {
			const addressTypeRenderer = addressTypeRenderers[ this.confirmationData.addressType ];
			let intervalString = this.$t( 'donation_payment_interval_' + this.confirmationData.donation.interval );
			let amountString = this.confirmationData.donation.amount.toFixed( 2 ).replace( '.', ',' );
			let paymentTypeString = this.$t( this.confirmationData.donation.paymentType );
			let personTypeString = this.$t( addressTypeRenderer.getPersonTypeMessageKey() );
			let addressString = addressTypeRenderer.getAddressString( this.confirmationData.address );

			return this.$t(
				'donation_confirmation_topbox_summary',
				{
					interval: intervalString,
					formattedAmount: amountString,
					paymentType: paymentTypeString,
					personType: personTypeString,
					address: addressString,
				}
			);
		},
		getEmail: function () {
			if ( this.confirmationData.addressType === 'anonym' ) {
				return '';
			}
			return this.$t( 'donation_confirmation_topbox_email', { email: this.confirmationData.address.email } );
		},
		getPaymentNotice: function () {
			const paymentTypeRenderer = paymentTypeRenderers[ this.confirmationData.donation.paymentType ];
			const paymentString = paymentTypeRenderer.getPaymentString( this.confirmationData.donation );
			if ( paymentString === '' ) {
				return '';
			}
			return this.$t( paymentString );
		},
		isBankDataVisible: function () {
			return this.confirmationData.donation.paymentType === 'UEB';
		},
	},
} );
</script>

<style lang="scss">
	@import "../../../scss/custom";

	.donation {
		&-summary {
			&-wrapper {
				border: 1px solid $fun-color-gray-mid;
				border-radius: 2px;
			}
			> span {
				display: block;
			}

			.bank-data-content {
				p {
					line-height: 2em;
				}
			}
		}
	}
	.payment {
		&-notice {
			margin-top: 18px;
		}
	}
</style>
