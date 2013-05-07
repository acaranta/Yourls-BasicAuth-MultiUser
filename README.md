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

* copy the 'multi-user-apacheauth' directory to your Yourls "user/plugins" directory
* activate the plugin from your Yourls admin panel



THIS PLUGIN AND DOCUMENTATION ARE WORK IN PROGRESS ;)
