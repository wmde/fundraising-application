# Authentication of the confirmation page in external payments

## Problem
The payment bounded context needs to send a "Return URL" to the external payment provider (e.g. PayPal) that the provider
will redirect the user to when the user has completed the payment on the provider's site. The return URL needs to contain
a token to protect the user from "URL hacking" (trying out IDs) attacks. But the token itself is not part of the
donation, membership or payment domain, it's the responsibility of the application. So the payment bounded context needs a way to
attach the token to the return URL, without knowing what will be attached or where the token will come from.

We want to change the authentication mechanism in the future, moving from a combination of donation/membership ID and 
two tokens (update and access token) to a single token.

## Architecture description

This is a high-level description of the steps the application and different bounded contexts interact, using donations 
as an example. The membership bounded context works in the same way.

1. The application implements the `DonationAuthorizer` interface (with the `PersistentAuthorizer` class). 
   The class has two dependencies:
   - a `TokenRepository` implementation for storage of the association between donation/membership and tokens
   - a `TokenGenerator` implementation to generate random tokens
2. The application initializes the `AddDonationUseCase` with its `DonationAuthorizer` implementation.
3. The `AddDonationUseCase` calls the `authorizeDonationAccess` method on the `DonationAuthorizer` with the donation ID.
   This returns an `URLAuthenticator` instance. `URLAuthenticator` is an interface from the payment bounded context that
   can append authentication information to URLs or parameter collections.
4. When the `AddDonationUseCase` calls the `authorizeDonationAccess` method, the `PersistentAuthorizer` generates a
   random token and stores it in the `TokenRepository` with the donation ID. Its returned `URLAuthenticator` instance
   contains the generated tokens and donation ID. 
5. The `AddDonationUseCase` passes the `URLAuthenticator` instance to `CreatePaymentUseCase` in the payment bounded context.
6. The `CreatePaymentUseCase` gets a `URLGenerator` from the `URLGeneratorFactory`, passing it the `URLAuthenticator`
   instance. The `URLGeneratorFactory` is a factory that creates `URLGenerator` instances for different payment providers.
   The `URLGenerator` is an interface that generates a URL *to the payment provider*. Sending an authenticated return URL
   to the payment provider is the responsibility of the `URLGenerator` implementation.
7. The `CreatePaymentUseCase` returns the generated URL to the `AddDonationUseCase`, which passes it back to the application,
   which redirects the user to the URL. The user completes the payment on the payment provider's site. Bank Transfer and
   Direct Debit payments don't redirect the user to the payment provider's site. Instead, the application uses the 
   `AuthenticationLoader` to append the authentication information to the URL generated inside the `AddDonationController`.
