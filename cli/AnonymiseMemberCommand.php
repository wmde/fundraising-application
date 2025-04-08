<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WMDE\Clock\SystemClock;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineMembershipAnonymizer;
use WMDE\Fundraising\MembershipContext\Domain\AnonymizationException;

/**
 * This is for anonymising a member in your local environment, to use it do the following:
 *
 * 1. Create a donation using the form
 * 2. Log into the Docker container: `docker compose exec app bash`
 * 3. Run: `php bin/console app:anonymise-member [MEMBER_ID]`
 */
#[AsCommand( name: 'app:anonymise-member' )]
class AnonymiseMemberCommand extends Command {

	public function __construct( private readonly FunFunFactory $ffFactory ) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->addArgument( 'id', InputArgument::REQUIRED, 'The id of the member you want to anonymize.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$membershipAnonymizer = new DoctrineMembershipAnonymizer(
			$this->ffFactory->getConnection(),
			new SystemClock(),
			new \DateInterval( 'P2D' )
		);

		try {
			/** @var string $annoyingPhpStanWorkaroundThatMakesOurCodeWorseMemberId */
			$annoyingPhpStanWorkaroundThatMakesOurCodeWorseMemberId = $input->getArgument( 'id' );
			$memberId = intval( $annoyingPhpStanWorkaroundThatMakesOurCodeWorseMemberId );

			$qb = $this->ffFactory->getEntityManager()->getConnection()->createQueryBuilder();
			$qb->update( 'request' )
				->set( 'export', ':current_date' )
				->set( 'backup', ':current_date' )
				->where( 'id = :id' )
				->setParameter( 'current_date', ( new SystemClock() )->now()->format( 'Y-m-d H:i:s' ) )
				->setParameter( 'id', $memberId )
				->executeQuery();

			$membershipAnonymizer->anonymizeWithIds( $memberId );
		} catch ( AnonymizationException | \InvalidArgumentException $e ) {
			if ( get_class( $e ) == \InvalidArgumentException::class ) {
				return Command::INVALID;
			}
			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}
}
