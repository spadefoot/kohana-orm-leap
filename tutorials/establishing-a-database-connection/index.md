---
title: Installing LEAP
layout: default
menu_item: tutorials
---

### Establishing a database connection

LEAP offers a variety of ways to connect to a database. However, before you can connect to any database you will need to [configure your database configuration file]({{ site.baseurl}}/tutorials/setting-up-a-database-connection) to set up your connection strings.

Once you have set up your connections in config/database.php, the next step is to begin with creating a database connection using LEAP.

#### Method #1

The most basic way to establish a connection is to use the DB_Connection_Driver's factory method in 3.3 (the DB_Connection's factory method in 3.2), as in the following example:

{% highlight php linenos %}
<?php
$connection = DB_Connection_Driver::factory('default');
$connection->open();
$results = $connection->query('SELECT * FROM `user`;');
$connection->close();
{% endhighlight %}

Notice that this connection must be opened and closed via this method. This allows you to open the connection when needed and close when needed.

#### Method #2

For this method, you will use the DB_Connection_Pool to retrieve the connection. This is often the most preferred way to connect to a database because the connection is managed for you by LEAP. This is also the method the ORM uses to establish its connections.

The database connection pool will return an open connection for the specified database connection group and will close that connection whenever it must or when the PHP script terminates. Here is how the database connection pool works:

{% highlight php linenos %}
<?php
$connection = DB_Connection_Pool::instance()->get_connection('default');
$results = $connection->query('SELECT * FROM `user`;');
{% endhighlight %}