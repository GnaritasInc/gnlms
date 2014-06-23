=== User Role Editor ===
Contributors: shinephp
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=vladimir%40shinephp%2ecom&lc=RU&item_name=ShinePHP%2ecom&item_number=User%20Role%20Editor%20WordPress%20plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: user, role, editor, security, access, permission, capability
Requires at least: 3.0
Tested up to: 3.4.1
Stable tag: trunk

User Role Editor WordPress plugin makes the role capabilities changing easy. You can change any standard WordPress user role (except administrator).

== Description ==

With User Role Editor WordPress plugin you can change user role (except Administrator) capabilities easy, with a few clicks.
Just turn on check boxes of capabilities you wish to add to the selected role and click "Update" button to save your changes. That's done. 
Add new roles and customize its capabilities according to your needs, from scratch of as a copy of other existing role. 
Unnecessary self-made role can be deleted if there are no users whom such role is assigned.
Role assigned every new created user by default may be changed too.
Capabilities could be assigned on per user basis. You can add new capabilities and remove unnecessary capabilities which could be left from uninstalled plugins.
Multi-site support is provided.

To read more about 'User Role Editor' visit [this page](http://www.shinephp.com/user-role-editor-wordpress-plugin/) at [shinephp.com](shinephp.com).

Русская версия этой статьи доступна по адресу [ru.shinephp.com](http://ru.shinephp.com/user-role-editor-wordpress-plugin-rus/)


== Installation ==

Installation procedure:

1. Deactivate plugin if you have the previous version installed.
2. Extract "user-role-editor.zip" archive content to the "/wp-content/plugins/user-role-editor" directory.
3. Activate "User Role Editor" plugin via 'Plugins' menu in WordPress admin menu. 
4. Go to the "Settings"-"User Role Editor" menu item and change your WordPress standard roles capabilities according to your needs.

== Frequently Asked Questions ==
- Does it work with WordPress 3.3 in multi-site environment?
Yes, it works with WordPress 3.3 multi-site. By default plugin works for every blog from your multi-site network as for locally installed blog.
To update selected role globally for the Network you should turn on the "Apply to All Sites" checkbox. You should have superadmin privileges to use User Role Editor under WordPress multi-site.

To read full FAQ section visit [this page](http://www.shinephp.com/user-role-editor-wordpress-plugin/#faq) at [shinephp.com](shinephp.com).

== Screenshots ==
1. screenshot-1.png User Role Editor main form
2. screenshot-2.png Add/Remove roles or capabilities
3. screenshot-3.png User Capabilities link
4. screenshot-4.png User Capabilities Editor

To read more about 'User Role Editor' visit [this page](http://www.shinephp.com/user-role-editor-wordpress-plugin/) at [shinephp.com](shinephp.com).


== Special Thanks to ==
* [Lorenzo Nicoletti](http://www.extera.com) - For the code enhancement. CUSTOM_USER_META_TABLE constant is used now for more compatibility with core WordPress API.
* Marcin - For the code enhancement. This contribution allows to not lose new custom capability if it is added to other than 'Administrator' role.
* [FullThrottle](http://fullthrottledevelopment.com/how-to-hide-the-adminstrator-on-the-wordpress-users-screen) - For the code to hide administrator role at admin backend.

= Translations =
* Dutch: [Frank Groeneveld](http://ivaldi.nl), [Rémi Bruggeman](http://www.remisan.be)
* French: [Presse et Multimedia](http://presse-et-multimedia.fr/blog), [Whiler](http://blogs.wittwer.fr/whiler)
* German: [Peter](http://www.becker-heidmann.de)
* Hebrew: [Aryo Digital](http://www.aryo.co.il), [Sagive](http://www.sagive.co.il)
* Hindi: [Outshine Solutions](http://outshinesolutions.com)
* Italian: [Tristano Ajmone](http://www.zenfactor.org), [Umberto Sartori](http://venezialog.net)
* Lithuanian: [Vincent G](http://host1free.com)
* Persian: [Parsa](http://parsa.ws), [Good Life](http://good-life.ir), Amir Khalilnejad
* Polish: [TagSite](http://www.tagsite.eu), [Bartosz](www.digitalfactory.pl)
* Russian: [Vladimir Garagulya](http://shinephp.com)
* Spanish: [Victor Ricardo Díaz (INFOMED)](http://www.sld.cu), [Dario Ferrer](http://www.darioferrer.com)
* Swedish: [Christer Dahlbacka](www.startlinks.eu), [Andréas Lundgren](http://adevade.com/)
* Turkish: [Muhammed YILDIRIM](http://ben.muhammed.im)
* -----------------------------------------------------
* translations below are included to the package, but all of them are outdated and every file needs to be updated. You are welcome!
* Finnish: [Lauri Merisaari](http://www.viidakkorumpu.fi)
* Japanese: Kaz, [Technolog.jp](http://technolog.jp)
* Belorussian: [Marsis G.](http://pc.de) - needs update
* Brasilian Portuguese: [Rafael Galdencio](http://www.arquiteturailustrada.com.br) - needs update
* Chinese: [Yackytsu](http://www.jackytsu.com) - needs update
* Hungarian: [István](http://www.blacksnail.hu) - needs update


Dear plugin User!
If you wish to help me with this plugin translation I very appreciate it. Please send your language .po and .mo files to vladimir[at-sign]shinephp.com email. Do not forget include you site link in order I can show it with greetings for the translation help at shinephp.com, plugin settings page and in this readme.txt file.
If you have better translation for some phrases, send it to me and it will be taken into consideration. You are welcome!
Share with me new ideas about plugin further development and link to your site will appear here.


== Changelog ==
= 3.7.5 =
* 11.08.2012
* Minor fix of German language translation file. One string translation was the reason of URE empty screen. Just replace your German language translation files in the ./lang directory with files from this package. 

= 3.7.5 =
* 29.07.2012
* Polish translation is updated. Thanks to Bartosz.
* "User Role Editor" menu item could be shown in translated form now. Do not lose it - it is on the same place at the "Users" submenu.


= 3.7.4 =
* 26.07.2012
* Persian translation is updated. Thanks to Amir Khalilnejad.

= 3.7.3 =
* 25.07.2012
* German translation is updated. Thanks to Piter.

= 3.7.2 =
* 20.07.2012
* SQL-injection vulnerability was found and fixed. Thanks to DDave for reporting it, look this [thread](http://shinephp.com/community/topic/little-bug-in-ure_has_administrator_role#post-819) for the details. 

= 3.7.1 =
* 25.06.2012
* Bug fix for "Fatal error: Call to a member function get_role() on a non-object in .../wp-content/plugins/user-role-editor/user-role-editor.php on line 185" 

= 3.7 =
* 23.06.2012
* 'Select All', 'Unselect All', 'Inverse' buttons were added to the from for more convenient capabilities management while role editing.
* Role and capability name could be started from digit, and underscore '_' character. Hyphen '-' character could be included into such name too.
* Old versions used 'edit_users' capability to check if show/hide 'User Role Editor' menu item under 'Users' menu. Starting from version 3.7 'administrator' role is checked. Existed inconsistency, when non-admin user with 'edit_users' capability saws 'User Role Editor' menu, but got 'Only Administrator is allowed to use User Role Editor' error message, was removed.
* Bug fix: if you work with WordPress admin via https, URE will use https instead of http, as it made in older versions.

= 3.6.2 =
* 23.05.2012
* Hindi translation is added. Thanks to Love Chandel.

= 3.6.1 =
* 07.05.2012
* Italian translation is updated. Thanks to Tristano Ajmone.

= 3.6 =
* 30.04.2012
* CSS and page layout fix for compatibility with WordPress 3.4.
* WordPress multi-site: when new blog created default role setting is copied for it from the main blog default role value now.
* Minor translations files update, e.g Russian roles names in plugin are identical to those WordPress uses itself now, etc.

= 3.5.4 =
* 4.04.2012
* Lithuanian translation is added, thanks to Vincent G.
* Spanish translation is updated, thanks to Victor Ricardo Díaz.

= 3.5.3 =
* 24.03.2012
* French translation is updated, thanks to Presse et Multimedia.
* Hebrew translation is updated, thanks to Aryo Digital.  
* Persian translation is updated, thanks to Parsa.  
* Minor CSS fix to provide compatibility with RTL languages.

= 3.5.2 =
* 17.03.2012
* Turkish translation is updated, thanks to Muhammed YILDIRIM.
* Dutch translation is updated, thanks to Frank Groeneveld.  

= 3.5.1 =
* 24.02.2012
* Bugs for multi-site WordPress network installation were discovered and fixed: 1) blocked login to admin back-end; 2) empty users list for administrators of single sites; 3) empty authors drop down list at the post editor page.
* If URE plugin is not enabled for single site administrator, then URE is automatically excluded from plugins list available to that administrator.

= 3.5 =
* 19.02.2012
* User Role Editor could be available now for single site administrators (Administrator role) under multi-site environment. You should define URE_ENABLE_SIMPLE_ADMIN_FOR_MULTISITE constant in your blog wp-config.php file for that. Insert this line "define('URE_ENABLE_SIMPLE_ADMIN_FOR_MULTISITE', 1);" there, if you decide to give single site admin such opportunity.
* One of "User Role Editor" users with 1100+ sites in the multi-site network reported that URE doesn't update roles for all sites, but stalls somewhere in the middle. Other network update method is realized as alternative. Due to my tests it works approximately 30 times faster. If you met the same problem, try it. It will be great if you share your experience with me. In order select alternative method of all sites update add this line to you blog wp-config.php file "define('URE_MULTISITE_DIRECT_UPDATE', 1);". But be careful. It's recommended to make 1st try on the backup copy, not on a live site.
* Persian translation is updated. Thanks to [Parsa](http://parsa.ws).

= 3.4 =
* 21.01.2012
* You can see/edit "Administrator" role now. Insert this line of code "define('URE_SHOW_ADMIN_ROLE', 1);" into your wp-config.php file and login with administrator account for that.
  If for some reason your Administrator role missed some capabilities added by plugins or themes, you can fix that. But be careful with changing "Administrator" role, do not turn off accidentally some critical capabilities to not block your admin users.

= 3.3.3 =
* 11.01.2012
* Spanish (Dario) and Swedish (Andréas) translations update.

= 3.3.2 =
* 02.01.2012
* Enhance server side validation for user input of new role name, minor bug fixes.

Older records are available at [this page](http://www.shinephp.com/user-role-editor-wordpress-plugin/#changelog).

== Additional Documentation ==

You can find more information about "User Role Editor" plugin at [this page](http://www.shinephp.com/user-role-editor-wordpress-plugin/)

I am ready to answer on your questions about plugin usage. Use [ShinePHP forum](http://shinephp.com/community/forum/user-role-editor/) or [plugin page comments](http://www.shinephp.com/user-role-editor-wordpress-plugin/) for it please.
