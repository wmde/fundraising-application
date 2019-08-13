export interface SupportersData {
	visibleSupporterId: number | null,
}

export interface Supporter {
	name : string,
	amount : string,
	comment : string,
}

export function supportersFromObject( obj: any ): Supporter[] {
	return obj.supporters.map( ( supporter: any ) => {
		return {
			name: supporter.name,
			amount: supporter.amount,
			comment: supporter.comment,
		};
	} );
}
