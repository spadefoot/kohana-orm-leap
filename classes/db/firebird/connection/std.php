<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Copyright 2011 Spadefoot
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * This class handles a standard Firebird connection.
 *
 * Firebird installation instruction:
 *
 *     	To install interbase (aka ibase) copy C:\Program Files\FishBowl\Client\bin\fbclient.dll
 *		into "C:\WINDOWS\system32\" and rename file to gds32.dll.
 *
 *     	Edit C:\WINDOWS\system32\drivers\etc\services by appending to the end the following:
 *     	gds_db           3050/tcp    fb                     #Firebird
 *
 *     	Restart either Apache or the computer
 *
 * @package Leap
 * @category Firebird
 * @version 2011-06-20
 *
 * @see http://us3.php.net/manual/en/book.ibase.php
 * @see http://us2.php.net/manual/en/ibase.installation.php
 * @see http://www.firebirdfaq.org/faq227/
 */
class DB_Firebird_Connection_Std extends Base_DB_Firebird_Connection_Std { }
?>