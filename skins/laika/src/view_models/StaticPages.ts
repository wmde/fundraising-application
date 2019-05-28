export interface PageContent {
	pageId : string
}

/**
 * Convert from input format (with snake case for example) to proper typescript format in interfaces ( camel case )
 */
export function pageContentFromObject( obj: any ): PageContent {
	return {
		pageId: obj.page_id,
	};
}
