<template>
	<div id="payment" class="column is-full">
		<amount-selection
				:payment-amounts="paymentAmounts"
				:amount="amount"
				:title="$t('donation_form_payment_amount_title')"
				:error="amountIsValid ? '' : $t('donation_form_payment_amount_error')"
				v-on:amount-selected="sendAmountToStore"
		></amount-selection>
		<payment-interval
				:payment-intervals="paymentIntervals"
				:current-interval="interval"
				:title="$t('donation_form_payment_interval_title')"
				v-on:interval-selected="sendIntervalToStore"
		></payment-interval>
		<payment-type :payment-types="paymentTypes"></payment-type>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import AmountSelection from '@/components/shared/AmountSelection.vue';
import PaymentInterval from '@/components/shared/PaymentInterval.vue';
import PaymentType from '@/components/pages/donation_form/PaymentType.vue';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { setAmount, setInterval } from '@/store/payment/actionTypes';
import { mapGetters, mapState } from 'vuex';

export default Vue.extend( {
	name: 'Payment',
	components: {
		AmountSelection,
		PaymentInterval,
		PaymentType,
	},
	props: [ 'validateAmountUrl', 'paymentAmounts', 'paymentIntervals', 'paymentTypes' ],
	computed: {
		...mapState( {
			amount: ( state: any ) => state[ NS_PAYMENT ].values.amount,
			interval: ( state: any ) => state[ NS_PAYMENT ].values.interval,
		} ),
		...mapGetters( NS_PAYMENT, [ 'amountIsValid' ] ),
	},
	methods: {
		sendAmountToStore( amountValue: string ): Promise<null> {
			const payload = {
				amountValue,
				validateAmountUrl: this.$props.validateAmountUrl,
			};
			return this.$store.dispatch( action( NS_PAYMENT, setAmount ), payload );
		},
		sendIntervalToStore( interval: string ): void {
			this.$store.dispatch( action( NS_PAYMENT, setInterval ), interval );
		},
	},
} );
</script>

<style lang="scss">
	@import "../../../scss/custom";
</style>
