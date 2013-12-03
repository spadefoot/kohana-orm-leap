---
title: Installing LEAP
layout: default
menu_item: tutorials
---

### Extending the Query Builder

The LEAP ORM is capable of extending the query builder for a particular ORM model. This capability allows one to encapsulate common build instructions into one function call and to reduce typing.

To extend a particular ORM model's builder, create a class that extends DB_ORM_Builder and add the new build instructions like so:

{% highlight php linenos %}
<?php defined('SYSPATH') or die('No direct script access.');

class Builder_Leap_User extends DB_ORM_Builder {

   public function has_credentials($username, $password) {
       $this->builder->where('Username', '=', trim($username));
       $this->builder->where('Password', '=', sha1(trim($password)));
       return $this;
   }

   public function is_active($status = TRUE) {
       $this->builder->where('IsActive', '=', (bool) $status);
       return $this;
   }

}
{% endhighlight %}

Now, use DB_ORM to create a query builder for the specified model and call the newly defined build instructions as the following example demonstrates:

{% highlight php linenos %}
<?php
$results = DB_ORM::select('user')
   ->has_credentials($username, $password)
   ->is_active()
   ->query();
{% endhighlight %}

