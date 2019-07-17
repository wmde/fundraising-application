<template>
	<fieldset>
		<legend class="title is-size-5">{{ title }}</legend>
		<div>
			<div class="wrap-radio" v-for="interval in paymentIntervals" :key="'interval-' + interval">
				<b-radio :class="{ 'is-active': selectedInterval === interval }"
						type="radio"
						:id="'interval-' + interval"
						name="interval"
						v-model="selectedInterval"
						:native-value="interval.toString()"
						@change.native="setInterval">
					{{ $t( 'donation_form_payment_interval_' + interval.toString() ) }}
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
