'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	dataDisplayHandlers = require( '../../lib/view_handler/display_address' ),
	createAddressDisplayHandler = dataDisplayHandlers.createDisplayAddressHandler,
	createElement = function () {
		return {
			text: sinon.spy()
		};
	},
	createAddressConfigSpy = function () {
		return {
			fullName: createElement(),
			street: createElement(),
			postcode: createElement(),
			city: createElement(),
			country: createElement(),
			email: createElement()
		};
	}
	;

test( 'Address data is set', function ( t ) {
	var addressConfig = createAddressConfigSpy(),
		handler = createAddressDisplayHandler( addressConfig );

	handler.update( {
		salutation: 'Herr',
		title: 'Dr.',
		firstName: 'Hans',
		lastName: 'Gruber',
		street: '2121 Avenue of the Stars',
		postcode: '90067',
		city: 'Los Angeles',
		country: 'United States of America',
		email: 'hans@example.com',
		addressType: 'person'
	} );
	t.ok( addressConfig.fullName.text.calledWith( 'Herr Dr. Hans Gruber' ), 'full name is set with salutation and title' );
	t.ok( addressConfig.street.text.calledWith( '2121 Avenue of the Stars' ), 'street is set'  );
	t.ok( addressConfig.postcode.text.calledWith( '90067' ), 'postcode is set'  );
	t.ok( addressConfig.city.text.calledWith( 'Los Angeles' ), 'city is set'  );
	t.ok( addressConfig.country.text.calledWith( 'United States of America' ), 'country is set'  );
	t.ok( addressConfig.email.text.calledWith( 'hans@example.com' ), 'email is set'  );

	t.end();
} );

test( 'Given a company address, Company name is displayed in address instead of name', function ( t ) {
	var addressConfig = createAddressConfigSpy(),
		handler = createAddressDisplayHandler( addressConfig );

	handler.update( {
		salutation: 'Herr',
		title: 'Dr.',
		firstName: 'Hans',
		lastName: 'Gruber',
		companyName: 'Nakatomi Corporation',
		street: '2121 Avenue of the Stars',
		postcode: '90067',
		city: 'Los Angeles',
		country: 'United States of America',
		email: 'hans@example.com',
		addressType: 'firma'
	} );
	t.ok( addressConfig.fullName.text.calledWith( 'Nakatomi Corporation' ), 'company name is set' );

	t.end();
} );

test( 'Address display handler checks if all elements are configured', function ( t ) {
	// TODO Do more than a spot check. How do i check all fields without using a loop in the test?
	var addressWithMissingFullName = {
			street: createElement(),
			postcode: createElement(),
			city: createElement(),
			country: createElement(),
			email: createElement()
		};
	t.throws( function () {
		createAddressDisplayHandler( addressWithMissingFullName );
	} );

	t.end();
} );
