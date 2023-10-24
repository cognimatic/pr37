CONTENTS OF THIS FILE
---------------------
  * Introduction
  * Requirements
  * Installation
  * FAQ
  * Configuration
  * Maintainers
  * Changelog


INTRODUCTION
------------

Cookie control module for drupal.

Civic Cookie Control
Module Name: Civic Cookie Control
Widget URI: https://www.civicuk.com/cookie-control
Author URI: https://www.civicuk.com
Author: Civicuk


This module enables you to comply with the UK and EU law on cookies.

This Drupal module simplifies the implementation and customisation process
of Cookie Control by [Civic UK](https://www.civicuk.com/).

With an elegant user-interface that doesn't hurt the look and feel of your site,
Cookie Control is a mechanism for controlling user consent for the use of
cookies on their computer.

There are several license types available, including:

**Community edition** - Provides the core functionality of Cookie Control,
and is of course GDPR compliant. You can use it to test Cookie Control,
or if you don't require any of its pro features.

**Pro edition** - Includes all of the pro features for use on a single website,
priority support and updates during your subscription.

**Multisite Pro Edition** - Offers all of the pro features for use on
up to ten websites, priority support and updates during your subscription.

**Pro edition** and **Multisite Pro Edition** support IAB (TCF v1.1).

To find out more about Cookie Control please visit
[Civic's Cookie Control home page](https://www.civicuk.com/cookie-control).


**Please Note**:

You will need to obtain an API KEY from [Civic UK](https://www.civicuk.com/cookie-control/download) in order to use the module.

Cookie Control is simply a mechanism to enable you to comply with UK and EU law on cookies. **You need to determine** which elements of your website are using cookies (this can be done via a [Cookie Audit](https://www.civicuk.com/cookie-control/deployment#audit), and ensure they are connected to Cookie Control.

REQUIREMENTS
------------

This module requires no modules outside of Drupal core.

INSTALLATION
------------

1. Obtain an API Key from [Civic UK](https://www.civicuk.com/cookie-control/download) for the site that you wish to deploy Cookie Control.*
2. Add the module in the corresponding Drupal folder.
3. Enable the module.
4. Run "drush updb" or update the database from update.php.
5. Configure the module from the 'Configuration->Civic Cookie Control 8' menu.
6. All done. Good job!

* If you already have an API Key and are wanting to update your domain records with CIVIC, please visit [Civic UK](https://www.civicuk.com/cookie-control/download)

FAQ
---

= API Key Error =

If you are using a community API key it is binded to a specific host domain.

Thus www.mydomain.org might work, but mydomain.org (naked domain) might not.

Be sure that you enter the correct host domain when registering for an API key.

In order to avoid this type of problem we suggest to create a 301 redirect
forwarding all requests to from www.mydomain.org to mydomain.org.

This may have [SEO benefits](http://www.mattcutts.com/blog/seo-advice-url-canonicalization/) too as it makes it very clear to search engines which is the canonical (one true) domain.

= Is installing and configuring the plugin enough for compliance? =

Only if the only cookies your site uses are the Google Analytics ones.
If other plugins set cookies, it is possible that you will need
to write additional JavaScript.
To determine what cookies your site uses please perform a [Cookie Audit](https://www.civicuk.com/cookie-control/deployment#audit).
This is a mandatory process to prepare a compliant privacy policy.
It is your responsibility as a webmaster to know what cookies your site sets,
what they do and when they expire.
If you don't you should consult whoever put your site together.

= I'm getting an error message Cookie Control isn't working? =

Support for Cookie Control is available via the forum: [https://groups.google.com/forum/#!forum/cookiecontrol](https://groups.google.com/forum/#!forum/cookiecontrol/) or open a support ticket in [Support](https://www.civicuk.com/support)

= Update from previous version =

Users with plugin version 8.x-1.0-rc1 (downloaded directly
from civicuk.com website) should backup their data,
delete the older plugin version and download the latest version from [Drupal](https://www.drupal.org/project/civicccookiecontrol) website.
Then run "drush updb" or visit /update.php.
Your data will remain intact, however you will have to re assign the third party
cookies inside each cookie category and then save your settings.
Users with version prior to 1.6 should review all settings and select values
for newly created configuration options.

CONFIGURATION
-------------

The module provides a configuration interface accessible
via the menu item "Configuration > Civic Cookie Control".
All configurations are performed within this interface.

MAINTAINERS
-----------
Current maintainers:
  * Thanassis Perperis (tper) - https://www.drupal.org/u/tper
  * Afroditi Ralli (ralliaf) - https://www.drupal.org/u/ralliaf

This project has been sponsored by:
  *  CIVIC Computing Limited - [CIVIC](https://www.drupal.org/civic-computing-limited)


CHANGELOG
---------

= 8.x-2.1-rc1 =
* Added alternative appearance styles for the notify bar's settings button.
* Added encodeCookie property to better support RFC
  standards and certain types of server processing.
* Added subDomains property to offer more flexibility
  on how user consent is recorded.
* IAB support (TCF v1.1)

= 8.x-3.0-rc1 =
* Bug fixes and update of cookie control script

= 8.x-4.0-rc1 =
* Major code refactoring.
    * Module is now extended to support
      both cookiecontrol v8 and cookiecontrol v9 api keys.
    * Module is now extended to support bot
      IAB 1.1 (v8 licenses) and IAB 2.0 (v9 licenses)
    * Module is prepared for drupal 9.
* Added support for IAB TCF v2.0 for cookiecontrol v9 api keys.
  Support for v1.1 has been dropped since it is to be depreciated
  by IAB at the end of March 2020; certain IAB related public methods
   have been removed and the iabCMP text object has been updated accordingly.
   It is no longer necessary to set optionalCookies when in iab mode
   since IAB purposes will be the first panel settings.
* Added support for California Consumer Privacy Act (CCPA).
  Cookie Control can work in either GDPR or CCPA
  mode based on the user’s location.
  For EU users only GDPR mode is applicable.
* Added new box option for the initialState property.
* Added sameSiteCookie property, to control whether
  SameSite:Strict is set to the CookieControl cookie.
  Setting this to false would mean Cookie Control can
  only work over HTTPS.
* Added acceptBehaviour property to control the behaviour
  of “Accept” buttons. They now default to accepting all
  cookies. Please note that this is different from the
  behaviour of v8 where only recommended cookies
  were accepted.
* Added locale property so that the selected locale
  is customisable. Thus user may select to user
  either the current drupal language for locale
  or the default browser locale.
* Added new closeOnGlobalChange option so that
  the there is control on whether the window
  should close or remain open when the user
  accepts/rejects cookies.
* Added settingsStyle option that determines
  the appearance of the settings button
  on the notification bar.
* Added branding sub-properties that
  control the styling of the reject buttons
* Added field to set the "Accept All" button text.
* Accessibility improvements and bug fixes.
* All apikeys now work under the following
  local adresses: localhost, 127.0.0.0/8,
  10.0.0.0/8, 192.168.0.0/16, 172.16.0.0/12

= 8.x-4.1-rc1 =
* Added notifyDismissButton option to hide
  the X close icon on the notify bar.
* Added sameSiteValue property to control
  the value of the SameSite flag for the CookieControl
  cookie.
* Added some legal texts required by IAB TCFv2.0

= 8.x-4.2-rc1 =
* Added wysiwyg editor in description fields.
* Added overlay option within Accessibility Object.

= 4.3.0-rc1 =
* Set required drupal core version to 8.8.
* Update codebase to pass phpcs with Drupal and DrupalPractice profiles.
* Add support for CCPA statement. When cookie control runs in CCPA
  mode both the privacy statement and the ccpa statement are displayed.
* Bug fixing.

= 4.3.1-rc1 =
* Add button to clear form configuration from private tempstore.
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3159774
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3156670
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3159820
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3159877
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3156775

= 4.3.2-rc1 =
* Update cookie control script to latest stable version.

= 4.3.3-rc1 =
* Update module to support cookie control 9.3.
    * Added support for LGPD legislation, by adding the
      vendors property to list vendors individually for
      each vookie contorl category.
    * Added new text properties for vendors:
      showVendors, thirdPartyCookies and readMore.
    * Added outline property in the Accessibility
      Object to allow users to use the default browser outline.
    * Added Close Text and Close Background options within the
      Branding Object to allow changing the styling of the
      "Close" button (if used).
    * Added Notify Font Color and Notify Background Color options
      within the Branding Object to allow changing the styling
      of notify interface (if used).
    * Added Full Legal Descriptions and DropDowns properties in
      the iabConfig object to make the IAB view more concise.
    * Added Legal Description text property for the updated IAB interface.
    * Added Save Only On Close property in the iabConfig object.
    * Added the ability to localise the statement and CCPA URLs.
    * The styling of the "Accept recommended settings" button
      is now consistent with the styling of the "Accept" button.
    * Removed deprecation notice from the geoTest function.
    * Bug fixes.

= 4.4.0 =
* Update module to support cookie control 9.4.
  * A number of fields is added to further configure displayed texts in IAB2.0.
    See further details in https://www.civicuk.com/cookie-control/v9/documentation-v9.4.html.
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3164371
* Ported patch of https://www.drupal.org/project/civicccookiecontrol/issues/3187652
* Ported patch of https://www.drupal.org/project/civicccookiecontrol/issues/3199876
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3206734
* Added the civic_govuk_cookiecontrol submodule that implements the GOVUK DWP
  pattern (https://design-system.dwp.gov.uk/patterns/consent-to-cookies) using
  Cookie Control to manage cookies.
* Run Drupal.behaviors.cookieControlWidget using once()
  as suggested in https://www.drupal.org/docs/drupal-apis/javascript-api/javascript-api-overview.
* Rework IAB form to load fields from YAML file.
* Replace \Drupal::service calls with proper dependency injection.
* Code cleanup and various bug fixes.

= 4.4.1 =
* Pass pareview comments.

= 4.4.2 =
* Rename branch from 4.4.x-dev to 4.4.x.

= 4.4.3 =
* Remove invocations of t() function in variables.

= 4.4.4 =
* Fix calls to clearTempstore which was renamed to
  civiccookiecontrol_clear_tempstore

= 4.4.5 =
* Modifications as requested by kiamlaluno in
  https://www.drupal.org/project/projectapplications/issues/3151960#comment-14073192

= 4.4.6 =
* Fix error in add alternative language button behaviour.

= 4.4.7 =
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3216931

= 4.4.8 =
* Alternative fix for https://www.drupal.org/project/civicccookiecontrol/issues/3216931
  after comment https://www.drupal.org/project/civicccookiecontrol/issues/3216931#comment-14128574.
* Remove CookieControl suggestions for anonymous users.

= 4.4.9 =
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3218216
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3219847

= 4.4.10 =
* Updates for Cookie Control 9.6.x.
** Added disableSiteScrolling property in the Accessibility Object,
   to determine if the module should prevent scrolling of the site
   when either the notification bar or panel are open.
* Add custom html format for https://www.drupal.org/project/civicccookiecontrol/issues/3319073
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3267310
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3231598
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3209090
* FIX for https://www.drupal.org/project/civicccookiecontrol/issues/3231879

= 4.4.11 =
* Update to include CCPA changes of cookie control v.9.8
** Added a new configuration field for a new rejectButton property
* Fix for https://www.drupal.org/project/civicccookiecontrol/issues/3225391

= 4.4.12 =
* Add missing dependency on the CCC8 config service

= 4.4.13 =
* Restructure the render arrays and the twig template for 
  GovUK submodule banner and details blocks.

= 4.5.0 =
* Update module to support drupal 10
* Set required drupal core version to 9 || 10.
* Update codebase to pass phpcs with Drupal and DrupalPractice profiles.
* Fix various issues in the configuration form

= 4.5.1 =
* Update module to support CookieControl v9.9 adding Google Vendors 
  in iabCMP property.

= 4.5.2 =
* Update module to support CookieControl v9.9 and TCF 2.2.
