<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories\EnvironmentSetup;

use Doctrine\ORM\ORMSetup;
use GuzzleHttp\Client;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\Frontend\Infrastructure\MembershipBannerCounting\DatabaseMembershipImpressionCounter;
use WMDE\Fundraising\Frontend\Presentation\Presenters\DevelopmentInternalErrorHtmlPresenter;
use WMDE\Fundraising\PaymentContext\Services\PayPal\GuzzlePaypalAPI;

class DevelopmentEnvironmentSetup implements EnvironmentSetup {

	private ErrorLogHandler $logHandler;

	public function setEnvironmentDependentInstances( FunFunFactory $factory ): void {
		$this->logHandler = new ErrorLogHandler();
		$this->setPaypalLogger( $factory );
		$this->setSofortLogger( $factory );
		$this->setDoctrineConfiguration( $factory );
		$this->setErrorPageHtmlPresenter( $factory );
		$this->setPayPalAPIClient( $factory );
		$this->setMembershipImpressionCounter( $factory );
	}

	private function setErrorPageHtmlPresenter( FunFunFactory $factory ): void {
		$factory->setInternalErrorHtmlPresenter(
			new DevelopmentInternalErrorHtmlPresenter()
		);
	}

	private function setPaypalLogger( FunFunFactory $factory ): void {
		$logger = new Logger( 'paypal', [ $this->logHandler ] );
		$factory->setPaypalLogger( $logger );
	}

	private function setSofortLogger( FunFunFactory $factory ): void {
		$logger = new Logger( 'sofort', [ $this->logHandler ] );
		$factory->setSofortLogger( $logger );
	}

	private function setDoctrineConfiguration( FunFunFactory $factory ): void {
		// Setup will use /tmp for proxies and ArrayCache for caching
		$factory->setDoctrineConfiguration( ORMSetup::createXMLMetadataConfiguration(
			$factory->getDoctrineXMLMappingPaths(),
			true,
			null,
			null,
			true
		) );
	}

	private function setPayPalAPIClient( FunFunFactory $factory ): void {
		$clientId = $_ENV['PAYPAL_API_CLIENT_ID'] ?? '';
		$secret = $_ENV['PAYPAL_API_CLIENT_SECRET'] ?? '';
		$baseUri = $_ENV['PAYPAL_API_URL'] ?? '';
		if ( !$clientId || !$secret || !$baseUri ) {
			throw new \LogicException( "You must put PAYPAL_API_URL, PAYPAL_API_CLIENT_ID and " .
				"PAYPAL_API_CLIENT_SECRET in the .env.* file" );
		}
		$factory->setPayPalAPI( new GuzzlePaypalAPI(
			new Client( [ 'base_uri' => $baseUri ] ),
			$clientId,
			$secret,
			$factory->getLogger()
		) );
	}

	/**
	 * @deprecated This is temporary for the 2023/2024 thank you banner campaign. It should be removed after the campaign (Feb 2024).
	 */
	private function setMembershipImpressionCounter( FunFunFactory $factory ): void {
		$factory->setMembershipImpressionCounter(
			new DatabaseMembershipImpressionCounter( $factory->getConnection() )
		);
	}

}
