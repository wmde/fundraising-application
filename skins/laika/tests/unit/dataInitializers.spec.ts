import FakeDataPersister from './TestDoubles/FakeDataPersister';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { MembershipTypeModel } from '@/view_models/MembershipTypeModel';
import persistenceAddress from '@/store/data_persistence/address';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import {
	createInitialBankDataValues,
	createInitialDonationAddressValues,
	createInitialDonationPaymentValues,
	createInitialMembershipAddressValues, createInitialMembershipFeeValues,
} from '@/store/dataInitializers';

describe( 'createInitialDonationAddressValues', () => {
	it( 'fills data from storage', () => {
		const firstName = { key: 'firstName', value: 'Spooky' };
		const lastName = { key: 'lastName', value: 'Magoo' };
		const addressType = 'anonym';

		const dataPersister = new FakeDataPersister( [ firstName, lastName ] );
		const values = createInitialDonationAddressValues( dataPersister, { addressType: addressType } );

		const firstNameValue = values.fields.find( field => field.name === firstName.key );
		const lastNameValue = values.fields.find( field => field.name === lastName.key );

		expect( firstNameValue ).toBeDefined();
		expect( ( firstNameValue || {} ).value ).toEqual( firstName.value );
		expect( lastNameValue ).toBeDefined();
		expect( ( lastNameValue || {} ).value ).toEqual( lastName.value );
		expect( values.addressType ).toEqual( AddressTypeModel.ANON );
	} );

	it( 'uses stored address type over initial address type', () => {
		const storedAddressType = { key: 'addressType', value: AddressTypeModel.PERSON };
		const initialAddressType = 'anonym';

		const dataPersister = new FakeDataPersister( [ storedAddressType ] );
		const values = createInitialDonationAddressValues( dataPersister, { addressType: initialAddressType } );

		expect( values.addressType ).toEqual( AddressTypeModel.PERSON );
	} );

	it( 'converts initial testType from string to AddressTypeModel', () => {
		const initialAddressType = 'person';
		const dataPersister = new FakeDataPersister( [] );

		const values = createInitialDonationAddressValues( dataPersister, { addressType: initialAddressType } );

		expect( values.addressType ).toEqual( AddressTypeModel.PERSON );
	} );
} );

describe( 'createInitialDonationPaymentValues', () => {
	it( 'fills data from storage', () => {
		const amount = '1200';
		const type = 'person';
		const paymentIntervalInMonths = '1';
		const storageValues = [
			{ key: 'amount', value: amount },
			{ key: 'type', value: type },
			{ key: 'interval', value: paymentIntervalInMonths },
		];

		const dataPersister = new FakeDataPersister( storageValues );
		const values = createInitialDonationPaymentValues( dataPersister, {} );

		expect( values.amount ).toEqual( amount );
		expect( values.type ).toEqual( type );
		expect( values.paymentIntervalInMonths ).toEqual( paymentIntervalInMonths );
	} );

	it( 'fills data from initial data', () => {
		const initialValues = {
			amount: '1200',
			paymentType: 'person',
			paymentIntervalInMonths: '1',
			isCustomAmount: true,
		};

		const dataPersister = new FakeDataPersister( [] );
		const values = createInitialDonationPaymentValues( dataPersister, initialValues );

		expect( values.amount ).toEqual( initialValues.amount );
		expect( values.type ).toEqual( initialValues.paymentType );
		expect( values.paymentIntervalInMonths ).toEqual( initialValues.paymentIntervalInMonths );
		expect( values.isCustomAmount ).toEqual( initialValues.isCustomAmount );
	} );

	it( 'uses initial data over stored data', () => {
		const storageValues = [
			{ key: 'amount', value: '1400' },
			{ key: 'type', value: 'company' },
			{ key: 'paymentIntervalInMonths', value: '2' },
			{ key: 'isCustomAmount', value: false },
		];

		const initialValues = {
			amount: '1200',
			paymentType: 'person',
			paymentIntervalInMonths: '1',
			isCustomAmount: true,
		};

		const dataPersister = new FakeDataPersister( storageValues );
		const values = createInitialDonationPaymentValues( dataPersister, initialValues );

		expect( values.amount ).toEqual( initialValues.amount );
		expect( values.type ).toEqual( initialValues.paymentType );
		expect( values.paymentIntervalInMonths ).toEqual( initialValues.paymentIntervalInMonths );
		expect( values.isCustomAmount ).toEqual( initialValues.isCustomAmount );
	} );
} );

