<template>
	<div class="has-margin-top-18 payment-notice" v-html="paymentNotice"></div>
</template>

<script>

class BankTransferRenderer {
	static getPaymentString() {
		return 'donation_confirmation_payment_bank_transfer';
	}
}

class DirectDebitRenderer {
	static getPaymentString() {
		return 'donation_confirmation_payment_direct_debit';
	}
}

class EmptyRenderer {
	static getPaymentString() {
		return '';
	}
}

const paymentTypeRenderers = {
	'UEB': BankTransferRenderer,
	'BEZ': DirectDebitRenderer,
	'PPL': EmptyRenderer,
	'MCP': EmptyRenderer,
	'SUB': EmptyRenderer,
};

export default {
	name: 'PaymentNotice',
	props: [ 'payment' ],
	computed: {
		paymentNotice: function () {
			const paymentTypeRenderer = paymentTypeRenderers[ this.payment.paymentType ];
			const paymentString = paymentTypeRenderer.getPaymentString();
			if ( paymentString === '' ) {
				return '';
			}
			return this.$t( paymentString );
		},
	},
};
</script>
