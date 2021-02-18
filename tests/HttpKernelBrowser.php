<?php

// phpcs:ignoreFile MediaWiki.Commenting.FunctionComment.ObjectTypeHintParam
declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Tests;

use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Request as DomRequest;
use Symfony\Component\BrowserKit\Response as DomResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

/**
 * This class replaces the deprecated Symfony\Component\HttpKernel\Client in WebRouteTestCase.
 *
 * @todo Replace this class with HttpKernelBrowser when the switch to Symfony is finished
 *       and we can use a HttpKernel version >= 4.4
 */
class HttpKernelBrowser extends AbstractBrowser {

	protected HttpKernelInterface $kernel;
	private bool $catchExceptions = true;

	public function __construct( HttpKernelInterface $kernel ) {
		$this->kernel = $kernel;
		$this->followRedirects = false;
		parent::__construct();
	}

	/**
	 * @inheritDoc
	 */
	protected function doRequest( $request ) {
		$response = $this->kernel->handle( $request, HttpKernelInterface::MASTER_REQUEST, $this->catchExceptions );

		if ( $this->kernel instanceof TerminableInterface ) {
			$this->kernel->terminate( $request, $response );
		}

		return $response;
	}

	/**
	 * Converts the BrowserKit request to a HttpKernel request.
	 *
	 * @param DomRequest $request
	 *
	 * @return Request A Request instance
	 */
	protected function filterRequest( DomRequest $request ) {
		return Request::create( $request->getUri(), $request->getMethod(), $request->getParameters(), $request->getCookies(), $request->getFiles(), $request->getServer(), $request->getContent() );
	}

	/**
	 * Converts the HttpKernel response to a BrowserKit response.
	 *
	 * @param object $response
	 * @return DomResponse A DomResponse instance
	 */
	protected function filterResponse( $response ) {
		// this is needed to support StreamedResponse
		ob_start();
		$response->sendContent();
		$content = ob_get_clean();

		return new DomResponse( $content, $response->getStatusCode(), $response->headers->all() );
	}

}
