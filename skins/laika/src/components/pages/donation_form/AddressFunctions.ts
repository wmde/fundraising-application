import { AddressFormData, AddressValidity, ValidationResult } from '@/view_models/Address';
import { computed, onMounted, reactive } from '@vue/composition-api';
import { Validity } from '@/view_models/Validity';
import { AddressTypeModel, addressTypeName as getAddressTypeName } from '@/view_models/AddressTypeModel';
import { mergeValidationResults } from '@/merge_validation_results';
import {
	setAddressField,
	validateAddress,
	validateEmail,
	validateAddressType,
	setReceiptOptOut,
	setAddressType as setAddressTypeActionType,
} from '@/store/address/actionTypes';
import { NS_ADDRESS } from '@/store/namespaces';
import { action } from '@/store/util';
import { camelizeName } from '@/camlize_name';

export const useAddressFunctions = ( props: any, store: any ) => {
	const formData: AddressFormData = reactive(
		{
			salutation: {
				name: 'salutation',
				value: '',
				pattern: props.addressValidationPatterns.salutation,
				optionalField: false,
			},
			title: {
				name: 'title',
				value: '',
				pattern: props.addressValidationPatterns.title,
				optionalField: true,
			},
			companyName: {
				name: 'companyName',
				value: '',
				pattern: props.addressValidationPatterns.companyName,
				optionalField: false,
			},
			firstName: {
				name: 'firstName',
				value: '',
				pattern: props.addressValidationPatterns.firstName,
				optionalField: false,
			},
			lastName: {
				name: 'lastName',
				value: '',
				pattern: props.addressValidationPatterns.lastName,
				optionalField: false,
			},
			street: {
				name: 'street',
				value: '',
				pattern: props.addressValidationPatterns.street,
				optionalField: false,
			},
			city: {
				name: 'city',
				value: '',
				pattern: props.addressValidationPatterns.city,
				optionalField: false,
			},
			postcode: {
				name: 'postcode',
				value: '',
				pattern: props.addressValidationPatterns.postcode,
				optionalField: false,
			},
			country: {
				name: 'country',
				value: 'DE',
				pattern: props.addressValidationPatterns.country,
				optionalField: false,
			},
			email: {
				name: 'email',
				value: '',
				pattern: props.addressValidationPatterns.email,
				optionalField: false,
			},
		}
	);

	// computed
	const fieldErrors = computed(
		(): AddressValidity => {
			return Object.keys( formData ).reduce( ( validity: AddressValidity, fieldName: string ) => {
				if ( !formData[ fieldName ].optionalField ) {
					validity[ fieldName ] = store.state.address.validity[ fieldName ] === Validity.INVALID;
				}
				return validity;
			}, ( {} as AddressValidity ) );
		}
	);
	const disabledAddressTypes = computed(
		(): Array<AddressTypeModel> => {
			return store.getters[ 'payment/isDirectDebitPayment' ] ? [ AddressTypeModel.EMAIL, AddressTypeModel.ANON ] : [];
		}
	);
	const addressType = computed( () => store.getters[ 'address/addressType' ] );
	const addressTypeIsNotAnon = computed( () => store.getters[ 'address/addressTypeIsNotAnon' ] );
	const addressTypeIsInvalid = computed( () => store.getters[ 'address/addressTypeIsInvalid' ] );

	const addressTypeName = computed(
		(): string => getAddressTypeName( store.state.address.addressType )
	);
	const receiptNeeded = computed(
		(): Boolean => !store.state.address.receiptOptOut
	);

	// methods
	function validateForm(): Promise<ValidationResult> {
		return Promise.all( [
			store.dispatch( action( NS_ADDRESS, validateAddressType ), store.state.address.addressType ),
			store.dispatch( action( NS_ADDRESS, validateAddress ), props.validateAddressUrl ),
			store.dispatch( action( NS_ADDRESS, validateEmail ), props.validateEmailUrl ),
		] ).then( mergeValidationResults );
	}

	function onFieldChange( fieldName: string ): void {
		store.dispatch( action( NS_ADDRESS, setAddressField ), formData[ fieldName ] );
	}

	function onAutofill( autofilledFields: { [key: string]: string; } ): void {
		Object.keys( autofilledFields ).forEach( key => {
			const fieldName = camelizeName( key );
			if ( formData[ fieldName ] ) {
				store.dispatch( action( NS_ADDRESS, setAddressField ), formData[ fieldName ] );
			}
		} );
	}

	function setReceiptOptedOut( optedOut: boolean ): void {
		store.dispatch( action( NS_ADDRESS, setReceiptOptOut ), optedOut );
	}

	function setAddressType( addressType: AddressTypeModel ): void {
		store.dispatch( action( NS_ADDRESS, setAddressTypeActionType ), addressType );
	}

	return {
		formData,
		fieldErrors,
		disabledAddressTypes,
		addressType, addressTypeIsNotAnon, addressTypeIsInvalid,
		addressTypeName,
		receiptNeeded,

		validateForm,
		onFieldChange,
		onAutofill,
		setReceiptOptedOut,
		setAddressType,
	};
};
