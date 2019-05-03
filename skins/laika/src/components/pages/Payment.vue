<template>
	<div class="column is-full">
		<form>
			<payment-amount :paymentAmounts="paymentAmounts"></payment-amount>
		</form>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import PaymentAmount from './donation_form/PaymentAmount.vue'

export default Vue.extend( {
	name: 'Payment',
	components: {
		PaymentAmount,
	},
	props: [ 'paymentAmounts' ],
} );
</script>
