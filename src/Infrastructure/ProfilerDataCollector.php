<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ProfilerDataCollector extends DataCollector {

	public function collect( Request $request, Response $response, \Exception $exception = null ) {
		$this->data['calls']['SomeService'] = [ [ 'foo' ], [ 'bar' ] ]; // TODO
	}

	public function &getModifiableData(): array {
		return $this->data;
	}

	public function getName(): string {
		return 'funprofiler';
	}

}
