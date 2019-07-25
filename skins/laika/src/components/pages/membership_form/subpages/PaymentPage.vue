<template>
	<div class="payment-page">
		<payment v-bind="$props"></payment>
		<submit-values></submit-values>
		<div class="summary-wrapper">
			<div class="columns has-margin-top-36">
				<div class="column">
					<b-button id="previous-btn" class="level-item"
						@click="$emit( 'previous-page' )"
						type="is-primary is-main"
						outlined>
						{{ $t('donation_form_section_back') }}
					</b-button>
				</div>
				<div class="column">
					<b-button id="submit-btn" class="level-item"
						@click="submit"
						type="is-primary is-main">
						{{ $t('membership_form_finalize') }}
					</b-button>
				</div>
			</div>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import Payment from '@/components/pages/membership_form/Payment.vue';
import SubmitValues from '@/components/pages/membership_form/SubmitValues.vue';
import { NS_BANKDATA, NS_MEMBERSHIP_FEE } from '@/store/namespaces';
import { action } from '@/store/util';
import { markEmptyValuesAsInvalid as markEmptyFeeValuesAsInvalid } from '@/store/membership_fee/actionTypes';
import { markEmptyValuesAsInvalid as markemptyBankDataValuesAsInvalid } from '@/store/bankdata/actionTypes';
import { waitForServerValidationToFinish } from '@/wait_for_server_validation';

export default Vue.extend( {
	name: 'PaymentPage',
	components: {
		Payment,
		SubmitValues,
	},
	props: {
		validateFeeUrl: String,
		paymentAmounts: Array as () => Array<String>,
		paymentIntervals: Array as () => Array<Number>,
		validateBankDataUrl: String,
		validateLegacyBankDataUrl: String,
	},
	methods: {
		submit() {
			waitForServerValidationToFinish( this.$store ).then( () => {
				return Promise.all( [
					this.$store.dispatch( action( NS_MEMBERSHIP_FEE, markEmptyFeeValuesAsInvalid ) ),
					this.$store.dispatch( action( NS_BANKDATA, markemptyBankDataValuesAsInvalid ) ),
				] ).then( () => {
					if ( this.$store.getters[ NS_MEMBERSHIP_FEE + '/paymentDataIsValid' ] &&
						this.$store.getters[ NS_BANKDATA + '/bankDataIsValid' ] ) {
						this.$emit( 'submit-membership' );
					}
				} );
			} );

		},
	},
} );
</script>
