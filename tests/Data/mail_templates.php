<?php

declare( strict_types = 1 );
/**
 * This file contains a list of all Mail templates and the variables rendered in them.
 *
 * Some templates contain if statements, leading to different outputs, these outputs are covered by additional context
 * variables called "variants".
 */


$mailTemplates = [

	'Mail_Contact_Confirm_to_User.txt.twig' => [
		'context' => []
	],

	'Mail_Donation_Cancellation_Confirmation.txt.twig' => [
		'context' => [
			'greeting_generator' => $ffFactory->getGreetingGenerator(),
			'recipient' => [
				'lastName' => "O'Reilly",
				'salutation' => 'Herr',
				'title' => 'Dr.'
			],
			'donationId' => 42
		]
	],

	'Mail_Donation_Confirmation.txt.twig' => [
		'context' => [
			'donation' => [
				'id' => 42,
				'amount' => 12.34,
			],
		],
		'variants' => [
			'deposit_unmoderated_non_recurring' => [
				'needsModeration' => false,
				'paymentType' => 'UEB',
				'interval' => 0
			],
			'direct_debit_unmoderated_non_recurring' => [
				'needsModeration' => false,
				'paymentType' => 'BEZ',
				'interval' => 0
			],
			'direct_debit_unmoderated_recurring' => [
				'needsModeration' => false,
				'paymentType' => 'BEZ',
				'interval' => 3
			],
			'paypal_unmoderated_non_recurring' => [
				'needsModeration' => false,
				'paymentType' => 'PPL',
				'interval' => 0
			],
			// PPL and MCP follow the same code path for recurring, no need to test each separately
			'micropayment_unmoderated_recurring' => [
				'needsModeration' => false,
				'paymentType' => 'MCP',
				'interval' => 6
			],
			// moderated all generate the same message, no need to test different payment types
			'micropayment_moderated_recurring' => [
				'needsModeration' => true,
				'paymentType' => 'MCP',
				'interval' => 6
			]
		],
	],

	'Mail_Membership_Application_Cancellation_Confirmation.txt.twig' => [
		'context' => [
			'greeting_generator' => $ffFactory->getGreetingGenerator(),
			'membershipApplicant' => [
				'lastName' => "O'Reilly",
				'salutation' => 'Herr',
				'title' => 'Dr.'
			],
			'applicationId' => 23
		]
	],

	'Mail_Membership_Application_Confirmation.txt.twig' => [
		'context' => [
			'greeting_generator' => $ffFactory->getGreetingGenerator(),
			'lastName' => "O'Reilly",
			'salutation' => 'Herr',
			'title' => 'Dr.',
			'applicationId' => 23
		],
		'variants' => [
			'active_yearly' => [
				'membershipType' => 'active',
				'paymentIntervalInMonths' => 12,
			],
			'sustaining_quarterly' => [
				'membershipType' => 'sustaining',
				'paymentIntervalInMonths' => 4,
			]
		]
	],

	'Mail_Subscription_Confirmation.txt.twig' => [
		'context' => [
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

	'Mail_Subscription_Request.txt.twig' => [
		'context' => [
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

return $mailTemplates;
