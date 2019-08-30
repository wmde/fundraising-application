<template>
	<div class="payment-summary has-outside-border has-margin-top-0 has-margin-bottom-18">
			<p class="has-margin-bottom-18" v-html="getSummary()"></p>
			<b-button @click="$emit( 'previous-page' )"
					type="is-primary is-low"
					outlined>
				{{ $t('donation_form_section_back') }}
			</b-button>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'PaymentSummary',
	props: [ 'amount', 'interval', 'paymentType' ],
	methods: {
		getSummary: function () {
			// TODO find a way to create a computed proiperty instead of method where $t is still available
			const interval = this.$t( 'donation_form_payment_interval_' + this.$props.interval );
			const formattedAmount = this.$props.amount.toFixed( 2 ).replace( '.', ',' );
			const paymentType = this.$t( this.$props.paymentType );
			return this.$t( 'donation_form_payment_summary', { interval: interval, formattedAmount, paymentType } );
		},
	},
} );
</script>
