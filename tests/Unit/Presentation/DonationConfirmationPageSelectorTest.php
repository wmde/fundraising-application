<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use WMDE\Fundraising\Frontend\Presentation\DonationConfirmationPageSelector;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationConfirmationPageSelectorTest extends \PHPUnit_Framework_TestCase {

	public function testWhenConfigIsEmpty_selectPageReturnsEmptyString() {
		$selector = new DonationConfirmationPageSelector( [] );
		$this->assertSame( '', $selector->selectPage() );
	}

	public function testWhenConfigContainsOneElement_selectPageReturnsThatElement() {
		$selector = new DonationConfirmationPageSelector( [ 'ThisIsJustATest.twig' ] );
		$this->assertSame( 'ThisIsJustATest.twig', $selector->selectPage() );
	}

	public function testWhenConfigContainsSomeElements_selectPageReturnsOne() {
		$selector = new DonationConfirmationPageSelector( [ 'ThisIsJustATest.twig', 'AnotherOne.twig' ] );
		$this->assertNotEmpty( $selector->selectPage() );
	}

}
