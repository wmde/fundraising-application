<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\Presentation;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Presentation\Salutations;

/**
 * @covers \WMDE\Fundraising\Frontend\Presentation\Salutations
 */
class SalutationsTest extends TestCase {
	public function testGetListReturnsSalutations(): void {
		$salutationsArray = [
			[
				'label' => 'Herr',
				'value' => 'Herr',
				'display' => '',
				'greetings' => [
					'formal' => 'formal',
					'informal' => 'informal',
					'last_name_informal' => 'lastNameInformal',
				],
			]
		];

		$salutations = new Salutations( $salutationsArray );

		$this->assertEquals( $salutationsArray, $salutations->getList() );
	}

	public function testGetSalutationReturnsSalutation(): void {
		$salutationsArray = [
			[
				'label' => 'Herr',
				'value' => 'Herr',
				'display' => 'Herr',
				'greetings' => [
					'formal' => 'formal',
					'informal' => 'informal',
					'last_name_informal' => 'lastNameInformal',
				],
			],
			[
				'label' => 'Frau',
				'value' => 'Frau',
				'display' => 'Frau',
				'greetings' => [
					'formal' => 'formal',
					'informal' => 'informal',
					'last_name_informal' => 'lastNameInformal',
				],
			]
		];

		$salutations = new Salutations( $salutationsArray );

		$this->assertEquals( $salutationsArray[1], $salutations->getSalutation( 'Frau' ) );
	}
}
