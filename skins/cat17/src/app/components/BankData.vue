<template>
	<div>
		<div v-bind:class="classesIBAN">
			<input type="text" id="iban" name="iban" :placeholder="labels.iban" v-model="iban">
			<label for="iban">{{ labels.iban }}</label>
		</div>
		<div v-bind:class="classesBIC">
			<input type="text" id="bic" name="bic" :placeholder="labels.bic" v-model="bic" :disabled="writableBIC">
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
		props: ['formContent'],
		data: function() {
			let iban = '',
				bic = '',
				validIBAN = 'DE00123456789012345678',
				validBIC = 'DEUTDEBBXXX',
				validKontonummer = validIBAN.substr(12, 10),
				validBankleitzahl = validIBAN.substr(4, 8);

			//TODO Translate these strings
			return {
				bankName: '',
				errorText: '',
				hasError: false,

				iban: iban,
				bic: bic,
				isValid: true,
				isValidBic: true,
				bicFilled: false,
				focusOut: false,
				bicFocusOut: false,
				validBankData: {
					iban: validIBAN,
					bic: validBIC,
					konto: validKontonummer,
					bankl: validBankleitzahl
				}
			}
		},
		methods: {
			isFieldEmpty: function () {
				return this.iban === '';
			},
			isBicEmpty: function () {
				return this.bic === '';
			},
			looksLikeIBAN: function () {
				return /^[A-Z]+([0-9]+)?$/.test( this.iban );
			},
			isValidIBAN: function () {
				//TODO remove this function probably
				return ( this.iban === this.validBankData.iban ) || ( this.iban === '' );
			},
			looksLikeBankAccountNumber: function () {
				return /^\d+$/.test( this.iban );
			},
			isValidKontonummmer: function () {
				//TODO remove this function probably
				return ( this.iban === this.validBankData.konto ) || ( this.iban === '' );
			},
			fillBIC: function () {
				if ( this.isValid && !this.isFieldEmpty() && this.looksLikeIBAN() ) {
					this.bic =  this.validBankData.bic;
				}
				this.focusOut = true;
			},
			validBic: function () {
				this.isValidBic = this.bic === this.validBankData.bankl && this.isValidKontonummmer();
				this.bicFocusOut = true;
			}
		},
		computed: {
			labels() {
				if ( this.looksLikeIBAN() ) {
					return {
						'iban': 'IBAN',
						'bic': 'BIC'
					};
				}
				if ( this.looksLikeBankAccountNumber() ) {
					return {
						'iban': 'Kontonummer',
						'bic': 'Bankleitzahl'
					};
				}
				return {
					'iban': 'IBAN / Kontonummer',
					'bic': 'BIC / Bankleitzahl'
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
					'invalid': !this.isValid,
					'valid': this.isValid && !this.isFieldEmpty() && this.focusOut
				}
			},
			classesBIC() {
				return {
					'field-grp': true,
					'field-iba': true,
					'field-labeled': true,
					'invalid': ( !this.isValid && this.focusOut ) || ( !this.isValidBic && !this.isValidIBAN() && this.bicFocusOut && this.focusOut ),
					'valid': this.isValid && !this.isFieldEmpty() && ( ( this.looksLikeIBAN() && this.focusOut ) || ( this.isValidBic && this.focusOut && !this.isBicEmpty() ) )
				}
			}
		},
	}

</script>
