<template>
    <div class="has-margin-top-18">
        <b-checkbox type="checkbox" class="is-inline-checkbox"
			id="donation_receipt"
			name="receiptOptOut"
			v-model="optedOut"
            @change.native="setReceiptOptOut()">
		</b-checkbox>
		<label class="news" for="donation_receipt">
            {{  $t( 'receipt_opt_out' ) }}
        </label>
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
			this.$emit( 'optedOut', this.optedOut );
		},
	},
} );
</script>
