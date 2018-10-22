import test from 'tape';
import { shallowMount } from '@vue/test-utils';
import BankData from '../../components/BankData.vue';

function newTestProperties( overrides ) {
	return Object.assign(
		{
			bankDataValidator: {
				validateClassicBankData: function () { },
				validateSepaBankData: function () { }
			},
			changeBankDataValidity: function () {},
			iban: '',
			bic: '',
			isValid: true
		},
		overrides
	);
}

/* Vue component dependencies */
const mocks = {
	// Dummy translation function
	t( text ) {
		return text;
	}
};

/* Basic sanity test */

test( 'BankData.vue renders', t => {
	t.plan( 1 );
	const wrapper = shallowMount( BankData, { mocks } );
	t.equal( typeof wrapper, 'object' );
} );

/*  Reaction to input */

test( 'Given German IBAN value, BIC-field becomes disabled', t => {
	t.plan( 2 );
	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties()
	} );
	const bankIdInput = wrapper.find( '#bank-id' );

	t.notOk( bankIdInput.attributes().disabled, '"disabled" attribute should be false' );

	const accountIdInput = wrapper.find( '#account-id' );
	accountIdInput.setValue( 'DE123' );
	accountIdInput.trigger( 'input' );

	t.ok( bankIdInput.attributes().disabled, '"disabled" attribute should have a truthy value' );
} );

/* Reaction property changes */

test( 'Given IBAN value changes in IBAN and BIC property, BankData.vue renders them to field values', t => {
	t.plan( 4 );
	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties( {} )
	} );
	const accountIdInput = wrapper.find( '#account-id' );
	const bankIdInput = wrapper.find( '#bank-id' );

	t.equal( accountIdInput.element.value, '' );
	t.equal( bankIdInput.element.value, '' );

	wrapper.setProps( {
		iban: 'DE123',
		bic: '98765'
	} );

	t.equal( accountIdInput.element.value, 'DE123' );
	t.equal( bankIdInput.element.value, '98765' );
} );

test( 'Given classic account value, BankData.vue does only render changes to IBAN and BIC field values if they are empty', t => {
	t.plan( 6 );
	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties( {} )
	} );
	const accountIdInput = wrapper.find( '#account-id' );
	const bankIdInput = wrapper.find( '#bank-id' );

	t.equal( accountIdInput.element.value, '' );
	t.equal( bankIdInput.element.value, '' );

	wrapper.setProps( {
		iban: '123',
		bic: '98765'
	} );

	t.equal( accountIdInput.element.value, '123' );
	t.equal( bankIdInput.element.value, '98765' );

	wrapper.setProps( {
		iban: '777',
		bic: '888'
	} );

	t.equal( accountIdInput.element.value, '123' );
	t.equal( bankIdInput.element.value, '98765' );
} );

test( 'Given bankName property changes, BankData.vue renders it', t => {
	t.plan( 2 );
	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties( {} )
	} );
	const bankName = wrapper.find( '.bank-name' );

	t.equal( bankName.text(), '' );

	wrapper.setProps( {
		bankName: 'Gringotts'
	} );

	t.equal( bankName.text(), 'Gringotts' );
} );


/* When does validation happen */

test( 'Given empty IBAN value on IBAN blur, validation is not triggered and no result is set', t => {
	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties( {
			bankDataValidator: {
				validateSepaBankData() {
					t.fail();
				},
				validateClassicBankData() {
					t.fail();
				}
			},
			changeBankDataValidity() {
				t.fail();
			}
		} )
	} );

	const accountIdInput = wrapper.find( '#account-id' );
	accountIdInput.trigger( 'blur' );
	t.end();
} );

test( 'Given filled IBAN value on IBAN blur, SEPA validation is triggered and validity is set to validation result', t => {
	t.plan( 2 );
	const validationResult = {
		state: 'OK',
		iban: 'DE12500105170648489890',
		bic: 'INGDDEFFXXX'
	};
	const fakeBankDataValidator = {
		validateClassicBankData: function () { },
		validateSepaBankData: function ( v ) {
			t.equals( v, 'DE12500105170648489890' );
			return validationResult;
		}
	};
	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties( {
			bankDataValidator: fakeBankDataValidator,
			changeBankDataValidity: function ( validity ) {
				t.deepEqual( validity, validationResult );
			}
		} )
	} );

	const accountIdInput = wrapper.find( '#account-id' );
	accountIdInput.setValue( 'DE12500105170648489890' );
	accountIdInput.trigger( 'input' );
	accountIdInput.trigger( 'blur' );
} );

