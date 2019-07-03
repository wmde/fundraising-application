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
        <div class="explanation">
            {{ $t( 'receipt_opt_out_explanation' ) }}
        </div>
    </div>

</template>

<script>
import Vue from 'vue';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { action } from '@/store/util';
import { setReceiptOptOut } from '@/store/membership_address/actionTypes';

export default Vue.extend( {
	name: 'ReceiptOptOut',
	data: function () {
		return {
			optedOut: false,
		};
	},
	methods: {
		setReceiptOptOut: function () {
			this.$store.dispatch( action( NS_MEMBERSHIP_ADDRESS, setReceiptOptOut ), this.$data.optedOut );
		},
	},
} );
</script>
