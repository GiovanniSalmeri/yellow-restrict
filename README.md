# Restrict 0.9.1

Restrict access to pages.

<p align="center"><img src="SCREENSHOT.png" alt="Screenshot"></p>

## How to install an extension

[Download ZIP file](https://github.com/GiovanniSalmeri/yellow-restrict/archive/refs/heads/main.zip) and copy it into your `system/extensions` folder. [Learn more about extensions](https://github.com/annaesvensson/yellow-update).

## How to restrict the access to one or more pages

Add a `Restrict` [setting](https://github.com/annaesvensson/yellow-core#settings-page) at the top of a page to protect it and its descendant pages (top pages are considered children of the home page). The value of the setting is a comma separated list of users and groups that may access the page with their credentials. 

This extension relies on the [HTTP basic authentication](https://en.wikipedia.org/wiki/Basic_access_authentication). For sensitive data use it only over an encrypted connection (`https://`). For a simpler alternative there is the [Private extension](https://github.com/schulle4u/yellow-private).

## How to define usernames, passwords and groups

Usernames, passwords and groups are defined in a file `system/extensions/restrict.ini`, each user for line, with the following syntax:

```
username:password:@group1,@group2...
```

You can specify for each username zero or more groups it belongs to. All usernames belong moreover to an implicit group `@all`.

## Examples

Page with restricted access:

```
---
Title: Reserved page
Restrict: john, @admin, @commitee
---

This page is reserved to John, administrators and members of the Commitee.
```

Usernames, passwords and groups in `restrict.ini`:

```
john:a2S%iZhK
mary:cAaRWC8&:@commitee,@admin
ben:5DgZAC&R:@member
lucy:ZWKD(8Jy:@admin,@member
antony:79wB5w@Z:@commitee
mark:9F5)F57e:@member
```

Passwords are stored in cleartext. Do not use them for any other purpose and transmit them securely to users.

## Settings

The following setting can be configured in file `system/extensions/yellow-system.ini`:

`RestrictUserFile` = filename for users, passwords and groups  

For the restricted pages to show the username and a tip for "logging out", add the following line to the file `system/layouts/footer.html`:

```
<?php echo $this->yellow->page->getExtraHtml("logout"); ?>
```

For this extension to work with the Apache webserver, sometimes it is necessary to add to the file `.htaccess` the following line:

```
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

or the following lines:

```
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```

[Other solutions](https://stackoverflow.com/questions/26475885/authorization-header-missing-in-php-post-request) may be needed.

## Developer

Giovanni Salmeri. [Get help](https://datenstrom.se/yellow/help/).
