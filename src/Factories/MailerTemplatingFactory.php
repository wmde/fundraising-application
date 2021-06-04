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

	public function newTemplatingEnvironment( TranslatorInterface $translator, ContentProvider $contentProvider, UrlGenerator $urlGenerator, string $dayOfWeek ): Environment {
		$filters = [
			new TwigFilter(
				'payment_interval',
				/** @var int|string $interval */
				static function ( $interval ) use ( $translator ): string {
					return $translator->trans( "donation_payment_interval_{$interval}" );
				}
			),
			new TwigFilter(
				'payment_method',
				static function ( string $method ) use ( $translator ): string {
					return $translator->trans( $method );
				}
			),
			new TwigFilter(
				'membership_type',
				static function ( string $membershipType ) use ( $translator ): string {
					return $translator->trans( $membershipType );
				}
			),
		];
		$functions = [
			new TwigFunction(
				'mail_content',
				static function ( string $name, array $context = [] ) use ( $contentProvider ): string {
					return $contentProvider->getMail( $name, $context );
				},
				[ 'is_safe' => [ 'all' ] ]
			),
			new TwigFunction(
				'url',
				static function ( string $name, array $parameters = [] ) use ( $urlGenerator ): string {
					return $urlGenerator->generateAbsoluteUrl( $name, $parameters );
				}
			)
		];
		$globals = [
			'day_of_the_week' => $dayOfWeek
		];

		return $this->newTwigEnvironment( $filters, $functions, $globals );
	}

}
