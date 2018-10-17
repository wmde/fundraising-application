<template>
	<div>
		<div v-bind:class="classesIBAN">
			<input type="text" id="account-id" :placeholder="labels.iban" v-model="accountId" v-on:blur="validate">
			<label for="account-id">{{ labels.iban }}</label>
		</div>
		<div v-bind:class="classesBIC">
			<input type="text" id="bank-id" :placeholder="labels.bic" :disabled="writableBIC" v-model="bankId" v-on:blur="validate">
			<label for="bank-id">{{ labels.bic }}</label>
		</div>

		<span id="bank-name">{{ bankName }}</span>
		<!-- Form field values that will be sent to the server -->
		<input type="hidden" name="bankname" id="field-bank-name" :value="bankName" />
		<input type="hidden" name="iban" :value="iban" />
		<input type="hidden" name="bic" :value="bic" />

		<span class="error-text" v-show="hasError">{{ errorText }}</span>
	</div>
</template>

<script>
	export default {
		name: 'bank-data',
		props: {
			changeBankDataValidity: Function,
			bankDataValidator: Object,
			iban: String,
			bic: String,
			isValid: Boolean
		},
		data: function () {
			// TODO Translate these strings
			return {
				accountId: '',
				bankId: '',
				bankName: '',
				errorText: 'Placeholder error text',
				hasError: false
			};
		},
		watch: {
			iban( v ) {
				if ( this.isAccountIdEmpty() || this.looksLikeIban() ) {
					this.accountId = v;
				}
			},
			bic( v ) {
				if ( this.isBankIdEmpty() || this.looksLikeIban() ) {
					this.bankId = v;
				}
			}
		},
		methods: {
			validate() {
				if ( this.isAccountIdEmpty() ) {
					return;
				}
				if ( this.looksLikeIban( this.accountId ) ) {
					this.changeBankDataValidity( this.bankDataValidator.validateSepaBankData( this.accountId ) );
					return;
				}
				this.changeBankDataValidity( this.bankDataValidator.validateClassicBankData( this.accountId, this.bankId ) );
			},
			isAccountIdEmpty: function () {
				return this.accountId === '';
			},
			isBankIdEmpty: function () {
				return this.bankId === '';
			},
			looksLikeIban: function () {
				return /^[A-Z]+([0-9]+)?$/.test( this.accountId );
			},
			looksLikeBankAccountNumber: function () {
				return /^\d+$/.test( this.accountId );
			}
		},
		computed: {
			labels() {
				// TODO Translate these strings
				if ( this.looksLikeIban() ) {
					return {
						iban: 'IBAN',
						bic: 'BIC'
					};
				}
				if ( this.looksLikeBankAccountNumber() ) {
					return {
						iban: 'Kontonummer',
						bic: 'Bankleitzahl'
					};
				}
				return {
					iban: 'IBAN / Kontonummer',
					bic: 'BIC / Bankleitzahl'
				};
			},
			writableBIC() {
				return /^(DE).*$/.test( this.accountId );
			},
			classesIBAN() {
				return {
					'field-grp': true,
					'field-iban': true,
					'field-labeled': true,
					invalid: !this.isValid,
					valid: this.isValid && !this.isAccountIdEmpty()
				};
			},
			classesBIC() {
				return {
					'field-grp': true,
					'field-bic': true,
					'field-labeled': true,
					invalid: !this.isValid && !this.isBankIdEmpty(),
					valid: this.isValid && !this.isBankIdEmpty()
				};
			}
		}
	};
</script>
