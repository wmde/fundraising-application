/* eslint-disable no-bitwise */
import { DataPersistenceRepository } from '@/view_models/DataPersistenceRepository';

const BASE_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

export default class LocalStorageRepository implements DataPersistenceRepository {

	getItems(): {[key: string]: any} {
		return localStorage;
	}

	getItem( key: string ): ArrayBuffer|null {
		const value = localStorage.getItem( key );

		if ( !value ) {
			return null;
		}
		return this.unserializeArrayBuffer( value );
	}

	setItem( key: string, data: ArrayBuffer ): void {
		const encoded = this.serializeArrayBuffer( data );
		localStorage.setItem( key, encoded );
	}

	removeItem( key: string ): void {
		localStorage.removeItem( key );
	}

	/**
	 * This was copied from:
	 * https://github.com/localForage/localForage/blob/master/src/utils/serializer.js
	 */
	unserializeArrayBuffer( serializedString: string ) {
		let bufferLength = serializedString.length * 0.75;
		const length = serializedString.length;
		let p = 0;

		if ( serializedString[ serializedString.length - 1 ] === '=' ) {
			bufferLength--;
			if ( serializedString[ serializedString.length - 2 ] === '=' ) {
				bufferLength--;
			}
		}

		let buffer = new ArrayBuffer( bufferLength );
		let bytes = new Uint8Array( buffer );

		for ( let i = 0; i < length; i += 4 ) {
			const encoded1 = BASE_CHARS.indexOf( serializedString[ i ] );
			const encoded2 = BASE_CHARS.indexOf( serializedString[ i + 1 ] );
			const encoded3 = BASE_CHARS.indexOf( serializedString[ i + 2 ] );
			const encoded4 = BASE_CHARS.indexOf( serializedString[ i + 3 ] );

			bytes[ p++ ] = ( encoded1 << 2 ) | ( encoded2 >> 4 );
			bytes[ p++ ] = ( ( encoded2 & 15 ) << 4 ) | ( encoded3 >> 2 );
			bytes[ p++ ] = ( ( encoded3 & 3 ) << 6 ) | ( encoded4 & 63 );
		}
		return buffer;
	}

	/**
	 * This was copied from:
	 * https://github.com/localForage/localForage/blob/master/src/utils/serializer.js
	 */
	serializeArrayBuffer( buffer: ArrayBuffer ) {
		const bytes = new Uint8Array( buffer );
		let base64String = '';

		for ( let i = 0; i < bytes.length; i += 3 ) {
			base64String += BASE_CHARS[ bytes[ i ] >> 2 ];
			base64String += BASE_CHARS[ ( ( bytes[ i ] & 3 ) << 4 ) | ( bytes[ i + 1 ] >> 4 ) ];
			base64String +=
				BASE_CHARS[ ( ( bytes[ i + 1 ] & 15 ) << 2 ) | ( bytes[ i + 2 ] >> 6 ) ];
			base64String += BASE_CHARS[ bytes[ i + 2 ] & 63 ];
		}

		if ( bytes.length % 3 === 2 ) {
			base64String = base64String.substring( 0, base64String.length - 1 ) + '=';
		} else if ( bytes.length % 3 === 1 ) {
			base64String =
				base64String.substring( 0, base64String.length - 2 ) + '==';
		}

		return base64String;
	}
}
