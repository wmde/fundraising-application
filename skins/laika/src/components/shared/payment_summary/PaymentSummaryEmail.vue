<template>
	<div class="payment-summary" v-html="getSummary()"></div>
</template>

<script>
export default {
	name: 'PaymentSummaryEmail',
	props: [
		'address',
		'interval',
		'formattedAmount',
		'paymentType',
		'country',
		'languageItem',
	],
	methods: {
		getSummary: function () {
			return this.$t(
				this.$props.languageItem,
				{
					interval: this.$props.interval,
					formattedAmount: this.$props.formattedAmount,
					paymentType: this.$props.paymentType,
					personType: this.$t( 'donation_confirmation_topbox_donor_type_email' ),
					address: this.addressString(),
				}
			);
		},
		addressString: function () {
			if ( !this.canRenderAddress() ) {
				return this.$t( 'donation_confirmation_review_address_missing' );
			}
			return this.$props.address.salutation + ' ' + this.$props.address.fullName;
		},
		canRenderAddress: function () {
			return this.$props.address.salutation
				&& this.$props.address.firstName
				&& this.$props.address.lastName;
		},
	},
};
</script>

<style scoped>

</style>
