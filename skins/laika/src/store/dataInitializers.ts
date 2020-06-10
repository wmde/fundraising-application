import { FieldInitialization } from '@/view_models/FieldInitialization';
import persistenceAddress from '@/store/data_persistence/address';
import { Validity } from '@/view_models/Validity';
import { DataPersister } from '@/store/data_persistence/DataPersister';
import { NS_ADDRESS, NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { InitialAddressValues, InitialMembershipAddressValues } from '@/view_models/Address';
import { addressTypeFromName } from '@/view_models/AddressTypeModel';
import { InitialPaymentValues } from '@/view_models/Payment';
import { BankAccountData, InitialBankAccountData } from '@/view_models/BankAccount';
import { InitialMembershipFeeValues } from '@/view_models/MembershipFee';
import { trackFormFieldRestored } from '@/tracking';

const replaceInitialValue = ( defaultValue: any, replacement: any ): any => {
	if ( replacement !== undefined && replacement !== null && replacement !== '' ) {
		return replacement;
	}
	return defaultValue;
};

const nullifyZeroString = ( value: string ): string|null => {
	if ( value === '0' ) {
		return null;
	}
	return value;
};

/**
 * Look for address fields in local storage and get their values
 */
export const createInitialDonationAddressValues = ( dataPersister: DataPersister, initialFormValues: any ): InitialAddressValues => {
	const addressPersistItems: FieldInitialization[] = [];

	if ( initialFormValues.addressType ) {
		initialFormValues.addressType = addressTypeFromName( initialFormValues.addressType );
	}

	persistenceAddress( NS_ADDRESS ).fields.forEach( field => {
		const value = dataPersister.getInitialValue( field );
		if ( value ) {
			addressPersistItems.push( { name: field, value: value, validity: Validity.RESTORED } );
			trackFormFieldRestored( 'donation', field );
		}
	} );

	return {
		addressType: replaceInitialValue( initialFormValues.addressType, dataPersister.getInitialValue( 'addressType' ) ),
		newsletterOptIn: replaceInitialValue( false, dataPersister.getInitialValue( 'newsletter' ) ),
		receiptOptOut: replaceInitialValue( false, dataPersister.getInitialValue( 'receiptOptOut' ) ),
		fields: addressPersistItems,
	};
};

/**
 * Look for fields in initial form values and set them
 * If they don't exist check in local storage
 */
export const createInitialDonationPaymentValues = ( dataPersister: DataPersister, initialFormValues: any ): InitialPaymentValues => {
	if ( initialFormValues.amount !== undefined ) {
		initialFormValues.amount = nullifyZeroString(
			initialFormValues.amount.replace( ',', '' ).replace( /^000$/, '0' )
		);
	}

	let paymentIntervalInMonths = replaceInitialValue( '0', dataPersister.getInitialValue( 'interval' ) );
	if ( initialFormValues.paymentIntervalInMonths !== undefined && initialFormValues.paymentIntervalInMonths !== null ) {
		paymentIntervalInMonths = replaceInitialValue(
			paymentIntervalInMonths,
			String( initialFormValues.paymentIntervalInMonths )
		);
	}

	return {
		amount: replaceInitialValue( dataPersister.getInitialValue( 'amount' ), initialFormValues.amount ),
		type: replaceInitialValue( dataPersister.getInitialValue( 'type' ), initialFormValues.paymentType ),
		paymentIntervalInMonths: paymentIntervalInMonths,
		isCustomAmount: initialFormValues.isCustomAmount,
	};
};

/**
 * Get address values from local storage and set them.
 * If one doesn't exist look for it in initial form values.
 */
export const createInitialMembershipAddressValues = ( dataPersister: DataPersister, initialFormValues: Map<string, any> ): InitialMembershipAddressValues => {
	const addressPersistItems: FieldInitialization[] = [];

	if ( initialFormValues.has( 'addressType' ) ) {
		const addressType = initialFormValues.get( 'addressType' );
		initialFormValues.set( 'addressType', addressTypeFromName( addressType ) );
	}

	persistenceAddress( NS_MEMBERSHIP_ADDRESS ).fields.forEach( field => {
		const value = dataPersister.getInitialValue( field );
		if ( value ) {
			addressPersistItems.push( { name: field, value: value, validity: Validity.RESTORED } );
			trackFormFieldRestored( 'membership_application', field );
		} else if ( initialFormValues.has( field ) ) {
			// We consider all non-empty values from the backend valid because they come from the donation and were validated there
			addressPersistItems.push( { name: field, value: initialFormValues.get( field ), validity: Validity.VALID } );
		}
	} );

	return {
		addressType: replaceInitialValue( initialFormValues.get( 'addressType' ), dataPersister.getInitialValue( 'addressType' ) ),
		membershipType: replaceInitialValue( initialFormValues.get( 'membershipType' ), dataPersister.getInitialValue( 'membershipType' ) ),
		date: dataPersister.getInitialValue( 'date' ),
		receiptOptOut: replaceInitialValue( false, dataPersister.getInitialValue( 'receiptOptOut' ) ),
		fields: addressPersistItems,
	};
};

/**
 * Look for initial membership fee values in local storage
 */
export const createInitialMembershipFeeValues = ( dataPersister: DataPersister, validateFeeUrl: string ): InitialMembershipFeeValues => {
	return {
		validateFeeUrl: validateFeeUrl,
		fee: dataPersister.getInitialValue( 'fee' ),
		interval: dataPersister.getInitialValue( 'interval' ),
	};
};

/**
 * Look for initial bank fields in initial form data
 */
export const createInitialBankDataValues = ( initialFormValues: InitialBankAccountData|null ): BankAccountData & { bankName: string } => {

	let iban = '';
	let bic = '';
	let bankname = '';

	if ( initialFormValues ) {
		iban = initialFormValues.iban || '';
		bic = initialFormValues.bic || '';
		bankname = initialFormValues.bankname || '';
	}

	return {
		accountId: iban,
		bankId: bic,
		bankName: bankname,
	};
};
