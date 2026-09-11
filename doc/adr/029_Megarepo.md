# Combining our Bounded Contexts into one Megarepo

Date: 2026-09-11

Deciders: Corinna Hillebrand, Tanuja D., Lucas Werkmeister, Abban Dunne

Technical Story: Merging our 6 bounded contexts into a single repository

## Status

[Proposed | Rejected | Accepted | Deprecated | … | Superseded by [ADR-0005](0005-example.md)] <!-- optional -->

## Context and Problem Statement

Our bounded contexts (domain logic) is distributed in a lot of separate git repositories. These consist of:

- https://github.com/wmde/fun-validators
- https://github.com/wmde/fundraising-address-change (Deprecated)
- https://github.com/wmde/fundraising-donations
- https://github.com/wmde/fundraising-memberships
- https://github.com/wmde/fundraising-payments
- https://github.com/wmde/fundraising-subscriptions

## Decision Drivers

* Having the code split across 6 projects makes maintenance more difficult.
    * Code navigation is harder when a person needs to have multiple projects open
    * Version management is hard when dependencies depened on other dependencies

## Considered Options

* A - Merge the 6 bounded context repos into a **single repository**
* B - Maintain the status quo of the 6 **separate repositories**

## Pros and Cons of the Options


### Option A

#### Positive Consequences

1. Simplified dependency management
    - Backwards breaking changes from Dependabot can be done in a single PR
2. Easier code navigation
    - It's easier to see code interaction when you don't need to have multiple projects open as IDE tools work better.
3. No more chains of versioning
    - Direct and indirect dependency releases are no longer necessary (e.g. payment BC)
4. Easier reviews when a feature crosses multiple contexts
    - No need to look at multiple PRs to get context for changes across the stack
5. Fewer PRs

#### Negative Consequences

1. Pull requests will become bigger (e.g. stan updates touching more files)
    - Fixing backwards breaking changes from Dependabot will be bigger tasks
    - More mental load on one task
2. Domains might start leaking into each other (separation no longer enforced by Git)
    - Will need to be enforced by other tooling and review processes instead
4. CI will take longer (more tests to run, files to lint etc.)


### Option B

- = status quo / "Option A inversed"

- - - 

## Decision Outcome
1. The change needs to be reversible
    - the new merged commit history needs to stay re-seperatable
        - we can use git filter-repo for that

2. We consider the new solution (Option A) safe enough to try in a 2 months probation period in 2027  
   a. everybody in the team saw the positive points to outweigh the negatives
