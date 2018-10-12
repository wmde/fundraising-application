<template>
	<div>
		<div v-bind:class="classesIBAN">
			<input type="text" id="iban" name="iban" :placeholder="labels.iban" v-model="iban" v-on:input="handleIbanChange" v-on:blur="validate">
			<label for="iban">{{ labels.iban }}</label>
		</div>
		<div v-bind:class="classesBIC">
			<input type="text" id="bic" name="bic" :placeholder="labels.bic" v-model="bic" :disabled="writableBIC" v-on:input="handleBicChange" v-on:blur="validate">
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
			changeIban: 'Function',
			changeBic: 'Function',
			changeBankDataValidity: 'Function',
			validateBankData: 'Function',
			iban: 'String',
			bic: 'String',
			isValid: 'Boolean'
		},
		data: function () {
			// TODO Translate these strings
			return {
				bankName: '',
				errorText: 'Placeholder error text',
				hasError: false,
				isValidBic: true,
				bicFilled: false
			};
		},
		methods: {
			handleIbanChange( evt ) {
				this.changeIban( evt.target.value );
			},
			handleBicChange( evt ) {
				this.changeBic( evt.target.value );
			},
			validate() {
				if ( this.iban === '' || ( this.bic === '' && !this.looksLikeIban() ) ) {
					return this.changeBankDataValidity( { status: 'INCOMPLETE', iban: this.iban, bic: this.bic } );
				}
				return this.validateBankData( this.iban, this.bic, this.looksLikeIban() )
					.then( this.changeBankDataValidity )
					.catch( () => {
						this.errorText = 'An error has occurred. Please reload the page and try again.'; // TODO translate
						this.hasError = true;
					} );
			},
			isIbanEmpty: function () {
				return this.iban === '';
			},
			isBicEmpty: function () {
				return this.bic === '';
			},
			looksLikeIban: function () {
				return /^[A-Z]+([0-9]+)?$/.test( this.iban );
			},
			looksLikeBankAccountNumber: function () {
				return /^\d+$/.test( this.iban );
			}
		},
		computed: {
			labels() {
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
				return /^(DE).*$/.test( this.iban );
			},
			classesIBAN() {
				return {
					'field-grp': true,
					'field-iba': true,
					'field-labeled': true,
					invalid: !this.isValid,
					valid: this.isValid && !this.isIbanEmpty()
				};
			},
			classesBIC() {
				return {
					'field-grp': true,
					'field-iba': true,
					'field-labeled': true,
					invalid: !this.isValid && !this.isBicEmpty(),
					valid: this.isValid && !this.isBicEmpty()
				};
			}
		}
	};
</script>
