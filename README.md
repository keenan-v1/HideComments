HideComments
============

HideComments plugin for Vanilla Forums  
*Version* 1.0.1

Allows for users to hide specific comments within a discussion. The hide preference is saved and subsequent views will show a collapsed comment that can be expanded again.

*Change Log*  
* 1.0.0 - _Initial Release_  
* 1.0.1 - _Added checks for roles_

*Version 1.0.1 Note*  
By default, this plugin will not let users hide comments from Moderators and Administrators. To override this, place this line in your config.php:
```
$Configuration['Plugins']['HideComments']['NoHideRoles'] = 'A,comma,separated,list';
```
Use role names as they are defined in your forum configuration.

---

*Developed on Vanilla Forums 2.1.9*

I welcome any feedback!

_This plugin is released under GPL v2. See header for more information._
