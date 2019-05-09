<template>
    <fieldset>
        <h2 class="title is-size-5">{{ $t('donation_form_interval_title') }}</h2>
        <div>
            <div class="wrap-radio" v-for="option in paymentIntervals" :key="option.id">
                <input class="is-checkradio is-info"
                       type="radio"
                       :id="option.id"
                       name="interval"
                       v-model="selectedInterval"
                       :value="option.interval"
                       @change="setInterval()">
                <label :for="option.id">
                    <span>{{ $t( "donation_payment_interval_" + option.interval.toString() ) }}</span>
                </label>
            </div>
        </div>
    </fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { IntervalData } from '@/view_models/Payment';
import { NS_PAYMENT } from '@/store/namespaces';
import { action } from '@/store/util';
import { setInterval } from '@/store/payment/actionTypes';

export default Vue.extend( {
	name: 'PaymentInterval',
	data: function (): IntervalData {
		return {
			selectedInterval: 0,
		};
	},
	props: [ 'paymentIntervals' ],
	methods: {
		setInterval(): void {
			this.$store.dispatch( action( NS_PAYMENT, setInterval ), this.$data.selectedInterval );
		},
	},
} );
</script>
