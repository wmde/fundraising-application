<template>
    <div class="column is-full">
        <form>
            <payment-amount :paymentAmounts="paymentAmounts" :validateAmountURL="validateAmountURL"></payment-amount>
            <payment-interval :paymentIntervals="paymentIntervals"></payment-interval>
            <payment-type :paymentTypes="paymentTypes"></payment-type>
            <div class="has-margin-top-36">
                <b-button type="is-primary is-main">Weiter</b-button>
            </div>
        </form>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import PaymentAmount from './donation_form/PaymentAmount.vue';
import PaymentInterval from './donation_form/PaymentInterval.vue';
import PaymentType from './donation_form/PaymentType.vue';
import { markEmptyValuesAsInvalid } from '@/store/payment/actionTypes';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';

export default Vue.extend( {
	name: 'Payment',
	components: {
		PaymentAmount,
		PaymentInterval,
		PaymentType,
	},
	props: [ 'paymentAmounts', 'validateAmountURL', 'paymentIntervals', 'paymentIntervalMessages', 'paymentTypes' ],
	methods: {
		validatePaymentData(): void {
			this.$store.dispatch( action( NS_PAYMENT, markEmptyValuesAsInvalid ) );
		},
	},
} );
</script>

<style lang="scss">
    button.is-main {
        height: 54px;
        font-size: 1em;
        font-weight: bold;
        width: 270px;
        border-radius: 0;
    }
</style>