describe( 'createInitialMembershipAddressValues', () => {
	it( 'fills data from storage', () => {
		const firstName = { key: 'firstName', value: 'Spooky' };
		const lastName = { key: 'lastName', value: 'Magoo' };
		const date = { key: 'date', value: '01.01.1980' };
		const addressType = { key: 'addressType', value: AddressTypeModel.PERSON };
		const membershipType = { key: 'membershipType', value: MembershipTypeModel.SUSTAINING };

		const dataPersister = new FakeDataPersister( [ firstName, lastName, date, addressType, membershipType ] );
		const values = createInitialMembershipAddressValues( dataPersister, new Map() );

		const firstNameValue = values.fields.find( field => field.name === firstName.key );
		const lastNameValue = values.fields.find( field => field.name === lastName.key );

		expect( firstNameValue ).toBeDefined();
		expect( ( firstNameValue || {} ).value ).toEqual( firstName.value );
		expect( lastNameValue ).toBeDefined();
		expect( ( lastNameValue || {} ).value ).toEqual( lastName.value );
		expect( values.addressType ).toEqual( addressType.value );
		expect( values.membershipType ).toEqual( membershipType.value );
		expect( values.date ).toEqual( date.value );
	} );

	it( 'fills data from initial data', () => {
		const initialValues = {
			addressType: 'person',
			salutation: 'Mr',
			title: 'Dr',
			firstName: 'Spooky',
			lastName: 'Magoo',
			companyName: 'ACME',
			street: 'Sesame',
			city: 'Berlin',
			postcode: '12345',
			country: 'de',
			email: 'spookymagoo@email.com',
		};

		const dataPersister = new FakeDataPersister( [] );
		const values = createInitialMembershipAddressValues( dataPersister, new Map( Object.entries( initialValues ) ) );

		expect( values.addressType ).toEqual( AddressTypeModel.PERSON );
		expect( values.membershipType ).toBeUndefined();
		expect( values.date ).toBeNull();

		persistenceAddress( NS_MEMBERSHIP_ADDRESS ).fields.forEach( key => {
			const field = values.fields.find( field => field.name === key );
			expect( field ).toBeDefined();
			expect( ( field || {} ).value ).toEqual( ( initialValues as any )[ key ] );
		} );
	} );

	it( 'uses stored data over initial data', () => {
		const firstName = { key: 'firstName', value: 'Spooky' };
		const lastName = { key: 'lastName', value: 'Magoo' };
		const date = { key: 'date', value: '01.01.1980' };
		const addressType = { key: 'addressType', value: AddressTypeModel.PERSON };
		const membershipType = { key: 'membershipType', value: MembershipTypeModel.SUSTAINING };

		const initialValues = {
			addressType: 'firma',
			firstName: 'Kooky',
			lastName: 'Magee',
			salutation: '',
			title: '',
			companyName: '',
			street: '',
			city: '',
			postcode: '',
			country: '',
			email: '',
		};

		const dataPersister = new FakeDataPersister( [ firstName, lastName, date, addressType, membershipType ] );
		const values = createInitialMembershipAddressValues( dataPersister, new Map( Object.entries( initialValues ) ) );

		const firstNameValue = values.fields.find( field => field.name === firstName.key );
		const lastNameValue = values.fields.find( field => field.name === lastName.key );

		expect( firstNameValue ).toBeDefined();
		expect( ( firstNameValue || {} ).value ).toEqual( firstName.value );
		expect( lastNameValue ).toBeDefined();
		expect( ( lastNameValue || {} ).value ).toEqual( lastName.value );
		expect( values.addressType ).toEqual( addressType.value );
		expect( values.membershipType ).toEqual( membershipType.value );
		expect( values.date ).toEqual( date.value );
	} );

	it( 'converts initial testType from string to AddressTypeModel', () => {
		const initialAddressType = 'person';
		const dataPersister = new FakeDataPersister( [] );

		const values = createInitialMembershipAddressValues( dataPersister, new Map( Object.entries( { addressType: initialAddressType } ) ) );

		expect( values.addressType ).toEqual( AddressTypeModel.PERSON );
	} );
} );

describe( 'createInitialMembershipFeeValues', () => {
	it( 'fills data from storage', () => {
		const validateFeeUrl = 'https://wikipedia.de';
		const fee = { key: 'fee', value: 'Spooky' };
		const interval = { key: 'interval', value: 'Magoo' };

		const dataPersister = new FakeDataPersister( [ fee, interval ] );
		const values = createInitialMembershipFeeValues( dataPersister, 'https://wikipedia.de' );

		expect( values.validateFeeUrl ).toEqual( validateFeeUrl );
		expect( values.fee ).toEqual( fee.value );
		expect( values.interval ).toEqual( interval.value );
	} );
} );

describe( 'createInitialBankDataValues', () => {
	it( 'fills data from initial data', () => {
		const initialValues = {
			iban: 'fakeAccountID',
			bic: 'IAmBIC',
			bankname: 'Bank of fakey fake',
		};

		const values = createInitialBankDataValues( initialValues );

		expect( values.accountId ).toEqual( initialValues.iban );
		expect( values.bankId ).toEqual( initialValues.bic );
		expect( values.bankName ).toEqual( initialValues.bankname );
	} );

	it( 'handles null initial value object', () => {
		const values = createInitialBankDataValues( null );

		expect( values.accountId ).toEqual( '' );
		expect( values.bankId ).toEqual( '' );
		expect( values.bankName ).toEqual( '' );
	} );
} );
