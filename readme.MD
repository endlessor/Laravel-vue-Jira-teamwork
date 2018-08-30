Jira - Teamwork Sync
====================

Development setup
-----------------


Requirements:

 * Apache, php and mysql
 * A hosted jira instance (connect, not self-hosted) https://www.atlassian.com/software/jira/try
 * A teamwork account (https://www.teamwork.com/signup)
 * NGrok to open up a https connection to your development environment

Installation:

 * Check out code
 * Copy .env.example to .env and set database parameters
 * Run `php artisan migrate` to create the database structure
 * Configure apache to serve the website on a specific port (and make sure your setup is listening for that port)
   Example apache conf:
    
        <VirtualHost *:8085>
            DocumentRoot /project/path/public
        </VirtualHost>
        
 * Run `ngrok http 8085` to expose your local development environment

Install jira plugin

 * Copy the https domain name you got from ngrok (ie https://74031ae8.ngrok.io)
 * Go to jira, Add ons => Manage add-ons => Settings
 * Check "Enable development mode"
 * Click "Upload add-on"
 * Enter the ngrok domain name you have copied earlier and add /description (ie https://74031ae8.ngrok.io/description)

The plugin should now be installed.

Errors?

 * Most errors will be because of wrong file access rights on storage/ or bootstrap/cache
 * Check storage/logs/laravel.log to see what is causing an error
 * Feel free to contact me for help.

Need help? Take a look at https://developer.atlassian.com/cloud/jira/software/getting-started/

Admin panel:

   * In Jira, go to System => Teamwork sync
