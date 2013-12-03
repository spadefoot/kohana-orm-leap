---
title: Installing LEAP
layout: default
menu_item: tutorials
---

### Defining a Model

By default, all models for the LEAP ORM are stored in 'classes/model/leap' so that they do not conflict with any other models you have created to work with other ORM modules. This means that the class name for all LEAP models are prefixed with 'Model_Leap_'.

Each LEAP model must extend 'DB_ORM_Model'. This class's constructor will define the fields for the model. It will also define all aliases, adaptors, and relations. A model may also override three methods to define which data source the model will use, the table's name, and the table's primary key.

Below is an example of a typical LEAP model:

{% highlight php linenos %}
<?php defined('SYSPATH') or die('No direct script access.');

class Model_Leap_User extends DB_ORM_Model {

   public function __construct() {
       parent::__construct();
       $this->fields = array(
           'ID' => new DB_ORM_Field_Integer($this, array(
               'max_length' => 11,
               'nullable' => FALSE,
               'unsigned' => TRUE,
           )),
           'Username' => new DB_ORM_Field_String($this, array(
               'max_length' => 50,
               'nullable' => FALSE,
           )),
           'Password' => new DB_ORM_Field_String($this, array(
               'max_length' => 32,
               'nullable' => FALSE,
           )),
           'FirstName' => new DB_ORM_Field_String($this, array(
               'max_length' => 35,
               'nullable' => FALSE,
           )),
           'LastName' => new DB_ORM_Field_String($this, array(
               'max_length' => 35,
               'nullable' => FALSE,
           )),
           'IsActive' => new DB_ORM_Field_Boolean($this, array(
               'default' => TRUE,
               'nullable' => FALSE,
           )),
       );
       $this->relations = array(
           'Roles' => new DB_ORM_Relation_HasMany($this, array(
               'child_key' => array('User_ID'),
               'child_model' => 'role',
           )),
       );
   }

   public static function data_source($instance = 0) {
       return ($instance > DB_DataSource::MASTER_INSTANCE) ? 'slave' : 'master';
   }

   public static function table() {
       return 'user';
   }

   public static function primary_key() {
       return array('ID');
   }

}
{% endhighlight %}

For more information on how to map fields, aliases, adaptors, and relations, see the tutorial on [mapping models]().

There are some other static methods worth mentioning, e.g. DB_ORM_Model::is_auto_incremented() and DB_ORM_Model::is_savable(). You should overload DB_ORM_Model::is_auto_incremented() when your table's primary key is not a composite key and is a non-integer. To overload it, do the following:

{% highlight php linenos %}
<?php
public static function is_auto_incremented() {
    return FALSE;
}
{% endhighlight %}

The purpose of overloading DB_ORM_Model::is_savable() is to prevent a model from attempting to delete or save a record. This is particularly useful with reference tables. It can be overloaded like so:

{% highlight php linenos %}
<?php
public static function is_savable() {
    return FALSE;
}
{% endhighlight %}