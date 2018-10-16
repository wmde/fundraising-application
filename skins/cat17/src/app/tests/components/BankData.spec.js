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

test( 'BankData.vue renders', t => {
	t.plan( 1 );
	const wrapper = shallowMount( BankData );
	t.equal( typeof wrapper, 'object' );
} );

test( 'Given German IBAN value, BIC-field becomes disabled', t => {
	t.plan( 2 );
	const wrapper = shallowMount( BankData, {
		propsData: newTestProperties()
	} );
	const bicInput = wrapper.find( '#bic' );

	t.notOk( bicInput.attributes().disabled, '"disabled" attribute should be false' );

	const ibanInput = wrapper.find( '#iban' );
	ibanInput.setValue( 'DE123' );
	ibanInput.trigger( 'input' );

	t.ok( bicInput.attributes().disabled, '"disabled" attribute should have a truthy value' );
} );

test( 'Given IBAN value changes in IBAN and BIC property, BankData.vue renders them to field values', t => {
	t.plan( 4 );
	const initialProperties = newTestProperties( {} );
	const wrapper = shallowMount( BankData, {
		propsData: initialProperties
	} );
	const ibanInput = wrapper.find( '#iban' );
	const bicInput = wrapper.find( '#bic' );

	t.equal( ibanInput.element.value, '' );
	t.equal( bicInput.element.value, '' );

	wrapper.setProps( {
		iban: 'DE123',
		bic: '98765'
	} );

	t.equal( ibanInput.element.value, 'DE123' );
	t.equal( bicInput.element.value, '98765' );
} );

test( 'Given classic account value, BankData.vue does only render changes to IBAN and BIC field values if they are empty', t => {
	t.plan( 6 );
	const initialProperties = newTestProperties( {} );
	const wrapper = shallowMount( BankData, {
		propsData: initialProperties
	} );
	const ibanInput = wrapper.find( '#iban' );
	const bicInput = wrapper.find( '#bic' );

	t.equal( ibanInput.element.value, '' );
	t.equal( bicInput.element.value, '' );

	wrapper.setProps( {
		iban: '123',
		bic: '98765'
	} );

	t.equal( ibanInput.element.value, '123' );
	t.equal( bicInput.element.value, '98765' );

	wrapper.setProps( {
		iban: '777',
		bic: '888'
	} );

	t.equal( ibanInput.element.value, '123' );
	t.equal( bicInput.element.value, '98765' );
} );

test( 'Given empty IBAN value on IBAN blur, validation is not triggered and no result is set', t => {
	const wrapper = shallowMount( BankData, {
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

	const ibanInput = wrapper.find( '#iban' );
	ibanInput.trigger( 'blur' );
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
		propsData: newTestProperties( {
			bankDataValidator: fakeBankDataValidator,
			changeBankDataValidity: function ( validity ) {
				t.deepEqual( validity, validationResult );
			}
		} )
	} );

	const ibanInput = wrapper.find( '#iban' );
	ibanInput.setValue( 'DE12500105170648489890' );
	ibanInput.trigger( 'input' );
	ibanInput.trigger( 'blur' );
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
		propsData: newTestProperties( {
			bankDataValidator: fakeBankDataValidator,
			changeBankDataValidity: function ( validity ) {
				t.deepEqual( validity, validationResult );
			}
		} )
	} );

	const ibanInput = wrapper.find( '#iban' );
	const bicInput = wrapper.find( '#bic' );

	ibanInput.setValue( '0648489890' );
	ibanInput.trigger( 'input' );
	bicInput.setValue( '50010517' );
	bicInput.trigger( 'input' );

	ibanInput.trigger( 'blur' );
} );

// TODO test validity classes

// TODO test labels when we have an i18n solution
