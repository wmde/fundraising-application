<template>
	<fieldset class="has-margin-bottom-36">
		<legend class="title is-size-5">{{ $t( 'donation_form_payment_bankdata_title' ) }}</legend>
		<div v-bind:class="['form-input', { 'is-invalid': bankDataIsInvalid }]">
			<label for="iban" class="subtitle">{{ $t( labels.iban ) }}</label>
			<b-field>
				<b-input class="is-medium"
						data-content-name="Bank Data Type"
						:data-track-content="getTrackingCode !== ''"
						:data-content-piece="getTrackingCode"
						type="text"
						id="iban"
						v-model="accountId"
						name="iban"
						:placeholder="$t( 'donation_form_payment_bankdata_account_iban_placeholder' )"
						@blur="validate">
				</b-input>
			</b-field>
		</div>
		<div v-show="isBankFieldEnabled" v-bind:class="['form-input', { 'is-invalid': bankDataIsInvalid }]">
			<label for="bic" class="subtitle">{{ $t( labels.bic ) }}</label>
			<b-field>
				<b-input class="is-medium"
						type="text"
						id="bic"
						v-model="bankIdentifier"
						name="bic"
						:disabled="isBankIdDisabled"
						:placeholder="$t( labels.bicPlaceholder )"
						@blur="validate">
				</b-input>
			</b-field>
		</div>
		<div>
			<span id="bank-name">{{ getBankName }}</span>
			<span v-if="bankDataIsInvalid" class="help is-danger">{{ $t( 'donation_form_payment_bankdata_error' ) }}</span>
		</div>
	</fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { BankAccountData, BankAccountRequest } from '@/view_models/BankAccount';
import { markBankDataAsIncomplete, markBankDataAsInvalid, setBankData } from '@/store/bankdata/actionTypes';
import { NS_BANKDATA } from '@/store/namespaces';
import { action } from '@/store/util';
import { mapGetters } from 'vuex';

export default Vue.extend( {
	name: 'PaymentBankData',
	data: function (): BankAccountData {
		return {
			accountId: this.$store.getters[ NS_BANKDATA + '/getAccountId' ],
			bankId: this.$store.getters[ NS_BANKDATA + '/getBankId' ],
		};
	},
	props: {
		validateBankDataUrl: String,
		validateLegacyBankDataUrl: String,
	},
	computed: {
		getTrackingCode(): string {
			if ( this.looksLikeIban() ) {
				return 'IBAN';
			} else if ( this.looksLikeBankAccountNumber() ) {
				return 'Classic';
			}
			return '';
		},
		isBankIdDisabled(): boolean {
			return this.looksLikeIban();
		},
		isBankFieldEnabled(): boolean {
			return this.looksLikeBankAccountNumber() || ( this.looksLikeIban() && this.bankDataIsValid );
		},
		bankIdentifier: {
			get: function (): string {
				if ( this.looksLikeGermanIban() ) {
					return this.$store.getters[ NS_BANKDATA + '/getBankId' ];
				}
				return this.$data.bankId;
			},
			set: function ( bankId: string ) {
				this.$data.bankId = bankId;
			},
		},
		labels() {
			if ( this.looksLikeIban() ) {
				return {
					iban: 'donation_form_payment_bankdata_account_iban_label',
					bic: 'donation_form_payment_bankdata_bank_bic_label',
					bicPlaceholder: 'donation_form_payment_bankdata_bank_bic_placeholder2',
				};
			} else if ( this.looksLikeBankAccountNumber() ) {
				return {
					iban: 'donation_form_payment_bankdata_account_legacy_label',
					bic: 'donation_form_payment_bankdata_bank_legacy_label',
					bicPlaceholder: 'donation_form_payment_bankdata_bank_legacy_placeholder',
				};
			}
			return {
				iban: 'donation_form_payment_bankdata_account_default_label',
				bic: 'donation_form_payment_bankdata_bank_default_label',
				bicPlaceholder: '',
			};
		},
		...mapGetters( NS_BANKDATA, [
			'bankDataIsInvalid',
			'bankDataIsValid',
			'getBankName',
		] ),
	},
	methods: {
		validate() {
			if ( !this.isAccountIdEmpty() && !this.looksLikeValidAccountNumber() ) {
				this.$store.dispatch( ( action( NS_BANKDATA, markBankDataAsInvalid ) ) );
				return;
			}
			if ( this.isAccountIdEmpty() || ( !this.looksLikeIban() && this.isBankIdEmpty() ) ) {
				this.$store.dispatch( action( NS_BANKDATA, markBankDataAsIncomplete ) );
				return;
			}
			if ( this.looksLikeIban() ) {
				this.$store.dispatch(
					action( NS_BANKDATA, setBankData ),
					{
						validationUrl: this.validateBankDataUrl,
						requestParams: { iban: this.$data.accountId.toUpperCase() },
					} as BankAccountRequest
				);
			} else {
				this.$store.dispatch(
					action( NS_BANKDATA, setBankData ),
						{
							validationUrl: this.validateLegacyBankDataUrl,
							requestParams: { accountNumber: this.$data.accountId, bankCode: this.$data.bankId },
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
			return /^[A-Z]{2}([A-Z0-9\s]+)?$/i.test( this.$data.accountId );
		},
		looksLikeBankAccountNumber: function () {
			return /^\d+$/.test( this.$data.accountId );
		},
		looksLikeGermanIban() {
			return /^DE+([0-9\s]+)?$/i.test( this.$data.accountId );
		},
		looksLikeValidAccountNumber() {
			return this.looksLikeIban() || this.looksLikeBankAccountNumber();
		},
	},
} );
</script>
