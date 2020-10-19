<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\Controllers\Donation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WMDE\Fundraising\DonationContext\UseCases\ListComments\CommentListingRequest;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;

class ListCommentsController {

	public function handleJson( FunFunFactory $ffFactory, Request $request ): Response {
		$response = JsonResponse::create(
			$ffFactory->newCommentListJsonPresenter()->present(
				$ffFactory->newListCommentsUseCase()->listComments(
					new CommentListingRequest(
						(int)$request->query->get( 'n', '10' ),
						(int)$request->query->get( 'page', '1' )
					)
				)
			)
		);

		if ( $request->query->get( 'f' ) ) {
			$response->setCallback( $request->query->get( 'f' ) );
		}

		return $response;
	}

	public function handleRss( FunFunFactory $ffFactory ): Response {
		$rss = $ffFactory->newCommentListRssPresenter()->present(
			$ffFactory->newListCommentsUseCase()->listComments(
				new CommentListingRequest( 100, CommentListingRequest::FIRST_PAGE )
			)
		);

		return new Response(
			$rss,
			200,
			[
				'Content-Type' => 'text/xml; charset=utf-8',
				'X-Moz-Is-Feed' => '1'
			]
		);
	}

	public function handleHtml( FunFunFactory $ffFactory, Request $request ): Response {
		return new Response(
			$ffFactory->newCommentListHtmlPresenter()->present(
				$ffFactory->newListCommentsUseCase()->listComments(
					new CommentListingRequest(
						10,
						(int)$request->query->get( 'page', '1' )
					)
				),
				(int)$request->query->get( 'page', '1' )
			)
		);
	}
}
