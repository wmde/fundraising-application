export function bucketIdToCssClass( bucketNames: string[] ): string[] {
	return bucketNames.map( b => b.replace( /(-{2,}|[^a-zA-Z0-9.]+)/g, '-' ).replace( /\./g, '--' ) );
}
