<template>
    <div class="column is-full">
        <form>
            <payment-amount :paymentAmounts="paymentAmounts" :validateAmountURL="validateAmountURL"></payment-amount>
            <payment-interval :paymentIntervals="paymentIntervals"></payment-interval>
            <payment-option :paymentOptions="paymentOptions"></payment-option>
            <a class="button is-large is-primary" @click="validatePaymentData">Weiter</a>
        </form>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import PaymentAmount from './donation_form/PaymentAmount.vue';
import PaymentInterval from './donation_form/PaymentInterval.vue';
import PaymentOption from './donation_form/PaymentOption.vue';
import { validatePayment } from '@/store/payment/actionTypes';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';

export default Vue.extend( {
	name: 'Payment',
	components: {
		PaymentAmount,
		PaymentInterval,
		PaymentOption,
	},
	props: [ 'paymentAmounts', 'validateAmountURL', 'paymentIntervals', 'paymentIntervalMessages', 'paymentOptions' ],
	methods: {
		validatePaymentData(): void {
			this.$store.dispatch( action( NS_PAYMENT, validatePayment ) );
		},
	},

} );
</script>
