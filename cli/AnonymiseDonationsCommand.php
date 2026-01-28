<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WMDE\Clock\SystemClock;
use WMDE\Fundraising\DonationContext\DataAccess\DatabaseDonationAnonymizer;
use WMDE\Fundraising\DonationContext\Domain\AnonymizationException;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

/**
 * This is for anonymising a donation in your local environment, to use it do the following:
 *
 * 1. Create a donation using the form
 * 2. Run: `docker compose exec app php bin/console app:anonymise-donations`
 */
#[AsCommand( name: 'app:anonymise-donations' )]
class AnonymiseDonationsCommand extends Command {

	public function __construct( private readonly FunFunFactory $ffFactory ) {
		parent::__construct();
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$donationAnonymizer = new DatabaseDonationAnonymizer(
			$this->ffFactory->getDonationRepository(),
			$this->ffFactory->getEntityManager(),
			new SystemClock(),
			new \DateInterval( 'P2D' )
		);

		try {
			$donationAnonymizer->anonymizeAll();
		} catch ( AnonymizationException $e ) {
			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}
}
