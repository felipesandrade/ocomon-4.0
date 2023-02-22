# OcoMon - version 4.x
## Date: 2021, October
## Author: Flávio Ribeiro (flaviorib@gmail.com)

## License: GPLv3


## IMPORTANT:


If you want to install OcoMon on your own, you need to know what a WEB server is and be familiar with the generic process of installing WEB systems.

To install OcoMon it is necessary to have an account with permission to create databases on MySQL or MariaDB and write access to the public folder of your web server.

Before starting the installation or update process, **read this file to the end**. 


## REQUIREMENTS:


+ Web-server with Apache (***not tested in others servers***) + PHP + MySQL (or MariaDB):
    
    - PHP at least from version **7.4** with:
        - PDO
        - pdo_mysql
        - mbstring
        - openssl
        - imap
        - curl
        - iconv
        - gd
        - ldap
    
    - MySQL at least version 5.6 or MariaDB(at least version 10.2):

<br>

## INSTALLATION OR UPDATE IN A PRODUCTION ENVIRONMENT: 


### IMPORTANT (in case of update)

+ It is strongly recommended back up your database! Do this first and avoid any headaches.

+ Identify which is **your installed version**. After that, go straight to the corresponding section to update your specific version. For each base version there are **only ONE specific file** (or none) to be imported into your database. 

