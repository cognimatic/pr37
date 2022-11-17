INTRODUCTION
------------
Migrate Conditions provides a framework of condition plugins exclusively for
use with the Migrate API. The module then provides a number of process plugins
that can leverage the conditions.

Process plugins
* skip_on_condition
* evaluate_condition
* if_condition
* first_meeting_condition
* filter_on_condition
* switch_on_condition

Migrate Condition plugins
* all_elements ("helper" condition to change handling of arrays)
* and ("helper" condition that can group other conditions)
* callback
* contains
* default (always true)
* empty
* entity_exists
* equals
* greater_than
* has_element ("helper" condition to change handling of arrays)
* in_array
* in_migrate_map
* is_null
* is_stub
* isset
* less_than
* matches
* older_than
* or ("helper" condition that can group other conditions)

REQUIREMENTS
------------
The core Migrate module.

RECOMMENDED MODULES
-------------------
Migrate Sandbox is an excellent way to test this stuff out.

INSTALLATION
------------
You should require this module using composer, like with any typical contrib
module.

CONFIGURATION
-------------
Details for configuring the process plugins and condition plugins can be found
in their respective docblocks.

See the project page for a few examples.
https://www.drupal.org/project/migrate_conditions

MAINTAINERS
-----------
danflanagan8, migrate enthusiast
