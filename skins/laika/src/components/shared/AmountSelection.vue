<template>
	<fieldset>
		<legend class="title is-size-5">{{ title }}</legend>
		<div class="amount-wrapper">
			<div v-for="amount in paymentAmounts"
				:key="'amount-' + toCents( amount )"
				:class="['amount-selector', 'is-form-input', amount < minimumAmount ? 'inactive' : '']"
				>
				<input type="radio"
					:id="'amount-' + amount "
					name="amount-grp"
					:value="amount"
					:checked="amount == selectedAmount"
					:disabled="amount < minimumAmount"
					@change="amountSelected( amount )"
					class="is-sr-only">
				<label class="has-border-rounded" :for="'amount-' + amount ">
					<span>{{ amount | formatAmount }}€</span>
				</label>
			</div>
			<div class="amount-custom-wrapper">
				<div class="amount-custom is-form-input">
					<input :class="[customAmount ? 'is-valid' : '', 'input', 'is-large', 'input-amount', 'has-border-rounded' ]"
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
		<span class="help is-danger" v-if="error !== ''">{{ error }}</span>
	</fieldset>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'AmountSelection',
	props: {
		amount: String,
		minimumAmount: {
			type: Number,
			default: 0,
		},
		paymentAmounts: Array,
		title: String,
		error: {
			type: String,
			default: '',
		},
	},
	computed: {
		hasErrors: {
			get: function (): boolean {
				return !this.$store.getters[ 'payment/amountIsValid' ];
			},
		},
		selectedAmount: {
			get: function (): string {
				const amount = this.$props.amount;
				const amountFound = this.$props.paymentAmounts.indexOf( Number( amount ) );
				return amountFound > -1 ? amount : '';
			},
		},
		customAmount: {
			get: function (): string {
				const amount = this.$props.amount;
				const amountFound = this.$props.paymentAmounts.indexOf( Number( amount ) );
				if ( amountFound > -1 || amount === '0' || amount === '' ) {
					return '';
				}
				// Format German number
				return String( ( Number( amount ) / 100 ).toFixed( 2 ) ).replace( /\./, ',' );
			},
		},
	},
	filters: {
		formatAmount: ( amount: string ) => ( Number( amount ) / 100 ).toFixed( 0 ),
	},
	methods: {
		toCents: ( amount: string ): number => Math.trunc( Number( amount ) * 100 ),
		amountSelected( amount: number ) {
			this.$emit( 'amount-selected', String( amount ) );
		},
		customAmountEntered( evt: Event ) {
			const amount = ( evt.target as HTMLInputElement ).value.trim();
			if ( amount === '' ) {
				// can't access computed props through this.$props here
				if ( ( this as any ).selectedAmount !== '' ) {
					return;
				}

				this.$emit( 'amount-selected', '' );
				return;
			}
			const englishDecimalAmount = Number( amount.replace( /,/, '.' ) );
			if ( isNaN( englishDecimalAmount ) ) {
				this.$emit( 'amount-selected', '' );
			}
			this.$emit( 'amount-selected', String( Math.trunc( englishDecimalAmount * 100 ) ) );
		},
	},
} );
</script>

<style lang="scss">
	@import "../../scss/custom";

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
			&.inactive label {
				color: $fun-color-gray-mid;
				cursor: default;
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
