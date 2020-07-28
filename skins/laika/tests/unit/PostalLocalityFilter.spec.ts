import { CSVPostalLocalityFilter } from '@/PostalLocalityFilter';

describe( 'Postal Locality Filter', () => {
	const validPostCode = '10115';
	const invalidPostCode = '_234A';
	const emptyPostCode = '';
	const

	it( 'retrieves localities with valid postcode', () => {
		const expectedLocalities = [ 'City1', 'Town1', 'Village1' ];
		const filter = new CSVPostalLocalityFilter();
		const localities = filter.getPostalLocalities( validPostCode );

		expect( localities ).toEqual( expectedLocalities );
	} );

	it( 'returns no data on invalid postcode', () => {
		const filter = new CSVPostalLocalityFilter();
		const localities = filter.getPostalLocalities( invalidPostCode );

		expect( localities ).toEqual( [] );
	} );

	it( 'returns no duplicate results', () => {
		fail( 'not implemented' );
	} );

	it( 'returns results alphabetically sorted', () => {
		fail( 'not implemented' );
	} );

} );
