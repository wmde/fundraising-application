<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Factories;

use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;
use WMDE\Fundraising\ContentProvider\ContentProvider;
use WMDE\Fundraising\Frontend\Infrastructure\Translation\TranslatorInterface;
use WMDE\Fundraising\Frontend\Infrastructure\UrlGenerator;

class MailerTemplatingFactory extends TwigFactory {

	private TranslatorInterface $translator;
	private ContentProvider $contentProvider;
	private UrlGenerator $urlGenerator;

	public function __construct( array $config, string $cachePath, TranslatorInterface $translator, ContentProvider $contentProvider, UrlGenerator $urlGenerator ) {
		parent::__construct( $config, $cachePath );
		$this->translator = $translator;
		$this->contentProvider = $contentProvider;
		$this->urlGenerator = $urlGenerator;
	}

	public function newTemplatingEnvironment( string $dayOfWeek ): Environment {
		$globals = [
			'day_of_the_week' => $dayOfWeek
		];

		return $this->newTwigEnvironment( $globals );
	}

	protected function getFilters(): array {
		return [
			new TwigFilter(
				'payment_interval',
				/** @var int|string $interval */
				function ( $interval ): string {
					return $this->translator->trans( "donation_payment_interval_{$interval}" );
				}
			),
			new TwigFilter(
				'payment_method',
				function ( string $method ): string {
					return $this->translator->trans( $method );
				}
			),
			new TwigFilter(
				'membership_type',
				function ( string $membershipType ): string {
					return $this->translator->trans( $membershipType );
				}
			),
		];
	}

	protected function getFunctions(): array {
		return [
			new TwigFunction(
				'mail_content',
				function ( string $name, array $context = [] ): string {
					return $this->contentProvider->getMail( $name, $context );
				},
				[ 'is_safe' => [ 'all' ] ]
			),
			new TwigFunction(
				'url',
				function ( string $name, array $parameters = [] ): string {
					return $this->urlGenerator->generateAbsoluteUrl( $name, $parameters );
				}
			)
		];
	}

}
