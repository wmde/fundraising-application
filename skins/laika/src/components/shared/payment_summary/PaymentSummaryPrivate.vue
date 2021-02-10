<template>
	<div class="payment-summary" v-html="getSummary()"></div>
</template>

<script>
export default {
	name: 'PaymentSummaryPrivate',
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
					personType: this.$t( 'donation_confirmation_topbox_donor_type_person' ),
					address: this.addressString(),
				}
			);
		},
		addressString: function () {
			if ( !this.canRenderAddress() ) {
				return this.$t( 'donation_confirmation_review_address_missing' );
			}
			return [
				this.$props.address.salutation + ' ' + this.$props.address.fullName,
				this.$props.address.streetAddress,
				this.$props.address.postalCode + ' ' + this.$props.address.city,
				this.$props.country,
			].join( ', ' );
		},
		canRenderAddress: function () {
			return this.$props.address.salutation
				&& this.$props.address.firstName
				&& this.$props.address.lastName
				&& this.$props.address.streetAddress
				&& this.$props.address.postalCode
				&& this.$props.address.city;
		},
	},
};
</script>

<style scoped>

</style>