+ Check out the news of the version in [https://ocomonphp.sourceforge.io/changelog-incremental/](https://ocomonphp.sourceforge.io/changelog-incremental/) To identify new possibilities for use and new settings.


### Update:


### If your current version is the 4.0RC1

In this case, no database action is required. To update simply overwrite the OCOMON application scripts. Done!



#### If your current version is the 4.0Beta1:

Some users had access to the preview of version 4 before the official release. If this is your case:

1. Import the database update file "DB_UPDATE_FROM_BETA1.sql" (install/4.x/): <br>

        Terminal command line:
        mysql -u root -p [database_name] < /path/to/ocomon_4.x/install/4.x/DB_UPDATE_FROM_BETA1.sql
        
        Where: [database_name]: It is the name of the OcoMon database

2. Overwrite the scripts of your old version by the scripts of the new version (recommended: Keep only your configurations file "config.inc.php" and move/remove all other scripts);

3. For security reasons, after importing SQL, remove the Install folder. Done! Simply set the new version settings directly via admin interface.


#### If your current version is the 3.3:

1. Import the database update file "06-DB-UPDATE-FROM-3.3.sql" (install/4.x/): <br>

        Terminal command line:
        mysql -u root -p [database_name] < /path/to/ocomon_4.x/install/4.x/06-DB-UPDATE-FROM-3.3.sql
        
        Where: [database_name]: It is the name of the OcoMon database

2. Overwrite the scripts of your old version by the scripts of the new version (recommended: Keep only your configurations file "config.inc.php" and move/remove all other scripts);

3. For security reasons, after importing SQL, remove the Install folder. Done! Simply set the new version settings directly via admin interface.

#### If your current version is the 3.2 or 3.1 or 3.1.1:

1. Import the database update file "05-DB-UPDATE-FROM-3.2.sql" (in install/4.x/) : <br>

        Terminal command line:
        mysql -u root -p [database_name] < /path/to/ocomon_4.x/install/4.x/04-DB-UPDATE-FROM-3.2.sql
        
        Where: [database_name]: It is the name of the OcoMon database

2. Overwrite the scripts of your old version by the scripts of the new version (recommended: Keep only your configurations file "config.inc.php" and move/remove all other scripts);

3. For security reasons, after importing SQL, remove the Install folder. Done! Simply set the new version settings directly via admin interface.

#### If your current version is the 3.0 (final release):

1. Import the database update file "04-DB-UPDATE-FROM-3.0.sql" (in install/4.x/) : <br>

        Terminal command line:
        mysql -u root -p [database_name] < /path/to/ocomon_4.x/install/4.x/04-DB-UPDATE-FROM-3.0.sql
        
        Where: [database_name]: It is the name of the OcoMon database

2. Overwrite the scripts of your old version by the scripts of the new version (recommended: Keep only your configurations file "config.inc.php" and move/remove all other scripts);

3. For security reasons, after importing SQL, remove the Install folder. Done! Simply set the new version settings directly via admin interface.


#### If your current version is any of the release candidates (rc) of version 3.0 (rc1, rc2, rc3):

+ It is always recommended to perform **BACKUP** of both the version scripts and the database currently in use by the system.

1. Import the database update file "03-DB-UPDATE-FROM-3.0rcx.sql" (in install/4.x/) : 

        Terminal command line:
        mysql -u root -p [database_name] < /path/to/ocomon_4.x/install/4.x/03-DB-UPDATE-FROM-3.0rcx.sql
        
        Where: [database_name]: It is the name of the OcoMon database

2. Overwrite the scripts of your old version by the scripts of the new version (recommended: Keep only your configurations file "config.inc.php" and move/remove all other scripts);

3. For security reasons, after importing SQL, remove the Install folder. Done! Simply set the new version settings directly via admin interface.


        
#### If your current version is the version 2.0 final

+ **IMPORTANT:** Carefully read the changelog-3.0.md file (*in /changelog*) to check the news and especially about **functions removed from previous versions** and some new **necessary settings** as well as counting time changes from SLAs to pre-existing tickets. 

+ Perform the **BACKUP** of both the version scripts and the database currently in use by the system. 

+ The update process considers that the current version is 2.0 (**final release**), so if your version is 2.0RC6, go to the related section.

+ **IMPORTANT**: Depending on the configuration of your database for the "Case Sensitive", you need to rename the following tables (if they have the name with the letter "X" in upper case): "areaXarea_abrechamado", "equipXpieces" to: "areaxarea_abrechamado ", "equipxpieces". This **MUST** be done **BEFORE** importing the SQL update file.

+ To update from version 2.0 (final release), simply overwrite the scripts of your OCOMON folder by the new version scripts (recommended: Keep only your "config.inc.php" settings file and move / remove all other scripts) and import to MySQL the update file: 02-DB-UPDATE-FROM-2.0.sql (in /install/4.x/). <br><br>

        Terminal command line:
        mysql -u root -p [database_name] < /path/to/ocomon_4.x/install/4.x/02-DB-UPDATE-FROM-2.0.sql
    
        Where: [database_name]: It is the name of the OcoMon database

+ For security reasons, after importing SQL, remove the Install folder. Done! Simply set the new version settings directly via admin interface.

<br>

#### If your current version is the version 2.0RC6

+ **IMPORTANT:** Carefully read the changelog-3.0.md file (*in /changelog*) to check the news and especially about **functions removed from previous versions** and some new **necessary settings** as well as counting time changes from SLAs to pre-existing tickets. 

+ Perform the **BACKUP** of both the version scripts and the database currently in use by the system. 

+ The update process considers that the current version is 2.0RC6 (**oficial release candidate**), so, if your version has any customization this **update action is not recommended**.

+ **IMPORTANT**: Depending on the configuration of your database for the "Case Sensitive", you need to rename the following tables (if they have the name with the letter "X" in upper case): "areaXarea_abrechamado", "equipXpieces" to: "areaxarea_abrechamado ", "equipxpieces". This **MUST** be done **BEFORE** importing the SQL update file.

+ To update from version 2.0RC6, simply overwrite the scripts of your OCOMON folder by the new version scripts (recommended: Keep only your configuration file "config.inc.php" and move / remove all other scripts) Import to MySQL The Update File: RC6-DB_UPDATE_FROM_RC6.sql (in /install/4.x/). <br><br>

        Terminal command line:
        mysql -u root -p [database_name] < /path/to/ocomon_4.x/install/4.x/RC6-DB_UPDATE_FROM_RC6.sql
    
        Where: [database_name]: It is the name of the OcoMon database

+ For security reasons, after importing SQL, remove the Install folder. Done! Simply set the new version settings directly via admin interface.

### First installation:

The installation process is very simple and can be done by following 3 steps:

1. **Install system scripts:**

    Unpack the contents of the OcoMon_3.3 package in the public directory of your web server (*the path may vary depending on the distribution or configuration, but in general it is usually **/var/www/html/***).

    File permissions can be the default of your server (except for the api/ocomon_api/storage folder, which needs to be written by the Apache user).

2. **Creation of the database:**<br>

    **LOCALHOST SYSTEM** (If your system will be installed on an external server jump to the section [EXTERNAL HOSTING SYSTEM ]):
    
    To create the entire datebase of OcoMon, you need to import a single file of SQL statements:
    
    The file is:
    
        01-DB_OCOMON_4.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql (in /install/4.x/).

    Terminal command line:
        
        mysql -u root -p < /path/to/ocomon_4.x/install/4.x/01-DB_OCOMON_4.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql
        
    The system will ask for the password of the MySQL root user (or any other user that was provided instead of root in the above command).

    The above command will create the user "ocomon_4" with the default password "senha_ocomon_mysql", and the database "ocomon_4".

    **It is important to change this password for the user "ocomon_4" in MySQL right after installing the system.**

    You can also import the SQL file using any other database manager of your choice.


    If you want the database to have another name (instead of "ocomon_4"), edit directly in the file (*identify the entries related to the database name, username and also the user password at the beginning of the file*):

    "01-DB_OCOMON_4.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql"

    Before importing it. Use this same new information in the system settings file (step **3**) .
    
    **After importing, it is recommended to delete the "install" folder.**<br>


    **EXTERNAL HOSTING SYSTEM:**

    In this case, due to possible limitations for naming databases and users (usually the provider stipulates a prefix for databases and users), it is recommended to use the username provided by the hosting service itself or create a specific user (if your user account allows it) directly through your database access interface. Therefore:

    - **create** a specific database for OcoMon (you define the name);
    - **create** a specific user to access the OcoMon database (or use your default user);
    - **Edit** the script "01-DB_OCOMON_4.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql" **removing** the following lines from the beginning of the file:

            CREATE DATABASE /*!32312 IF NOT EXISTS*/`ocomon_4` /*!40100 DEFAULT CHARACTER SET utf8 */;

            CREATE USER 'ocomon_4'@'localhost' IDENTIFIED BY 'senha_ocomon_mysql';
            GRANT SELECT , INSERT , UPDATE , DELETE ON `ocomon_4` . * TO 'ocomon_4'@'localhost';
            GRANT Drop ON ocomon_4.* TO 'ocomon_4'@'localhost';
            FLUSH PRIVILEGES;

            USE `ocomon_4`;

    - After that just import the changed file and continue with the installation process.

            mysql -u root -p [database_name] < /path/to/ocomon_4.x/install/4.x/01-DB_OCOMON_4.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql

        Where: [database_name] is the name of the database that was manually created.<br>

    - **After importing, it is recommended to delete the "install" folder.**<br>

3. **Create the settings file:**

    Make a copy of the file config.inc.php-dist (*/includes/*) and rename it to config.inc.php. In this new file, check the information related to the database connection (*dbserver, database name, user and password*). <br><br>


TEST VERSION:
-------------

If you want to test the system before installing, you can run a Docker container with the system already working with some data already populated. If you already have Docker installed, then just run the following command on your terminal: 

        docker run -it --name ocomon_4 -p 8000:80 flaviorib/ocomon_demo-4.0:20211021 /bin/ocomon

Then just open your browser and access the following address:

        localhost:8000

And ready! You already have an installation of OcoMon ready for testing with the following registered users:<br>


| user      | Pass      | Description                         |
| :-------- | :-------- | :---------------------------------  |
| admin     | admin     | System administration level         |
| operador  | operador  | Standard operator - level 1         |
| operador2 | operador  | Standard operator - level 2         |
| abertura  | abertura  | Only for tickets opening            |


If you don't have Docker, go to the website and install the version for your operating system:

[https://docs.docker.com/get-docker/](https://docs.docker.com/get-docker/)<br>

Or watch this video (Brazilian Portuguese) to see how simple it is to test OcoMon without needing any installation:
[https://www.youtube.com/watch?v=Wtq-Z4M9w5M](https://www.youtube.com/watch?v=Wtq-Z4M9w5M)<br>



## FIRST STEPS


ACCESS

    user: admin
    
    password: admin (**Don't forget to change this password as soon as you have access to the system!!**)

New users can be created in the menu [Admin::Users]
<br><br>


## GENERAL SYSTEM SETTINGS


Some settings need to be adjusted depending on the intent for use for the system:

- configuration file: /includes/config.inc.php
    - this file contains the database connection information, and default paths.

- To enable the use of the e-mail queue function you need to configure the server task scheduler to run, in the desired periodicity, the following script:

        api/ocomon_api/service/sendEmail.php (change the file permissions to make it executable)

    - Example using Crontab:

            * * * * * /usr/local/bin/php /var/www/html/ocomon-4.0/api/ocomon_api/service/sendEmail.php

- To enable the use of the opening ticket by e-mail function you need to configure the server task scheduler to run, in the desired periodic, the following script:

        ocomon/open_tickets_by_email/service/getMailAndOpenTicket.php (change the file permissions to make it executable)

    - Example using Crontab:

            * * * * * /usr/local/bin/php /var/www/html/ocomon-4.0/ocomon/open_tickets_by_email/service/getMailAndOpenTicket.php

- To enable the control of amount of requisitions, if you are using the API for integration with other systems, it is necessary that the Apache user must have write permission to the directory "api/ocomon_api/storage".

- The other system configurations are all accessible through the administration menu directly on the system interface. 
<br><br>



## DOCUMENTATION:


All OcoMon documentation is available on the project website and on the YouTube channel:

+ Official site: [https://ocomonphp.sourceforge.io/](https://ocomonphp.sourceforge.io/)

+ Changelog: [https://ocomonphp.sourceforge.io/changelog-incremental/](https://ocomonphp.sourceforge.io/changelog-incremental/)

+ Twitter: [https://twitter.com/OcomonOficial](https://twitter.com/OcomonOficial)

+ Youtube Channel: [https://www.youtube.com/c/OcoMonOficial](https://www.youtube.com/c/OcoMonOficial)



## Donations

If OCOMON has been useful to you, spared your work and allowed you to direct your resources to other investments, consider contributing to the continuity and growth of the project: [https://ocomonphp.sourceforge.io/doacoes/](https:// ocomonphp.sourceforge.io/doacoes/)

<br>I am convinced that OcoMon has the potential to be the tool that will be indispensable in the organization and management of your service area, freeing up your precious time for other accomplishments.

Have a good using!! :)

### Contact:
+ E-mail: [ocomon.oficial@gmail.com](ocomon.oficial@gmail.com)


Flávio Ribeiro
[flaviorib@gmail.com](flaviorib@gmail)

