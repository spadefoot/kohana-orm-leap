---
title: Mapping a Model
layout: default
menu_item: tutorials
---

### Mapping a Model

#### Defining Fields

Fields are defined in the model's constructor and are a representation of a table's columns. LEAP provides 13 pre-defined field types (which enforce the integrity of the data being stored and/or accessed): Binary, Bit, Blob, Boolean, Data, Date, DateTime, Decimal, Double, Integer, String, Text, and Time fields.

##### Binary Field

{% highlight php linenos %}
<?php
$this->fields = array(
   ...
   'FieldName' => new DB_ORM_Field_Binary($this, array(
       'callback' => 'callback_function',
       'default' => '0101010101010101',
       'label' => 'My Label',
       'max_length' => 16,
       'nullable' => FALSE,
       'savable' => TRUE,
   )),
   ...
);
{% endhighlight %}

##### Bit Field

{% highlight php linenos %}
<?php
$this->fields = array(
   ...
   'FieldName' => new DB_ORM_Field_Bit($this, array(
       'callback' => 'callback_function',
       'default' => 0x0FF0FFF0F,
       'label' => 'My Label',
       'nullable' => FALSE,
       'pattern' => array('A' => 1, 'B' => 4, 'C' => 7, 'D' => 12, 'E' => 8),
       'savable' => TRUE,
   )),
   ...
);
{% endhighlight %}

##### Blob Field

{% highlight php linenos %}
<?php
$this->fields = array(
   ...
   'FieldName' => new DB_ORM_Field_Blob($this, array(
       'callback' => 'callback_function',
       'default' => new Data('Sample Textual Data', Data::STRING_DATA),
       'label' => 'My Label',
       'nullable' => FALSE,
       'savable' => TRUE,
   )),
   ...
);
{% endhighlight %}

##### Boolean Field

{% highlight php linenos %}
<?php
$this->fields = array(
   ...
   'FieldName' => new DB_ORM_Field_Boolean($this, array(
       'callback' => 'callback_function',
       'default' => TRUE,
       'label' => 'My Label',
       'nullable' => FALSE,
       'savable' => TRUE,
   )),
   ...
);
{% endhighlight %}

##### Date Field

{% highlight php linenos %}
<?php
$this->fields = array(
   ...
   'FieldName' => new DB_ORM_Field_Date($this, array(
       'callback' => 'callback_function',
       'control' => 'select',
       'default' => '2011-12-24',
       'enum' => array('2011-12-24', '2011-12-25'),
       'label' => 'My Label',
       'nullable' => FALSE,
       'savable' => TRUE,
   )),
   ...
);
{% endhighlight %}

##### DateTime Field

{% highlight php linenos %}
<?php
$this->fields = array(
   ...
   'FieldName' => new DB_ORM_Field_DateTime($this, array(
       'callback' => 'callback_function',
       'control' => 'select',
       'default' => '2011-12-24 00:00:00',
       'enum' => array('2011-12-24 00:00:00', '2011-12-25 00:00:00'),
       'label' => 'My Label',
       'nullable' => FALSE,
       'savable' => TRUE,
   )),
   ...
);
{% endhighlight %}

##### Decimal Field

{% highlight php linenos %}
<?php
$this->fields = array(
   ...
   'FieldName' => new DB_ORM_Field_Decimal($this, array(
       'callback' => 'callback_function',
       'control' => 'select',
       'default' => 60.00,
       'enum' => array(0.00, 20.00, 40.00, 60.00, 80.00, 100.00),
       'label' => 'My Label',
       'nullable' => FALSE,
       'precision' => 15,
       'savable' => TRUE,
       'scale' => 2,
   )),
   ...
);
{% endhighlight %}

##### Double Field

{% highlight php linenos %}
<?php
$this->fields = array(
   ...
   'FieldName' => new DB_ORM_Field_Double($this, array(
       'callback' => 'callback_function',
       'control' => 'select',
       'default' => 60.00,
       'enum' => array(0.00, 20.00, 40.00, 60.00, 80.00, 100.00),
       'label' => 'My Label',
       'max_decimals' => 2,
       'max_digits' => 15,
       'nullable' => FALSE,
       'range' => array(0.00, 100.00),
       'savable' => TRUE,
       'unsigned' => FALSE,
   )),
   ...
);
{% endhighlight %}

