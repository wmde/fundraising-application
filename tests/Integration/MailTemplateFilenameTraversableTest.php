<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * @covers \WMDE\Fundraising\Frontend\Infrastructure\Mail\MailTemplateFilenameTraversable
 */
class MailTemplateFilenameTraversableTest extends KernelTestCase {

	public function testTraversableContainsSomeEntriesInTheRightFormat(): void {
		static::bootKernel();

		/** @var FunFunFactory $funFunFactory */
		$funFunFactory = static::getContainer()->get( FunFunFactory::class );
		$mailTemplatePaths = $funFunFactory->newMailTemplateFilenameTraversable();

		$pathArray = iterator_to_array( $mailTemplatePaths );

		$this->assertContains( 'Contact_Confirm_to_User.txt.twig', $pathArray );
		$this->assertContains( 'Subscription_Request.txt.twig', $pathArray );
	}

}
