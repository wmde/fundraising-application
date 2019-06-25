<template>
	<div class="column is-full">
		<div class="donation-summary-wrapper has-background-bright columns has-padding-18">

			<div class="column is-half">
				<donation-summary :address="confirmationData.address" :address-type="confirmationData.addressType"
					:payment="confirmationData.donation"></donation-summary>
				<payment-notice :payment="confirmationData.donation"></payment-notice>
				<div v-if="showBankTransferCode">
					<BankData :bank-transfer-code="confirmationData.donation.bankTransferCode"></BankData>
					<div class="has-margin-top-18"
						v-html="$t( 'donation_confirmation_reminder_bank_transfer', { bankTransferCode: confirmationData.donation.bankTransferCode } )">
					</div>
				</div>
			</div>

			<div class="column is-half">
				<SummaryLinks :confirmation-data="confirmationData"/>
			</div>
		</div>
		<membership-info :confirmation-data="confirmationData"></membership-info>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import BankData from '@/components/BankData.vue';
import DonationSummary from '@/components/DonationSummary.vue';
import MembershipInfo from '@/components/pages/donation_confirmation/MembershipInfo.vue';
import PaymentNotice from '@/components/pages/donation_confirmation/PaymentNotice.vue';
import SummaryLinks from '@/components/pages/donation_confirmation/SummaryLinks.vue';

export default Vue.extend( {
	name: 'DonationConfirmation',
	components: {
		BankData,
		DonationSummary,
		MembershipInfo,
		PaymentNotice,
		SummaryLinks,
	},
	props: [
		'confirmationData',
	],
	computed: {
		showBankTransferCode: function () {
			return this.confirmationData.donation.paymentType === 'UEB';
		},

	},
} );
</script>

<style lang="scss">
	@import "../../scss/custom";

	.donation {
		&-summary {
			&-wrapper {
				border: 1px solid $fun-color-gray-mid;
				border-radius: 2px;
			}

			.bank-data-content {
				p {
					line-height: 2em;
				}
			}
		}
	}
</style>
