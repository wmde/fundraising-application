<template>
	<form name="laika-donation-personal-data"
			id="laika-donation-personal-data"
			class="address-page"
			ref="personal"
			action="/donation/add"
			method="post">
		<h1 v-if="!paymentWasInitialized" class="title is-size-1">{{ $t( 'donation_form_section_headline' ) }}</h1>
		<div class="has-margin-left-18 has-margin-right-18">
			<payment-summary v-if="paymentWasInitialized"
							:amount="paymentSummary.amount"
							:payment-type="paymentSummary.paymentType"
							:interval="paymentSummary.interval"
							v-on:previous-page="previousPage">
			</payment-summary>
		</div>
		<address-fields
				:validate-address-url="validateAddressUrl"
				:validate-bank-data-url="validateBankDataUrl"
				:validate-legacy-bank-data-url="validateLegacyBankDataUrl"
				:countries="countries"
				ref="address">
		</address-fields>
			<div class="summary-wrapper has-margin-top-18 has-outside-border">
				<donation-summary :payment="paymentSummary" :address-type="addressType" :address="addressSummary">
					<div class="title is-size-5">{{ $t( 'donation_confirmation_review_headline' ) }}</div>
				</donation-summary>
				<submit-values :tracking-data="{}"></submit-values>
				<div class="columns has-margin-top-18">
					<div class="column">
						<b-button id="previous-btn" class="level-item"
								@click="previousPage"
								type="is-primary is-main"
								outlined>
							{{ $t('donation_form_section_back') }}
						</b-button>
					</div>
					<div class="column">
						<b-button id="submit-btn" :class="[ $store.getters.isValidating ? 'is-loading' : '', 'level-item' ]"
								@click="submit"
								type="is-primary is-main">
							{{ $t('donation_form_finalize') }}
						</b-button>
					</div>
				</div>
				<div class="summary-notice" v-if="isExternalPayment">{{ $t('donation_form_summary_external_payment') }}</div>
				<div class="summary-notice" v-if="isBankTransferPayment">{{ $t('donation_form_summary_bank_transfer_payment') }}</div>
		</div>
	</form>
</template>

<script lang="ts">
import Vue from 'vue';
import { addressTypeName } from '@/view_models/AddressTypeModel';
import { NS_ADDRESS, NS_BANKDATA, NS_PAYMENT } from '@/store/namespaces';
import AddressFields from '@/components/pages/donation_form/Address.vue';
import DonationSummary from '@/components/DonationSummary.vue';
import SubmitValues from '@/components/pages/donation_form/SubmitValues.vue';
import { TrackingData } from '@/view_models/SubmitValues';
import { action } from '@/store/util';
import { markEmptyValuesAsInvalid } from '@/store/bankdata/actionTypes';
import { waitForServerValidationToFinish } from '@/wait_for_server_validation';
import PaymentSummary from '@/components/pages/donation_form/PaymentSummary.vue';
import { discardInitialization } from '@/store/payment/actionTypes';
import { trackFormSubmission } from '@/tracking';

export default Vue.extend( {
	name: 'AddressPage',
	components: {
		AddressFields,
		DonationSummary,
		SubmitValues,
		PaymentSummary,
	},
	props: {
		validateAddressUrl: String,
		validateBankDataUrl: String,
		validateLegacyBankDataUrl: String,
		countries: Array as () => Array<String>,
		trackingData: Object as () => TrackingData,
	},
	computed: {
		paymentSummary: {
			get(): object {
				const payment = this.$store.state[ NS_PAYMENT ].values;
				return {
					interval: payment.interval,
					amount: payment.amount / 100,
					paymentType: payment.type,
				};
			},
		},
		addressType: {
			get(): string {
				return addressTypeName( this.$store.state[ NS_ADDRESS ].addressType );
			},
		},
		addressSummary: {
			get(): object {
				return {
					...this.$store.state[ NS_ADDRESS ].values,
					fullName: this.$store.getters[ NS_ADDRESS + '/fullName' ],
					streetAddress: this.$store.state[ NS_ADDRESS ].values.street,
					postalCode: this.$store.state[ NS_ADDRESS ].values.postcode,
					countryCode: this.$store.state[ NS_ADDRESS ].values.country,
				};
			},
		},
		isExternalPayment: {
			get(): boolean {
				return this.$store.getters[ NS_PAYMENT + '/isExternalPayment' ];
			},
		},
		isBankTransferPayment: {
			get(): boolean {
				return this.$store.getters[ NS_PAYMENT + '/isBankTransferPayment' ];
			},
		},
		paymentWasInitialized: {
			get(): boolean {
				return this.$store.state[ NS_PAYMENT ].initialized;
			},
		},
	},
	methods: {
		previousPage() {
			this.$store.dispatch( action( NS_PAYMENT, discardInitialization ) );
			this.$emit( 'previous-page' );
		},
		submit() {
			const validationCalls = [
				( this.$refs.address as any ).validateForm(),
			];
			if ( this.$store.getters[ NS_PAYMENT + '/isDirectDebitPayment' ] ) {
				validationCalls.push( this.$store.dispatch( action( NS_BANKDATA, markEmptyValuesAsInvalid ) ) );
			}
			Promise.all( validationCalls ).then( () => {
				// We need to wait for the asynchronous bank data validation, that might still be going on
				waitForServerValidationToFinish( this.$store ).then( () => {
					if ( this.$store.getters[ NS_ADDRESS + '/requiredFieldsAreValid' ] ) {
						if ( this.$store.getters[ NS_PAYMENT + '/isDirectDebitPayment' ] &&
							!this.$store.getters[ NS_BANKDATA + '/bankDataIsValid' ] ) {
							document.getElementsByClassName( 'help is-danger' )[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center', inline: 'nearest' } );
							return;
						}
						( this as any ).submitDonationForm();
					} else {
						document.getElementsByClassName( 'help is-danger' )[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center', inline: 'nearest' } );
					}
				} );
			} );
		},
		submitDonationForm(): void {
			const formPersonal = this.$refs.personal as HTMLFormElement;
			trackFormSubmission( formPersonal );
			formPersonal.submit();
		},
	},
} );
</script>
