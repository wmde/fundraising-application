/**
 * @param namespacesAndName namespace1, namespace2, ..., mutationOrActionName
 */
function buildActionOrMutationName( ...namespacesAndName: string[] ): string {
	return namespacesAndName.join( '/' );
}

export const action = buildActionOrMutationName;
export const mutation = buildActionOrMutationName;
