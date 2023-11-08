<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\Infrastructure\Translation;

use WMDE\Fundraising\Frontend\Presentation\Salutations;

/**
 * @license GPL-2.0-or-later
 */
class GreetingGenerator {
	private TranslatorInterface $translator;
	private Salutations $salutations;
	private string $genericGreeting;

	public function __construct( TranslatorInterface $translator, Salutations $salutations, string $genericGreeting ) {
		$this->translator = $translator;
		$this->salutations = $salutations;
		$this->genericGreeting = $genericGreeting;
	}

	private static function getSpacedTitle( ?string $title ): string {
		return $title ? $title . ' ' : '';
	}

	public function createFormalGreeting( ?string $salutation, ?string $firstName, ?string $lastName, ?string $title ): string {
		if ( !$lastName ) {
			return $this->translator->trans( $this->genericGreeting );
		}

		return $this->translator->trans( $this->getSalutationTranslationKey( $salutation, 'formal' ), [
			'%spacedTitle%' => self::getSpacedTitle( $title ),
			'%firstName%' => $firstName,
			'%lastName%' => $lastName,
		] );
	}

	public function createInformalGreeting( ?string $salutation, ?string $firstName, ?string $lastName ): string {
		if ( !$firstName || !$lastName ) {
			return $this->translator->trans( $this->genericGreeting );
		}

		return $this->translator->trans( $this->getSalutationTranslationKey( $salutation, 'informal' ), [
			'%firstName%' => $firstName,
			'%lastName%' => $lastName,
		] );
	}

	public function createInformalLastnameGreeting( ?string $salutation, ?string $firstName, ?string $lastName, ?string $title ): string {
		if ( !$lastName ) {
			return $this->translator->trans( $this->genericGreeting );
		}

		return $this->translator->trans( $this->getSalutationTranslationKey( $salutation, 'last_name_informal' ), [
			'%spacedTitle%' => self::getSpacedTitle( $title ),
			'%firstName%' => $firstName,
			'%lastName%' => $lastName,
		] );
	}

	private function getSalutationTranslationKey( ?string $salutation, string $greetingType ): string {
		$salutationConfig = $this->salutations->getSalutation( $salutation ?? '' );
		if ( !$salutation || !$salutationConfig ) {
			return $this->genericGreeting;
		}
		return $salutationConfig['greetings'][$greetingType];
	}
}
