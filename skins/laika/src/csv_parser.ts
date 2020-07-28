import Papa from 'papaparse';

export default function parse( file: string ) {
	return new Promise( function ( complete, error ) {
		Papa.parse( file, {
			complete,
			error,
			download: true,
			delimiter: ',',
			header: true,
			skipEmptyLines: true,
		} );
	} );
}
