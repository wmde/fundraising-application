# How the database setup and migration works

The Fundraising Application has two different database environments:

- The **local development environment**. We're running a MariaDB database in a Docker container for local user and acceptance testing. We are using an in-memory SQLite database for running unit tests. This makes running the tests fast and allows for a clear separation between interactive testing and automated testing. 
- The **server environment** has a dedicated database server with two databases (test and production) that our test and production web servers connect to. The Fundraising App and the Fundraising Operation Center both share the same production or testing database.

The two environments have different strategies for initial setup and migration of existing database structure and data:

- The local environment is a "throwaway" environment - When one developer on the team changes the database structure, they notify the rest of the team that they should throw away their docker container and docker volumes and restart the development environment. The mechanism we use is putting **SQL files** with structure and data into the directory `.docker/database/`. The `docker-compose.yml` file defines a database volume that we can throw away. When the docker container starts and has no volume from previous runs, MariaDB will execute the files (in alphabetical order).
- The server environment needs to preserve the existing data (all donations since 2011, etc.), so we can't throw the database away when we want to make changes to the structure. Therefore we use **Database Migration Scripts** that modify the database structure (and copy or modify the existing data if it needs adaptation to the new structure).


## How to regenerate the local environment

Run the command `docker compose down -v`. This will stop all containers and will also clear out their data volumes. 

Afterwards, run `docker compose up -d` to start the environment again.

### Troubleshooting

If you're getting errors from the application after the containers are up, check if the database tables exist by running the following command:

```shell
docker compose exec database mysql -u fundraising -p"INSECURE PASSWORD" fundraising -e "SHOW TABLES"
```

If there are no tables, run `docker compose logs database` to see if there are any errors.


## How to integrate a database change in the local environment

To integrate a database change from a bounded context you need to do the following steps:

1. Create an integration branch in in the application repository, pointing the version of the bounded context in `composer.json` to the `-dev` branch of the bounded context.
2. Run CI and fix failing tests.
3. Run the database migration on the *old* version of the database schema
4. User-test the changed database (to check if the migrated database is correct)
5. Re-generate SQL for the local environment.
4. User-test the changed database (again, this time for the auto-generated local environment)
6. Create a new minor/major release of the bounded context and rebase/change your integration branch, pointing the version of the bounded context in `composer.json` to the new release.

**Important:** You have to do the steps 3 and 4 exactly in this order, because the migration won't work on the new schema!

### Testing the migration

When checking out a branch (or creating a new integration branch for updating a version from a bounded context), your local environment Docker container should still have the old version of the database. If you want to be extra sure to "reset" your local container, do the following steps:

1. Check out the `main` branch
2. Follow the steps from the "How to regenerate the local environment" section of this document.
3. Check out your branch again.

To check if the applications "sees" the new migration, run the following command:

```shell
docker compose exec app php bin/doctrine migrations:list
```

You should see a list of migrations, none them applied. In the local environment this is fine, because we throw away the Doctrine migration metadata table whenever we regenerate the local environment. If the migration does not show up, check the migrations directory in `vendor/wmde/your-bounded-context` and the configuration file `app/config/migrations.php`.

To test / run the new migration, run the following command:

```shell
docker-compose exec app vendor/bin/doctrine-migrations migrations:migrations:execute 'Your\\Migration\\Name'
```

Replace the placeholder `Your\\Migration\\Name` with the namespace name of the actual migration, e.g. `'WMDE\\Fundraising\\MembershipContext\\DataAccess\\Migrations\\Version20240528122513'`. 

**Important:** The `migrations:list` command lists the migrations with single backslashes! If you copy-paste the migration name form the list or the code, remember to use double backslashes! Otherwise your command line shell will interpret the combination of backslash and the following character as a Characters and the resulting name passed to PHP will not contain any backslashes which will make the command fail.

After running the migration you can user-test the application (to see if the changed database works with the code) and inspect the changed database in the IDE or CLI.

If the migration fails for syntactic reasons (e.g. wrong column or table names) or semantic reasons (code does not work with changed schema) you need to change the migration in the branch of the bounded context and run `make update-php` in the integration branch of the application.

### Regenerating the local SQL

Run the following command:

```shell
make generate-database-schema
```

This will regenerate the file `.docker/database/01.Database_Schema.sql`. Check the file to make sure it only contains SQL commands and no PHP error messages/warnings.

Follow the steps from the "How to regenerate the local environment" section of this document. Check that the database tables are still there in the container.

User-test the application in your local environment. If  the application does not work correctly, you'll have to edit the code in the branch of the bounded context.

## How to migrate the database on the server

See our [infrastructure documentation how to deploy a new version of the Fundraising application that needs migrations](https://github.com/wmde/fundraising-infrastructure/blob/main/docs/deployment/Fundraising_Application.md#deploying-a-release-that-needs-database-changes)

