<template>
	<fieldset>
		<legend class="title is-size-5">{{ $t('donation_form_payment_amount_title') }}</legend>
		<div class="amount-wrapper">
			<div class="amount-selector is-form-input" v-for="amount in paymentAmounts" :key="'amount-' + toCents( amount )">
				<input type="radio"
					:id="'amount-' + amount "
					name="amount-grp"
					:value="amount"
					:checked="amount == selectedAmount"
					@change="amountSelected( amount )"
					class="is-sr-only">
				<label class="has-border-rounded" :for="'amount-' + amount ">
					<span>{{ amount | formatAmount }}€</span>
				</label>
			</div>
			<div class="amount-custom-wrapper">
				<div class="amount-custom is-form-input">
					<input v-bind:class="[customAmount ? 'is-valid' : '', 'input', 'is-large', 'input-amount', 'has-border-rounded' ]"
							type="text"
							id="amount-custom"
							@blur="customAmountEntered"
							maxlength="9"
							:value="customAmount"
							:placeholder="$t('donation_form_custom_placeholder')">
					<label for="amount-custom" class="is-sr-only">{{ $t('donation_form_payment_amount_legend') }}</label>
				</div>
			</div>
		</div>
		<span class="help is-danger" v-if="hasErrors">{{ $t('donation_form_payment_amount_error') }}</span>
	</fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { AmountData } from '@/view_models/Payment';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { markEmptyAmountAsInvalid, setAmount } from '@/store/payment/actionTypes';
import { mapState } from 'vuex';

export default Vue.extend( {
	name: 'PaymentAmount',
	props: [ 'paymentAmounts', 'validateAmountUrl' ],
	computed: {
		hasErrors: {
			get: function (): boolean {
				return !this.$store.getters[ 'payment/amountIsValid' ];
			},
		},
		...mapState( {
			selectedAmount: function ( state: any ) {
				const amount = state[ NS_PAYMENT ].values.amount;
				const amountFound = this.$props.paymentAmounts.indexOf( Number( amount ) );
				return amountFound > -1 ? amount : '';
			},
			customAmount: function ( state: any ): string {
				const amount = state[ NS_PAYMENT ].values.amount;
				const amountFound = this.$props.paymentAmounts.indexOf( Number( amount ) );
				if ( amountFound > -1 || amount === '0' || amount === '' ) {
					return '';
				}
				// Format German number
				return String( ( Number( amount ) / 100 ).toFixed( 2 ) ).replace( /\./, ',' );
			},
		} ),
	},
	filters: {
		formatAmount: ( amount: string ) => ( Number( amount ) / 100 ).toFixed( 0 ),
	},
	methods: {
		toCents: ( amount: string ): number => Math.trunc( Number( amount ) * 100 ),
		amountSelected( amount: number ) {
			this.sendAmountToStore( amount );
		},
		customAmountEntered( evt: Event ) {
			const amount = ( evt.target as HTMLInputElement ).value.trim();
			if ( amount === '' ) {
				// can't access computed props through this.$props here
				if ( ( this as any ).selectedAmount !== '' ) {
					return;
				}

				this.$store.dispatch( action( NS_PAYMENT, markEmptyAmountAsInvalid ) );
				return;
			}
			const englishDecimalAmount = amount.replace( /,/, '.' );
			this.sendAmountToStore( this.toCents( englishDecimalAmount ) );
		},
		sendAmountToStore( amount: number ): Promise<null> {
			const amountValue = isNaN( amount ) ? '' : amount.toString();
			const payload = {
				amountValue,
				validateAmountUrl: this.$props.validateAmountUrl,
			};
			return this.$store.dispatch( action( NS_PAYMENT, setAmount ), payload );
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
				font-size: 1.1em;
				position: absolute;
				right: 10px;
				top: 50%;
				transform: translateY(-50%);
			}
		}
	}
</style>
