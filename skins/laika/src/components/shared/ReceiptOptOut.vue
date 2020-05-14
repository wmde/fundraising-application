<template>
	<div class="opt-out-box">
		<b-checkbox type="checkbox" class="is-inline-checkbox"
			id="donation_receipt"
			name="receiptOptOut"
			v-model="receiptNeeded"
			@change.native="setReceiptOptOut()">
			{{ message }}
		</b-checkbox>
	</div>

</template>

<script>
import Vue from 'vue';

export default Vue.extend( {
	name: 'ReceiptOptOut',
	data: function () {
		return {
			receiptNeeded: true,
		};
	},
	props: {
		message: String,
	},
	methods: {
		setReceiptOptOut: function () {
			this.$emit( 'opted-out', !this.receiptNeeded );
		},
	},
} );
</script>
