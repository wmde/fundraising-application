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
```
AddDonationController.php
ApplyForMembershipController.php
``` 
Payment data is used for instantiating subclasses of redirects/presenters

**Suggestion:**: Move creation of the response into a unit-tested [factory](https://en.wikipedia.org/wiki/Factory_method_pattern). The factory should return a class that implements a common interface, e.g. `Symfony\Component\HttpFoundation\Response` 

## Data access layer
```DoctrineDonationRepository.php
DoctrineMembershipRepository.php
```
Converting our Domain entities into ORM entities and back

**Suggestion:** Introduce a separate payment data repository, that uses donation/membership table, then refactor to separate table.

## Domain
### cancel Donation
`Donation.php`
Fundraising Operation Center, see [T221841](https://phabricator.wikimedia.org/T221841)

Only direct debit can be canceled/deleted

**Suggestion:** Add `cancel` method to PaymentMethod that behaves accordingly (e.g. throws exception or returns failure object).

### Check if Payment has external provider
`Donation.php`, hasExternalPayment

**Suggestion:** Move to PaymentMethod interface

### Payment provider notifications
```HandlePayPalPaymentCompletionNotificationUseCase.php
SofortPaymentNotificationUseCase.php
CreditCardNotificationUseCase.php
```

* Checks if the donation/membership payment type matches the payment type of the notification
* Add transaction data to donation payment data
* Check PayPal transactions if they were booked before (for recurring payments)

**Suggestions:**
* Use payment data repository instead of having methods on Donation for manipulating payment data (one method per payment data type, which is a code smell)
* The donation/membership domains should be completely separate from the payment domain. The payment domain should implement a `ConfirmPayment` use case for receiving confirmation requests from the payment provider, with donations and memberships implementing `ReceivePayment` use cases that they can use to update themselves with the DTO returned from the ConfirmPayment use case.
* Introduce the concept of "Followup Donations" (currently known as "child payments"). Check the log of processed transaction IDs to avoid duplicate payments. 

### Add Donation use case
```AddDonationUseCase.php```

Construct minimal payment method data from request (only bank data has real data)

**Suggestion:** Create an `AddPayment` use case in Payment domain, that constructs the PaymentMethod (and saves it if needed).

### Iban should be a valid domain object
The `Iban` class should not allow for malformed IBANs (not starting with 2 letters, shorter than 12 chars) to perform its duties.
It should throw `UnexpectedValueException` when it encounters an invalid string. 
Code using the domain object should pre-check for malformed IBANs and validate accordingly (avoiding the `UnexpectedValueException`).
The `IbanValidator` (and its kontocheck implementation) should validate the *Domain* validity of the IBAN object (i.e. country prefix, bank or account does not exist).

 