test( 'Given filled classic values on blur, classic validation is triggered and validity is set to validation result', t => {
	t.plan( 3 );
	const validationResult = {
		state: 'OK',
		iban: 'DE12500105170648489890',
		bic: 'INGDDEFFXXX'
	};
	const fakeBankDataValidator = {
		validateClassicBankData: function ( accountNumber, bankNumber ) {
			t.equals( accountNumber, '0648489890' );
			t.equals( bankNumber, '50010517' );
			return validationResult;
		},
		validateSepaBankData: function () { t.fail(); }
	};
	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties( {
			bankDataValidator: fakeBankDataValidator,
			changeBankDataValidity: function ( validity ) {
				t.deepEqual( validity, validationResult );
			}
		} )
	} );

	const accountIdInput = wrapper.find( '#account-id' );
	const bankIdInput = wrapper.find( '#bank-id' );

	accountIdInput.setValue( '0648489890' );
	accountIdInput.trigger( 'input' );
	bankIdInput.setValue( '50010517' );
	bankIdInput.trigger( 'input' );

	accountIdInput.trigger( 'blur' );
} );

/* Setting of "valid" CSS class */

test( 'Given empty fields, they are not marked as valid ', t => {
	t.plan( 2 );

	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties()
	} );

	const accountIdContainer = wrapper.find( '.field-account-id' );
	const bankIdContainer = wrapper.find( '.field-bank-id' );

	t.equal( accountIdContainer.classes().indexOf( 'valid' ), -1, 'empty account id field should not be valid' );
	t.equal( bankIdContainer.classes().indexOf( 'valid' ), -1, 'empty bank id field should not be valid' );
} );

function fillField( wrapper, name, value ) {
	const field = wrapper.find( name );
	field.setValue( value );
	field.trigger( 'input' );
	field.trigger( 'blur' );
}

test( 'Given filled fields, they are marked as valid ', t => {
	// Note: the class name "valid" is an unfortunate term, there for historical reasons.
	// The more appropriate name should be "filled".
	// The CSS highlighting that "valid" does, is overridden by the "invalid" class,
	// so in the final markup an element can have both "valid" and "invalid" classes.

	t.plan( 2 );

	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties()
	} );
	// we have to use an Austrian IBAn since a German one locks the BIC field for input
	fillField( wrapper, '#account-id', 'AT022050302101023600' );
	fillField( wrapper, '#bank-id', 'RLNWATW1647' );

	const accountIdContainer = wrapper.find( '.field-account-id' );
	const bankIdContainer = wrapper.find( '.field-bank-id' );

	t.notEqual( accountIdContainer.classes().indexOf( 'valid' ), -1, 'filled account id field should be valid' );
	t.notEqual( bankIdContainer.classes().indexOf( 'valid' ), -1, 'filled bank id field should be valid' );
} );

/* Setting of "invalid" CSS class */

test( 'Given account id is filled with IBAN and validity is true, invalid class is not set', t => {
	t.plan( 2 );

	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties()
	} );

	fillField( wrapper, '#account-id', 'DE12500105170648489890' );
	wrapper.setProps( { isValid: true } );

	const accountIdContainer = wrapper.find( '.field-account-id' );
	const bankIdContainer = wrapper.find( '.field-bank-id' );

	t.equal( accountIdContainer.classes().indexOf( 'invalid' ), -1, 'filled account id field should not be invalid' );
	t.equal( bankIdContainer.classes().indexOf( 'invalid' ), -1, 'filled bank id field should not be invalid' );

} );

test( 'Given account id is filled with IBAN and validity is false, invalid class is set for IBAN field', t => {
	t.plan( 2 );

	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties()
	} );

	fillField( wrapper, '#account-id', 'DE12500105170648489890' );
	wrapper.setProps( { isValid: false } );

	const accountIdContainer = wrapper.find( '.field-account-id' );
	const bankIdContainer = wrapper.find( '.field-bank-id' );

	t.notEqual( accountIdContainer.classes().indexOf( 'invalid' ), -1, 'filled account id field should not be invalid' );
	t.equal( bankIdContainer.classes().indexOf( 'invalid' ), -1, 'filled bank id field should not be invalid' );
} );

test( 'Given account and bank id are filled with classic data and validity is true, invalid class is not set', t => {
	t.plan( 2 );

	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties()
	} );

	fillField( wrapper, '#account-id', '0648489890' );
	fillField( wrapper, '#bank-id', '50010517' );
	wrapper.setProps( { isValid: true } );

	const accountIdContainer = wrapper.find( '.field-account-id' );
	const bankIdContainer = wrapper.find( '.field-bank-id' );

	t.equal( accountIdContainer.classes().indexOf( 'invalid' ), -1, 'filled account id field should not be invalid' );
	t.equal( bankIdContainer.classes().indexOf( 'invalid' ), -1, 'filled bank id field should not be invalid' );

} );

