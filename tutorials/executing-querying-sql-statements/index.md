---
title: Installing LEAP
layout: default
menu_item: tutorials
---

### Executing/Querying SQL statements

LEAP provides you will the ability to execute/query SQL statements directly on a database using either the DB_Connection or DB_Connection_Pool classes. Through a set of database connection wrappers, LEAP has standardize the way all SQL statements are executed or queried on a database. In other words, you will use the same methods for querying on a MySQL database and an Oracle database. This makes working with database way easier and you will have less of a learning curve when switching between database dialects.

#### Querying an SQL statement

The query method is used only for handling read statements, i.e. select statements. This means that the query method will return a result set, which can then be looped through.

{% highlight php linenos %}
<?php
$connection = DB_Connection_Pool::instance()->get_connection('default');
$results = $connection->query('SELECT * FROM `user`;');
if ($results->is_loaded()) {
   foreach ($results as $record) {
       echo Debug::vars($record);
   }
}
$results->free(); // optional
{% endhighlight %}

LEAP's query method has an added feature: it allows you to specified the data type of the result set. You can choose to have the results stored as an array, an object, or a model. To do so, you just specify a second argument defining the data type to use:

{% highlight php linenos %}
<?php
$results = $connection->query('SELECT * FROM `user`;', 'object');
{% endhighlight %}

By default, results will be stored as an array.

Another way to query your database is to use the reader method. The reader method will return an instance of the data reader class.

{% highlight php linenos %}
<?php
$connection = DB_Connection_Pool::instance()->get_connection('default');
$reader = $connection->reader('SELECT * FROM `user`;');
while ($reader->read()) {
   echo Debug::vars($reader->row('object'));
}
$reader->free();
{% endhighlight %}

#### Executing an SQL statement

For all other SQL statements, the execute method should be used. In other words, you will use the execute method for creates, inserts, updates, deletes, etc. Here is an example of a simple update statement on the 'user' table using the execute method:

{% highlight php linenos %}
<?php
$connection = DB_Connection_Pool::instance()->get_connection('default');
$connection->execute('UPDATE `user` SET `FirstName` = 'Spadefoot' WHERE ID = 1;');
{% endhighlight %}

You can also execute statements within a transaction.

{% highlight php linenos %}
<?php
$connection = DB_Connection_Pool::instance()->get_connection('default');
$connection->begin_transaction();
$connection->execute('UPDATE `user` SET `FirstName` = 'Spadefoot' WHERE ID = 1;');
$connection->execute('UPDATE `user` SET `FirstName` = 'John' WHERE ID = 2;');
$connection->commit();
{% endhighlight %}

If necessary, you can rollback a transaction by doing the following:

{% highlight php linenos %}
<?php
$connection->rollback();
{% endhighlight %}

