## CONTENTS OF THIS FILE

 * Introduction
 * Requirements
 * Recommended modules
 * Installation
 * Configuration
 * Maintainers

## INTRODUCTION

This module is a child module of [simpleSAMLphp Authentication][2]. It provides
a user interface to map attributes from SAML login to user fields.

Once attributes are mapped the module calls the
'[hook_simplesamlphp_auth_user_attributes][3]' to save the values to a user's
fields on login.

This module is based on
[rael9's sandbox: SAML Auth Custom Attribute Mapping][1].

[1]: https://www.drupal.org/sandbox/rael9/samlauth_custom_attributes
[2]: https://www.drupal.org/project/simplesamlphp_auth
[3]: https://git.drupalcode.org/project/simplesamlphp_auth/blob/8.x-3.x/simplesamlphp_auth.api.php#L130

## REQUIREMENTS

This module requires the following modules:

 * simpleSAMLphp Authentication (https://drupal.org/project/simplesamlphp_auth)

## RECOMMENDED MODULES

 * Markdown filter (https://www.drupal.org/project/markdown):
   When enabled, display of the project's README.md help will be rendered
   with markdown.

## INSTALLATION

 * Install as you would normally install a contributed Drupal module. Visit:
   https://www.drupal.org/documentation/install/modules-themes/modules-8
   for further information.

## CONFIGURATION

Attributes are mapped in 'Configuration' -> 'People' -> 'SimpleSAMLphp Auth
Attribute Mapping'.

## MAINTAINERS

Current maintainers:
 * Daniel Mundra (dmundra) - https://drupal.org/u/dmundra

Project sponsors:
 * Dev Services - https://devservices.uoregon.edu/
 * CivicActions - https://civicactions.com/
