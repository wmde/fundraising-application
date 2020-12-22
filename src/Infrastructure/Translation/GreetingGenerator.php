<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Translation;

/**
 * @license GPL-2.0-or-later
 */
class GreetingGenerator {

	private const GREETING_MALE = 'Herr';
	private const GREETING_FEMALE = 'Frau';
	private const GREETING_FAMILY = 'Familie';

	private TranslatorInterface $translator;

	public function __construct( TranslatorInterface $translator ) {
		$this->translator = $translator;
	}

	private static function getSpacedTitle( ?string $title ): string {
		return $title ? $title . ' ' : '';
	}

	public function createFormalGreeting( ?string $lastName, ?string $salutation, ?string $title ): string {
		if ( !$lastName ) {
			return $this->translator->trans( 'mail_introduction_generic' );
		}

		$spacedTitle = self::getSpacedTitle( $title );

		switch ( $salutation ) {
			case self::GREETING_MALE:
				return $this->translator->trans( 'mail_introduction_male_formal', [ '%spacedTitle%' => $spacedTitle, '%lastName%' => $lastName ] );
			case self::GREETING_FEMALE:
				return $this->translator->trans( 'mail_introduction_female_formal', [ '%spacedTitle%' => $spacedTitle, '%lastName%' => $lastName ] );
			case self::GREETING_FAMILY:
				return $this->translator->trans( 'mail_introduction_family_formal', [ '%spacedTitle%' => $spacedTitle, '%lastName%' => $lastName ] );
			default:
				return $this->translator->trans( 'mail_introduction_generic' );
		}
	}

	public function createInformalGreeting( ?string $salutation, ?string $firstName, ?string $lastName ): string {
		if ( ( $salutation !== self::GREETING_FAMILY && !$firstName ) ||
			( $salutation === self::GREETING_FAMILY && !$lastName ) ) {
			return $this->translator->trans( 'mail_introduction_generic' );
		}

		switch ( $salutation ) {
			case self::GREETING_MALE:
				return $this->translator->trans( 'mail_introduction_male_informal', [ '%firstName%' => $firstName ] );
			case self::GREETING_FEMALE:
				return $this->translator->trans( 'mail_introduction_female_informal', [ '%firstName%' => $firstName ] );
			case self::GREETING_FAMILY:
				return $this->translator->trans( 'mail_introduction_family_informal', [ '%lastName%' => $lastName ] );
			default:
				return $this->translator->trans( 'mail_introduction_generic' );
		}
	}

	public function createInformalLastnameGreeting( ?string $salutation, ?string $lastName, ?string $title ): string {
		if ( !$lastName ) {
			return $this->translator->trans( 'mail_introduction_generic' );
		}

		$spacedTitle = self::getSpacedTitle( $title );

		switch ( $salutation ) {
			case self::GREETING_MALE:
				return $this->translator->trans( 'mail_introduction_male_lastname_informal', [
					'%spacedTitle%' => $spacedTitle,
					'%lastName%' => $lastName
				] );
			case self::GREETING_FEMALE:
				return $this->translator->trans( 'mail_introduction_female_lastname_informal', [
					'%spacedTitle%' => $spacedTitle,
					'%lastName%' => $lastName
				] );
			case self::GREETING_FAMILY:
				return $this->translator->trans( 'mail_introduction_family_informal', [
					'%spacedTitle%' => $spacedTitle,
					'%lastName%' => $lastName
				] );
			default:
				return $this->translator->trans( 'mail_introduction_generic' );
		}
	}
}
