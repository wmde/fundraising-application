<?php

declare(strict_types = 1);

namespace WMDE\Fundraising\Frontend\Tests\Unit;

use RuntimeException;
use WMDE\Fundraising\Frontend\Tests\Fixtures\FrozenValueObject;

/**
 * @covers WMDE\Fundraising\Frontend\FreezableValueObject
 * @covers WMDE\Fundraising\Frontend\Tests\Fixtures\FrozenValueObject
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FreezableValueObjectTest extends \PHPUnit_Framework_TestCase {

	public function testCanSetAndGetValuesBeforeFreeze() {
		$object = new FrozenValueObject();

		$object->setHeaderContent( 'header' );
		$object->setMainContent( 'body' );
		$object->setFooterContent( 'footer' );

		$this->assertSame( 'header', $object->getHeaderContent() );
		$this->assertSame( 'body', $object->getMainContent() );
		$this->assertSame( 'footer', $object->getFooterContent() );
	}

	public function testCanBeforeFreezeAndGetValuesAfter() {
		$object = new FrozenValueObject();

		$object->setHeaderContent( 'header' );
		$object->setMainContent( 'body' );
		$object->setFooterContent( 'footer' );

		$object->freeze();

		$this->assertSame( 'header', $object->getHeaderContent() );
		$this->assertSame( 'body', $object->getMainContent() );
		$this->assertSame( 'footer', $object->getFooterContent() );
	}

	public function testWhenFreezing_settersCauseException() {
		$object = new FrozenValueObject();

		$object->setHeaderContent( 'header' );
		$object->setMainContent( 'body' );
		$object->setFooterContent( 'footer' );

		$object->freeze();

		$this->setExpectedException( RuntimeException::class );
		$object->setHeaderContent( 'header' );
	}

	public function testWhenFreezingAndValueNotSet_settersCauseException() {
		$object = new FrozenValueObject();

		$object->freeze();

		$this->setExpectedException( RuntimeException::class );
		$object->setHeaderContent( 'header' );
	}

	public function testWhenNoValuesAreSet_assertNoNullFieldsThrowsException() {
		$object = new FrozenValueObject();

		$this->setExpectedException( RuntimeException::class );
		$object->assertNoNullFields();
	}

	public function testWhenSettingAllValues_assertNoNullFieldsDoesNothing() {
		$object = new FrozenValueObject();

		$object->setHeaderContent( 'header' );
		$object->setMainContent( 'body' );
		$object->setFooterContent( 'footer' );
		$object->freeze();

		$object->assertNoNullFields();
		$this->assertTrue( true );
	}

}
