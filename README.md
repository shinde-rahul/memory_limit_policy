CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

Memory limit policy is a base module to override the default php memory_limit
based on various constraints.

Some pages break because these are too heavy to generate? A user role has access
to some interfaces displaying complex entities? With memory limit policy
you can override the default php memory_limit for these situation only without
doing it for all the pages. It provides a UI for non-developers
to configure new memory overrides. Constraints can be combined
to fit "complex" situation such as overriding the memory_limit only
for one path, if it has a specific query argument and if the current
visitor has a specific role.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/memory_limit_policy

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/memory_limit_policy


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

Visit /admin/config/performance/memory-limit-policy/list to configure
policies.


MAINTAINERS
-----------

 * vbouchet - https://www.drupal.org/u/vbouchet
