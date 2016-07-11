<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests\Unit\UseCases\AddComment;

use WMDE\Fundraising\Frontend\UseCases\AddComment\AddCommentRequest;
use WMDE\Fundraising\Frontend\UseCases\AddComment\AddCommentValidator;

class AddCommentValidatorTest extends \PHPUnit_Framework_TestCase {

	private function newValidAddCommentRequest(): AddCommentRequest {
		$request = new AddCommentRequest();
		$request->setAuthorDisplayName( 'Gandalf the Grey' );
		$request->setCommentText( 'In the common tongue it reads "One Wiki to Rule Them All. One Wiki to Find Them. ' .
			'One Wiki to Bring Them All and In The Darkness Bind Them." ' );
		$request->setIsPublic( true );
		$request->setDonationId( 1 );
		return $request;
	}

	public function testValidCommentRequest_isSuccessful() {
		$validator = new AddCommentValidator();
		$this->assertTrue( $validator->validate( $this->newValidAddCommentRequest() )->isSuccessful() );
	}

	public function testLongComment_isNotSuccessful() {
		$validator = new AddCommentValidator();
		$request = $this->newValidAddCommentRequest();
		$request->setCommentText( str_repeat( 'All work and no play makes jack a dull boy.', 1000 ) );
		$this->assertFalse( $validator->validate( $request )->isSuccessful() );
	}

	public function testLongName_isNotSuccessful() {
		$validator = new AddCommentValidator();
		$request = $this->newValidAddCommentRequest();
		$request->setAuthorDisplayName( str_repeat( 'Dr. ', 50 ) . $request->getAuthorDisplayName() );
		$this->assertFalse( $validator->validate( $request )->isSuccessful() );
	}

}
