export enum AddressTypeModel {
    PERSON,
    COMPANY,
	EMAIL,
    ANON,
	UNSET,
}

export const AddressTypeNames = new Map<number, string>( [
	[ AddressTypeModel.ANON, 'anonym' ],
	[ AddressTypeModel.EMAIL, 'email' ],
	[ AddressTypeModel.PERSON, 'person' ],
	[ AddressTypeModel.COMPANY, 'firma' ],
	[ AddressTypeModel.UNSET, 'unset' ],
] );

export const AddressTypes = new Map<string, number>( [
	[ 'anonym', AddressTypeModel.ANON ],
	[ 'email', AddressTypeModel.EMAIL ],
	[ 'person', AddressTypeModel.PERSON ],
	[ 'firma', AddressTypeModel.COMPANY ],
	[ 'unset', AddressTypeModel.UNSET ],
] );

export function addressTypeName( t: AddressTypeModel ): string {
	const name = AddressTypeNames.get( t );
	// poor man's type check to protect against future extensions of AddressTypeModel, e.g. https://phabricator.wikimedia.org/T220367
	if ( typeof name === 'undefined' ) {
		return AddressTypeNames.get( AddressTypeModel.UNSET ) as string;
	}
	return name;
}

export function addressTypeFromName( n: string ): AddressTypeModel {
	const type = AddressTypes.get( n );
	// poor man's type check to protect against future extensions of AddressTypeModel, e.g. https://phabricator.wikimedia.org/T220367
	if ( typeof type === 'undefined' ) {
		return AddressTypes.get( 'unset' ) as AddressTypeModel;
	}
	return type;
}
