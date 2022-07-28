# Content Readability
This modules takes the default body field on any node/block that has a configuration page and provides a readability score.  This module leverages [Dave Child's Text Statistics](https://github.com/DaveChild/Text-Statistics).

The goal of this module is to provide feedback to content editors about the quality of their writing.  Additionally this is a learning experience for myself.  

## Configuration
Navigating to the following path enables two settings:
`/admin/config/content/content_readability`

* Help Link
* Profiles

The help link is a url that can be changed to a resource to help users or an example page with a good score.  

Profiles are used to have multiple grade levels available to the users to compare their target against. The report is calculated by the [Flesch–Kincaid Grade Level](https://en.wikipedia.org/wiki/Flesch%E2%80%93Kincaid_readability_tests#Flesch%E2%80%93Kincaid_grade_level).  It compares the active grade level of the body compared to the target profile's grade level.   If you match or are under you receive and A.  For each whole integer above the threshold value it decreases the score.

The idea behind this was to give a representation to our users to encourage better content and make it more readability to a wider audience.


## TODO
* Support Non body fields?
* Views?
