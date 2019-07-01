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
					@blur="validate">
			</b-input>
		</div>
		<div v-bind:class="[{ 'is-invalid': accountIsValid }]">
			<label for="bic" class="subtitle has-margin-top-36">{{ $t( 'donation_form_payment_bankdata_bic_label' ) }}</label>
			<b-input class="is-medium"
					type="text"
					id="bic"
					v-model="bankIdentifier"
					name="bic"
					:placeholder="$t( 'donation_form_payment_bankdata_bic_placeholder' )"
					@blur="validate">
			</b-input>
			<span>{{ getBankName }}</span>
		</div>
	</fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { BankAccountData, BankAccountRequest } from '@/view_models/BankAccount';
import { setBankData } from '@/store/bankdata/actionTypes';
import { NS_BANKDATA } from '@/store/namespaces';
import { action } from '@/store/util';
import { mapGetters } from "vuex";

export default Vue.extend( {
	name: 'PaymentBankData',
	data: function (): BankAccountData {
		return { accountId: '', bankId: '' };
	},
	props: {
		validateBankDataUrl: String,
		validateLegacyBankDataUrl: String,
	},
	computed: {
		bankIdentifier: {
			get: function (): string {
				if ( this.looksLikeGermanIban() ) {
					return this.$store.getters[ 'bankdata/getBankId' ];
				}
				return this.$data.bankId;
			},
			set: function ( bankId: string ) {
				this.$data.bankId = bankId;
			}
		},
		...mapGetters( NS_BANKDATA, [
			'accountIsValid',
			'bankIsValid',
			'getBankName',
		])
	},
	methods: {
		validate() {
			if ( this.isAccountIdEmpty() ) {
				return;
			}
			if ( this.looksLikeIban() ) {
				this.$store.dispatch(
					action( NS_BANKDATA, setBankData ),
					{
						validationUrl: this.validateBankDataUrl,
						requestParams: { iban: this.$data.accountId },
					} as BankAccountRequest
				);
			} else {
				this.$store.dispatch(
					action( NS_BANKDATA, setBankData ),
					{
						validationUrl: this.validateLegacyBankDataUrl,
						requestParams:  { accountNumber: this.$data.accountId, bankCode: this.$data.bankId },
					} as BankAccountRequest
				);
			}
		},
		isAccountIdEmpty: function () {
			return this.$data.accountId === '';
		},
		isBankIdEmpty: function () {
			return this.bankId === '';
		},
		looksLikeIban: function () {
			return /^[A-Z]{2}([0-9\s]+)?$/.test( this.$data.accountId );
		},
		looksLikeBankAccountNumber: function () {
			return /^\d+$/.test( this.$data.accountId );
		},
		looksLikeGermanIban() {
			return /^DE+([0-9\s]+)?$/.test( this.$data.accountId );
		},
	},
} );
</script>
