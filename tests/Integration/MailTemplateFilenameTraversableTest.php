<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Integration;

use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MailTemplateFilenameTraversableTest extends \PHPUnit\Framework\TestCase {

	public function testTraversableContainsSomeEntriesInTheRightFormat() {
		$mailTemplatePaths = TestEnvironment::newInstance()->getFactory()->newMailTemplateFilenameTraversable();

		$pathArray = iterator_to_array( $mailTemplatePaths );

		$this->assertContains( 'Mail_Contact_Confirm_to_User.txt.twig', $pathArray );
		$this->assertContains( 'Mail_Subscription_Request.txt.twig', $pathArray );
	}

}

