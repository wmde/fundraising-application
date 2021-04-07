# Data model for recurring payments

For all payment channels except PayPal our data warehousing and CRM
provider handles the payments. We don't update our data when they process
payments.

For recurring donations paid with PayPal, we're getting a subscription
notification from PayPal with the original donation id. We handle that
notification by creating a new donation that we export to the CRM provider.
The code for handling PayPal notifications clones the original donation
record and writes a new one with a new primary key (donation id) but
otherwise identical data. It uses the `data` blob field to write to the
`log` array field in both the parent and the child donation to record the
"relationship" between the records.  This is just for safekeeping and in
case we need it, we're not processing the log entries. Our export script
in the [Fundraising Operation
Center](https://github.com/wmde/fundraising-backend) will then export the
"new" donation to the CRM provider.

See
`HandlePayPalPaymentCompletionNotificationUseCase::createChildDonation`
in the [fundraising-donations bounded context](https://github.com/wmde/fundraising-donations).
