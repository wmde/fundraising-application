<template>
	<div class="address-page">
		<address-fields v-bind="$props"></address-fields>
		<div class="column">
			<div class="summary-wrapper">
				<donation-summary :payment="paymentSummary" :address-type="addressType" :address="addressSummary">
					<div class="title is-size-5">{{ $t( 'donation_confirmation_review_headline' ) }}</div>
				</donation-summary>
				<submit-values :tracking-data="{}"></submit-values>
				<div class="level has-margin-top-36">
					<div class="level-left">
						<b-button id="previous-btn" class="level-item"
								@click="$emit( 'previous-page' )"
								type="is-primary is-main"
								outlined>
							{{ $t('donation_form_section_back') }}
						</b-button>
					</div>
					<div class="level-right">
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
import { addressTypeName } from '../../../../view_models/AddressTypeModel';
import { NS_ADDRESS, NS_PAYMENT } from '@/store/namespaces';
import AddressFields from '@/components/pages/donation_form/Address.vue';
import DonationSummary from '@/components/DonationSummary.vue';
import SubmitValues from '@/components/pages/donation_form/SubmitValues.vue';
import { TrackingData } from '@/view_models/SubmitValues';

export default Vue.extend( {
	name: 'AddressPage',
	components: {
		AddressFields,
		DonationSummary,
		SubmitValues,
	},
	props: {
		validateAddressUrl: String,
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
			// TODO validate address, then submit form
		},
	},
} );
</script>

<style lang="scss" scoped>
	@import "../../../../scss/custom";

	.summary-wrapper {
		border: 2px solid $fun-color-gray-light-solid;
		padding: 18px;
	}

	.external-notice {
		color: $fun-color-dark-lighter;
	}
</style>
