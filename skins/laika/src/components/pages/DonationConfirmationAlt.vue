<template>
	<div class="donation-confirmation">
		<div class="donation-summary-wrapper has-background-bright columns has-padding-18">
			<div class="column is-half">
				<div class="title is-size-5" v-if="showBankTransferContent">{{ $t( 'donation_confirmation_topbox_payment_title_bank_transfer_alt' ) }}</div>
				<div class="title is-size-5" v-if="!showBankTransferContent">{{ $t( 'donation_confirmation_topbox_payment_title_alt' ) }}</div>
				<payment-notice :payment="donation"></payment-notice>
				<div id="bank-data" v-if="showBankTransferContent">
					<bank-data :bank-transfer-code="donation.bankTransferCode"></bank-data>
					<div class="has-margin-top-18"
						v-html="$t( 'donation_confirmation_reminder_bank_transfer', { bankTransferCode: donation.bankTransferCode } )">
					</div>
				</div>
				<div id="newsletter-optin" class="has-margin-top-18" v-if="donation.optsIntoNewsletter">
					{{ $t( 'donation_confirmation_newsletter_confirmation' ) }}
				</div>
				<summary-links
					:donation="donation"
					:address-type="currentAddressType"
					:cancel-donation-url="cancelDonationUrl"
					:post-comment-url="postCommentUrl"
				/>
			</div>

			<div class="column is-half">
				<div class="donation-cta">
					<div v-if="showAddressChangeContent">
						<p class="has-margin-bottom-18"><strong>Benötigen Sie eine Spendenquittung?</strong></p>
						<p class="has-margin-bottom-18">Hier erläutern wir die Vorteile der Angabe von Adressdaten und auch, warum sie diese Spende benötigen</p>
						<b-button
							id="address-change-button"
							class="address-change-button"
							@click="showAddressModal()"
							type="is-primary is-main"
						>
							{{ $t('donation_confirmation_address_update_button') }}
						</b-button>
						<address-usage-toggle></address-usage-toggle>
					</div>
					<b-modal :active.sync="isAddressModalOpen" scroll="keep" class="address-modal" has-modal-card>
						<address-modal
							:countries="countries"
							:donation="donation"
							:updateDonorUrl="updateDonorUrl"
							:validate-address-url="validateAddressUrl"
							:validate-email-url="validateEmailUrl"
							:has-errored="addressChangeHasErrored"
							:has-succeeded="addressChangeHasSucceeded"
							:address-validation-patterns="addressValidationPatterns"
							v-on:address-update-failed="addressChangeHasErrored = true"
							v-on:address-updated="updateAddress( $event )"
						>
						</address-modal>
					</b-modal>
					<donation-summary
						v-if="!showAddressChangeContent"
						:address="currentAddress"
						:address-type="currentAddressType"
						:payment="donation"
						:countries="countries"
					>
						<div><strong>{{ $t( 'donation_confirmation_summary_title_alt' ) }}</strong></div>
					</donation-summary>
				</div>
			</div>
		</div>
		<membership-info :donation="donation"></membership-info>
		<img :src="'https://de.wikipedia.org/wiki/Special:HideBanners?duration=' + donation.cookieDuration + '&reason=donate'"
			alt=""
			width="0"
			height="0"
		/>
		<img src="https://bruce.wikipedia.de/finish-donation?c=fundraising"
			alt=""
			width="0"
			height="0"
		/>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import BankData from '@/components/BankData.vue';
import DonationSummary from '@/components/DonationSummary.vue';
import MembershipInfo from '@/components/pages/donation_confirmation/MembershipInfo.vue';
import PaymentNotice from '@/components/pages/donation_confirmation/PaymentNoticeAlt.vue';
import SummaryLinks from '@/components/pages/donation_confirmation/SummaryLinks.vue';
import AddressUsageToggle from '@/components/pages/donation_confirmation/AddressUsageToggle.vue';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';
import AddressModal from '@/components/pages/donation_confirmation/AddressModal.vue';
import { Country } from '@/view_models/Country';
import { SubmittedAddress } from '@/view_models/Address';
import { Donation } from '@/view_models/Donation';
import { AddressValidation } from '@/view_models/Validation';

export default Vue.extend( {
	name: 'DonationConfirmation',
	components: {
		BankData,
		DonationSummary,
		MembershipInfo,
		PaymentNotice,
		SummaryLinks,
		AddressUsageToggle,
		AddressModal,
	},
	data: function () {
		return {
			isAddressModalOpen: false,
			addressChangeHasErrored: false,
			addressChangeHasSucceeded: false,
			currentAddress: this.$props.address,
			currentAddressType: this.$props.addressType,
		};
	},
	props: {
		donation: Object as () => Donation,
		address: Object,
		addressType: String,
		updateDonorUrl: String,
		cancelMembershipUrl: String,
		validateAddressUrl: String,
		validateEmailUrl: String,
		cancelDonationUrl: String,
		postCommentUrl: String,
		countries: Array as () => Array<Country>,
		addressValidationPatterns: Object as () => AddressValidation,
	},
	methods: {
		showAddressModal: function () {
			this.$data.isAddressModalOpen = true;
		},
		updateAddress: function ( submittedAddress: SubmittedAddress ) {
			this.$data.addressChangeHasSucceeded = true;
			this.$data.currentAddress = submittedAddress.addressData;
			this.$data.currentAddressType = submittedAddress.addressType;
		},
	},
	computed: {
		showBankTransferContent: function () {
			return this.$props.donation.paymentType === 'UEB';
		},
		showAddressChangeContent: function () {
			return this.$props.addressType === addressTypeName( AddressTypeModel.ANON ) &&
					!this.$data.addressChangeHasErrored && !this.$data.addressChangeHasSucceeded;
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

				.address-change-button {
					width: 100%;
					white-space: normal;
				}
			}

			.bank-data-content {
				p {
					line-height: 2em;
				}
			}
		}
	}
</style>
