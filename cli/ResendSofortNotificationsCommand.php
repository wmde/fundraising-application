<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Cli;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\Frontend\Authentication\OldStyleTokens\NullToken;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ResendSofortNotificationsCommand extends Command {
	private const NAME = 'app:resend-sofort-notifications';

	private FunFunFactory $ffFactory;

	public function __construct( FunFunFactory $ffFactory ) {
		parent::__construct( self::NAME );
		$this->ffFactory = $ffFactory;
	}

	protected function configure(): void {
		$this->setName( self::NAME )
			->setDescription( 'Resend failed Sofort notifications from log' )
			->setDefinition(
				new InputDefinition( [
					new InputOption(
						'base-url',
						'u',
						InputOption::VALUE_REQUIRED,
						'Base URL',
						'https://spenden.wikimedia.de'
					),
					new InputOption(
						'log-path',
						'l',
						InputOption::VALUE_REQUIRED,
						'Path to log file',
						'sofort.log'
					),
				] )
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$tokenRepo = $this->ffFactory->getTokenRepositoryWithLegacyFallback();
		$client = new Client();

		$baseUrl = $input->getOption( 'base-url' );
		$logPath = $input->getOption( 'log-path' );

		$processedDonations = [];

		foreach ( file( $logPath ) as $line ) {
			$content = json_decode( $line, true, 512, JSON_THROW_ON_ERROR );
			$donationId = intval( $content['context']['query_vars']['id'] );
			$requestContent = $content['context']['request_content'];

			if ( !empty( $processedDonations[$donationId] ) ) {
				continue;
			}
			$processedDonations[$donationId] = true;

			$token = $tokenRepo->getTokenById( $donationId, AuthenticationBoundedContext::Donation );
			if ( $token instanceof NullToken ) {
				echo "Token not found for donation $donationId\n";
				continue;
			}
			$response = $client->request( 'POST', $baseUrl . '/sofort-payment-notification', [
				'headers' => [
					'Content-Type' => 'application/xml',
				],
				'body' => $requestContent,
				'query' => [
					'id' => $donationId,
					'updateToken' => $token->getUpdateToken(),
				]
			] );
			if ( $response->getStatusCode() !== 200 ) {
				echo "Error for donation $donationId: " . $response->getBody() . "\n";
			} else {
				echo "Success for donation $donationId\n";
			}
		}
		return Command::SUCCESS;
	}

}
