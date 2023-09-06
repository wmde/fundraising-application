INSERT INTO last_generated_payment_id (payment_id) VALUES (0);
INSERT INTO last_generated_donation_id (donation_id) VALUES (0);
INSERT INTO last_generated_membership_id (membership_id) VALUES (0);

-- Only needed until https://phabricator.wikimedia.org/T270721 is done
INSERT INTO incentive (`id`, `name`) VALUES (1, 'tote_bag');
