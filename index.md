---
title: Hello, World!
layout: default
menu_item: home
---

### Leap ORM for the Kohana PHP Framework

The <a href="https://github.com/spadefoot/kohana-orm-leap">Leap ORM</a> is a powerful new module for <a href="http://kohanaframework.org/">Kohana</a> <a href="https://github.com/kohana/kohana/tree/3.3/master">3.3.X</a>, <a href="https://github.com/kohana/kohana/tree/3.2/master">3.2.X</a>, and <a href="https://github.com/kohana/kohana/tree/3.1/master">3.1.X</a>.  Designed around both the builder and active record design patterns, this ORM makes working with SQL databases easy.  The Spadefoot Team has worked extensively on making sure that certain components within the module itself are decoupled from each other as much as possible and that new SQL database plugins can be easily added, while maintaining a consistent interface between the core code and the user's implementation.

Leap is NOT just another database ORM.  It is provides a number of features that other ORMs do not.  Leap also provides a way to cleanly add new SQL database drivers and offers a database connection pool to manage its resources. Moreover, Leap addresses a number of dependency issues persisting in other ORMs by following a loosely coupled and tightly integrated programming methodology.  Leap is a full-fledge data access layer with loads of database tools.

### Features

<ul>
		    <li>
		        <span class="feature">Works with all major database.</span><br>
		        <span>Has plugins for <a href="http://publib.boulder.ibm.com/infocenter/db2luw/v8/index.jsp">DB2</a>, <a href="http://www.drizzle.org/">Drizzle</a>, <a href="http://www.firebirdsql.org/">Firebird</a>, <a href="http://mariadb.org/">MariaDB</a>, <a href="http://www.microsoft.com/sqlserver/en/us/default.aspx">MS SQL</a>, <a href="http://www.mysql.com/">MySQL</a>, <a href="http://www.oracle.com/technetwork/developer-tools/sql-developer/overview/index.html">Oracle</a>, <a href="http://www.postgresql.org/">PostgreSQL</a>, and <a href="http://www.sqlite.org/">SQLite</a>.</span>
		    </li>
		    <li>
		        <span class="feature">Standardizes how SQL statements are built.</span><br>
		        <span>Builder classes remove the differences between the various <a href="http://en.wikibooks.org/wiki/SQL_Dialects_Reference">SQL dialects</a>.</span>
		    </li>
            <li>
                <span class="feature">Enforces the data types of fields.</span><br>
                <span>Takes PHP to the next level by encouraging strong data types.</span>
            </li>
		    <li>
		        <span class="feature">Easily extensible.</span><br>
		        <span>Utilizes dependency injection to allow codebase to be extended.</span>
		    </li>
		    <li>
		        <span class="feature">Well documented.</span><br>
		        <span>Plenty of <a href="http://orm.spadefootcode.com/tutorials/index/">examples and tutorials</a>, and has an easy-to-read <a href="http://orm.spadefootcode.com/api/annotated.html">API</a>.</span>
		    </li>
		</ul>

### Comparison

<table class="table-striped table-condensed">
		    <thead>
		        <tr>
		          <td>&nbsp;</td>
		          <td style="color: #B40404; font-weight: bold;">LEAP</td>
		          <td style="font-weight: bold;">K3 ORM</td>
		          <td style="font-weight: bold;">Jelly</td>
		          <td style="font-weight: bold;">Sprig</td>
		        </tr>
		    </thead>
		    <tbody>
		        <tr>
		          <td>Documentation</td>
		          <td style="color: #B40404;">YES</td>
		          <td>YES</td>
		          <td>YES</td>
		          <td>LIMITED</td>
		        </tr>
		        <tr>
		          <td>Consistent API</td>
		          <td style="color: #B40404;">YES</td>
		          <td>MOSTLY</td>
		          <td>MOSTLY</td>
		          <td>YES</td>
		        </tr>
		        <tr>
		          <td>Easily Extensible</td>
		          <td style="color: #B40404;">YES</td>
		          <td>MOSTLY</td>
		          <td>MOSTLY</td>
		          <td>MOSTLY</td>
		        </tr>
		        <tr>
		          <td>Supports Multiple SQL Dialects</td>
		          <td style="color: #B40404;">YES</td>
		          <td>KIND OF</td>
		          <td>KIND OF</td>
		          <td>KIND OF</td>
		        </tr>
		        <tr>
		          <td>Database Connection Pool</td>
		          <td style="color: #B40404;">YES</td>
		          <td>NO</td>
		          <td>NO</td>
		          <td>NO</td>
		        </tr>
		        <tr>
		          <td>Query Caching</td>
		          <td style="color: #B40404;">YES</td>
		          <td>YES</td>
		          <td>YES</td>
		          <td>YES</td>
		        </tr>
		        <tr>
		          <td>Query Builder</td>
		          <td style="color: #B40404;">YES</td>
		          <td>YES</td>
		          <td>YES</td>
		          <td>YES</td>
		        </tr>
		        <tr>
		          <td>Strongly Typed</td>
		          <td style="color: #B40404;">YES</td>
		          <td>NO</td>
		          <td>MOSTLY</td>
		          <td>MOSTLY</td>
		        </tr>
		        <tr>
		          <td>Non-Integer Primary Keys</td>
		          <td style="color: #B40404;">YES</td>
		          <td>NO</td>
		          <td>NO</td>
		          <td>YES</td>
		        </tr>
		        <tr>
		          <td>Composite Keys</td>
		          <td style="color: #B40404;">YES</td>
		          <td>NO</td>
		          <td>NO</td>
		          <td>PARTIALLY</td>
		        </tr>
		        <tr>
		          <td>Field Aliases</td>
		          <td style="color: #B40404;">YES</td>
		          <td>NO</td>
		          <td>YES</td>
		          <td>NO</td>
		        </tr>
		        <tr>
		          <td>Field Adaptors</td>
		          <td style="color: #B40404;">YES</td>
		          <td>NO</td>
		          <td>NO</td>
		          <td>NO</td>
		        </tr>
		        <tr>
		          <td>Field Relations</td>
		          <td style="color: #B40404;">YES</td>
		          <td>YES</td>
		          <td>YES</td>
		          <td>YES</td>
		        </tr>
		        <tr>
		          <td>Read-Only Models</td>
		          <td style="color: #B40404;">YES</td>
		          <td>NO</td>
		          <td>NO</td>
		          <td>NO</td>
		        </tr>
		        <tr>
		          <td>Auto-Generates HTML Form Labels/Controls</td>
		          <td style="color: #B40404;">YES</td>
		          <td>NO</td>
		          <td>NO</td>
		          <td>YES</td>
		        </tr>
		        <tr>
		          <td>Auth Classes</td>
		          <td style="color: #B40404;">YES</td>
		          <td>YES</td>
		          <td>YES</td>
		          <td>YES</td>
		        </tr>
		        <tr>
		          <td>Modified Preorder Tree Traversal (MPTT)</td>
		          <td style="color: #B40404;">YES</td>
		          <td>YES</td>
		          <td>YES</td>
		          <td>YES</td>
		        </tr>
		    </tbody>
		</table>

<p style="margin-top: 20px; font-size: 12px;">* Please report any discrepancies with this comparison in LEAP's <a href="https://github.com/spadefoot/kohana-orm-leap/issues?sort=comments&amp;direction=desc&amp;state=open">issue tracker</a> on github and, someone will address the correction as soon as possible.</p>