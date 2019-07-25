<template>
	<div class="address-page">
		<address-fields v-bind="$props" ref="address"></address-fields>
		<div class="column">
			<div class="summary-wrapper">
				<donation-summary :payment="paymentSummary" :address-type="addressType" :address="addressSummary">
					<div class="title is-size-5">{{ $t( 'donation_confirmation_review_headline' ) }}</div>
				</donation-summary>
				<submit-values :tracking-data="{}"></submit-values>
				<div class="columns has-margin-top-36">
					<div class="column">
						<b-button id="previous-btn" class="level-item"
								@click="$emit( 'previous-page' )"
								type="is-primary is-main"
								outlined>
							{{ $t('donation_form_section_back') }}
						</b-button>
					</div>
					<div class="column">
						<b-button id="submit-btn" class="level-item"
								@click="submit"
								type="is-primary is-main">
							{{ $t('donation_form_finalize') }}
						</b-button>
					</div>
				</div>
				<div class="external-notice" v-if="isExternalPayment">{{ $t('donation_form_summary_external_payment') }}</div>
			</div>
		</div>

	</div>
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

export default Vue.extend( {
	name: 'AddressPage',
	components: {
		AddressFields,
		DonationSummary,
		SubmitValues,
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
	},
	methods: {
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
							return;
						}
						this.$emit( 'submit-donation' );
					}
				} );
			} );
		},
	},
} );
</script>
