<?php
declare(strict_types=1);

namespace WMDE\Fundraising\Frontend\Tests\Unit\Infrastructure;

use Symfony\Component\HttpFoundation\ParameterBag;
use WMDE\Fundraising\Frontend\Infrastructure\ParameterBagEncodingConverter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\ParameterBagEncodingConverter
 */
class ParameterBagEncodingConverterTest extends TestCase
{
	public function testGivenEmptyParameterBagResultIsEmpty(): void {
		$input = new ParameterBag();

		$result = ParameterBagEncodingConverter::convert( $input,'ISO-8859-1' );

		$this->assertSame( [], $result->all() );
	}
}
