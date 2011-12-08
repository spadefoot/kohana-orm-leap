# LEAP ORM for Kohana PHP Framework

LEAP is an ORM for the [Kohana PHP Framework](http://kohanaframework.org).  It also provides a powerful query builder.  More importantly, it
works with the following databases: DB2, Firebird, MariaDB, MS SQL, MySQL, Oracle, PostgreSQL, and SQLite.  This ORM has been completely built
from the ground up and therefore is NOT a hack implemetation.

The Leap ORM is based on the active record design pattern.  It provides a way to model database tables using PHP objects.

## Motivation

The Kohana PHP framework is one of the cleanest frameworks.  However, Kohana's official ORM is written only for MySQL.  Although some developers
have attempted to make it work with other databases, it really doesn't work well with any database other than MySQL.  Therefore, the primary goal
of this project was to create an ORM for Kohana that works with any major database.

Another goal of this project was to make the LEAP ORM a true Kohana module so that it could be just dropped into the Kohana's modules folder and
work.  Even though ORMs like [Doctrine](http://www.doctrine-project.org/projects/orm) could be adapted for Kohana, doing so requires a lot of work
to work correctly.  Besides, Doctrine is now so blotted and old that it really needs to be refactored.

A third goal for this project was to create an ORM for Kohana that can harness the power of composite keys, which many other ORMs (e.g. [Kohana's
official ORM](https://github.com/kohana/orm), [Jelly](https://github.com/creatoro/jelly), and [Sprig](https://github.com/sittercity/sprig/)) cannot
handle.  LEAP, on the other hand, is able to load models using composite keys.

## Features

LEAP provides a number of features, such as:

* Plugins for DB2, Firebird, MariaDB, MS SQL, MySQL, Oracle, PostgreSQL, and SQLite.
* Designed to work in conjunction with other database tools for Kohana.
* Config files for designating the database driver (e.g. PDO) and connection strings.
* Classes are easily extendible.
* A database connection pool for managing resources.
* A powerful query builder for creating SQL statements.
* Sanitizes data to help prevent SQL injection attacks.
* Supports composite primary keys and composite foreign keys.
* Enforces strong data types on database fields.
* Allows aliases to be declared.
* Makes working with certain database fields easy with field adaptors.
* A toolkit of useful functions.
* A [Web site](http://orm.spadefootcode.com) documenting its API and with examples.

## Getting Started

To start using LEAP, follow these steps:

1. Just download the module from github.
2. Unzip the download to the modules folder in Kohana.
3. Rename the uncompressed folder to "leap".
4. Modify the two config files: leap/config/database.php and leap/config/leap.php.
5. Navigate to your application folder and add "leap" as a module to the bootstrap.
6. Begin creating your models in the application/classes/model/leap/ folder.

## Required Files

The LEAP ORM module is meant to be completely independent of other Kohana modules.  However, it is recommended that Kohana's database module is
installed as well so that you can utilize the Database_Expression class.  As for the files within LEAP, you can remove any database plugins that
you are not using.  This is possible because each database plugin in LEAP is considered independent of the others.

## Documentation

This project is well-documented.  The API has been posted on [Spadefoot's LEAP ORM for Kohana](http://orm.spadefootcode.com) Web site.  Likewise,
this Web site also has numerous examples and tutorials.  You can also find other tutorials and examples online (please let us know if you find one
that we should highlight here).

## Further Assistance

Although LEAP is simple to use with the Kohana PHP Framework, you can get further assistance by asking questions on [Stack Overflow](http://stackoverlow.com).
You can also send an email to spadefoot.oss@gmail.com.

## Reporting Bugs & Making Recommendations

If you find a bug in the code or if you would like to make a recommendation, we would be happy to hear from you.  Here are three methods you can
use to submit bugs:

* Log an issue in this project's issue tracker.
* Create a fork of this project and submit a [pull request](http://help.github.com/send-pull-requests/).
* Send an email to spadefoot.oss@gmail.com.

## Known Issues

Please see this project's issue tracker on github for any known issues.

## Updates

Make sure that you add yourself as a watcher of this project so that you can watch for updates.  If you would like to be notified directly via
email please send an email to spadefoot.oss@gmail.com.

## Future Development

This project is constantly being improved and extended.  Here is a list of some of the features to come:

* Plugins for CouchDB, Drizzle, MongoDB, and XML RDB.
* Master / Slave support.
* Unit tests.

If you would like to take on some of these features, please fork this project and then send a pull request when your done.

## License (Apache v2.0)

Copyright 2011 Spadefoot

Licensed under the Apache License, Version 2.0 (the "License"); you may not use these files except in compliance with the License. You may obtain
a copy of the License at:

[http://www.apache.org/licenses/LICENSE-2.0](http://www.apache.org/licenses/LICENSE-2.0)

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations
under the License.
