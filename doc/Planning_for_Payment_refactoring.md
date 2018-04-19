# Planning for refactoring in Payment domain

This document is for identifying points where payment data objects are used and how they can be developed into a more expressive and decoupled Payment domain.

Current state: Gabriel and Tim looked into Frontend and Donation Domain code and made suggestions

Next steps: 
* Look at membership 
* present to whole team, create tickets

Phabricator: https://phabricator.wikimedia.org/T192323

## Presentation layer
### Template data
```
DonationMembershipApplicationAdapter.php
DonationConfirmationHtmlPresenter.php
DonationConfirmationMailer.php
```

Payment data is extracted as array, depending on payment type

**Suggestion:** Add `getData` or `asArray` to `PaymentMethod` interface

### Redirect/display template based on payment type
```AddDonationHandler.php
ApplyForMembershipHandler.php
``` 
Payment data is used for instantiating subclasses of redirects/presenters


## Data access layer
```DoctrineDonationRepository.php
DoctrineApplicationRepository.php
```
Converting our Domain entities into ORM entities and back

**Suggestion:** Introduce separate payment data repository, that uses donation/membership table first, then refactor to separate table.

## Domain
### cancel Donation
`Donation.php`

Only direct debit can be canceled

**Suggestion:** Add cancel method to PaymentMethod that behaves accordingly

### Check if Payment has external provider
`Donation.php`, hasExternalPayment

**Suggestion:** Move to PaymentMethod interface

### Payment provider notifications
```HandlePayPalPaymentCompletionNotificationUseCase.php
SofortPaymentNotificationUseCase.php
CreditCardNotificationUseCase.php
```

* Checks if donation has the right payment type
* Add transaction data to donation payment data
* Check paypal transactions if they were booked before (for recurring payments)

**Suggestions:**
* Use payment data repository instead of having methods on Donation for manipulating payment data (one method per payment data type, which is a code smell)
* Add 'markAsPaid' method to `PaymentMethod` interface that adds the necessary payment data. This method has `object` as input type and individual `PaymentMethod` implementations have to check the input (requires PHP 7.2). This could help us to have one generic use case for accepting external payments.

### Add Donation use case
```AddDonationUseCase.php```

Construct minimal payment method data from request (only bank data has real data)

**Suggestion:** Move construction to factory in Payment domain


