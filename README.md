# LEAP for Kohana PHP Framework

LEAP is an database management library for the [Kohana PHP Framework](http://kohanaframework.org) and is designed to work with DB2,
Drizzle, Firebird, MariaDB, MS SQL, MySQL, Oracle, PostgreSQL, and SQLite.  LEAP acts as a common interface between the different
database dialects and connections.  It provides a powerful query builder and ORM.  LEAP's ORM is based on the active record design
pattern, which utilizes PHP objects to model database tables.

Make sure to "Star" this project if you like it.

## Motivation

LEAP is meant to be a simple, clean project.  The primary goal of LEAP is to create an ORM for the Kohana PHP Framework that works
with all major databases and is meant to be a true Kohana module so that it could be just dropped into Kohana's modules folder and
work.  Even though other ORMs like [Doctrine](http://www.doctrine-project.org/projects/orm) are adaptable to Kohana, doing so
requires a lot of work.  Another goal for the development of LEAP is to create an ORM for Kohana that can harness the power of
composite keys, which many other ORMs (e.g. [Kohana's official ORM](https://github.com/kohana/orm), [Jelly](https://github.com/creatoro/jelly),
and [Sprig](https://github.com/sittercity/sprig/)) cannot handle.

## Features

LEAP provides a number of features, such as:

* Plugins for DB2, Drizzle, Firebird, MariaDB, MS SQL, MySQL, Oracle, PostgreSQL, and SQLite.
* Designed to work in conjunction with other database tools for Kohana.
* [Config file for designating the database driver (e.g. PDO) and connection strings](http://orm.spadefootcode.com/tutorials/setting-up-a-database-connection/).
* Classes are easily extensible.
* A [database connection pool](http://orm.spadefootcode.com/tutorials/establishing-a-database-connection/) for managing resources.
* A powerful [query builder for creating SQL statements](http://orm.spadefootcode.com/tutorials/building-sql-statements/).
* Sanitizes data to help prevent SQL injection attacks.
* Capable of handling non-integers primary keys.
* Supports composite primary keys and composite foreign keys.
* Enforces strong data types on [database fields](http://orm.spadefootcode.com/tutorials/mapping-a-model/#fields).
* Allows [field aliases](http://orm.spadefootcode.com/tutorials/mapping-a-model/#aliases) to be declared.
* Makes working with certain database fields easy with [field adaptors](http://orm.spadefootcode.com/tutorials/mapping-a-model/#adaptors).
* A set of Auth classes for authenticating user logins.
* A toolkit of useful functions.
* [Leap's API](http://orm.spadefootcode.com/api/annotated.html) that documents each class.
* Lots of [tutorials](http://orm.spadefootcode.com/tutorials/index/).

## Getting Started

To start using LEAP, follow these steps:

1. Just download the module (see below regarding as to which branch) from github.
2. Unzip the download to the modules folder in Kohana.
3. Rename the uncompressed folder to "leap".
4. Modify leap/config/database.php.
5. Add "leap" as a module to application/bootstrap.php.
6. Begin creating your models in the application/classes/model/leap/ folder.

For more information, see the tutorial on [installing LEAP](http://orm.spadefootcode.com/tutorials/installing-leap/).

### About Branches

* 3.3/master  - PHP 5.3+, Kohana 3.3.X, maintained, stable
* 3.3/develop - PHP 5.3+, Kohana 3.3.X, maintained, unstable
* 3.3/legacy  - PHP 5.2+, Kohana 3.3.X, deprecated, stable
* 3.2/master  - PHP 5.3+, Kohana 3.2.X, maintained, stable
* 3.2/develop - PHP 5.3+, Kohana 3.2.X, maintained, unstable
* 3.2/legacy  - PHP 5.2+, Kohana 3.2.X, deprecated, stable
* 3.1/master  - PHP 5.3+, Kohana 3.1.X, maintained, stable
* 3.1/develop - PHP 5.3+, Kohana 3.1.X, maintained, unstable
* 3.1/legacy  - PHP 5.2+, Kohana 3.1.X, deprecated, stable

## Required Files

The LEAP ORM module is meant to be completely independent of other Kohana modules.  However, it is recommended that Kohana's database module be
installed as well so that you can utilize the Database_Expression class.  As for the files within LEAP, you can remove any database plugins that
you are not using.  This is possible because each database plugin in LEAP is considered independent of the others.

## Documentation

This project is accompanied by [a companion Web site](http://orm.spadefootcode.com), which documents the [API for the LEAP ORM](http://orm.spadefootcode.com/api/annotated.html)
and has a number of [examples and tutorials](http://orm.spadefootcode.com/tutorials/index/). You can also find other tutorials and examples
online (please let us know if you find one that we should highlight here).

## Further Assistance

Although LEAP is simple to use with the Kohana PHP Framework, you can get further assistance by asking questions on either [Kohana's Forum](http://forum.kohanaframework.org/)
or [Stack Overflow](http://stackoverlow.com). You can also send an email to spadefoot.oss@gmail.com.

## Reporting Bugs & Making Recommendations

If you find a bug in the code or if you would like to make a recommendation, we would be happy to hear from you.  Here are three methods
you can use to notify us:

* Log an issue in this project's [issue tracker](https://github.com/spadefoot/kohana-orm-leap/issues?sort=comments&direction=desc&state=open).
* Create a fork of this project and submit a [pull request](http://help.github.com/send-pull-requests/).
* Send an email to spadefoot.oss@gmail.com.

## Known Issues

Please see this project's [issue tracker](https://github.com/spadefoot/kohana-orm-leap/issues?sort=comments&direction=desc&state=open) on github for any known issues.

## Updates

Make sure that you add yourself as a watcher of this project so that you can watch for updates.  If you would like to be notified directly
via email please send an email to spadefoot.oss@gmail.com.

## Future Development

This project is constantly being improved and extended. If you would like to contribute to LEAP, please fork this project and then send
us your additions/modifications using a [pull request](http://help.github.com/send-pull-requests/).

## License

### Apache v2.0

Copyright © 2011–2013 Spadefoot Team.

Unless otherwise noted, LEAP is licensed under the Apache License, Version 2.0 (the "License"); you may not use these files except in
compliance with the License. You may obtain a copy of the License at:

[http://www.apache.org/licenses/LICENSE-2.0](http://www.apache.org/licenses/LICENSE-2.0)

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions
and limitations under the License.
