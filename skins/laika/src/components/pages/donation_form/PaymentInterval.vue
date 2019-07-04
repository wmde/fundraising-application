<template>
	<fieldset class="has-margin-top-36">
		<legend class="title is-size-5">{{ $t('donation_form_payment_interval_title') }}</legend>
		<div>
			<div class="wrap-radio" v-for="interval in paymentIntervals" :key="'interval-' + interval">
				<b-radio :class="{ 'is-active': selectedInterval === interval }"
						type="radio"
						:id="'interval-' + interval"
						name="interval"
						v-model="selectedInterval"
						:native-value="interval"
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
			this.$store.dispatch( action( NS_PAYMENT, setInterval ), this.selectedInterval );
		},
	},
	// workaround for Buefy state update, recommendation taken from https://github.com/buefy/buefy/issues/698
	mounted: function () {
		// Update on mount, since "mounted" is be called after the store was already updated
		// when we skip the payment page
		const currentStoreInterval = this.$store.state[ NS_PAYMENT ].values.interval;
		if ( Number( currentStoreInterval ) !== this.selectedInterval ) {
			this.selectedInterval = currentStoreInterval;
		}
		this.$store.watch(
			( state: any ) => state[ NS_PAYMENT ].values.interval,
			( newInterval ) => { this.selectedInterval = newInterval; }
		);
	},
} );
</script>
