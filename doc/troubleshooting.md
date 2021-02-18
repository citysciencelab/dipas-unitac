## PostgreSQL 9.6 installation trouble
### "Database cluster initialisation failed"
At some cases of postgress installation you run in the error message "Database cluster initialisation failed"

1. install the EXE of Postgres e.g. postgresql-9.6.18-1-windows.exe as local admin
  - Installation path e.g. C:\_Local_Data_unsecured\PostgreSQL (outside Win-Folder!)
  - will throw an error in the form "post-install" failed and "Database cluster initialisation failed
  - Reason: The installation process creates the "data" directory (the actual database) and tries to assign permissions to a user "postgres". But the local admin is not allowed to set the permissions. Therefore the above mentioned error is thrown.
2. as local admin in the computer administration via "local users and groups" - "users" create the user "postgres" and make him a member of administrators. Remember PW.
3. below the postgres installation is the folder "data". Add the user "postgres" to this folder under "Properties" - "Security" and give it full rights (Attention: Set path to local computer).
3a. So 4. didn't work with this either. Therefore I gave the user "postgres" the rights to the whole folder "PostgreSQL".
4. in console: "runas /user:postgres cmd" to start a console as "postgres
5. execute "initdb -D myPath\data -E UTF8". That worked then. Close the console.
6. run pg_ctl register -D mypath\data as local admin. This starts the "postgreSQL" service.
7. the user "postgres" can then have his administrator rights revoked. 
