<template>
	<fieldset>
		<h2 class="title is-size-5">{{ $t('donation_form_amount_title') }}</h2>
		<div class="amount-wrapper">
			<div class="amount-selector" v-for="amount in paymentAmounts" :key="'amount-' + toCents( amount )">
				<input type="radio"
					:id="'amount-' + toCents( amount )"
					name="amount-grp"
					v-model="amountValue"
					:value="toCents( amount )"
					@change="amountSelected()"
					class="is-sr-only">
				<label class="has-border-rounded" :for="'amount-' + toCents( amount )">
					<span>{{ amount | formatAmount }}€</span>
				</label>
			</div>
			<div class="amount-custom-wrapper">
				<div class="amount-custom">
					<input v-bind:class="[amountCustomValue ? 'is-valid' : '', 'input', 'is-large', 'input-amount', 'has-border-rounded' ]"
							type="text"
							id="amount-custom"
							v-model="amountCustomValue"
							@blur="customAmountEntered(); clearSelectedAmount()"
							maxlength="9">
					<label for="amount-custom" class="is-sr-only">{{ $t('payment_amount_legend') }}</label>
				</div>
			</div>
		</div>
		<span class="help is-danger" v-if="hasErrors">{{ $t('donation_form_amount_error') }}</span>

		<label for="amount-custom" class="sr-only">{{ $t('payment_amount_legend') }}</label>
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
		toCents: ( amount: string ) => Math.trunc( Number( amount ) * 100 ),
		amountSelected() {
			this.sendAmountToStore( this.$data.amountValue );
			this.clearCustomAmount();
			this.markEmptyAmountAsInvalid();
		},
		customAmountEntered() {
			const englishDecimalAmount = this.$data.amountCustomValue.replace( /,/, '.' );
			if ( englishDecimalAmount !== '' ) {
				this.sendAmountToStore( this.toCents( englishDecimalAmount ) );
				this.clearSelectedAmount();
			} else {
				this.markEmptyAmountAsInvalid();
			}
		},
		sendAmountToStore( amount: number ) {
			const amountValue = isNaN( amount ) ? '' : amount.toString();
			const payload = {
				amountValue,
				validateAmountURL: this.$props.validateAmountURL,
			};
			this.$store.dispatch( action( NS_PAYMENT, setAmount ), payload );
		},
		markEmptyAmountAsInvalid() {
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

<style lang="scss">
	@import "../../../scss/custom";
	.amount {
		&-wrapper {
			display: flex;
			justify-content: space-between;
			flex-wrap: wrap;
			width: 270px;
		}
		&-selector {
			position: relative;
			display: flex;
			margin-bottom: 12px;
			label {
				align-items: center;
				color: $fun-color-primary;
				cursor: pointer;
				display: flex;
				justify-content: center;
				height: 55px;
				width: 55px;
				&:active {
					background-color: $fun-color-primary-lightest;
				}
			}
			input[type="radio"]:checked + label {
				color: $fun-color-bright;
				font-weight: bold;
				background-color: $fun-color-primary;
			}
		}
		&-custom {
			width: 100%;
			&-wrapper {
				position: relative;
				width: 100%;
			}
			&:after {
                color: $fun-color-dark-lighter;
				content: "€";
				position: absolute;
				right: 10px;
				top: 50%;
				transform: translateY(-50%);
			}
		}
	}
</style>
