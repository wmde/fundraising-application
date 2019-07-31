<template>
	<div class="payment-page">
		<payment v-bind="$props"></payment>
		<div class="level has-margin-top-36">
			<div class="level-left">
				<b-button id="next" :class="[ $store.getters.isValidating ? 'is-loading' : '', 'level-item']"
						@click="next()"
						type="is-primary is-main">
					{{ $t('donation_form_section_continue') }}
				</b-button>
			</div>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import Payment from '@/components/pages/donation_form/Payment.vue';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { markEmptyValuesAsInvalid } from '@/store/payment/actionTypes';
import { waitForServerValidationToFinish } from '@/wait_for_server_validation';

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
						this.$emit( 'next-page' );
					} else {
						document.getElementsByClassName( 'is-danger' )[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center', inline: 'nearest' } );
					}
				} );
			} );
		},
	},
} );
</script>

<style scoped>

</style>
