# Module Unit

The foodsharing platform use different simular types to manages groups and regions.
This module combines the basic structure of them into a unit.
This allows to reuse the code which access to the table 'fs_bezirk' which is used by both system.
This is only a internal module so that the group and regions specific parts can base on top of it.

