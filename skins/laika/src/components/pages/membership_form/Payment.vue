<template>
	<div id="payment" class="column is-full">
		<payment-interval
				:payment-intervals="paymentIntervals"
				:current-interval="interval"
				:title="$t('donation_form_payment_interval_title')"
				v-on:interval-selected="sendIntervalToStore"
		></payment-interval>
		<amount-selection
				class="has-margin-top-36"
				:payment-amounts="paymentAmounts"
				:amount="fee"
				:minimum-amount="getMinimumAmount"
				:title="$t('membership_form_payment_amount_title')"
				:caption="$t('membership_form_payment_amount_description')"
				:error="feeIsValid ? '' : $t('donation_form_payment_amount_error')"
				v-on:amount-selected="sendAmountToStore"
		></amount-selection>
		<payment-bank-data
			class="has-margin-top-36"
			:validateBankDataUrl="validateBankDataUrl"
			:validateLegacyBankDataUrl="validateLegacyBankDataUrl"
		></payment-bank-data>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import AmountSelection from '@/components/shared/AmountSelection.vue';
import PaymentInterval from '@/components/shared/PaymentInterval.vue';
import PaymentBankData from '@/components/shared/PaymentBankData.vue';

import { action } from '@/store/util';
import { NS_MEMBERSHIP_ADDRESS, NS_MEMBERSHIP_FEE } from '@/store/namespaces';
import { mapGetters, mapState } from 'vuex';
import { setFee, setInterval } from '@/store/membership_fee/actionTypes';
import { SetFeePayload } from '@/view_models/MembershipFee';

export default Vue.extend( {
	name: 'Payment',
	components: {
		AmountSelection,
		PaymentInterval,
		PaymentBankData,
	},
	props: {
		validateFeeUrl: String,
		paymentAmounts: Array as () => Array<Number>,
		paymentIntervals: Array as () => Array<String>,
		validateBankDataUrl: String,
		validateLegacyBankDataUrl: String,
	},
	computed: {
		...mapState( {
			fee: ( state: any ) => state[ NS_MEMBERSHIP_FEE ].values.fee,
			interval: ( state: any ) => state[ NS_MEMBERSHIP_FEE ].values.interval,
		} ),
		...mapGetters( NS_MEMBERSHIP_FEE, {
			feeIsValid: 'feeIsValid',
			typeIsValid: 'typeIsValid',
			minimumAmount: 'minimumAmount',
		} ),
		getMinimumAmount: {
			get: function () {
				return ( this as any ).minimumAmount( this.$store.getters[ NS_MEMBERSHIP_ADDRESS + '/addressType' ] );
			},
		},
	},
	methods: {
		sendAmountToStore( amountValue: string ): Promise<null> {
			const payload = {
				feeValue: amountValue,
				validateFeeUrl: this.validateFeeUrl,
			} as SetFeePayload;
			return this.$store.dispatch( action( NS_MEMBERSHIP_FEE, setFee ), payload );
		},
		sendIntervalToStore( interval: string ): void {
			this.$store.dispatch( action( NS_MEMBERSHIP_FEE, setInterval ), interval );
		},
	},
} );
</script>

<style lang="scss">
	@import "../../../scss/custom";
</style>
