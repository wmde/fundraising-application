# PayPal IPN Queue

Date: 2021-06-14

Deciders: Kai Nissen, Gabriel Birke, Corinna Hillebrandt, Abban Dunne, Conny Kawohl

Technical Story: https://phabricator.wikimedia.org/T284893

## Status

Declined

## Context and Problem Statement

The PayPal IPNs on Fundraising Frontend started to fail after a deployment 
and was only noticed some days after when the Project Manager needed to 
export the data. Upon investigation, it was discovered that:

* Error logging was inactive on the application. (Now fixed)
* We can’t debug using the responses our system returned to PayPal as 
  we don’t have access to the IPN log.

This led to a situation where we couldn't get the information required 
to debug the error. It was suggested we queue all incoming requests from 
PayPal on our own system for processing by our system.

## Decision Drivers

* **Transparency**: If our system fails we would have a stored queue to 
  use for debugging.
* **Automation**: The IPNs wouldn't need to be fired again once an error 
  becomes fixed as our system would resume processing the queue.

## Pros and Cons

* Good, because debugging would be easier - we could use existing data 
  and run it against our code without involvement from PayPal.
* Good, because our system would resume processing automatically.
* Good, because there is less logic that can go wrong, which means PayPal 
  server errors. When PayPal receives too many server errors,
  it will stop sending IPNs.
* Bad, another system to support.
* Bad, because we send back each IPN to PayPal to verify that it's genuine.
  It's unclear how PayPal would behave if there is a time delay or
  a duplicate verification (when re-processing old items).
* Bad, there will be a lag between a donation and a confirmation email.
* Bad, if something goes silently wrong while processing the queue, then PayPal
  won’t have a log of failed IPNs, because to them it looks as if their 
  notification was successful.
* Bad, our current method of handling IPNs is robust and this has the 
  chance to introduce brittleness.
* Bad, more complexity.

## Decision Outcome

Since this was the first occurrence of the problem, and the Fundraising 
Application system is now running well again, we decided against introducing 
the IPN queue feature.
