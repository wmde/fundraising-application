<template>
	<div>
		<div v-bind:class="classesIBAN">
			<input type="text" id="iban" name="iban" :placeholder="labels.iban" v-bind:value="ibanValue" v-on:input="handleIbanChange" v-on:blur="validate">
			<label for="iban">{{ labels.iban }}</label>
		</div>
		<div v-bind:class="classesBIC">
			<input type="text" id="bic" name="bic" :placeholder="labels.bic" v-bind:value="bicValue" :disabled="writableBIC" v-on:input="handleBicChange" v-on:blur="validate">
			<label for="bic">{{ labels.bic }}</label>
		</div>

		<span id="bank-name">{{ bankName }}</span>
		<input type="hidden" name="bankname" id="field-bank-name" :value="bankName" />

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
				ibanValue: '',
				bicValue: '',
				bankName: '',
				errorText: 'Placeholder error text',
				hasError: false
			};
		},
		watch: {
			iban( v ) {
				if ( this.isIbanEmpty() || this.looksLikeIban() ) {
					this.ibanValue = v;
				}
			},
			bic( v ) {
				if ( this.isBicEmpty() || this.looksLikeIban() ) {
					this.bicValue = v;
				}
			}
		},
		methods: {
			handleIbanChange( evt ) {
				this.ibanValue = evt.target.value;
			},
			handleBicChange( evt ) {
				this.bicValue = evt.target.value;
			},
			validate() {
				if ( this.isIbanEmpty() ) {
					return;
				}
				if ( this.looksLikeIban( this.ibanValue ) ) {
					this.changeBankDataValidity( this.bankDataValidator.validateSepaBankData( this.ibanValue ) );
					return;
				}
				this.changeBankDataValidity( this.bankDataValidator.validateClassicBankData( this.ibanValue, this.bicValue ) );
			},
			isIbanEmpty: function () {
				return this.ibanValue === '';
			},
			isBicEmpty: function () {
				return this.bicValue === '';
			},
			looksLikeIban: function () {
				return /^[A-Z]+([0-9]+)?$/.test( this.ibanValue );
			},
			looksLikeBankAccountNumber: function () {
				return /^\d+$/.test( this.ibanValue );
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
				return /^(DE).*$/.test( this.ibanValue );
			},
			classesIBAN() {
				return {
					'field-grp': true,
					'field-iban': true,
					'field-labeled': true,
					invalid: !this.isValid,
					valid: this.isValid && !this.isIbanEmpty()
				};
			},
			classesBIC() {
				return {
					'field-grp': true,
					'field-bic': true,
					'field-labeled': true,
					invalid: !this.isValid && !this.isBicEmpty(),
					valid: this.isValid && !this.isBicEmpty()
				};
			}
		}
	};
</script>
