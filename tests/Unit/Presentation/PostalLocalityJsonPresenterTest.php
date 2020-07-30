<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Presentation\Presenters\PostalLocalityJsonPresenter;
use WMDE\Fundraising\Frontend\Tests\Data\TestPostalLocalities;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\Presenters\PostalLocalityJsonPresenter
 */
class PostalLocalityJsonPresenterTest extends TestCase {

	private const VALID_POSTCODE = '99999';
	private const INVALID_POSTCODE = '_234A';
	private const EMPTY_POSTCODE = '';
	private const TOO_LONG_POSTCODE = '1234567';
	private const TOO_SHORT_POSTCODE = '123';
	private const VALID_POSTCODE_WITH_DUPLICATE_RESULT = '66666';

	public function testGivenValidPostcodeReturnsSortedLocalities() {
		$postalLocalityJsonPresenter = new PostalLocalityJsonPresenter( TestPostalLocalities::data() );
		$expectedLocalities = [ 'Mushroom Kingdom City', 'Takeshi\'s Castle' ];
		$filteredLocalities = $postalLocalityJsonPresenter->present( self::VALID_POSTCODE );

		$this->assertEquals( $expectedLocalities, $filteredLocalities );
	}

	public function testGivenInvalidPostcodeReturnsEmptyArray() {
		$postalLocalityJsonPresenter = new PostalLocalityJsonPresenter( TestPostalLocalities::data() );

		$filteredLocalities = $postalLocalityJsonPresenter->present( self::INVALID_POSTCODE );
		$this->assertEquals( [], $filteredLocalities );

		$filteredLocalities = $postalLocalityJsonPresenter->present( self::EMPTY_POSTCODE );
		$this->assertEquals( [], $filteredLocalities );

		$filteredLocalities = $postalLocalityJsonPresenter->present( self::TOO_LONG_POSTCODE );
		$this->assertEquals( [], $filteredLocalities );

		$filteredLocalities = $postalLocalityJsonPresenter->present( self::TOO_SHORT_POSTCODE );
		$this->assertEquals( [], $filteredLocalities );
	}

	public function testReturnsNoDuplicateResults() {
		$postalLocalityJsonPresenter = new PostalLocalityJsonPresenter( TestPostalLocalities::data() );

		$filteredLocality = $postalLocalityJsonPresenter->present( self::VALID_POSTCODE_WITH_DUPLICATE_RESULT );

		$this->assertEquals( [ 'Satan City' ], $filteredLocality );
	}
}
