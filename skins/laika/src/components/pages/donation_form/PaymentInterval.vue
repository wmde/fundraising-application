<template>
    <fieldset>
        <h2>Wie häufig möchten Sie spenden?</h2>
        <div>
            <div class="wrap-radio" v-for="option in paymentIntervals" :key="option.id">
                <input type="radio"
                       :id="option.id"
                       name="interval"
                       v-model="selectedInterval"
                       :value="option.interval"
                       @change="registerInterval()">
                <label :for="option.id">
                    <span>{{ $t( "donation_payment_interval_" + option.interval.toString() ) }}</span>
                </label>
            </div>
        </div>
    </fieldset>
</template>

<script lang="ts">
	import Vue from 'vue';
	import { IntervalData } from "@/view_models/Payment";

	export default Vue.extend( {
		name: 'PaymentInterval',
		data: function (): IntervalData {
			return {
				selectedInterval: 0,
			};
		},
		props: [ 'paymentIntervals' ],
		methods: {
			registerInterval(): void {
				this.$store.dispatch( 'payment/registerInterval', this.$data.selectedInterval );
            }
        },
	} );
</script>
