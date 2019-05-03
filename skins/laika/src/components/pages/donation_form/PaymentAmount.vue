<template>
    <fieldset class="amount-qty">
        <h2>How much cash do you wanna hand over?</h2>
        <div class="wrap-amounts">
            <div class="wrap-radio" v-for="amount in paymentAmounts" :key="'amount-' + toCents( amount )">
                <input type="radio" :id="'amount-' + toCents( amount )" name="amount-grp" v-model="amountValue" :value="amount">
                <label :for="'amount-' + toCents( amount )">
                    <span>{{ amount | formatAmount }} €</span>
                </label>
            </div>
            <div class="" data-currency="€">
                <input type="text" id="amount-typed" v-model="amountCustomValue">
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
		data: function(): AmountData {
			return {
				amountValue: '',
				amountCustomValue: ''
			}
		},
		props: [ 'paymentAmounts' ],
		computed: {
			hasErrors: {
				get: function (): boolean {
					return true; //TODO also add a class to the span above
				}
			}
		},
		filters: {
			formatAmount: ( amount: string ) => Number( amount ).toFixed( 0 )
		},
		methods: {
			toCents: ( amount: string ) => Number( amount ) * 100
		}
	} );
</script>
