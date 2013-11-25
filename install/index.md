---
title: Installing LEAP
layout: default
menu_item: install
---

### Installing the LEAP ORM Module

Since the LEAP ORM was written for the Kohana PHP Framework, it is ease to install. The installation process will only take a few minutes to get up and working provided that your server is already setup to run the necessary drivers. To start using LEAP, follow these steps:

#### Step #1: Download Source Code

All of the source code to the <a href="https://github.com/spadefoot/kohana-orm-leap">LEAP ORM module for Kohana</a> is available on github.

#### Step #2: Unarchive/Unzip Files

Locate the download in your explorer window (or in finder) and right-click. Select the unarchive/unzip option on the context menu. Once the download is unarchived/unzipped, rename the newly created folder to "leap" using all lowercase letters for the folder name.

#### Step #3: Enable Module in Bootstrap

After the module placed in the modules folder, you will need to enable the "leap" module in your application/bootstrap.php file. This is done by modifying the bootstrap by adding the "leap" module to array argument in the call to Kohana::modules().

{% highlight php linenos %}
Kohana::modules(array(
    ...
    'leap' => MODPATH . 'leap',
    ...
));
{% endhighlight %}

#### Step #4: Setup Database Connections

The next step will be to [setup your database connections](http://orm.spadefootcode.com/tutorials/setting-up-a-database-connection/) in your [config/database.php](https://github.com/spadefoot/kohana-orm-leap/tree/3.2/master/config).  Once your database connections are setup, you should be able to use any [database connection classes](http://orm.spadefootcode.com/tutorials/establishing-a-database-connection/).

#### Step #5: Start Writing ORM Models

However, before you can start using LEAP's ORM you will need to <a href="http://orm.spadefootcode.com/tutorials/defining-a-model/">create models</a> for each one of your database tables.  These models will then allow you to take full advantage of all of LEAP's functionality.