test( 'Given account and bank id are filled with classic data and validity is false, invalid class is set on both fields', t => {
	t.plan( 2 );

	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties()
	} );

	fillField( wrapper, '#account-id', '0648489890' );
	fillField( wrapper, '#bank-id', '50010517' );
	wrapper.setProps( { isValid: false } );

	const accountIdContainer = wrapper.find( '.field-account-id' );
	const bankIdContainer = wrapper.find( '.field-bank-id' );

	t.notEqual( accountIdContainer.classes().indexOf( 'invalid' ), -1, 'filled account id field should not be invalid' );
	t.notEqual( bankIdContainer.classes().indexOf( 'invalid' ), -1, 'filled bank id field should not be invalid' );

} );

test( 'Given only account id was filled with classic data and validity is undetermined, invalid class is not set', t => {
	t.plan( 2 );

	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties()
	} );

	fillField( wrapper, '#account-id', '0648489890' );
	wrapper.setProps( { isValid: null } );

	const accountIdContainer = wrapper.find( '.field-account-id' );
	const bankIdContainer = wrapper.find( '.field-bank-id' );

	t.equal( accountIdContainer.classes().indexOf( 'invalid' ), -1, 'filled account id field should not be invalid' );
	t.equal( bankIdContainer.classes().indexOf( 'invalid' ), -1, 'unfilled bank id field should not be invalid' );

} );

test( 'Given only bank id was filled with classic data and validity is undetermind, invalid class is not set', t => {
	t.plan( 2 );

	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties()
	} );

	fillField( wrapper, '#bank-id', '50010517' );
	wrapper.setProps( { isValid: null } );

	const accountIdContainer = wrapper.find( '.field-account-id' );
	const bankIdContainer = wrapper.find( '.field-bank-id' );

	t.equal( accountIdContainer.classes().indexOf( 'invalid' ), -1, 'unfilled account id field should not be invalid' );
	t.equal( bankIdContainer.classes().indexOf( 'invalid' ), -1, 'filled bank id field should not be invalid' );

} );

/* Label change for different values in account id and bank id */

test( 'Given no input, BankData shows labels for undetermined value', t => {
	t.plan( 4 );
	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties( {} )
	} );

	const accountIdInput = wrapper.find( '#account-id' );
	const bankIdInput = wrapper.find( '#bank-id' );
	const accountIdLabel = wrapper.find( '.field-account-id label' );
	const bankIdLabel = wrapper.find( '.field-bank-id label' );

	t.equal( accountIdInput.element.attributes.placeholder.value, 'iban_or_account_number' );
	t.equal( bankIdInput.element.attributes.placeholder.value, 'bic_or_bank_code' );
	t.equal( accountIdLabel.text(), 'iban_or_account_number' );
	t.equal( bankIdLabel.text(), 'bic_or_bank_code' );
} );

test( 'Given account id input that looks like an IBAN, BankData shows labels for SEPA', t => {
	t.plan( 4 );
	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties( {} )
	} );

	const ibanInput = wrapper.find( '#account-id' );
	ibanInput.setValue( 'DE12345' );
	ibanInput.trigger( 'input' );

	const accountIdInput = wrapper.find( '#account-id' );
	const bankIdInput = wrapper.find( '#bank-id' );
	const accountIdLabel = wrapper.find( '.field-account-id label' );
	const bankIdLabel = wrapper.find( '.field-bank-id label' );

	t.equal( accountIdInput.element.attributes.placeholder.value, 'iban' );
	t.equal( bankIdInput.element.attributes.placeholder.value, 'bic' );
	t.equal( accountIdLabel.text(), 'iban' );
	t.equal( bankIdLabel.text(), 'bic' );
} );

test( 'Given account id input that looks like an account number, BankData shows labels for classic bank data', t => {
	t.plan( 4 );
	const wrapper = shallowMount( BankData, {
		mocks,
		propsData: newTestProperties( {} )
	} );

	const ibanInput = wrapper.find( '#account-id' );
	ibanInput.setValue( '12345' );
	ibanInput.trigger( 'input' );

	const accountIdInput = wrapper.find( '#account-id' );
	const bankIdInput = wrapper.find( '#bank-id' );
	const accountIdLabel = wrapper.find( '.field-account-id label' );
	const bankIdLabel = wrapper.find( '.field-bank-id label' );

	t.equal( accountIdInput.element.attributes.placeholder.value, 'account_number' );
	t.equal( bankIdInput.element.attributes.placeholder.value, 'bank_code' );
	t.equal( accountIdLabel.text(), 'account_number' );
	t.equal( bankIdLabel.text(), 'bank_code' );
} );
