# Database Structure for Donors

Date: 2025-03-28

Deciders: Abban Dunne, Corinna Hillebrand, Gabriel Birke, Tanuja D.

## Status

Accepted

## Context and Problem Statement

We have different donor types that are implemented as classes. They have
different properties, although some classes share properties (email address, name,
postal address). We don't have a deep hierarchy but follow the
"composition over inheritance" best practice.

We have a "scrubbing" feature that removes the personal data from donors
but preserves statistical data (contact preferences, donor type and
salutation). The "Scrubbed Donor" is its own donor type. Only a small
percentage of donors (from the last 2 days) are actual donors, the rest
are "Scrubbed Donors".

For a long time we have stored the properties of the donors in one table column,
as a PHP-serialized and base64-encoded "blob", together with other data
(tracking and payment metadata). This has many drawbacks:

- we can't query address data via SQL, neither as fields nor as selection
    criteria
- the values are not immediately visible in a database client
- it needs a "translation layer" between our domain objects (that are comprised of many
nested classes) and the database entity.
- The database table is larger than it needs to be
- Data migration needs custom PHP scripts that read and write the data blob
- The keys are in German

For a new feature (splitting street name and house number) we want to
extract the address data from the "data blob".

The Fundraising Application is the one that writes individual donation and
donor entities. The Fundraising Operation Center does more database-level
querying of the tables to summarize, analyze and export the data.

## Decision Drivers

* Performance (doing as much in SQL as possible, having as little PHP code
    as possible)
* Storage space requirements. While disk drive space is not a big issue,
    copying the data (during backups or data migration) has bothered us in
    the past.
* Effort to change both the Fundraising Application and the Fundraising
    Operation Center
* Best practices (Database Normalization)
* Enforcing data integrity and consistency ("discoverability", i.e.
    representing PHP code properties as typed columns and PHP classes as
    tables)

## Considered Options

1. A normalized table model with Doctrine Table inheritance
2. A custom relational model with self-written translation layer
3. One table for donors
4. Keeping donor data in a JSON column

## Pros and Cons of the Options

### 1. Normalized table model with Doctrine Table inheritance 

Uses [Doctrine Table Level inheritance](https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/reference/inheritance-mapping.html#class-table-inheritance). This would create 9 new tables (a base donor table, one table for each donor type plus tables for names and addresses) 

- Pro: we could ditch lots of code, (converters, translators to DB code) by
      relying on Doctrine
- Pro: address and donor name tables become really small
    - (because of the export+scrubbing happens after 2 days)  
    - Migrations of these table definitions will become very fast, which means migrations might not be so scary/cumbersome as they are now.
- Pro: The most space-efficient solution, we would not "waste" space on empty/optional fields.
- Pro: Direct representation of our domain model and database
- Pro: Doing things "by the book" - a maximum of normalization.
- Con: Lots of JOINs and more involved queries, which might be not as performant. Needs `CASE` and `COALESCE` SQL functions to pick the right fields.
  - Example 1: export, where we create 1 large table row (we have to pick the field values out of many tables)
  - Example 2: Analysis (where we analyse the salutation and donor type
      and need to pick the salutation from different tables, doing a large
      JOIN)
- Con: the scrubbing code needs to be rewritten. Replace private donor entity with a scrubbed donor entity (create new scrubbed donor, delete old private donor (orphaned)). This leads to several insert/update/delete statements and we have to check that deletions cascade correctly to avoid orphaned data.
- Con: Daily backup becomes more complex - we'll have to select the right
    tables in the right order and check if they can be restored correctly
- Con: The direct mapping between domain model and database means frequent
    database migrations whenever the domain model changes.

### 2. A custom relational model with self-written translation layer

Instead of using Doctrine Table Inheritance, we would define custom
entities that hold our data.

- Pro: Fewer tables
- Pro: more flexible control over table layout   
- Con: All drawbacks of Doctrine table inheritance solution
- Con: having to write/maintain the ORM/conversion code
- Con: Database does not enforce structure: 
  - It’s not showing well that we have anonymous / email only users  
  - Many fields would have to be nullable to be flexible for all the address types  


### 3. One Big Donor Table

- Pro: Easy to scrub with an UPDATE statement (set fields to empty and change donor type)  
- Pro: easy to query (for analytics and reports) because there is only 1 JOIN (to spenden)  
- Pro: Reusable salutation and donor type for scrubbed donors (scrubbed
    donors would get the `is_scrubbed` flag from `spenden`)
- Con: Wasted space (about 1-2 bytes per unused column, with 10 columns for name and address fields that's approx. 20 bytes per row). The space would be thoroughly wasted because 99% of our database entries are “scrubbed donors”  
- Con: donor table would have too many columns (14-15) 
- Con: We still need a PHP "Mapping Layer"  
- Con: If me make mistakes in our PHP code, database won't enforce data structure or integrity (which columns are allowed to be nullable) to help catch errors.

### 4. JSON instead of a base64 encoded blob

We keep our existing huge donations table but add one column for address
data. We define it as a JSON field.

- Pro: minimal code changes, while still getting the query and visibility
    benefits.
- Con: We still need a PHP "Mapping Layer"  
- Con: If me make mistakes in our PHP code, database won't enforce data structure or integrity (which columns are allowed to be nullable) to help catch errors.

## Decision Outcome

Most of our decision drivers point to the Doctrine Table Inheritance
solution. The only drawback is the huge effort and complexity (in terms of
multiple tables, JOINs and querying) generated by this solution,
especially for the Fundraising Operation Center. The changes in the
Fundraising Application are straightforward and manageable. If we discover
huge pain points (performance, developer experience) during the changes in
the Fundraising Operation center, we might reconsider.

