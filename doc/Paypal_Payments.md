# Documentation of PayPal payments

## Steps of a successful donation payment

1. The user submits the donation form
2. The Fundraising Application stores donation data in the database,
   generating an ID for the donation. The payment state of the donation is
   "incomplete"
3. The Fundraising Application redirects the user to PayPal page, with the
   donation ID, access token, confirmation and notification routes (see
   below) as extra URL parameters
4. The User submits the PayPal form
5. PayPal redirects the user to success page of the Fundraising
   Application (passed to PayPal in step 3), passing back the donation ID
   and access token as URL parameters
6. PayPal sends a separate HTTP request called **"IPN"** ([Instant Payment
   Notification][1]
   to the "PayPal Notification" route (passed to PayPal in step 3) of the
   Fundraising Application. The HTTP request contains payment metadata
   (amount, currency, date, account information) and the donation ID.
7. The Fundraising Application sends the received data back to PayPal to
   verify that it originated from PayPal.
8. The Fundraising Application validates the data and marks the payment
   state donation as "booked".


## Metadata for PayPal

When redirecting the user to PayPal, we need to specify the following
data:

* Amount
* Currency (Euro)
* Payment type (one-time payment or subscription)
* Payment description (e.g. "Ihre Spende für Wikimedia")
* Receiver ID (email address of WMDE account, `account-address` in our
  configuration)
* Donation ID (The [IPN processing][1] will later use the donation ID to
	associate a PayPal notification to a donation).
* Access and update tokens for the donation (access token for the user to
	access the confirmation page after successful payment, update token
	for PayPal as a small protection against unauthorized access from
	stray PayPal processes).
* Return URL (confirmation page)
* Notification URL (where to send the [IPN][1])
* Cancel URL (Route to load when the user hits "Cancel")

The class
`WMDE\Fundraising\PaymentContext\Domain\PaymentUrlGenerator\PayPal`
creates the URL parameters, getting its configuration from the
`PayPalConfig` class, which gets initialized with parameters from the
application configuration file (section `paypal-donation`).

When the URL for receiving notifications changes, we need to adapt it in
the configuration file.

## IPN Processing

PayPal sends *all* notifications (IPNs ([Instant Payment Notification][1])
to the notification route, but we only process two types of messages:

* Payment notifications (for one-time donations)
* Subscription payment notifications (for recurring donations)

We write all other messages (expired credit cards, refunds, subscription
changes, etc) to the file `paypal.log`.

It's [important that the URL end point returns a "200 OK" HTTP status
code][2] and no content. Otherwise the PayPal will retry sending the
message up to 15 times, in increasing intervals. When too many
notifications have failed, PayPal will stop sending notifications and
the account owner of the receiver ID (at WMDE, that's a person from the
finance team) will have to turn the notifications on again.

Before marking a donation as "booked" in the database, we validate the
incoming request:

* We can process the message type (see above)
* We can verify that the message came from PayPal (by sending it back to a
	special HTTP API endpoint on the PayPal server)
* We find a donation with matching ID and security token in the database
* The found donation was not booked before

### IPN History

PayPal stores a history of IPNs that were sent to us in the last 28 days.
It provides detailed information about single IPNs including transmission
meta data and the message body, which can be used to troubleshoot failed
notifications.

The IPNs can also be triggered to be sent again, either single
notifications or in bulk. The notifications will not be sent immediately
but added to PayPal's notification queue. It may take some minutes
for the notifications to hit our servers.

The [IPN History][4] page can only be accessed when logged in using the
receiver e-mail address of the respective account. The Head of Team
Finance and the Head of Team Spenden und Mitglieder both have access to
that account and need to be contacted in order to get information from
the IPN history.

Apart from a date, the only available filters are the transaction ID and
the delivery status of IPNs. PayPal has a more detailed [documentation][5]
on how to use it.


### Processing PayPal-only donations

People can donate by "sending" money through the PayPal web site or app
directly to a receiver email address (`account-address` in our
configuration),skipping the donation form that would create a donation
entry in the database. Our IPN notification URL can process these
notifications and creates anonymous donations for them.

To be able to receive these donations, the account holder of the receiver
ID needs to configure a default notification URL in the administrative
settings for the PayPal account. When the URL for receiving notifications
changes, we need to adapt it in the configuration file.


## Recurring donations

When a user selects a recurring donation interval in the donation form, we
request a "subscription" with PayPal. We only process the payment
notifications, and ignore all other changes (people canceling their
subscriptions, changing their address, changing their credit card data,
etc).

For all payment channels *except* PayPal our accounting department
and/or our data warehousing and CRM provider handles recurring payments.
We don't update our donation or payment data when they process payments.

For recurring donations paid with PayPal, we're getting a subscription
notification from PayPal with the original donation id. We handle that
notification by creating a new donation that we export to the CRM provider.
The code for handling PayPal notifications clones the original donation
record and writes a new one with a new primary key (donation id) but
otherwise identical data. It uses the `data` blob field to write to the
`log` array field in both the parent and the child donation to record the
"relationship" between the records. This is just for safekeeping and in
case we need it, we're not processing the log entries. Our export script
in the [Fundraising Operation
Center](https://github.com/wmde/fundraising-backend) will then export the
"new" donation to the CRM provider.

See
`HandlePayPalPaymentCompletionNotificationUseCase::createChildDonation`
in the [fundraising-donations bounded context](https://github.com/wmde/fundraising-donations).

For *all* past subscriptions PayPal will send the notifications to the URL
that we specified in the original donation. It's important
that we support old notification URLs indefinitely, otherwise
subscription notifications for old donations will fail and eventually we
won't get notified of any payments!

## PayPal for memberships

We have implemented PayPal as a [payment type for memberships][3], but
never made it available to new membership applicants, because after we
implemented the feature, we discovered a user experience issue: When
sending the membership form in the Fundraising Application, this is an
*application for membership* - the actual membership must be approved by
the directorate. The bulk approval happens in January, while the
membership applications come in during the donation campaign in November
and December. This means we need a "start date" for the subscription
payments, which PayPal did not support at the time (2016/2017). The best
way we could implement the delay, was using the "free trial period" of the
PayPal subscription feature. But because the wording and explanations
around this "free trial period" were too awkward, we never showed the
payment option to users.

Instead of implementing one unified IPN notification URL for both
memberships and donations, we have duplicated the payment receiver code
for memberships and send different notification and confirmation end
points when redirecting the user to PayPal.


[1]: https://developer.paypal.com/docs/api-basics/notifications/ipn/
[2]: https://developer.paypal.com/docs/api-basics/notifications/ipn/IPNIntro/#ipn-protocol-and-architecture
[3]: https://phabricator.wikimedia.org/T147400
[4]: https://www.paypal.com/ie/cgi-bin/webscr?cmd=_display-ipns-history&nav=0.3.2
[5]: https://developer.paypal.com/docs/api-basics/notifications/ipn/IPNOperations/

