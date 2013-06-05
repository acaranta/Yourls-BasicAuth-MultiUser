Yourls-BasicAuth-MultiUser
==========================

A Yourls plugin to manage multiuser via the apache basic auth.

How it Works ?
--------------
Basically, it relies on the Apache Basic Authentication mecanism.
To be simple, if a page is protected by an Basic HTTP Auth here is how it goes :
* the user accesses the page
* Auth is provided by the web server and the user is requested to log in using his/her credentials
* if the Auth is OK --> user can access the page
* if the Auth fails for any reasons --> user get a error page

When the Auth has succeeded, we get the credentials from the webserver and use these to automatically log the user to yourls.
* If that user does not exist in the local user db, then we create it (with a fake password) and we create the user's API key (token/signature/blahblahblah)
* If the user exists, we log him in and reuse the info stored previously

Plugin Installation
-------------------

* copy the 'multi-user-basicauth' directory to your Yourls "user/plugins" directory
* activate the plugin from your Yourls admin panel


Apache Config
-------------

Authentication
==============

Several Methods are possible.
* First you are selfhosted and you fully control your apache configuration file (GOOOOD ;) ).
In This case you could just add your Auth method to your Yourls Vhost configuration like this :
```
<Location /user/plugins/multi-user-basicauth>
AuthName "Yourls user identification"
AuthType Basic
AuthUserFile <path to your htpasswd file>
require valid-user
Allow from all
Order deny,allow
</Location>
```
* You can not modify your apache main config files (DAMN :( ).
In this case using an .htaccess file in /user/plugins/multi-user-basicauth you do the trick :
```
<Location /user/plugins/multi-user-basicauth>
AuthName "Yourls user identification"
AuthType Basic
AuthUserFile <path to your htpasswd file>
require valid-user
Allow from all
Order deny,allow
</Location>
```

Enhancements
============
* Next you would sure like the user to be redirected to the correct URL, easily, etc.
First if you are able to use the Apache Proxy Module, go ahead activate it and add to your your vhost config file :
```
ProxyPass /users http://coupe.la/user/plugins/multi-user-basicauth
ProxyPassReverse /users http://coupe.la/user/plugins/multi-user-basicauth
```

For .htaccess syntax you could add in the base Yourls install dir .htaccess
```
<IfModule mod_proxy.c>
ProxyPass /users http://coupe.la/user/plugins/multi-user-basicauth
ProxyPassReverse /users http://coupe.la/user/plugins/multi-user-basicauth
</IfModule>
```

Bear in mind not to use a path already existing :p ''/users'' is currently OK with Yourls
This will allow you to direct users to :
http://<YOURLS_BASE>/users/
instead of :
http://<YOURLS_BASE>/user/plugins/multi-user-basicauth/
Ouuuuuuuhhhh Niiice ;)

* Now ... What if the default redirect was not on the Yourls default ''/admin'' panel but to this multi-user interface ?
To do so, edit the default Redirect Yourls creates in its base instal directory, in the file '.htaccess''
```
# BEGIN YOURLS
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /yourls-loader.php [L]
</IfModule>
# END YOURLS
```
And change the last rewrite to :
```
# BEGIN YOURLS
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /users [L]
</IfModule>
# END YOURLS
```

Voila !
