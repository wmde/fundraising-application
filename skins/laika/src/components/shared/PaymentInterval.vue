<template>
	<fieldset>
		<legend class="title is-size-5">{{ title }}</legend>
		<div>
			<div class="wrap-radio" v-for="interval in paymentIntervals" :key="'interval-' + interval">
				<b-radio :class="{ 'is-active': selectedInterval === interval.toString() }"
						:id="'interval-' + interval"
						name="interval"
						v-model="selectedInterval"
						:native-value="interval.toString()" :disabled="disabledPaymentIntervals.indexOf( interval.toString() ) > -1"
						@change.native="setInterval">
					{{ $t( 'donation_form_payment_interval_' + interval.toString() ) }}
					<div v-show="disabledPaymentIntervals.length && disabledPaymentIntervals.indexOf( interval.toString() ) === -1 " class="has-margin-top-18">
						Eine regelmäßige Zahlung per Sofortüberweisung ist nicht möglich.
					</div>
				</b-radio>
			</div>
		</div>
	</fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { IntervalData } from '@/view_models/Payment';

export default Vue.extend( {
	name: 'PaymentInterval',
	data: function (): IntervalData {
		return {
			selectedInterval: this.$props.currentInterval,
		};
	},
	props: {
		currentInterval: String,
		paymentIntervals: Array,
		title: String,
		disabledPaymentIntervals: {
			type: Array,
			default: () => [],
		},
	},
	methods: {
		setInterval(): void {
			this.$emit( 'interval-selected', this.$data.selectedInterval );
		},
	},
	watch: {
		currentInterval: function ( newInterval: string ): void {
			this.selectedInterval = newInterval;
		},
	},
} );
</script>
