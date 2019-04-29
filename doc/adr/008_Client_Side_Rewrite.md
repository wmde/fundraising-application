# Doing a Rewrite of the Client-Side Code

Date: 2019-04-25

## Status

Accepted

## Context

The mobile-friendly, more aesthetically pleasing design of the software (called `cat17`) did not gather as much donations as the "old" design, called `10h16`. UX engineering, product management and stakeholders from the fundraising department decided against going "back" to the mobile-unfriendly and hard-to-maintain `10h16` design and instead improving the existing `cat17` design by applying UX best practices and making educated guesses at what made `10h16` so attractive to donors.

The engineering team, planning the modernization of the frontend code base as outlined in "[ADR 006 - Using Vue.js](006_Vue.js.md) and "[ADR 007 - Using Typescript](007_Typescript.md) now had to choose between an *evolution* of `cat17` and a *rewrite* of the client-side code. The benefits of one approach would be the drawbacks of the other, so the following sections will describe each approach through the lens of benefits.

### Benefits of an evolution of `cat17`

By applying small, incremental changes to the existing design, we are following good agile practices - always delivering value, always being able to stop what we're doing and always having a running application.  

We don't risk breaking something that already works (ui/ux wise). [A rewrite always carries the risk of throwing away past bugfixes, "microfeatures", lessons learnt and implicit organizational knowledge](https://www.joelonsoftware.com/2000/04/06/things-you-should-never-do-part-i/).

We can improve the skin "step by step" so to speak. First implement the Vue components, then get rid of Bootstrap 3 and replace it with Bulma possibly, then transform our CSS structure into a more meaningful one.

This approach doesn't stop us from developing new features if need be. If we have more than 1 or 2 skins, the cost of UI changes due to new features becomes prohibitive.

We have already invested effort in experiments (i.e. the BankData component in `cat17`) that show the evolution from the old code base to Vue components.    

We are avoiding the temptation to test 3 different designs (`10h16`, `cat17` and `new`) against each other. From a UX perspective, testing 3 designs does not make sense, however experience shows that if the option is there, it might be done.


### Benefits of a rewrite

The new design ditches some behaviors and styles of `cat17`. A rewrite avoids having to re-implement what we might later throw away.

The Javascript architecture of `cat17` makes it hard to find the right place for improvements. Rewriting with Vue.js and Typescript makes sure all developers have the same knowledge about the code base and can look up patterns and examples in the official documentation. See "[ADR 006 - Using Vue.js](006_Vue.js.md) and "[ADR 007 - Using Typescript](007_Typescript.md).

The CSS organization of `cat17` does not contain enough abstractions, the CSS is very tailored to each individual page. A rewrite allows us to discover the inherent components and their hierarchy, avoiding duplication and creating a consistent design language.

A rewrite that does not have to integrate with existing client-side code can be done faster than transitioning to Vue.js through incremental refactorings. A transition would require additional compatibility layers in the architecture during the transition phase. Those layers would affect the performance and make the code base even harder to understand. When the transition is finished, the compatibility layers would have to be removed, making the effort spent on them "wasted". The compatibility layers would also affect the overall architecture: They could improve it by making everything decoupled and testable, on the other hand could make it "non-standard" and avoid the easy integration of data model and view thet VueX and Vue offer.  

The build and dev system are easier to set up: We want to use [`vue-cli`](https://cli.vuejs.org/) which generates useful and usable webpack configurations for development and testing.

### General risks

Regardless of the approach (evolution or rewrite), it could happen that the new design shows no improvement in donations. The logical consequence would be to stay with 10h16 or to reimplement 10h16 with the new technology. This is the worst-case scenario and we can not fully mitigate the risk. The risk has two factors: The new technology negatively affecting the user experience and the user experience itself. Both factors have mitigation strategies.

## Decision

Given the benefits of knowledge sharing and the assumptions about a greater velocity, we decide to do a rewrite of the client-side code.

To avoid the technology (especially client-side rendering) affecting the user experience we will measure the functionality and performance of the new design throughout the transition. The best performance metrics of both `10h16` and `cat17` are the baseline for the performance budget of the new design.

When the design has feature-parity with `cat17` and has been successfully deployed to production, we will delete `cat17`.

When the new design has proven successful (i.e. the donation rate is at least not worse than with `10h16`), we will remove `10h16`.

## Consequences

During the transition period, we will have three designs instead of two. This blocks us from implementing any new features that would require changes in client-side code, because they would have to be done three times, with different approaches.

When `10h16` and `cat17` are removed, the build and speed will increase and we will have less security warnings caused by outdated packages in GitHub.