# Database index naming convention

Date: 2024-06-04

Deciders: Abban Dunne, Corinna Hillebrand, Gabriel Birke, Tanuja D.


## Status

Accepted

## Context

When defining database tables, we also need to define indexes for these
tables. We want these indexes to have distinct names that hint at the
columns contained in the index.

## Decision

1. The naming schema for all new database indexes will be `idx_[table_prefix]_[column_description]`.
2. We leave the index name generation to our database library (Doctrine) for primary keys, foreign keys and unique indexes.
3. We leave the current index names which don't follow this conventions
   as-is.

We want to start all indexes with the `idx_` prefix to distinguish them
from column names. The distinction is useful for us as developers, not for
the database itself, because to our knowledge most DBMS could have the
same name for column and index. 

The `[table_prefix]` is a one- or two-character prefix for the table. This
helps us to distinguish indexes for the same column name in different
tables. We *might* use an additional index type prefix in the `table_prefix`, e.g.
`ft_` for full-text indexes, but currently we don't have index types.

`[column_description]` usually is the name of the column. In cases of
multi-column indexes, try describe the purpose of the index semantically.

Example index names:

- `idx_m_first_name` - first name in the "memberships" table
- `idx_ac_street` - street address column in the "address change" table

