<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\Entities\Donation;
use WMDE\Fundraising\Frontend\Domain\TransferCodeGenerator;

/**
 * @licence GNU GPL v2+
 * @author Kai Nissen < kai.nissen@wikimedia.de >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class UniqueTransferCodeGenerator implements TransferCodeGenerator {

	private $generator;
	private $entityRepository;

	public function __construct( TransferCodeGenerator $generator, EntityManager $entityManager ) {
		$this->generator = $generator;
		$this->entityRepository = $entityManager->getRepository( Donation::class );
	}

	public function generateTransferCode(): string {
		do {
			$transferCode = $this->generator->generateTransferCode();
		} while ( $this->codeIsNotUnique( $transferCode ) );

		return $transferCode;
	}

	private function codeIsNotUnique( string $transferCode ): bool {
		return !empty( $this->entityRepository->findBy( [ 'transferCode' => $transferCode ] ) );
	}

}
