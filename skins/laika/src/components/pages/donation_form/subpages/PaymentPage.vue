<template>
	<form name="laika-donation-payment"
		id="laika-donation-payment"
		class="payment-page"
		ref="payment"
		action="/donation/add"
		method="post"
		@keydown.enter.prevent="next()">
		<payment v-bind="$props"></payment>
		<div class="level has-margin-top-36">
			<div class="level-left">
				<b-button id="next" :class="[ 'is-form-input-width', $store.getters.isValidating ? 'is-loading' : '', 'level-item']"
						@click="next()"
						type="is-primary is-main">
					{{ $t('donation_form_section_continue') }}
				</b-button>
			</div>
		</div>
	</form>
</template>

<script lang="ts">
import Vue from 'vue';
import Payment from '@/components/pages/donation_form/Payment.vue';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { markEmptyValuesAsInvalid } from '@/store/payment/actionTypes';
import { waitForServerValidationToFinish } from '@/wait_for_server_validation';
import { trackFormSubmission } from '@/tracking';
import scrollToFirstError from '@/scroll_to_first_error';

export default Vue.extend( {
	name: 'PaymentPage',
	components: {
		Payment,
	},
	props: {
		validateAmountUrl: String,
		paymentAmounts: Array as () => Array<String>,
		paymentIntervals: Array as () => Array<Number>,
		paymentTypes: Array as () => Array<String>,
	},
	methods: {
		next() {
			return waitForServerValidationToFinish( this.$store ).then( () => {
				this.$store.dispatch( action( NS_PAYMENT, markEmptyValuesAsInvalid ) ).then( () => {
					if ( this.$store.getters[ NS_PAYMENT + '/paymentDataIsValid' ] ) {
						const formPayment = this.$refs.payment as HTMLFormElement;
						trackFormSubmission( formPayment );
						this.$emit( 'next-page' );
					} else {
						scrollToFirstError();
					}
				} );
			} );
		},
	},
} );
</script>
