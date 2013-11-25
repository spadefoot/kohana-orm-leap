---
title: Installing LEAP
layout: default
menu_item: tutorials
---

### Setting up a database connection

The LEAP ORM utilizes the same database configuration file (i.e. config/database.php) as Kohana's official ORM, Jelly, and Sprig, with a few minor additions/changes:

{% highlight php linenos %}
<?php defined('SYSPATH') OR die('No direct script access.');
$config = array();
$config['default'] = array(
    'type'          => 'SQL',       // string (e.g. SQL, NoSQL, or LDAP)
    'dialect'       => 'MySQL',     // string (e.g. DB2, Drizzle, Firebird, MariaDB, MsSQL, MySQL, Oracle, PostgreSQL, or SQLite)
    'driver'        => 'Standard',  // string (e.g. Standard, Improved, or PDO)
    'connection'    => array(
        'persistent'    => FALSE,       // boolean
        'hostname'      => 'localhost', // string
        'port'          => '',          // string
        'database'      => '',          // string
        'username'      => 'root',      // string
        'password'      => 'root',      // string
        'role'          => '',          // string
    ),
    'caching'       => FALSE,       // boolean
    'charset'       => 'utf8',      // string
);
return $config;
{% endhighlight %}

The biggest difference is that 'type' now represents the database language category being used. The specific dialect is now specified using the 'dialect' setting.

As you can see, there are two additional settings. The first addition is the 'driver' setting. This setting allows the user to define which database driver to use: the standard driver (i.e. 'standard'), the improved driver (i.e. 'improved'), or the PDO driver (i.e. 'pdo'). By having a 'driver' setting, we can now create additional database driver wrappers with ease. The other addition to this array is the port setting.

Which settings you will use depends on which database you are using and where your database is located. Therefore, not all settings are needed for every database. For example, SQLite does not require a username and password (as well as a few others) whereas MySQL does require these settings.

