<?php

declare( strict_types = 1 );

/**
 * This file contains a list of all Mail templates and the variables rendered in them.
 *
 * Some templates contain if statements, leading to different permutations of output, which are rendered individually.
 * These outputs are covered by the "variants", which are automatically recursively merged into the main "context".
 */
return [

	'Mail_Contact_Confirm_to_User.txt.twig' => [
		'context' => []
	],

	'Mail_Contact_Forward_to_Operator.txt.twig' => [
		'context' => [
			'firstName' => 'John',
			'lastName' => 'Doe',
			'emailAddress' => 'j.doe808@example.com',
			'subject' => 'Missing Link',
			'message' => 'Please advise',
		],
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
			'greeting_generator' => $ffFactory->getGreetingGenerator(),
			'donation' => [
				'id' => 42,
				'amount' => 12.34,
				'needsModeration' => false,
			],
			'recipient' => [
				'lastName' => '姜',
				'salutation' => 'Frau',
				'title' => ''
			],
		],
		'variants' => [
			'deposit_unmoderated_non_recurring' => [
				'donation' => [
					'paymentType' => 'UEB',
					'interval' => 0,
					'bankTransferCode' => 'WZF3984Y',
				]
			],
			'direct_debit_unmoderated_non_recurring' => [
				'donation' => [
					'paymentType' => 'BEZ',
					'interval' => 0,
				]
			],
			'direct_debit_unmoderated_recurring' => [
				'donation' => [
					'paymentType' => 'BEZ',
					'interval' => 3,
				]
			],
			'paypal_unmoderated_non_recurring' => [
				'donation' => [
					'paymentType' => 'PPL',
					'interval' => 0,
				]
			],
			// PPL and MCP follow the same code path for recurring, no need to test each separately
			'micropayment_unmoderated_recurring' => [
				'donation' => [
					'paymentType' => 'MCP',
					'interval' => 6,
				]
			],
			// moderated all generate the same message, no need to test different payment types
			'micropayment_moderated_recurring' => [
				'donation' => [
					'needsModeration' => true,
					'paymentType' => 'MCP',
					'interval' => 6
				],
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
			'applicationId' => 23,
			'membershipFee' => 15.23,
		],
		'variants' => [
			'active_yearly' => [
				'membershipType' => 'active',
				'paymentIntervalInMonths' => 12,
			],
			'sustaining_quarterly' => [
				'membershipType' => 'sustaining',
				'paymentIntervalInMonths' => 3,
			]
		]
	],

	'Mail_Subscription_Confirmation.txt.twig' => [
		'context' => [
			'greeting_generator' => $ffFactory->getGreetingGenerator(),
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
			'greeting_generator' => $ffFactory->getGreetingGenerator(),
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
