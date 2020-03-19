<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MailTemplateFilenameTraversableTest extends TestCase {

	public function testTraversableContainsSomeEntriesInTheRightFormat(): void {
		$mailTemplatePaths = TestEnvironment::newInstance()->getFactory()->newMailTemplateFilenameTraversable();

		$pathArray = iterator_to_array( $mailTemplatePaths );

		$this->assertContains( 'Contact_Confirm_to_User.txt.twig', $pathArray );
		$this->assertContains( 'Subscription_Request.txt.twig', $pathArray );
	}

}

