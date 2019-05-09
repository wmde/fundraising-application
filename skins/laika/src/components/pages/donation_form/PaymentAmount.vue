<template>
	<fieldset>
		<h2>Welchen Betrag möchten Sie spenden?</h2>
		<div>
			<div v-for="amount in paymentAmounts" :key="'amount-' + toCents( amount )">
				<input type="radio"
					:id="'amount-' + toCents( amount )"
					name="amount-grp"
					v-model="amountValue"
					:value="toCents( amount )"
					@change="amountSelected()">
				<label :for="'amount-' + toCents( amount )">
					<span>{{ amount | formatAmount }} €</span>
				</label>
			</div>
			<div>
				<input type="text" id="amount-typed" v-model="amountCustomValue" @blur="customAmountEntered()">
			</div>
			<span v-if="hasErrors">{{ $t('form_amount_error') }}</span>

			<label for="amount-typed" class="sr-only">{{ $t('payment_amount_legend') }}</label>
		</div>
	</fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { AmountData } from '@/view_models/Payment';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { markEmptyAmountAsInvalid, setAmount } from '@/store/payment/actionTypes';

export default Vue.extend( {
	name: 'PaymentAmount',
	data: function (): AmountData {
		return {
			amountValue: '',
			amountCustomValue: '',
		};
	},
	props: [ 'paymentAmounts', 'validateAmountURL' ],
	computed: {
		hasErrors: {
			get: function (): boolean {
				return !this.$store.getters[ 'payment/amountIsValid' ];
			},
		},
	},
	filters: {
		formatAmount: ( amount: string ) => Number( amount ).toFixed( 0 ),
	},
	methods: {
		toCents: ( amount: string ) => Number( amount ) * 100,
		amountSelected() {
			this.sendAmountToStore( this.$data.amountValue );
			this.clearCustomAmount();
			this.checkIfAmountIsEmpty();
		},
		customAmountEntered() {
			const englishDecimalAmount = this.$data.amountCustomValue.replace( /,/, '.' );
			this.sendAmountToStore( this.toCents( englishDecimalAmount ) );
			this.clearSelectedAmount();
			this.checkIfAmountIsEmpty();
		},
		sendAmountToStore( amount: number ) {
			const amountValue = isNaN( amount ) ? '' : amount.toString();
			const payload = {
				amountValue,
				validateAmountURL: this.$props.validateAmountURL,
			};
			this.$store.dispatch( action( NS_PAYMENT, setAmount ), payload );
		},
		checkIfAmountIsEmpty() {
			this.$store.dispatch( action( NS_PAYMENT, markEmptyAmountAsInvalid ) );
		},
		clearSelectedAmount() {
			this.amountValue = '';
		},
		clearCustomAmount() {
			this.amountCustomValue = '';
		},
	},
} );
</script>
