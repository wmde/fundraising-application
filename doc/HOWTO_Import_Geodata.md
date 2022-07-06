# How to Import the Third Party Geodata

The Geodata zip contains 2 sql files, a small one with community names, and a large one which includes streets.

1. Copy the smaller sql file into /.docker/database.
2. Rename it to `00.Geodata.sql`. This is needed because Docker runs the files in order, and the Geodata import needs to run before the Schema.
3. Remove the database volume with the command:
   
   ```$ docker volume rm APP_DIRECTORY_db-storage```
   
   APP_DIRECTORY is the directory name where you have checked out the `fundraising-application` code. You can check for the volume name by listing all docker volumes with the command `$ docker volume ls`.
4. Run `$ docker-compose up`. When importing the Geotata this might take several minutes as it imports.