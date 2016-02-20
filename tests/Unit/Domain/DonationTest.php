<?php

namespace WMDE\Fundraising\Frontend\Tests\Unit\Domain;

use WMDE\Fundraising\Frontend\Domain\Donation;
use WMDE\Fundraising\Frontend\Domain\PersonalInfo;
use WMDE\Fundraising\Frontend\Domain\PersonName;

/**
 * @covers WMDE\Fundraising\Frontend\Domain\Donation
 *
 * @license GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 */
class DonationTest extends \PHPUnit_Framework_TestCase {

	public function testGenerateBankTransferCode_matchesRegex() {
		$donation = new Donation();
		$this->assertRegExp( '/W-Q-[A-Z]{6}-[A-Z]/', $donation->generateTransferCode() );
	}

	public function testPersonalInfoNotSet_determineFullNameReturnsAnonymous() {
		$donation = new Donation();
		$this->assertSame( 'Anonym', $donation->determineFullName() );
	}

	/** @dataProvider privatePersonProvider */
	public function testGivenPersonName_determineFullNameReturnsFullName( $expectedValue, $data ) {
		$personName = PersonName::newPrivatePersonName();
		$personName->setCompanyName( $data['company'] );
		$personName->setFirstName( $data['firstName'] );
		$personName->setLastName( $data['lastName'] );
		$personName->setTitle( $data['title'] );

		$personalInfo = new PersonalInfo();
		$personalInfo->setPersonName( $personName );

		$donation = new Donation();
		$donation->setPersonalInfo( $personalInfo );

		$this->assertSame( $expectedValue, $donation->determineFullName() );
	}

	public function privatePersonProvider() {
		return [
			[
				'Ebenezer Scrooge',
				[
					'title' => '',
					'firstName' => 'Ebenezer',
					'lastName' => 'Scrooge',
					'company' => ''
				]
			],
			[
				'Sir Ebenezer Scrooge',
				[
					'title' => 'Sir',
					'firstName' => 'Ebenezer',
					'lastName' => 'Scrooge',
					'company' => ''
				]
			],
			[
				'Prof. Dr. Friedemann Schulz von Thun',
				[
					'title' => 'Prof. Dr.',
					'firstName' => 'Friedemann',
					'lastName' => 'Schulz von Thun',
					'company' => ''
				]
			],
			[
				'Hank Scorpio, Globex Corp.',
				[
					'title' => '',
					'firstName' => 'Hank',
					'lastName' => 'Scorpio',
					'company' => 'Globex Corp.'
				]
			],
			[
				'Evil Hank Scorpio, Globex Corp.',
				[
					'title' => 'Evil',
					'firstName' => 'Hank',
					'lastName' => 'Scorpio',
					'company' => 'Globex Corp.'
				]
			]
		];
	}

}
