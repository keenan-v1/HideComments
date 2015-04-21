# HideComments
HideComments plugin for Vanilla Forums

v1.0.1

Allows for users to hide specific comments within a discussion. The hide preference is saved and subsequent views will show a collapsed comment that can be expanded again.

Change Log:
1.0.0 - Initial Release
1.0.1 - Added NO_HIDE_ROLES and checks for them, so users can't hide Administrators and Moderators

Version 1.0.1 Note:
By default, this plugin will not let users hide comments from Moderators and Administrators. To override this, place this line in your config.php:

`$Configuration['Plugins']['HideComments']['NoHideRoles'] = 'A,comma,separated,list';`

Use role names as they are defined in your forum configuration.


Developed on Vanilla Forums 2.1.9

Side Note: This is my first plugin. I welcome feedback! If you feel I've done something wrong or could do something better, please let me know!

This plugin is released under GPL v2. See header for more information.
