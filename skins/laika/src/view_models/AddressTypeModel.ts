export enum AddressTypeModel {
    PERSON,
    COMPANY,
    ANON,
}

export const AddressTypeNames = new Map<number, string>( [
	[ AddressTypeModel.ANON, 'anonym' ],
	[ AddressTypeModel.PERSON, 'person' ],
	[ AddressTypeModel.COMPANY, 'firma' ],
] );

export function addressTypeName( t: AddressTypeModel ): string {
	const name = AddressTypeNames.get( t );
	// poor man's type check to protect against future extensions of AddressTypeModel, e.g. https://phabricator.wikimedia.org/T220367
	if ( typeof name === 'undefined' ) {
		throw new Error( 'Unknown address type: ' + t );
	}
	return name;
}
