<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App\MailTemplateFixtures;

use WMDE\Fundraising\DonationContext\Domain\Model\ModerationIdentifier as DonationModerationIdentifier;
use WMDE\Fundraising\MembershipContext\Domain\Model\ModerationIdentifier as MembershipModerationIdentifier;

/**
 * This file contains a list of all Mail templates and the variables rendered in them.
 *
 * Some templates contain if statements, leading to different permutations of output, which are rendered individually.
 * These outputs are covered by the "variants". The VariantTemplateProvider will merge its "base" template data with the template data from the variants.
 */
class MailTemplateFixtures {
	/**
	 * @return iterable<TemplateSettingsGenerator>
	 */
	public static function getTemplateProviders(): iterable {
		yield new SimpleSettingsGenerator( 'Contact_Confirm_to_User.txt.twig' );

		yield new SimpleSettingsGenerator( 'Contact_Forward_to_Operator.txt.twig', [
			'firstName' => 'John',
			'lastName' => 'Doe',
			'emailAddress' => 'j.doe808@example.com',
			'donationNumber' => '123456',
			'subject' => 'Missing Link',
			'category' => 'Other',
			'message' => 'Please advise',
		] );

		yield new VariantSettingsGenerator( 'Donation_Confirmation.txt.twig',
			[
				'recipient' => [
					'lastName' => '姜',
					'firstName' => '留美子',
					'salutation' => 'Frau',
					'title' => ''
				],
			],
			new TemplateVariant( 'deposit_unmoderated_non_recurring', [
				'donation' => [
					'id' => 42,
					'amount' => 12.34,
					'paymentType' => 'UEB',
					'interval' => 0,
					'bankTransferCode' => 'WZF3984Y',
					'receiptOptIn' => true
				]
			] ),
			new TemplateVariant( 'deposit_unmoderated_recurring', [
				'donation' => [
					'id' => 42,
					'amount' => 12.34,
					'paymentType' => 'UEB',
					'interval' => 6,
					'bankTransferCode' => 'WZF3984Y',
					'receiptOptIn' => false,
				]
			] ),
			new TemplateVariant( 'direct_debit_unmoderated_non_recurring', [
				'donation' => [
					'id' => 42,
					'amount' => 12.34,
					'paymentType' => 'BEZ',
					'interval' => 0,
					'receiptOptIn' => false
				]
			] ),
			new TemplateVariant( 'direct_debit_unmoderated_recurring', [
				'donation' => [
					'id' => 42,
					'amount' => 12.34,
					'paymentType' => 'BEZ',
					'interval' => 3,
					'receiptOptIn' => true
				]
			] ),
			new TemplateVariant( 'paypal_unmoderated_non_recurring', [
				'donation' => [
					'id' => 42,
					'amount' => 12.34,
					'paymentType' => 'PPL',
					'interval' => 0,
					'receiptOptIn' => false,
				]
			] ),
			new TemplateVariant( 'sofort_unmoderated_non_recurring', [
				'donation' => [
					'id' => 42,
					'amount' => 12.34,
					'paymentType' => 'SUB',
					'interval' => 0,
					'status' => 'Z',
					'receiptOptIn' => false,
				]
			] ),
			new TemplateVariant( 'credit_card_unmoderated_recurring', [
				'donation' => [
					'id' => 42,
					'amount' => 12.34,
					'paymentType' => 'MCP',
					'interval' => 1,
					'receiptOptIn' => false,
				]
			] ),
			new TemplateVariant( 'paypal_unmoderated_recurring', [
				'donation' => [
					'id' => 42,
					'amount' => 12.34,
					'paymentType' => 'PPL',
					'interval' => 6,
					'receiptOptIn' => false,
				]
			] ),
			new TemplateVariant( 'micropayment_unmoderated_recurring', [
				'donation' => [
					'id' => 42,
					'amount' => 12.34,
					'paymentType' => 'MCP',
					'interval' => 6,
					'receiptOptIn' => false,
				],
			] ),
			new TemplateVariant( 'moderated_amount_too_high', [
				'donation' => [
					'id' => 42,
					'paymentType' => 'UEB',
					'amount' => 99999.99,
					'interval' => 1,
					'moderationFlags' => [
						DonationModerationIdentifier::AMOUNT_TOO_HIGH->name => true
					],
					'receiptOptIn' => false,
				]
			] ),
			new TemplateVariant( 'moderated_other_reason', [
				'donation' => [
					'id' => 42,
					'amount' => 12.34,
					'paymentType' => 'PPL',
					'interval' => 1,
					'moderationFlags' => [
						DonationModerationIdentifier::MANUALLY_FLAGGED_BY_ADMIN->name => true
					],
					'receiptOptIn' => false,
				]

			] )
		);

		yield new VariantSettingsGenerator( 'Membership_Application_Confirmation.txt.twig',
			[
				'firstName' => 'Timothy',
				'lastName' => "O'Reilly",
				'salutation' => 'Herr',
				'title' => 'Dr.',
				'incentives' => [ 'totebag' ],
			],
			new TemplateVariant( 'direct_debit_active_yearly', [
				'membershipFee' => 15.23,
				'membershipType' => 'active',
				'paymentIntervalInMonths' => 12,
				'paymentType' => 'BEZ',
				'hasReceiptEnabled' => true
			] ),
			new TemplateVariant( 'direct_debit_active_yearly_receipt_optout', [
				'membershipFee' => 15.23,
				'membershipType' => 'active',
				'paymentIntervalInMonths' => 12,
				'paymentType' => 'BEZ',
				'hasReceiptEnabled' => false
			] ),
			new TemplateVariant( 'direct_debit_sustaining_quarterly', [
				'membershipFee' => 15.23,
				'membershipType' => 'sustaining',
				'paymentIntervalInMonths' => 3,
				'paymentType' => 'BEZ',
				'hasReceiptEnabled' => true
			] ),
			new TemplateVariant( 'paypal_sustaining_monthly', [
				'membershipFee' => 15.23,
				'membershipType' => 'sustaining',
				'paymentIntervalInMonths' => 1,
				'paymentType' => 'PPL',
				'hasReceiptEnabled' => true
			] ),
			new TemplateVariant( 'bank_transfer_active_yearly', [
				'membershipFee' => 15.23,
				'membershipType' => 'active',
				'paymentIntervalInMonths' => 12,
				'paymentType' => 'UEB',
				'hasReceiptEnabled' => true
			] ),
			new TemplateVariant( 'moderated_amount_too_high', [
				'membershipFee' => 90000.00,
				'paymentIntervalInMonths' => 1,
				'membershipType' => 'sustaining',
				'moderationFlags' => [
					MembershipModerationIdentifier::MEMBERSHIP_FEE_TOO_HIGH->name => true
				]
			] ),
			new TemplateVariant( 'moderated_other_reason', [
				'membershipFee' => 15.23,
				'paymentIntervalInMonths' => 1,
				'membershipType' => 'sustaining',
				'moderationFlags' => [
					MembershipModerationIdentifier::MANUALLY_FLAGGED_BY_ADMIN->name => true
				]
			], )
		);

		yield new VariantSettingsGenerator( 'Admin_Moderation.txt.twig',
			[],
			new TemplateVariant( 'membership', [
				'membershipFee' => 90000.00,
				'itemType' => 'ein Mitgliedschaftsantrag',
				'focURL' => 'https://backend.wikimedia.de/backend/member/list',
				'id' => '1'
			] ),
			new TemplateVariant( 'donation', [
				'amount' => 7777777.77,
				'itemType' => 'eine Spende',
				'focURL' => 'https://backend.wikimedia.de/backend/donation/list',
				'id' => '42'
			] )
		);

		yield new SimpleSettingsGenerator( 'Subscription_Confirmation.txt.twig', [
			'subscription' => [
				'email' => 'test@example.com',
				'address' => [
					'lastName' => "O'Reilly",
					'salutation' => 'Herr',
					'title' => 'Dr.'
				]
			]
		] );

		yield new SimpleSettingsGenerator( 'Subscription_Request.txt.twig', [
			'subscription' => [
				'email' => 'test@example.com',
				'confirmationCode' => '00deadbeef',
				'address' => [
					'lastName' => "O'Reilly",
					'salutation' => 'Herr',
					'title' => 'Dr.'
				]
			]
		] );
	}

	/**
	 * @return iterable<TemplateSettings>
	 */
	public static function getTemplates(): iterable {
		foreach ( self::getTemplateProviders() as $provider ) {
			foreach ( $provider->getTemplateSettings() as $templateObject ) {
				yield $templateObject;
			}
		}
	}
}
