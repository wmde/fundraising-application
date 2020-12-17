<template>
	<div class="payment-summary" v-html="getSummary"></div>
</template>

<script>
export default {
	name: 'PaymentSummaryAnonymous',
	props: [
		'address',
		'interval',
		'formattedAmount',
		'paymentType',
		'country',
	],
	computed: {
		getSummary: function () {
			return this.$t(
				'donation_confirmation_topbox_summary',
				{
					interval: this.$props.interval,
					formattedAmount: this.$props.formattedAmount,
					paymentType: this.$props.paymentType,
					personType: this.$t( 'donation_confirmation_topbox_donor_type_anonymous' ),
					address: '',
				}
			);
		},
	},
};
</script>

<style scoped>

</style>
