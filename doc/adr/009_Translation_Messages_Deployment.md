# Deployment of Translation Messages

2019-04-26

## Status
Accepted

## Context

The "Fundraising Frontend Content" repository is a git repository where the Fundraising Department can make edits to the translated messages and texts of the Fundraising Application. Those changes get deployed automatically, independently from the code deployments.

The [`wmde19` skin](008_Client_Side_Rewrite.md) uses client-side rendering and the [Vue i18n](https://kazupon.github.io/vue-i18n/) plugin for translating messages. There are several possibilities to get the translated strings into the client-side code:

1. Importing it directly in JavaScript, with an `import` statement. This requires a continuous delivery pipeline that creates a new client-side code bundle on every content change.  
2. Loading it asynchronously when the client-side code loads. This has the benefit of working out of the box, but the drawback of an additional HTTP request.
3. Reading the file on the server side and putting its contents in a HTML [data attribute](https://developer.mozilla.org/en-US/docs/Learn/HTML/Howto/Use_data_attributes) where the bootstrap code will read it an inject it into the i18n plugin. 

## Decision
Since we don't have the engineering resources to create a continuous delivery pipeline, only options 2 and 3 remain. We choose the data attribute method for performance reasons: We want one less HTTP request and the size of the messages is acceptable: At the time of writing this ADR, it's 30K uncompressed, 7K compressed. 

We want to keep the message size down and implement the server-side code in a way that allows for splitting the messages into "common" and page-specific bundles.