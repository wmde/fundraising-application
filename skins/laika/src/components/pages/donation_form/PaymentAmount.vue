<template>
	<fieldset class="amount-qty">
		<h2>Welchen Betrag möchten Sie spenden?</h2>
		<div class="wrap-amounts">
			<div class="wrap-radio" v-for="amount in paymentAmounts" :key="'amount-' + toCents( amount )">
				<input type="radio"
					:id="'amount-' + toCents( amount )"
					name="amount-grp"
					v-model="amountValue"
					:value="amount"
					@change="clearCustomAmount()">
				<label :for="'amount-' + toCents( amount )">
					<span>{{ amount | formatAmount }} €</span>
				</label>
			</div>
			<div class="" data-currency="€">
				<input type="text" id="amount-typed" v-model="amountCustomValue" @blur="validateAmount(); clearSelectedAmount()">
			</div>
			<span v-if="hasErrors">{{ $t('form_amount_error') }}</span>

			<input type="hidden" name="betrag" id="amount-hidden">
			<label for="amount-typed" class="sr-only">{{ $t('payment_amount_legend') }}</label>
		</div>
	</fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { AmountData } from '../../../view_models/Payment';

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
		validateAmount() {
			this.$store.dispatch( 'payment/validateAmount', this.$data );
		},
		clearSelectedAmount() {
			if ( this.$store.getters[ 'payment/amountIsValid' ] && this.amountCustomValue !== '' ) {
				this.amountValue = '';
			}
			let payload = {
				amountValue: this.toCents( this.amountCustomValue ).toString(),
				validateAmountURL: this.$props.validateAmountURL,
			};
			this.$store.dispatch( 'payment/registerAmount', payload );
		},
		clearCustomAmount() {
			this.amountCustomValue = '';
			let payload = {
				amountValue: this.toCents( this.amountValue ).toString(),
				validateAmountURL: this.$props.validateAmountURL,
			};
			this.$store.dispatch( 'payment/validateAmount', this.$data );
			this.$store.dispatch( 'payment/registerAmount', payload );
		},
	},
} );
</script>
