<template>
	<fieldset class="has-margin-bottom-36">
		<legend class="title is-size-5">{{ $t( 'donation_form_payment_bankdata_title' ) }}</legend>
		<span>{{ $t( 'donation_form_payment_bankdata_legend' ) }}</span>
		<div v-bind:class="[{ 'is-invalid': accountIsValid }]">
			<label for="iban" class="subtitle has-margin-top-18">{{ $t( 'donation_form_payment_bankdata_iban_label' ) }}</label>
			<b-input class="is-medium"
					 type="text"
					 id="iban"
					 v-model="accountId"
					 name="iban"
					 :placeholder="$t( 'donation_form_payment_bankdata_iban_placeholder' )"
					 @blur="setAccountId">
			</b-input>
		</div>
		<div v-bind:class="[{ 'is-invalid': accountIsValid }]">
			<label for="bic" class="subtitle has-margin-top-36">{{ $t( 'donation_form_payment_bankdata_bic_label' ) }}</label>
			<b-input class="is-medium"
					 type="text"
					 id="bic"
					 v-model="bankId"
					 name="bic"
					 :placeholder="$t( 'donation_form_payment_bankdata_bic_placeholder' )"
					 @blur="setBankId">
			</b-input>
		</div>
	</fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { BankAccountData } from '@/view_models/Payment';
import { NS_PAYMENT } from '@/store/namespaces';
import { action } from '@/store/util';
import { setAccountId, setBankId } from '@/store/payment/actionTypes';

export default Vue.extend( {
	name: 'PaymentBankData',
	data: function (): BankAccountData {
		return { accountId: '', bankId: '' };
	},
	computed: {
		accountIsValid: {
			get: function (): boolean {
				return !this.$store.getters[ 'payment/accountIsValid' ];
			},
		},
		bankIsValid: {
			get: function (): boolean {
				return !this.$store.getters[ 'payment/bankIsValid' ];
			},
		},
	},
	methods: {
		setAccountId(): void {
			this.$store.dispatch( action( NS_PAYMENT, setAccountId ), this.$data.accountId );
		},
		setBankId(): void {
			this.$store.dispatch( action( NS_PAYMENT, setBankId ), this.$data.bankId );
		},
	},
} );
</script>
