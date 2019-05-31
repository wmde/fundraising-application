<template>
    <div class="column is-full">
        <form>
            <payment-amount :payment-amounts="paymentAmounts" :validate-amount-url="validateAmountUrl"></payment-amount>
            <payment-interval :payment-intervals="paymentIntervals"></payment-interval>
            <payment-type :payment-types="paymentTypes"></payment-type>
            <div class="has-margin-top-36">
                <b-button @click="next()" type="is-primary is-main">{{ $t('donation_section_continue') }}</b-button>
            </div>
        </form>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import PaymentAmount from '@/components/pages/donation_form/PaymentAmount.vue';
import PaymentInterval from '@/components/pages/donation_form/PaymentInterval.vue';
import PaymentType from '@/components/pages/donation_form/PaymentType.vue';
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
	props: [ 'validateAmountUrl', 'paymentAmounts', 'paymentIntervals', 'paymentTypes' ],
	methods: {
		validatePaymentData(): void {
			this.$store.dispatch( action( NS_PAYMENT, markEmptyValuesAsInvalid ) );
		},
		next(): void {
			this.$emit( 'change-component', 'AddressForm' );
		},
	},
} );
</script>

<style lang="scss">
    @import "../../../scss/custom";
</style>
