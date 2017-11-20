<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\RouteHandlers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\Donor;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorAddress;
use WMDE\Fundraising\Frontend\DonationContext\Domain\Model\DonorName;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\FunValidators\ConstraintViolation;

/**
 * Validates donor information. The route is named badly.
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidateAddressHandler {

	private $ffFactory;
	private $app;

	public function __construct( FunFunFactory $ffFactory, Application $app ) {
		$this->ffFactory = $ffFactory;
		$this->app = $app;
	}

	public function handle( Request $request ): Response {
		if ( $request->get( 'adressType', '' ) === 'anonym' ) {
			return $this->newSuccessResponse();
		}

		$validationResult =
			$this->ffFactory->newDonorValidator()
				->validate( $this->getDonorFromRequest( $request ) );

		if ( $validationResult->isSuccessful() ) {
			return $this->newSuccessResponse();
		}

		return $this->newErrorResponse( ...$validationResult->getViolations() );
	}

	private function newSuccessResponse(): Response {
		return $this->app->json( [ 'status' => 'OK' ] );
	}

	private function newErrorResponse( ConstraintViolation ...$violations ) {
		$errors = [];

		foreach( $violations as $violation ) {
			$errors[$violation->getSource()] = $this->ffFactory->getTranslator()->trans( $violation->getMessageIdentifier() );
		}

		return $this->app->json( [ 'status' => 'ERR', 'messages' => $errors ] );
	}

	private function getDonorFromRequest( Request $request ): Donor {
		return new Donor(
			$this->getNameFromRequest( $request ),
			$this->getPhysicalAddressFromRequest( $request ),
			$request->get( 'email', '' )
		);
	}

	private function getPhysicalAddressFromRequest( Request $request ): DonorAddress {
		$address = new DonorAddress();

		$address->setStreetAddress( $request->get( 'street', '' ) );
		$address->setPostalCode( $request->get( 'postcode', '' ) );
		$address->setCity( $request->get( 'city', '' ) );
		$address->setCountryCode( $request->get( 'country', '' ) );

		return $address->freeze()->assertNoNullFields();
	}

	private function getNameFromRequest( Request $request ): DonorName {
		$name = $request->get( 'addressType', '' ) === 'firma'
			? DonorName::newCompanyName() : DonorName::newPrivatePersonName();

		$name->setSalutation( $request->get( 'salutation', '' ) );
		$name->setTitle( $request->get( 'title', '' ) );
		$name->setCompanyName( $request->get( 'companyName', '' ) );
		$name->setFirstName( $request->get( 'firstName', '' ) );
		$name->setLastName( $request->get( 'lastName', '' ) );

		return $name->freeze()->assertNoNullFields();
	}

}