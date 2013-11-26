---
title: Installing LEAP
layout: default
menu_item: tutorials
---

### Working with result sets

LEAP returns all queried data in a result set. The returned result set is essentially a wrapper class around a PHP array with a set of convenience functions.

The mostly commonly used convenience function is DB_ResultSet::count(). This function maintains the number of records stored in the result set. Unlike some other count() functions in PHP, the count() in this class does not have to recalculate every time it is called. It is called like so:

{% highlight php linenos %}
<?php
$results->count();
{% endhighlight %}

Another commonly used convenience function is DB_ResultSet::is_loaded(). It returns whether any records were fetched from the database. To call this function, it is called like so:

{% highlight php linenos %}
<?php
if ($results->is_loaded()) {
	// do something
}
{% endhighlight %}

LEAP's result set can also act like an array in that it can be access like one and looped through as one. There are two ways data can be access:

#### Method #1

{% highlight php linenos %}
<?php
$record = $results->fetch(0);
{% endhighlight %}

#### Method #2

{% highlight php linenos %}
<?php
$record = $results[0];
{% endhighlight %}

To loop through the results, use the foreach loop.

{% highlight php linenos %}
<?php
foreach ($results as $record) {
    // do something
}
{% endhighlight %}

The results can also be looped through using the while loop:

{% highlight php linenos %}
<?php
while ($record = $results->fetch()) {
    // do something
}
{% endhighlight %}

Likewise, the results can be looped through using the traditional for loop:

{% highlight php linenos %}
<?php
for ($i = 0; $i < $results->count(); $i++) {
    $record = $results[$i];
    // do something
}
{% endhighlight %}

An added feature to LEAP's result set is that you can also get at a particular column's value in the current row by using the DB_ResultSet::get(). A default value can also be set should no value be found for the specified column.

{% highlight php linenos %}
<?php
$value = $results->get('FirstName', '');
{% endhighlight %}

If you prefer to work with the result set as an array, you can call as_array() like so:

{% highlight php linenos %}
<?php
$records = $results->as_array();
{% endhighlight %}

Similarly, you can call as_csv() to dump a result set into LEAP's CSV class, which can be used to export query data.

{% highlight php linenos %}
<?php
$csv = $results->as_csv(array(
   'file_name' => 'data.csv',
   'default_headers' => TRUE,
   'delimiter' => ',',
   'enclosure' => '"',
   'eol' => "\n",
));
$csv->output(TRUE);
{% endhighlight %}

