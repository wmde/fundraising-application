<template>
	<div class="has-background-bright columns">
		<div class="column is-two-thirds is-half-desktop">
			<div class="donation-summary">
				<span class="payment-summary" v-html="getSummary"></span>
				<span class="payment-email" v-html="getEmail"></span>
				<span class="payment-notice" v-html="getPaymentNotice"></span>
			</div>
		</div>
		<div class="column is-one-third is-half-desktop">Abc</div>
	</div>
</template>

<script lang="js">
	import Vue from 'vue';

	const addressTypeRenderers = {
		'person': ( address ) => { return new PrivateDonorRenderer( address ) },
		'firma': ( address ) => { return new CompanyDonorRenderer( address ) },
		'anonym': ( address ) => { return new AnonymousDonorRenderer( address ) },
	};

	class PrivateDonorRenderer {
		constructor ( address ) {
			this.address = address;
		}
		getPersonTypeString() {
			return 'donation_confirmation_topbox_donor_type_person'
		}
		getAddressString() {
			return this.address.salutation + ' ' + this.address.fullName + ', '
				+ this.address.streetAddress + ', ' + this.address.postalCode + ' ' + this.address.city;
		}
	}
	class CompanyDonorRenderer {
		constructor ( address ) {
			this.address = address;
		}
		getPersonTypeString() {
			return 'donation_confirmation_topbox_donor_type_company'
		}
		getAddressString() {
			return this.address.salutation + ' ' + this.address.fullName + ', '
				+ this.address.streetAddress + ', ' + this.address.postalCode + ' ' + this.address.city;
		}
	}
	class AnonymousDonorRenderer {
		constructor ( address ) {
			this.address = address;
		}
		getPersonTypeString() {
			return 'donation_confirmation_topbox_donor_type_anonymous';
		}
		getAddressString() {
			return '';
		}
	}

	const paymentTypeRenderers = {
		'UEB': ( donation ) => { return new BankTransferRenderer( donation ) },
		'BEZ': ( donation ) => { return new DirectDebitRenderer( donation ) },
		'PPL': ( donation ) => { return new EmptyRenderer( donation ) },
		'MCP': ( donation ) => { return new EmptyRenderer( donation ) },
		'SUB': ( donation ) => { return new EmptyRenderer( donation ) },
	};

	class BankTransferRenderer {
		constructor ( donation ) {
			this.donation = donation;
		}
		getPaymentString() {
			return 'donation_confirmation_subhead_bank_transfer';
		}
	}

	class DirectDebitRenderer {
		constructor ( donation ) {
			this.donation = donation;
		}
		getPaymentString() {
			return 'donation_confirmation_subhead_direct_debit';
		}
	}

	class EmptyRenderer {
		constructor ( donation ) {
			this.donation = donation;
		}
		getPaymentString() {
			return '';
		}
	}


	export default Vue.extend( {
		name: 'DonationSummary',
		props: [
			'confirmationData'
		],
		computed: {
			getSummary: function() {
				console.log(this.confirmationData.addressType);
				const addressTypeRenderer = addressTypeRenderers[ this.confirmationData.addressType ]( this.confirmationData.address );
				let intervalString = this.$t( 'donation_payment_interval_' + this.confirmationData.donation.interval );
				let amountString = this.confirmationData.donation.amount.toFixed( 2 ).replace( '.', ',' );
				let paymentTypeString = this.$t( this.confirmationData.donation.paymentType );
				let personTypeString = this.$t( addressTypeRenderer.getPersonTypeString() );
				let addressString = addressTypeRenderer.getAddressString();

				return this.$t(
					'donation_confirmation_topbox_summary',
					{
						interval: intervalString,
						formattedAmount: amountString,
						paymentType: paymentTypeString,
						personType: personTypeString,
						address: addressString
					}
				);
			},
			getEmail: function() {
				if ( this.confirmationData.addressType === 'anonym' ) {
					return ''
				}
				return this.$t( 'donation_confirmation_topbox_email', { email: this.confirmationData.address.email } )
			},
			getPaymentNotice: function() {
				const addressTypeRenderer = paymentTypeRenderers[ this.confirmationData.donation.paymentType ]( this.confirmationData.donation );
				const paymentString = addressTypeRenderer.getPaymentString();
				if ( paymentString === '' ) {
					return ''
				}
				return this.$t( paymentString );
			}
		}
	} );
</script>

<style lang="scss">
	@import "../../../scss/custom";

	.donation-summary {
		> span {
			display: block;
		}
	}
	.payment {
		&-notice {
			margin-top: 18px;
		}
	}
</style>
