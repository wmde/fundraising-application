<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WMDE\Clock\SystemClock;
use WMDE\Fundraising\DonationContext\Domain\AnonymizationException;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * This is for anonymising a donation in your local environment, to use it do the following:
 *
 * 1. Create a donation using the form
 * 2. Log into the Docker container: `docker compose exec app bash`
 * 3. Run: `php bin/console app:anonymise-donation [DONATION_ID]`
 */
#[AsCommand( name: 'app:anonymise-donation' )]
class AnonymiseDonationCommand extends Command {

	public function __construct( private readonly FunFunFactory $ffFactory ) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->addArgument('id', InputArgument::REQUIRED, 'The id of the donation you want to anonymize.');
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$donationAnonymizer = $this->ffFactory->newDonationAnonymizer();

		try {
			$donationId = intval( $input->getArgument( 'id' ) );

			$qb = $this->ffFactory->getEntityManager()->getConnection()->createQueryBuilder();
			$qb->update( 'spenden' )
				->set( 'dt_exp', ':current_date' )
				->set( 'dt_gruen', ':current_date' )
				->set( 'dt_backup', ':current_date' )
				->where( 'id = :id' )
				->setParameter( 'current_date', ( new SystemClock() )->now()->format( 'Y-m-d H:i:s' ) )
				->setParameter( 'id', $donationId )
				->executeQuery();

			$donationAnonymizer->anonymizeWithIds( $donationId );
		}
		catch ( AnonymizationException|\InvalidArgumentException $e ) {
			if ( gettype( $e ) === \InvalidArgumentException::class ) {
				return Command::INVALID;
			}
			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}
}
