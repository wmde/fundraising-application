export interface DataPersistenceRepository {
	setItem( key: string, data: ArrayBuffer ): void,
	getItems(): {[key: string]: any},
	getItem( key: string ): ArrayBuffer|null,
	removeItem( key: string ): void,
}
