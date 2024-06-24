<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\Mail\MailTemplateFilenameTraversable;

#[CoversClass( MailTemplateFilenameTraversable::class )]
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
