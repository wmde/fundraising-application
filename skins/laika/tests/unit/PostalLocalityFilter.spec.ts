import { CSVPostalLocalityFilter } from '@/PostalLocalityFilter';
import { postalLocalities } from '../data/postalLocalities';

describe( 'Postal Locality Filter', () => {
	const validPostcode = '99999';
	const invalidPostcode = '_234A';
	const emptyPostcode = '';
	const tooLongPostcode = '1234567';
	const tooShortPostcode = '123';
	const validPostcodeWithDuplicateResult = '66666';

	it( 'retrieves localities alphabetically sorted with valid postcode', () => {
		const expectedLocalities = [ 'Mushroom Kingdom City', 'Takeshi\'s Castle' ];
		const filter = new CSVPostalLocalityFilter( postalLocalities );
		const localities = filter.getPostalLocalities( validPostcode );

		expect( localities ).toEqual( expectedLocalities );
	} );

	it( 'returns no data on invalid postcode', () => {
		const filter = new CSVPostalLocalityFilter( postalLocalities );

		let localities = filter.getPostalLocalities( invalidPostcode );
		expect( localities ).toEqual( [] );

		localities = filter.getPostalLocalities( emptyPostcode );
		expect( localities ).toEqual( [] );

		localities = filter.getPostalLocalities( tooLongPostcode );
		expect( localities ).toEqual( [] );

		localities = filter.getPostalLocalities( tooShortPostcode );
		expect( localities ).toEqual( [] );
	} );

	it( 'returns no duplicate results', () => {
		const filter = new CSVPostalLocalityFilter( postalLocalities );
		const localities = filter.getPostalLocalities( validPostcodeWithDuplicateResult );

		expect( localities ).toEqual( [ 'Satan City' ] );
	} );

} );
