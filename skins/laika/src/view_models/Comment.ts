export interface Comment {
	amount: string,
	donor: string,
	comment: string,
	date: string
}

export function commentModelsFromObject( obj: any ): Comment[] {
	return obj.map( ( rawComment: any ) => {
		return {
			amount: rawComment.betrag,
			donor: rawComment.spender,
			comment: rawComment.kommentar,
			date: rawComment.lokalisiertes_datum,
		} as Comment;
	} );
}
