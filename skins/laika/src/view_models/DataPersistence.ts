export interface DataPersister {
	getPlugin( items: DataPersistenceItem[] ): any;
	initialize( items: DataPersistenceItem[] ): Promise<void>;
	getValue( key: string ): string|null;
}

/**
 * Data is captured by mutation name on Vuex mutations,
 * but address fields all share the same mutation name
 * so their values have to be captured differently
 */
export enum DataPersistenceMutationType {
	VALUE,
	KEY_VALUE_PAIR,
}

/**
 * The fields array is only used for KEY_VALUE_PAIR mutation types
 */
export interface DataPersistenceItem {
	storageKey: string,
	mutationType: DataPersistenceMutationType,
	mutationKey: string,
	fields: string[]
}
