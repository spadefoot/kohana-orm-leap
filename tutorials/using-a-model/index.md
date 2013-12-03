---
title: Installing LEAP
layout: default
menu_item: tutorials
---

### Using a Model

In LEAP, a model represents a row (i.e. a record) in database table with some added functionality. The easiest way to create a model is to use the following factory method:

{% highlight php linenos %}
<?php
$model = DB_ORM::model('user');
{% endhighlight %}

This will create an empty 'user' model. You can then set the model's fields as you see fit. To set a model's field, do the following:

{% highlight php linenos %}
<?php
$data = array(
   'Username' => 'spadefoot',
   'Password' => sha1('a5b4c3d2e1'),
   'FirstName' => 'John',
   'LastName' => 'Smith',
   'IsActive' => TRUE,
);
$model->load($data);
{% endhighlight %}

A model can also be loaded in the following fashion:

If you would like to populate the model with data from your database, you can use the record's primary key to load the data into your model:

{% highlight php linenos %}
<?php
$model = DB_ORM::model('user', array('15'));
{% endhighlight %}

In this example, you are populating the model with the table record that's primary key is equal to '15'. The reason you have to wrap the primary key in an array is because LEAP is capable of using composite primary keys, which other ORMs for Kohana cannot. This array is treated by LEAP as an ordered list so fields names are not need to be declared but the list order must line up with the model's primary key.

Another way to load data from a database is to first set the model's properties and then call the load function on the model.

{% highlight php linenos %}
<?php
$model = DB_ORM::model('user');
$model->ID = 15;
$model->load();
{% endhighlight %}

Once your model is loaded with data you want, you can manipulate that data as needed. When you done manipulating your model, you can save your model's data to the database by using the save function, which acts like an upsert statement.

{% highlight php linenos %}
<?php
$model->save();
{% endhighlight %}

LEAP also allows you to reload the record from the database after you save the record to it should it necessary. This is particularly handy when a database table has a trigger on it that the record to be changed when inserting or updating. To reload the model, pass TRUE:

{% highlight php linenos %}
<?php
$model->save(TRUE);
{% endhighlight %}

In cases when you need to delete a record, you can use the same model. To delete a record, you just have to make sure that all fields making up the primary key are defined and then make a call to the delete function.

{% highlight php linenos %}
<?php
$model = DB_ORM::model('user');
$model->ID = 15;
$model->delete();
{% endhighlight %}

If you just want to empty out the data in your model, you can call the reset function.

{% highlight php linenos %}
<?php
$model->reset();
{% endhighlight %}

However, should you want to convert your model into an array, you can call the as_array function. Do note that relations will pass with this conversion but will not be converted.

{% highlight php linenos %}
<?php
$data = $model->as_array();
{% endhighlight %}

Besides using a model to load, save, and delete records, a model can be used to check if a particular field, alias, relation, or adaptor has been defined.

{% highlight php linenos %}
<?php
echo Debug::vars($model->is_field('Username'), $model->is_alias('Username'), $model->is_relation('Username'), $model->is_adaptor('Username'));
{% endhighlight %}