##### Integer Field

{% highlight php linenos %}
<?php
$this->fields = array(
   ...
   'FieldName' => new DB_ORM_Field_Integer($this, array(
       'callback' => 'callback_function',
       'control' => 'select',
       'default' => 60,
       'enum' => array(0, 20, 40, 60, 80, 100),
       'label' => 'My Label',
       'max_length' => 11,
       'nullable' => FALSE,
       'range' => array(0, 100),
       'savable' => TRUE,
       'unsigned' => FALSE,
   )),
   ...
);
{% endhighlight %}

##### String Field

{% highlight php linenos %}
<?php
$this->fields = array(
   ...
   'FieldName' => new DB_ORM_Field_String($this, array(
       'callback' => 'callback_function',
       'control' => 'select',
       'default' => 'data',
       'enum' => array('data', 'text'),
       'label' => 'My Label',
       'max_length' => 5,
       'nullable' => FALSE,
       'regex' => '/^regex(pr)?$/i',
       'savable' => TRUE,
   )),
   ...
);
{% endhighlight %}

##### Text Field

{% highlight php linenos %}
<?php
$this->fields = array(
   ...
   'FieldName' => new DB_ORM_Field_Text($this, array(
       'callback' => 'callback_function',
       'default' => 'data',
       'label' => 'My Label',
       'nullable' => FALSE,
       'savable' => TRUE,
   )),
   ...
);
{% endhighlight %}

##### Time Field

{% highlight php linenos %}
<?php
$this->fields = array(
   ...
   'FieldName' => new DB_ORM_Field_Time($this, array(
       'callback' => 'callback_function',
       'control' => 'select',
       'default' => '12:00:00',
       'enum' => array('00:00:00', '12:00:00', '23:00:00'),
       'label' => 'My Label',
       'nullable' => FALSE,
       'savable' => TRUE,
   )),
   ...
);
{% endhighlight %}

#### Defining Field Adaptors

An adaptor makes working with certain data easier. The idea behind an adaptor is to process data that would otherwise be a pain to work with. An adaptor helps to minimize the amount of coding that you have to do over and over again without one. Adaptors are not fields, they just act as an interface for saving and fetching data from a field. Since there are some common types of data that developers typically deal with on a day-to-day basis, LEAP supplies a number of adaptors to handle such data.

##### Boolean Field Adaptor

{% highlight php linenos %}
<?php
$this->adaptors = array(
   ...
   'AdaptorName' => new DB_ORM_Field_Adaptor_Boolean($this, array(
       'field' => 'answer',
       'values' => array('good', 'bad'),
   )),
   ...
);
{% endhighlight %}

##### DateTime Field Adaptor

{% highlight php linenos %}
<?php
$this->adaptors = array(
   ...
   'AdaptorName' => new DB_ORM_Field_Adaptor_DateTime($this, array(
       'field' => 'DateModified',
       'format' => 'Y-m-d H:i:s',
   )),
   ...
);
{% endhighlight %}

##### Encryption Field Adaptor

{% highlight php linenos %}
<?php
$this->adaptors = array(
   ...
   'AdaptorName' => new DB_ORM_Field_Adaptor_Encryption($this, array(
       'config' => 'default',
       'field' => 'password',
   )),
   ...
);
{% endhighlight %}

##### GZ Field Adaptor

{% highlight php linenos %}
<?php
$this->adaptors = array(
   ...
   'AdaptorName' => new DB_ORM_Field_Adaptor_GZ($this, array(
       'field' => 'image',
       'level' => 9,
   )),
   ...
);
{% endhighlight %}

##### JSON Field Adaptor

{% highlight php linenos %}
<?php
$this->adaptors = array(
   ...
   'AdaptorName' => new DB_ORM_Field_Adaptor_JSON($this, array(
       'field' => 'data',
       'prefix' => 'while(1); [',
       'suffix' => ']',
   )),
   ...
);
{% endhighlight %}

