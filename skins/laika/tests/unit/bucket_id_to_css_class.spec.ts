import { bucketIdToCssClass } from '@/bucket_id_to_css_class';

describe( 'bucketIdToCssClass', () => {
	it( 'returns multiple class names', () => {
		const bucketIds = [ 'something', 'to', 'test' ];
		const expectedClassNames = [ 'something', 'to', 'test' ];

		expect( bucketIdToCssClass( bucketIds ) ).toEqual( expectedClassNames );
	} );

	it( 'converts dots to double dashes', () => {
		const bucketIds = [ 'campaigns.name1.bucket1', 'campaigns.name2.bucket1' ];
		const expectedClassNames = [ 'campaigns--name1--bucket1', 'campaigns--name2--bucket1' ];

		expect( bucketIdToCssClass( bucketIds ) ).toEqual( expectedClassNames );
	} );

	it( 'converts all non-word characters to dashes', () => {
		const bucketIds = [ 'sömething', 'to__test', 'dashing--forward', 'campaigns.name1.bucket1' ];
		const expectedClassNames = [ 's-mething', 'to-test', 'dashing-forward', 'campaigns--name1--bucket1' ];

		expect( bucketIdToCssClass( bucketIds ) ).toEqual( expectedClassNames );
	} );
} );
