<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Fixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use PHPUnit_Framework_TestCase;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ThrowingEntityManager {

	public static function newInstance( PHPUnit_Framework_TestCase $testCase ): EntityManager {
		$entityManager = $testCase->getMockBuilder( EntityManager::class )
			->disableOriginalConstructor()->getMock();

		$entityManager->expects( $testCase->any() )
			->method( $testCase->anything() )
			->willThrowException( new ORMException() );

		return $entityManager;
	}

}