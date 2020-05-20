export enum DataPersistenceMutationType {
	VALUE,
	KEY_VALUE_PAIR,
}

export interface DataPersistenceItem {
	key: string,
	mutationType: DataPersistenceMutationType,
	mutation: string,
	fields: string[]
}
