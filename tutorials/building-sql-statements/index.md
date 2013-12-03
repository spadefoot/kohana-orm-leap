---
title: Installing LEAP
layout: default
menu_item: tutorials
---

### Building SQL statements

Leap makes building SQL statements easy using its query builder. Leap's query builder is capable of building [create](#create), [read](#read), [update](#update), and [delete](#delete) (CRUD) statements. The query builder will help prevent SQL insertion attacks by escaping identifiers and values. The query builder also makes it easier to switch database dialects for it standardizes the way you write SQL statements.

Depending on what your needs are, you can use either 'DB_SQL' or 'DB_ORM' to generate your SQL statements. These two classes are for the most part the same; however, there are some minor differences between them. The main difference between the classes is that one uses a data source to determine the database dialect and the another uses a model to determine the dialect. For some commands, DB_ORM is a little more restrictive than DB_SQL; however, [DB_ORM can be extended]({{ site.baseurl}}/tutorials/extending-the-query-builder) using a model's corresponding builder class. And, with select statements, DB_ORM will return an array of models of the specified type whereas DB_SQL will return an array of associated arrays. Notwithstanding these minor differences, they both essentially act the same and share the majority of the same functions.

<a name="create"> </a>

#### Create (aka Insert) Statements


##### Inserting a single record

The query builder simplifies the writing of insert statements. The following example demonstrates how to build an insert statement using DB_SQL:

{% highlight php linenos %}
<?php
$builder = DB_SQL::insert('default')
   ->into('user')
   ->column('Username', 'spadefoot')
   ->column('Password', sha1('a5b4c3d2e1'))
   ->column('FirstName', 'John')
   ->column('LastName', 'Smith')
   ->column('IsActive', TRUE);
$sql = $builder->statement();
$id = $builder->execute();
{% endhighlight %}

The same can be accomplished using DB_ORM; however, notice that there is no call to the 'into' function and that the database config id has been replaced with the name of the model:

{% highlight php linenos %}
<?php
$builder = DB_ORM::insert('user')
   ->column('Username', 'spadefoot')
   ->column('Password', sha1('a5b4c3d2e1'))
   ->column('FirstName', 'John')
   ->column('LastName', 'Smith')
   ->column('IsActive', TRUE);
$sql = $builder->statement();
$id = $builder->execute();
{% endhighlight %}

In the above examples, please note that you do not need to call statement() to execute the SQL statement. It is just in case you want to debug the SQL statement; otherwise, you can just chain execute() onto your other calls.

##### Inserting multiple records

There are two ways you can insert multiple records into a database in one SQL statement (i.e. as long as your database dialect supports such types of insertions; otherwise, only the first row will be inserted). However, this feature is only supported by DB_SQL.

The first way is the most intuitive, which uses the "row" method. Notice the second parameter which represents the index of the row.

