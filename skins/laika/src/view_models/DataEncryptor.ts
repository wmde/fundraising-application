export interface DataEncryptor {
	encrypt( data: string ): Promise<ArrayBuffer>;
	decrypt( data: ArrayBuffer ): Promise<string>;
}
