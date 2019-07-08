export enum MembershipTypeModel {
    SUSTAINING,
    ACTIVE,
}

export const MembershipTypeNames = new Map<number, string>( [
	[ MembershipTypeModel.SUSTAINING, 'sustaining' ],
	[ MembershipTypeModel.ACTIVE, 'active' ],
] );

export function membershipTypeName( t: MembershipTypeModel ): string {
	const name = MembershipTypeNames.get( t );
	// poor man's type check to protect against future extensions of MembershipTypeNames, e.g. https://phabricator.wikimedia.org/T220367
	if ( typeof name === 'undefined' ) {
		throw new Error( 'Unknown membership type: ' + t );
	}
	return name;
}
