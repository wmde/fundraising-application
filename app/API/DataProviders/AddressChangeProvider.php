<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\App\API\DataProviders;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use WMDE\Fundraising\AddressChangeContext\UseCases\ReadAddressChange\AddressChangeData;
use WMDE\Fundraising\AddressChangeContext\UseCases\ReadAddressChange\ReadAddressChangeUseCase;

class AddressChangeProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface {
	public function __construct( private ReadAddressChangeUseCase $useCase ) {
	}

	public function supports( string $resourceClass, string $operationName = null, array $context = [] ): bool {
		return AddressChangeData::class === $resourceClass;
	}

	public function getItem( string $resourceClass, $id, string $operationName = null, array $context = [] ): ?AddressChangeData {
		// error_log("$operationName item $resourceClass with $id in context ".var_export($context, true));
		// TODO find a way to have a "composite" id with multiple keys. Using the filter property is not clean
		$previousIdentifier = $context['filter']['previousIdentifier'] ?? '';
		$found = $this->useCase->getAddressChangeByUuids( $id, $previousIdentifier );
		// error_log("Found item ".var_export($found, true));
		return $found;
	}
}
