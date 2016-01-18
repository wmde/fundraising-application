<?php

namespace WMDE\Fundraising\Frontend\Tests\Integration\DataAccess;

use DateTime;
use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Spenden;
use WMDE\Fundraising\Frontend\DataAccess\DbalCommentRepository;
use WMDE\Fundraising\Frontend\Domain\Comment;
use WMDE\Fundraising\Frontend\Tests\TestEnvironment;

/**
 * @covers WMDE\Fundraising\Frontend\DataAccess\DbalCommentRepository
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DbalCommentRepositoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp() {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
		parent::setUp();
	}

	private function getOrmRepository() {
		return $this->entityManager->getRepository( Spenden::class );
	}

	public function testWhenThereAreNoComments_anEmptyListIsReturned() {
		$repository = new DbalCommentRepository( $this->getOrmRepository() );

		$this->assertEmpty( $repository->getComments( 10 ) );
	}

	public function testWhenThereAreLessCommentsThanTheLimit_theyAreAllReturned() {
		$firstSpenden = new Spenden();
		$firstSpenden->setName( 'First name' );
		$firstSpenden->setKommentar( 'First comment' );
		$firstSpenden->setBetrag( '100' );
		$firstSpenden->setDtNew( new DateTime( '1984-01-01' ) );
		$firstSpenden->setIsPublic( true ); // TODO
		$this->entityManager->persist( $firstSpenden );

		$secondSpenden = new Spenden();
		$secondSpenden->setName( 'Second name' );
		$secondSpenden->setKommentar( 'Second comment' );
		$secondSpenden->setBetrag( '200' );
		$secondSpenden->setDtNew( new DateTime( '1984-02-02' ) );
		$secondSpenden->setIsPublic( true );
		$this->entityManager->persist( $secondSpenden );

		$thirdSpenden = new Spenden();
		$thirdSpenden->setName( 'Third name' );
		$thirdSpenden->setKommentar( 'Third comment' );
		$thirdSpenden->setBetrag( '300' );
		$thirdSpenden->setDtNew( new DateTime( '1984-03-03' ) );
		$thirdSpenden->setIsPublic( true );
		$this->entityManager->persist( $thirdSpenden );

		$this->entityManager->flush();
		$repository = new DbalCommentRepository( $this->getOrmRepository() );

		$this->assertEquals(
			[
				Comment::newInstance()->setAuthorName( 'First name' )->setCommentText( 'First comment' )
					->setDonationAmount( '100' )->setPostingTime( new \DateTime( '1984-01-01' ) ),
				Comment::newInstance()->setAuthorName( 'Second name' )->setCommentText( 'Second comment' )
					->setDonationAmount( '200' )->setPostingTime( new \DateTime( '1984-02-02' ) ),
				Comment::newInstance()->setAuthorName( 'Third name' )->setCommentText( 'Third comment' )
					->setDonationAmount( '300' )->setPostingTime( new \DateTime( '1984-03-03' ) ),
			],
			$repository->getComments( 10 )
		);
	}


}
