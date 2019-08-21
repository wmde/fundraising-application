<template>
	<div class="has-margin-top-36">
		<b-checkbox type="checkbox" class="is-inline-checkbox"
			id="donation_receipt"
			name="receiptOptOut"
			v-model="optedOut"
			@change.native="setReceiptOptOut()">
			{{ $t( 'receipt_opt_out' ) }}
		</b-checkbox>
	</div>

</template>

<script>
import Vue from 'vue';

export default Vue.extend( {
	name: 'ReceiptOptOut',
	data: function () {
		return {
			optedOut: false,
		};
	},
	methods: {
		setReceiptOptOut: function () {
			this.$emit( 'opted-out', this.optedOut );
		},
	},
} );
</script>
