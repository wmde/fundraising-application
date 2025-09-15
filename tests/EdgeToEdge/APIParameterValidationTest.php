<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\Frontend\Tests\EdgeToEdge;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\Frontend\App\EventHandlers\HandleExceptions;

#[CoversClass( HandleExceptions::class )]
class APIParameterValidationTest extends WebRouteTestCase {
	/**
	 * We're using this API route as an example because it uses the #[MapRequestPayload] attribute
	 */
	private const string PATH = '/api/v1/donation/comment';

	public function testGivenJSONRequestWithoutParameters_responseContainsFieldNames(): void {
		/** @var KernelBrowser $client */
		$client = $this->createClient();

		$client->jsonRequest(
			Request::METHOD_POST,
			self::PATH,
			[]
		);

		$response = $client->getResponse();
		$responseJson = json_decode( $response->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR );
		$this->assertSame( Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode() );
		$this->assertIsArray( $responseJson );
		$this->assertArrayHasKey( 'validationErrors', $responseJson );
		$this->assertSame(
			[
				'comment' => 'This value should be of type string.',
				'donationId' => 'This value should be of type int.',
			],
			$responseJson['validationErrors']
		);
	}
}