{% highlight php linenos %}
<?php
DB_SQL::insert('default')
   ->into('user')
   ->row(array('Username' => 'spadefoot', 'Password' => sha1('a5b4c3d2e1'), 0)
   ->row(array('Username' => 'bluesnowman', 'Password' => sha1('z1y2x3w4v5'), 1)
   ->execute();
{% endhighlight %}

The other way to insert multiple records is to use the "column" method. Like in the previous example, notice the third parameter which represents the index of the row.

{% highlight php linenos %}
<?php
DB_SQL::insert('default')
   ->into('user')
   ->column('Username', 'spadefoot', 0)
   ->column('Password', sha1('a5b4c3d2e1'), 0)
   ->column('Username', 'bluesnowman', 1)
   ->column('Password', sha1('z1y2x3w4v5'), 1)
   ->execute();
{% endhighlight %}

<a name="read"> </a>

#### Read (aka Select) Statements

You can build a simple select statement by doing the following using DB_SQL:

{% highlight php linenos %}
<?php
$builder = DB_SQL::select('default')
   ->from('user');
$sql = $builder->statement();
{% endhighlight %}

The same can be accomplished with the DB_ORM:

{% highlight php linenos %}
<?php
$builder = DB_ORM::select('user');
$sql = $builder->statement();
{% endhighlight %}

Either builder can query a database using the query method:

{% highlight php linenos %}
<?php
$results = $builder->query();
{% endhighlight %}

Or, you can use the reader method with DB_SQL:

{% highlight php linenos %}
<?php
$reader = $builder->reader();
{% endhighlight %}

Both builders can be further modified using the following functions:

##### from

With DB_SQL, you can assign an alias to table's name.

{% highlight php linenos %}
<?php
$builder->from('user', 'u');
{% endhighlight %}

However, DB_ORM does not support aliases for table names.

##### column

By default, DB_SQL will select all records from the specified table. To just select certain columns, you can do the following:

{% highlight php linenos %}
<?php
$builder->column('FirstName');
{% endhighlight %}

You can specify an alias for this column by:

{% highlight php linenos %}
<?php
$builder->column('FirstName', 'GivenName');
{% endhighlight %}

However, DB_ORM does not support aliases for column names.

##### count

There is also a short-cut way of creating a count expression using DB_SQL's select builder, which will create an expression like so: COUNT(\*) AS "count".

{% highlight php linenos %}
<?php
$builder->count();
{% endhighlight %}

You can alter the field the count function is performed by doing the following (which is create an expression like COUNT("id") AS "count"):

{% highlight php linenos %}
<?php
$builder->count('id');
{% endhighlight %}

Like other columns, you can assign an alias:

{% highlight php linenos %}
<?php
$builder->count('*', 'total');
{% endhighlight %}

##### distinct

Some queries may require that the result set on contain records that are distinct. This can be done by setting the distinct function to TRUE.

{% highlight php linenos %}
<?php
$builder->distinct(TRUE);
{% endhighlight %}

This may done via both DB_SQL and DB_ORM.

##### join

Both DB_SQL and DB_ORM can join tables; however, DB_ORM cannot return data from the joined tables (i.e. since DB_ORM returns a result set of models of the specified type and a model essentially represents a row in a database table, an instance of a model class will not store any data from the joined tables...for that you will need to use DB_SQL).

The join function is written like so:

{% highlight php linenos %}
<?php
$builder->join('LEFT', 'role');
{% endhighlight %}

If you want to assign an alias to the joined table, add the following third parameter:

{% highlight php linenos %}
<?php
$builder->join('LEFT', 'role', 'r');
{% endhighlight %}

Although you can use strings for specifying the join type, you can also use one of LEAP' predefined join type constants. Considering that different SQL dialects use different join types, here is a simple lookup table for determining whether your SQL dialect supports a particular join type:

<table>
	<thead>
	    <tr style="background-color: #A4A4A4; color: #000000;">
	        <td>Join Type</td>
	        <td>Constant</td>
	        <td>Supported By</td>
	    </tr>
	</thead>
	<tbody>
	    <tr>
	        <td>CROSS</td>
	        <td>_CROSS_</td>
	        <td>DB2, Drizzle, Firebird, MariaDB, MS SQL, MySQL, Oracle, PostgreSQL, SQLite</td>
	    </tr>
	    <tr>
	        <td>EXCEPTION</td>
	        <td>_EXCEPTION_</td>
	        <td>DB2</td>
	    </tr>
	    <tr>
	        <td>INNER</td>
	        <td>_INNER_</td>
	        <td>DB2, Firebird, MariaDB, MS SQL, MySQL, Oracle, PostgreSQL, SQLite</td>
	    </tr>
	    <tr>
	        <td>LEFT</td>
	        <td>_LEFT_</td>
	        <td>DB2, Drizzle, Firebird, MariaDB, MS SQL, MySQL, Oracle, PostgreSQL, SQLite</td>
	    </tr>
	    <tr>
	        <td>LEFT OUTER</td>
	        <td>_LEFT_OUTER_</td>
	        <td>DB2, Firebird, MariaDB, MS SQL, MySQL, Oracle, PostgreSQL, SQLite</td>
	    </tr>
	    <tr>
	        <td>RIGHT</td>
	        <td>_RIGHT_</td>
	        <td>DB2, Drizzle, Firebird, MariaDB, MS SQL, MySQL, Oracle, PostgreSQL</td>
	    </tr>
	    <tr>
	        <td>RIGHT OUTER</td>
	        <td>_RIGHT_OUTER_</td>
	        <td>DB2, Firebird, MariaDB, MS SQL, MySQL, Oracle, PostgreSQL</td>
	    </tr>
	    <tr>
	        <td>FULL</td>
	        <td>_FULL_</td>
	        <td>DB2, Firebird, MS SQL, Oracle, PostgreSQL</td>
	    </tr>
	    <tr>
	        <td>FULL OUTER</td>
	        <td>_FULL_OUTER_</td>
	        <td>DB2, Firebird, MS SQL, Oracle, PostgreSQL</td>
	    </tr>
	    <tr>
	        <td>NATURAL</td>
	        <td>_NATURAL_</td>
	        <td>Firebird, MariaDB, MySQL, Oracle, PostgreSQL, SQLite</td>
	    </tr>
	    <tr>
	        <td>NATURAL CROSS</td>
	        <td>_NATURAL_CROSS_</td>
	        <td>SQLite</td>
	    </tr>
	    <tr>
	        <td>NATURAL INNER</td>
	        <td>_NATURAL_INNER_</td>
	        <td>Firebird, Oracle, PostgreSQL, SQLite</td>
	    </tr>
	    <tr>
	        <td>NATURAL LEFT</td>
	        <td>_NATURAL_LEFT_</td>
	        <td>Firebird, MariaDB, MySQL, Oracle, PostgreSQL, SQLite</td>
	    </tr>
	    <tr>
	        <td>NATURAL LEFT OUTER</td>
	        <td>_NATURAL_LEFT_OUTER_</td>
	        <td>Firebird, MariaDB, MySQL, Oracle, PostgreSQL, SQLite</td>
	    </tr>
	    <tr>
	        <td>NATURAL RIGHT</td>
	        <td>_NATURAL_RIGHT_</td>
	        <td>Firebird, MS SQL, Oracle, PostgreSQL</td>
	    </tr>
	    <tr>
	        <td>NATURAL RIGHT OUTER</td>
	        <td>_NATURAL_RIGHT_OUTER_</td>
	        <td>Firebird, MS SQL, Oracle, PostgreSQL</td>
	    </tr>
	    <tr>
	        <td>NATURAL FULL</td>
	        <td>_NATURAL_FULL_</td>
	        <td>Firebird, MS SQL, Oracle, PostgreSQL</td>
	    </tr>
	    <tr>
	        <td>NATURAL FULL OUTER</td>
	        <td>_NATURAL_FULL_OUTER_</td>
	        <td>Firebird, MS SQL, Oracle, PostgreSQL</td>
	    </tr>
	    <tr>
	        <td>STRAIGHT</td>
	        <td>_STRAIGHT_</td>
	        <td>MariaDB, MySQL</td>
	    </tr>
	</tbody>
</table>

##### on

To place a constraint on a join, it is done like so in both DB_SQL and DB_ORM:

{% highlight php linenos %}
<?php
$builder->on('Roles.User_ID', '=', 'User.ID');
{% endhighlight %}

##### using

Both DB_SQL and DB_ORM also support the using constrain:

{% highlight php linenos %}
<?php
$builder->using('Username');
{% endhighlight %}

##### where

Adding a where clause can be done in DB_SQL and DB_ORM.

{% highlight php linenos %}
<?php
$builder->where('LastName', '=', 'Smith');
{% endhighlight %}

Multiple where clauses can be affixed. By default, when there are more than one where clause, the builder will connect such where clauses using the AND connector; however, you can change the connector to use the OR connector like so:

{% highlight php linenos %}
<?php
$builder->where('FirstName', '=', 'John', 'OR');
{% endhighlight %}

As expected, LEAP supports all comparison operators that SQL supports. In most cases, you can just replace the '=' sign with the comparison operator of your choice. See your specific SQL dialect's API for more details on what comparison operators your SQL dialect supports.

However, there are two types of comparison operators that all SQL dialects have that are worth noting: the BETWEEN and IN comparison operators. With both of these operators, the third parameter in the where call is an array.

Here is an example using the BETWEEN operator:

{% highlight php linenos %}
<?php
$builder->where('DateCreated', 'BETWEEN', array('2011-01-01 00:00:00', '2012-12-31 23:59:59'));
{% endhighlight %}

Below is an example using the IN operator:

{% highlight php linenos %}
<?php
$builder->where('FirstName', 'IN', array('Matthew', 'Mark', 'Luke', 'John'));
{% endhighlight %}

##### where_block

In some circumstances, it may be necessary to group a set of where clauses together. To do so, LEAP provides a function for specifying such a group. To open a where block, do the following:

{% highlight php linenos %}
<?php
$builder->where_block('(');
{% endhighlight %}

To close this where block, you just use the closing parenthesis as in the following example:

{% highlight php linenos %}
<?php
$builder->where_block(')');
{% endhighlight %}

Like the where call, you can change the connector used (which by default is the AND connector):

{% highlight php linenos %}
<?php
$builder->where_block('(', 'OR');
{% endhighlight %}

##### group_by

Specifying a group_by clause is simple:

{% highlight php linenos %}
<?php
$builder->group_by('FirstName');
{% endhighlight %}

Although you can add as many group_by clauses as you want using multiple function calls, you can specify more than one field in a single function call...such as in the following example:

{% highlight php linenos %}
<?php
$builder->group_by(array('LastName', 'FirstName'));
{% endhighlight %}

##### having

Adding a having clause is identical to adding a where clause, except it must be declared only after at least one group by clause has been declared; otherwise, an exception will be thrown.

{% highlight php linenos %}
<?php
$builder->having('LastName', '=', 'Smith');
{% endhighlight %}

If you need more than one having clauses and need to change the connector, it can be done in the same fashion as the where clause:

{% highlight php linenos %}
<?php
$builder->having('FirstName', '=', 'John', 'OR');
{% endhighlight %}

If you need to use a comparison operator like BETWEEN and IN, do the same as in the where clause example above.

##### having_block

Likewise, a having block is defined the same way as a where block. A having block is created like so:

{% highlight php linenos %}
<?php
$builder->having_block('(');
{% endhighlight %}

And, a having block is closed like so:

{% highlight php linenos %}
<?php
$builder->having_block(')');
{% endhighlight %}

The connector can be changed in the same manner:

{% highlight php linenos %}
<?php
$builder->having_block('(', 'OR');
{% endhighlight %}

##### order_by

An order by clause can be declared by:

{% highlight php linenos %}
<?php
$builder->order_by('LastName');
{% endhighlight %}

If you want to specify the sort direction, do the following:

{% highlight php linenos %}
<?php
$builder->order_by('LastName', 'DESC');
{% endhighlight %}

You can take it a little farther by defining how NULLs are to be treated:

{% highlight php linenos %}
<?php
$builder->order_by('LastName', 'DESC', 'LAST');
{% endhighlight %}

##### limit

A limit clause is created like so:

{% highlight php linenos %}
<?php
$builder->limit(5);
{% endhighlight %}

##### offset

An offset clause is created like so:

{% highlight php linenos %}
<?php
$builder->offset(20);
{% endhighlight %}

##### page

If you prefer, you can add both the offset and limit constraints using the page function, where the first parameter is the offset and the second parameter is the limit:

{% highlight php linenos %}
<?php
$builder->page(20, 5);
{% endhighlight %}

##### combine

In cases where you need to combine two SQL statements, you can do so using the following function call:

{% highlight php linenos %}
<?php
$builder->combine('UNION', "SELECT * FROM `employee`");
{% endhighlight %}

<a name="update"> </a>

#### Update Statements

In its simplest form, an update statement is created using the DB_SQL like so:

{% highlight php linenos %}
<?php
$builder = DB_SQL::update('default')
   ->table('user')
   ->set('Username', 'spadefoot')
   ->where('ID', '=', 15);
$sql = $builder->statement();
$id = $builder->execute();
{% endhighlight %}

The DB_ORM also creates update statements, for example:

{% highlight php linenos %}
<?php
$builder = DB_ORM::update('user')
   ->set('Username', 'spadefoot')
   ->where('ID', '=', 15);
$sql = $builder->statement();
$id = $builder->execute();
{% endhighlight %}

##### where

As you can see above, both DB_SQL and DB_ORM are able to add where clauses. The syntax for adding a where clause is:

{% highlight php linenos %}
<?php
$builder->where('LastName', '=', 'Smith');
{% endhighlight %}

This function has a fourth parameter, which can be used to change the connector that will be used when using multiple where clauses:

{% highlight php linenos %}
<?php
$builder->where('FirstName', '=', 'John', 'OR');
{% endhighlight %}

To create a where clause using the BETWEEN operator, the syntax will be as follows:

{% highlight php linenos %}
<?php
$builder->where('DateCreated', 'BETWEEN', array('2011-01-01 00:00:00', '2012-12-31 23:59:59'));
{% endhighlight %}

Similarly, a where clause using the IN operator is create like so:

{% highlight php linenos %}
<?php
$builder->where('FirstName', 'IN', array('Matthew', 'Mark', 'Luke', 'John'));
{% endhighlight %}

##### where_block

Like the select builder, you can create where blocks.

{% highlight php linenos %}
<?php
$builder->where_block('(');
{% endhighlight %}

It is closed by:

{% highlight php linenos %}
<?php
$builder->where_block(')');
{% endhighlight %}

For when you need to change the connector, do:

{% highlight php linenos %}
<?php
$builder->where_block('(', 'OR');
{% endhighlight %}

##### order_by

If your update statement requires an order by clause, you can add it by:

{% highlight php linenos %}
<?php
$builder->order_by('LastName');
{% endhighlight %}

You can change the sort direction by:

{% highlight php linenos %}
<?php
$builder->order_by('LastName', 'DESC');
{% endhighlight %}

An additional parameter can be set to assign how NULLs should be treated:

{% highlight php linenos %}
<?php
$builder->order_by('LastName', 'DESC', 'LAST');
{% endhighlight %}

##### limit

You can add a limit clause by:

{% highlight php linenos %}
<?php
$builder->limit(5);
{% endhighlight %}

##### offset

An offset clause can also be added to your update statement by:

{% highlight php linenos %}
<?php
$builder->offset(20);
{% endhighlight %}

<a name="delete"> </a>

#### Delete Statements

You can build a delete statement using DB_SQL like so:

{% highlight php linenos %}
<?php
$builder = DB_SQL::delete('default')
   ->from('user')
   ->where('ID', 15);
$sql = $builder->statement();
$id = $builder->execute();
{% endhighlight %}

You can also build a delete statement with DB_ORM as the following example shows:

{% highlight php linenos %}
<?php
$builder = DB_ORM::delete('user')
   ->where('ID', '=', 15);
$sql = $builder->statement();
$id = $builder->execute();
{% endhighlight %}

##### where

To add a where clause to your delete statement, use the following call:

{% highlight php linenos %}
<?php
$builder->where('LastName', '=', 'Smith');
{% endhighlight %}

If you want, you can pass the connector you desire like this example shows:

{% highlight php linenos %}
<?php
$builder->where('FirstName', '=', 'John', 'OR');
{% endhighlight %}

A BETWEEN operator is created by:

{% highlight php linenos %}
<?php
$builder->where('DateCreated', 'BETWEEN', array('2011-01-01 00:00:00', '2012-12-31 23:59:59'));
{% endhighlight %}

An IN operator is created in the same way:

{% highlight php linenos %}
<?php
$builder->where('FirstName', 'IN', array('Matthew', 'Mark', 'Luke', 'John'));
{% endhighlight %}

##### where_block

A where block can be added with the following call:

{% highlight php linenos %}
<?php
$builder->where_block('(');
{% endhighlight %}

A where block can be closed by:

{% highlight php linenos %}
<?php
$builder->where_block(')');
{% endhighlight %}

To change the connector, use the following call:

{% highlight php linenos %}
<?php
$builder->where_block('(', 'OR');
{% endhighlight %}

##### order_by

If you choose to add an order by clause to your delete statement, do so in the following way:

{% highlight php linenos %}
<?php
$builder->order_by('LastName');
{% endhighlight %}

You may specify the sort direction explicitly if you want like so:

{% highlight php linenos %}
<?php
$builder->order_by('LastName', 'DESC');
{% endhighlight %}

If situations where you need to specify how NULLs will be treated, use the following call:

{% highlight php linenos %}
<?php
$builder->order_by('LastName', 'DESC', 'LAST');
{% endhighlight %}

##### limit

You can also limit a delete statement.

{% highlight php linenos %}
<?php
$builder->limit(5);
{% endhighlight %}

##### offset

An offset may also be specified by using a call similar to this one:

{% highlight php linenos %}
<?php
$builder->offset(20);
{% endhighlight %}








