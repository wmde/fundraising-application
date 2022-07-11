<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\App;

use WMDE\Fundraising\DonationContext\Domain\Model\ModerationIdentifier as DonationModerationIdentifier;
use WMDE\Fundraising\Frontend\Factories\FunFunFactory;
use WMDE\Fundraising\MembershipContext\Domain\Model\ModerationIdentifier as MembershipModerationIdentifier;

/**
 * This file contains a list of all Mail templates and the variables rendered in them.
 *
 * Some templates contain if statements, leading to different permutations of output, which are rendered individually.
 * These outputs are covered by the "variants", which are automatically recursively merged into the main "context".
 */
class MailTemplates {

	/**
	 * @var FunFunFactory
	 */
	private $factory;

	public function __construct( FunFunFactory $factory ) {
		$this->factory = $factory;
	}

	public function get(): array {
		return [

			'Contact_Confirm_to_User.txt.twig' => [
				'context' => []
			],

			'Contact_Forward_to_Operator.txt.twig' => [
				'context' => [
					'firstName' => 'John',
					'lastName' => 'Doe',
					'emailAddress' => 'j.doe808@example.com',
					'donationNumber' => '123456',
					'subject' => 'Missing Link',
					'category' => 'Other',
					'message' => 'Please advise',
				],
			],

			'Donation_Cancellation_Confirmation.txt.twig' => [
				'context' => [
					'greeting_generator' => $this->factory->getGreetingGenerator(),
					'recipient' => [
						'firstName' => 'Timothy',
						'lastName' => "O'Reilly",
						'salutation' => 'Herr',
						'title' => 'Dr.'
					],
					'donationId' => 42
				]
			],

			'Donation_Confirmation.txt.twig' => [
				'context' => [
					'greeting_generator' => $this->factory->getGreetingGenerator(),
					'donation' => [
						'id' => 42,
						'receiptOptIn' => false,
					],
					'recipient' => [
						'lastName' => '姜',
						'firstName' => '留美子',
						'salutation' => 'Frau',
						'title' => ''
					],
				],
				'variants' => [
					'deposit_unmoderated_non_recurring' => [
						'donation' => [
							'amount' => 12.34,
							'paymentType' => 'UEB',
							'interval' => 0,
							'bankTransferCode' => 'WZF3984Y',
							'receiptOptIn' => true
						]
					],
					'deposit_unmoderated_recurring' => [
						'donation' => [
							'amount' => 12.34,
							'paymentType' => 'UEB',
							'interval' => 6,
							'bankTransferCode' => 'WZF3984Y',
						]
					],
					'direct_debit_unmoderated_non_recurring' => [
						'donation' => [
							'amount' => 12.34,
							'paymentType' => 'BEZ',
							'interval' => 0,
						]
					],
					'direct_debit_unmoderated_recurring' => [
						'donation' => [
							'amount' => 12.34,
							'paymentType' => 'BEZ',
							'interval' => 3,
							'receiptOptIn' => true
						]
					],
					'paypal_unmoderated_non_recurring' => [
						'donation' => [
							'amount' => 12.34,
							'paymentType' => 'PPL',
							'interval' => 0,
						]
					],
					'sofort_unmoderated_non_recurring' => [
						'donation' => [
							'amount' => 12.34,
							'paymentType' => 'SUB',
							'interval' => 0,
							'status' => 'Z'
						]
					],
					'credit_card_unmoderated_recurring' => [
						'donation' => [
							'amount' => 12.34,
							'paymentType' => 'MCP',
							'interval' => 1
						]
					],
					'paypal_unmoderated_recurring' => [
						'donation' => [
							'amount' => 12.34,
							'paymentType' => 'PPL',
							'interval' => 6,
						]
					],
					'micropayment_unmoderated_recurring' => [
						'donation' => [
							'amount' => 12.34,
							'paymentType' => 'MCP',
							'interval' => 6
						],
					],
					'moderated_amount_too_high' => [
						'donation' => [
							'paymentType' => 'UEB',
							'amount' => 99999.99,
							'interval' => 1,
							'moderationFlags' => [
								DonationModerationIdentifier::AMOUNT_TOO_HIGH->name => true
							]
						],
					],
					'moderated_other_reason' => [
						'donation' => [
							'amount' => 12.34,
							'paymentType' => 'PPL',
							'interval' => 1,
							'moderationFlags' => [
								DonationModerationIdentifier::MANUALLY_FLAGGED_BY_ADMIN->name => true
							]
						]

					]
				],
			],

			'Membership_Application_Cancellation_Confirmation.txt.twig' => [
				'context' => [
					'greeting_generator' => $this->factory->getGreetingGenerator(),
					'membershipApplicant' => [
						'firstName' => 'Timothy',
						'lastName' => "O'Reilly",
						'salutation' => 'Herr',
						'title' => 'Dr.'
					],
					'applicationId' => 23
				]
			],

			'Membership_Application_Confirmation.txt.twig' => [
				'context' => [
					'greeting_generator' => $this->factory->getGreetingGenerator(),
					'firstName' => 'Timothy',
					'lastName' => "O'Reilly",
					'salutation' => 'Herr',
					'title' => 'Dr.',
					'incentives' => [ 'totebag' ],
				],
				'variants' => [
					'direct_debit_active_yearly' => [
						'membershipFee' => 15.23,
						'membershipType' => 'active',
						'paymentIntervalInMonths' => 12,
						'paymentType' => 'BEZ',
						'hasReceiptEnabled' => true
					],
					'direct_debit_active_yearly_receipt_optout' => [
						'membershipFee' => 15.23,
						'membershipType' => 'active',
						'paymentIntervalInMonths' => 12,
						'paymentType' => 'BEZ',
						'hasReceiptEnabled' => false
					],
					'direct_debit_sustaining_quarterly' => [
						'membershipFee' => 15.23,
						'membershipType' => 'sustaining',
						'paymentIntervalInMonths' => 3,
						'paymentType' => 'BEZ',
						'hasReceiptEnabled' => true
					],
					'paypal_sustaining_monthly' => [
						'membershipFee' => 15.23,
						'membershipType' => 'sustaining',
						'paymentIntervalInMonths' => 1,
						'paymentType' => 'PPL',
						'hasReceiptEnabled' => true
					],
					'moderated_amount_too_high' => [
						'membershipFee' => 90000.00,
						'paymentIntervalInMonths' => 1,
						'membershipType' => 'sustaining',
						'moderationFlags' => [
							MembershipModerationIdentifier::MEMBERSHIP_FEE_TOO_HIGH->name => true
						]
					],
					'moderated_other_reason' => [
						'membershipFee' => 15.23,
						'paymentIntervalInMonths' => 1,
						'membershipType' => 'sustaining',
						'moderationFlags' => [
							MembershipModerationIdentifier::MANUALLY_FLAGGED_BY_ADMIN->name => true
						]
					]
				]
			],

			'Admin_Moderation.txt.twig' => [
				'context' => [],
				'variants' => [
					'membership' => [
						'membershipFee' => 90000.00,
						'itemType' => 'ein Mitgliedschaftsantrag',
						'focURL' => 'https://backend.wikimedia.de/backend/member/list',
						'id' => '1'
					],
					'donation' => [
						'amount' => 7777777.77,
						'itemType' => 'eine Spende',
						'focURL' => 'https://backend.wikimedia.de/backend/donation/list',
						'id' => '42'
					],
				]
			],

			'Subscription_Confirmation.txt.twig' => [
				'context' => [
					'greeting_generator' => $this->factory->getGreetingGenerator(),
					'subscription' => [
						'email' => 'test@example.com',
						'address' => [
							'lastName' => "O'Reilly",
							'salutation' => 'Herr',
							'title' => 'Dr.'
						]
					]
				]
			],

			'Subscription_Request.txt.twig' => [
				'context' => [
					'greeting_generator' => $this->factory->getGreetingGenerator(),
					'subscription' => [
						'email' => 'test@example.com',
						'confirmationCode' => '00deadbeef',
						'address' => [
							'lastName' => "O'Reilly",
							'salutation' => 'Herr',
							'title' => 'Dr.'
						]
					]
				]
			],
		];
	}
}