##### List Field Adaptor

{% highlight php linenos %}
<?php
$this->adaptors = array(
   ...
   'AdaptorName' => new DB_ORM_Field_Adaptor_List($this, array(
       'delimiter' => ';',
       'field' => 'roles',
   )),
   ...
);
{% endhighlight %}

##### Number Field Adaptor

{% highlight php linenos %}
<?php
$this->adaptors = array(
   ...
   'AdaptorName' => new DB_ORM_Field_Adaptor_Number($this, array(
       'delimiter' => ',',
       'field' => 'balance',
       'precision' => 2,
       'separator' => '.',
   )),
   ...
);
{% endhighlight %}

##### Object Field Adaptor

{% highlight php linenos %}
<?php
$this->adaptors = array(
   ...
   'AdaptorName' => new DB_ORM_Field_Adaptor_Object($this, array(
       'class' => 'MyObject',
       'field' => 'data',
   )),
   ...
);
{% endhighlight %}

##### UOM Field Adaptor

{% highlight php linenos %}
<?php
$this->adaptors = array(
   ...
   'AdaptorName' => new DB_ORM_Field_Adaptor_UOM($this, array(
       'field' => 'freight_weight',
       'measurement' => 'weight',
       'units' => array('pounds', 'kilograms'),
   )),
   ...
);
{% endhighlight %}

##### XML Field Adaptor

{% highlight php linenos %}
<?php
$this->adaptors = array(
   ...
   'AdaptorName' => new DB_ORM_Field_Adaptor_XML($this, array(
       'field' => 'data',
   )),
   ...
);
{% endhighlight %}

#### Defining Field Aliases

Many database tables have poorly named fields and there are some circumstances where it is necessary to use another name to reference certain fields. LEAP, therefore, offers the ability to use aliases to reference to such fields. Below is an example of how aliases are declared in LEAP:

{% highlight php linenos %}
<?php
$this->aliases = array(
   ...
   'AliasName' => new DB_ORM_Field_Alias($this, 'FieldName');
   ...
);
{% endhighlight %}

#### Defining Relations

By creating a relation, you can access data from another table. LEAP allows for three types of relations: belongs to, has many, and has one relations.

##### Belongs To Relation

{% highlight php linenos %}
<?php
$this->relations = array(
   ...
   'RelationName' => new DB_ORM_Relation_BelongsTo($this, array(
       'child_key' => array('Address_ID'),
       'parent_key' => array('ID'),
       'parent_model' => 'address',
   )),
   ...
);
{% endhighlight %}

##### Has Many Relation

{% highlight php linenos %}
<?php
$this->relations = array(
   ...
   'RelationName' => new DB_ORM_Relation_HasMany($this, array(
       'child_key' => array('User_ID'),
       'child_model' => 'address',
       'options' => array(
           array('where', array('Addresses.User_ID', '>=', '10')),
           array('order_by', array('Addresses.ID', 'DESC')),
           array('limit', array(5)),
       ),
       'parent_key' => array('ID'),
   )),
   ...
);
{% endhighlight %}

##### Has Many Through Relation

{% highlight php linenos %}
<?php
$this->relations = array(
   ...
   'RelationName' => new DB_ORM_Relation_HasMany($this, array(
       'child_key' => array('rID'),
       'child_model' => 'role',
       'options' => array(
           array('where', array('rID', '>=', '10')),
           array('order_by', array('rID', 'DESC')),
           array('limit', array(5)),
       ),
       'parent_key' => array('uID'),
       'through_keys' => array(
           array('uID'), // [0] matches with parent
           array('rID'), // [1] matches with child
       ),
       'through_model' => 'user_role',
   )),
   ...
);
{% endhighlight %}

##### Has One Relation

{% highlight php linenos %}
<?php
$this->relations = array(
   ...
   'RelationName' => new DB_ORM_Relation_HasOne($this, array(
       'child_key' => array('User_ID'),
       'child_model' => 'log',
       'parent_key' => array('ID'),
   )),
   ...
);
{% endhighlight %}