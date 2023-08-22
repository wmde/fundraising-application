<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Authentication\OldStyleTokens;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\DonationContext\DataAccess\DoctrineEntities\Donation;
use WMDE\Fundraising\Frontend\Authentication\AuthenticationBoundedContext;
use WMDE\Fundraising\MembershipContext\DataAccess\DoctrineEntities\MembershipApplication;

class FallbackTokenRepository implements TokenRepository {

	public function __construct(
		private readonly TokenRepository $primaryRepository,
		private readonly EntityManager $entityManager
	) {
	}

	public function storeToken( AuthenticationToken $token ): void {
		$this->primaryRepository->storeToken( $token );
	}

	public function getTokenById( int $id, AuthenticationBoundedContext $authenticationDomain ): ?AuthenticationToken {
		$token = $this->primaryRepository->getTokenById( $id, $authenticationDomain );
		if ( $token !== null ) {
			return $token;
		}
		return match ( $authenticationDomain ) {
			AuthenticationBoundedContext::Donation => $this->findLegacyDonationToken( $id ),
			AuthenticationBoundedContext::Membership => $this->findLegacyMembershipToken( $id ),
		};
	}

	private function findLegacyDonationToken( int $donationId ): ?AuthenticationToken {
		$donation = $this->entityManager->getRepository( Donation::class )->find( $donationId );
		if ( $donation === null ) {
			return null;
		}
		$dataObject = $donation->getDataObject();
		$updateToken = $dataObject->getUpdateToken();
		$accessToken = $dataObject->getAccessToken();
		$updateTokenExpiry = $dataObject->getUpdateTokenExpiry();
		if ( !$updateToken || !$accessToken || !$updateTokenExpiry ) {
			return null;
		}
		return new AuthenticationToken(
			$donationId,
			AuthenticationBoundedContext::Donation,
			$updateToken,
			$accessToken,
			new \DateTimeImmutable( $updateTokenExpiry )
		);
	}

	private function findLegacyMembershipToken( int $membershipId ): ?AuthenticationToken {
		$membership = $this->entityManager->getRepository( MembershipApplication::class )->find( $membershipId );
		if ( $membership === null ) {
			return null;
		}
		$dataObject = $membership->getDataObject();
		$updateToken = $dataObject->getUpdateToken();
		$accessToken = $dataObject->getAccessToken();
		if ( !$updateToken || !$accessToken ) {
			return null;
		}
		return new AuthenticationToken(
			$membershipId,
			AuthenticationBoundedContext::Membership,
			$updateToken,
			$accessToken,
			null
		);
	}
}
