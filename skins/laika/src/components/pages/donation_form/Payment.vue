<template>
	<div class="payment-section">
		<amount-selection
				:payment-amounts="paymentAmounts"
				:amount="amount"
				:title="$t('donation_form_payment_amount_title')"
				:error="showErrorMessage"
				v-on:amount-selected="sendAmountToStore"
		></amount-selection>
		<payment-interval
				class="has-margin-top-36"
				:payment-intervals="paymentIntervals"
				:current-interval="interval"
				:title="$t('donation_form_payment_interval_title')"
				:disabled-payment-intervals="disabledPaymentIntervals"
				v-on:interval-selected="sendIntervalToStore"
		></payment-interval>
		<payment-type
				class="has-margin-top-36"
				:current-type="type"
				:payment-types="paymentTypes"
				:error="typeIsValid ? '' : $t('donation_form_payment_type_error')"
				:title="$t('donation_form_payment_type_title')"
				:disabled-payment-types="disabledPaymentTypes"
				v-on:payment-type-selected="sendTypeToStore"
		></payment-type>
		<div v-show="disabledPaymentTypes.indexOf( 'BEZ' ) > -1 " class="has-margin-top-18">{{ $t( 'donation_form_address_choice_direct_debit_disclaimer' ) }}</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import AmountSelection from '@/components/shared/AmountSelection.vue';
import PaymentInterval from '@/components/shared/PaymentInterval.vue';
import PaymentType from '@/components/pages/donation_form/PaymentType.vue';
import { action } from '@/store/util';
import { NS_ADDRESS, NS_PAYMENT } from '@/store/namespaces';
import { setAmount, setInterval, setType } from '@/store/payment/actionTypes';
import { mapGetters, mapState } from 'vuex';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { AmountValidity } from '@/view_models/Payment';

export default Vue.extend( {
	name: 'Payment',
	components: {
		AmountSelection,
		PaymentInterval,
		PaymentType,
	},
	props: [ 'validateAmountUrl', 'paymentAmounts', 'paymentIntervals', 'paymentTypes' ],
	computed: {
		...mapState( {
			amount: ( state: any ) => state[ NS_PAYMENT ].values.amount,
			interval: ( state: any ) => state[ NS_PAYMENT ].values.interval,
			type: ( state: any ) => state[ NS_PAYMENT ].values.type,
			disabledPaymentTypes: ( state: any ) => {
				var disabledTypes : String[] = [];
				if ( state[ NS_ADDRESS ].addressType === AddressTypeModel.ANON ) {
					disabledTypes.push( 'BEZ' );
				}
				if ( state[ NS_PAYMENT ].values.interval !== '0' ) {
					disabledTypes.push( 'SUB' );
				}
				return disabledTypes;
			},
			disabledPaymentIntervals: ( state: any ) => {
				var disabledIntervals : String[] = [];
				if ( state[ NS_PAYMENT ].values.type === 'SUB' ) {
					disabledIntervals.push( '1', '3', '6', '12' );
				}
				return disabledIntervals;
			},
		} ),
		...mapGetters( NS_PAYMENT, [ 'amountValidity', 'typeIsValid' ] ),
		showErrorMessage(): String {
			const messages : { [ key:number ]:string; } = {
				[ AmountValidity.AMOUNT_VALID ]: '',
				[ AmountValidity.AMOUNT_TOO_LOW ]: this.$t( 'donation_form_payment_amount_error' ) as string,
				[ AmountValidity.AMOUNT_TOO_HIGH ]: this.$t( 'donation_form_payment_amount_too_high' ) as string,
			};
			return messages[ this.$store.getters[ NS_PAYMENT + '/amountValidity' ] ];
		},
	},
	methods: {
		sendAmountToStore( amountValue: string ): Promise<null> {
			const payload = {
				amountValue,
				validateAmountUrl: this.$props.validateAmountUrl,
			};
			return this.$store.dispatch( action( NS_PAYMENT, setAmount ), payload );
		},
		sendIntervalToStore( interval: string ): void {
			this.$store.dispatch( action( NS_PAYMENT, setInterval ), interval );
		},
		sendTypeToStore( paymentType: string ): void {
			this.$store.dispatch( action( NS_PAYMENT, setType ), paymentType );
		},
	},
} );
</script>

<style lang="scss">
	@import "../../../scss/custom";
</style>
