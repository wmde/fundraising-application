INSERT INTO last_generated_payment_id (payment_id) VALUES (0);
INSERT INTO last_generated_donation_id (donation_id) VALUES (0);
INSERT INTO last_generated_membership_id (membership_id) VALUES (0);

-- Only needed until https://phabricator.wikimedia.org/T270721 is done
INSERT INTO incentive (`id`, `name`) VALUES (1, 'tote_bag');

INSERT INTO membership_fee_changes (
	uuid,
	member_name,
	external_member_id,
	current_amount_in_cents,
	suggested_amount_in_cents,
	current_interval,
	state,
	export_date
) VALUES (
	'a37ba58d-6049-443e-b8e7-1a0a2a502b95',
	'',
	987654321,
	500,
	1000,
	3,
	'NEW',
	null
);

INSERT INTO membership_fee_changes (
	uuid,
	member_name,
	external_member_id,
	current_amount_in_cents,
	suggested_amount_in_cents,
	current_interval,
	state,
	export_date
) VALUES (
	 '543a0eb1-9981-4d00-b50b-96ff4e35be9a',
  '',
	 98765432,
	 1000,
	 2000,
	 12,
	 'FILLED',
	 null
 );

INSERT INTO membership_fee_changes (
	uuid,
	member_name,
	external_member_id,
	current_amount_in_cents,
	suggested_amount_in_cents,
	current_interval,
	state,
	export_date
) VALUES (
	 'b6459bfb-24b9-4d29-9aa9-70e0ff6b91bc',
	 '',
	 9876543,
	 500,
	 1000,
	 6,
	 'EXPORTED',
	 '2025-04-03 1:02:00'
 );