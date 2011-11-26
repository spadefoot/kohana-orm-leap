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
 * This class allows an application to translate a MariaDB statement into another SQL dialect.
 *
 * @package Leap
 * @category MariaDB
 * @version 2011-11-20
 *
 * @abstract
 */
abstract class Base_DB_MariaDB_Translator implements Base_DB_SQL_Translator_Interface {

    /**
    * This variable stores the MariaDB to Firebird SQL conversions mappings.
    *
    * @access protected
    * @static
    * @var array
    */
    protected static $fbsql_conversions = array(
        // MYSQL TO FIREBIRD MAPPINGS
        'ABS' => 'ABS',
        'ACOS' => 'ACOS',
        'ASCII' => 'ASCII_CHAR', // http://www.firebirdsql.org/refdocs/langrefupd21-intfunc-ascii_char.html
        'ASIN' => 'ASIN',
        'ATAN' => 'ATAN',
        'ATN2' => 'ATAN2',
        'AVG' => 'AVG',
        'CEIL' => 'CEILING',
        'CEILING' => 'CEILING',
        'CHAR' => 'ASCII_VAL', // http://www.firebirdsql.org/refdocs/langrefupd21-intfunc-ascii_val.html
        'CHAR_LENGTH' => 'CHAR_LENGTH',
        'CHARACTER_LENGTH' => 'CHAR_LENGTH',
        'COALESCE' => 'COALESCE',
        'COS' => 'COS',
        'COT' => 'COT',
        'CURTIME' => 'CURRENT_TIME',
        'CURRENT_TIME' => 'CURRENT_TIME',
        'CURRENT_TIMESTAMP' => 'CURRENT_TIMESTAMP',
        'FLOOR' => 'FLOOR',
        'GREATEST' => 'MAX',
        'IFNULL' => 'COALESCE',
        'LEAST' => 'MIN',
        'LENGTH' => 'CHAR_LENGTH',
        'LCASE' => 'LOWER',
        'LOWER' => 'LOWER',
        'LPAD' => 'LPAD',
        'MAX' => 'MAX',
        'MIN' => 'MIN',
        'NULLIF' => 'NULLIF',
        'POW' => 'POWER',
        'POWER' => 'POWER',
        'REPEAT' => 'STRREPEAT',
        'RPAD' => 'RPAD',
        'SIGN' => 'SIGN',
        'SIN' => 'SIN',
        'SQRT' => 'SQRT',
        'SUM' => 'SUM',
        'TAN' => 'TAN',
        'UCASE' => 'UPPER',
        'UPPER' => 'UPPER',
        // FIREBIRD ONLY MAPPINGS
        'COSH' => 'COSH',
        'CURRENT_DATE' => 'CURRENT_DATE',
        'SINH' => 'SINH',
        'TANH' => 'TANH',        
    );

    /**
    * This variable stores the MariaDB to MS SQL many-to-one conversions mapping.
    *
    * @access protected
    * @static
    * @var array
    */
    protected static $mssql_conversions = array(
        // MYSQL TO MSSQL MAPPINGS
        'ABS' => 'ABS', // http://msdn.microsoft.com/en-us/library/ms189800.aspx
        'ACOS' => 'ACOS',
        'ASIN' => 'ASIN',
        'ATAN' => 'ATAN',
        'ATN2' => 'ATAN2',
        'AVG' => 'AVG',
        'CAST' => 'CAST', // http://msdn.microsoft.com/en-us/library/ms187928.aspx
        'CEIL' => 'CEILING',
        'CEILING' => 'CEILING',
        'CHAR_LENGTH' => 'LEN', // http://msdn.microsoft.com/en-us/library/ms190329.aspx
        'CHARACTER_LENGTH' => 'LEN', // http://msdn.microsoft.com/en-us/library/ms190329.aspx
        'COALESCE' => 'COALESCE',
        'CONVERT' => 'CONVERT', // http://msdn.microsoft.com/en-us/library/ms187928.aspx
        'COS' => 'COS',
        'COT' => 'COT',
        'COUNT' => 'COUNT', // http://msdn.microsoft.com/en-us/library/ms175997.aspx
        'CURRENT_TIMESTAMP' => 'CURRENT_TIMESTAMP',
        'DATE_ADD' => 'DATEADD',
        'DATE_SUB' => 'DATEDIFF',
        'DATEDIFF' => 'DATEDIFF',        
        'EXP' => 'EXP',
        'EXTRACT' => 'DATEPART', // http://msdn.microsoft.com/en-us/library/ms174420.aspx
        'FLOOR' => 'FLOOR',
        'IFNULL' => 'ISNULL', // http://msdn.microsoft.com/en-us/library/ms184325.aspx
        'LCASE' => 'LOWER',
        'LENGTH' => 'LEN', // http://msdn.microsoft.com/en-us/library/ms190329.aspx
        'LOG10' => 'LOG10',
        'LOWER' => 'LOWER',
        'LPAD' => 'LPAD',
        'MAX' => 'MAX',
        'MIN' => 'MIN',
        'NOW' => 'GETDATE',
        'NULLIF' => 'NULLIF',
        'POW' => 'POWER',
        'POWER' => 'POWER',
        'RAND' => 'RAND',
        'REPEAT' => 'REPLICATE', // http://msdn.microsoft.com/en-us/library/ms174383.aspx
        'RPAD' => 'RPAD',
        'SIGN' => 'SIGN', // http://msdn.microsoft.com/en-us/library/ms188420.aspx
        'SIN' => 'SIN',
        'SOUNDEX' => 'SOUNDEX', // http://msdn.microsoft.com/en-us/library/ms187384.aspx
        'SPACE' => 'SPACE',
        'SQRT' => 'SQRT',
        'SUM' => 'SUM',
        'TAN' => 'TAN',
        'UCASE' => 'UPPER',
        'UPPER' => 'UPPER',
        'YEAR' => 'YEAR',
    );

    // TODO
    // -- EXP(x)
    // -- LN | LOG
    // -- MOD(x, y)
    // -- RAND(x)
    // -- ROUND(x[, d])

    /**
    * This variable stores the original MariaDB statement.
    *
    * @access public
    * @var string
    */
    protected $sql = NULL;

    /**
    * This constructor creates an instance of this class.
    *
    * @access public
    * @param string $sql                            the MariaDB statement to be translated
    */
    public function __construct($sql) {
        $this->sql = $sql;
    }

    /**
    * This function translates the MariaDB statement to a Firebird SQL statement.
    *
    * @access public
    * @return string                                the translated Firebird SQL statement
    */
    public function fbsql() {
        $SQLTokenizer = new SQLTokenizer($this->sql, TRUE);
        $tuples = $SQLTokenizer->get_tuples();
        unset($SQLTokenizer);

        self::mysql_to_fbsql($tuples);
        
        $sql = '';
        for ($i = 0; $i < $tuples->count(); $i++) {
            $tuple = $tuples->get_element($i);
            $sql .= $tuple[0];
        }
        return $sql;
    }

    /**
    * This function translates the MariaDB statement to a MS SQL statement.
    *
    * @access public
    * @return string                                the translated MS SQL statement
    */
    public function mssql() {
        $SQLTokenizer = new SQLTokenizer($this->sql, TRUE);
        $tuples = $SQLTokenizer->get_tuples();
        unset($SQLTokenizer);

        self::mysql_to_mssql($tuples);
        
        $sql = '';
        for ($i = 0; $i < $tuples->count(); $i++) {
            $tuple = $tuples->get_element($i);
            $sql .= $tuple[0];
        }
        return $sql;
    }

    /**
    * This function returns a cleaned-up version of the MariaDB statement.
    *
    * @access public
    * @return string                                the cleaned-up MariaDB statement
    */
    public function mysql() {
        $SQLTokenizer = new SQLTokenizer($this->sql, TRUE);
        $tuples = $SQLTokenizer->get_tuples();
        unset($SQLTokenizer);
        
        $sql = '';
        for ($i = 0; $i < $tuples->count(); $i++) {
            $tuple = $tuples->get_element($i);
            $sql .= $tuple[0];
        }
        return $sql;
    }

    ///////////////////////////////////////////////////////////////HELPERS//////////////////////////////////////////////////////////////

    /**
    * This function parses the tokens in the specified Vector of tuples and translates
    * them so that tuples can be reconstructed to build an SQL statement for a Firebird
    * database.
    *
    * @access public
    * @static
    * @param Vector &$tuples            a collection of tuples
    * @param integer $index             the index to be begin parsing at
    * @return integer                   the last index processed
    */
    protected static function mysql_to_fbsql(Vector &$tuples, $index = 0) {
        $start = -1;
        while ($index < $tuples->count()) {
            $tuple = $tuples->get_element($index);
            $tuple[0] = strtoupper($tuple[0]);
            if ($tuple[0] == '(') {
                $index++;
                $index = self::mysql_to_fbsql($tuples, $index);
            }
            else if ($tuple[0] == ')') {
                return $index;
            }
            else if (isset(self::$fbsql_conversions[$tuple[0]])) {
                $tuples->set_element($index, array(self::$fbsql_conversions[$tuple[0]], SQLTokenizer::OTHER_TOKEN));
            }
            else if (preg_match('/^CONCAT$/', $tuple[0])) {
                $tuples->remove_index($index);
                $matches = 0;
                for ($position = $index; $position < $tuples->count(); $position++) {
                    $tuple = $tuples->get_element($position);
                    if ($tuple[0] == '(') {
                        if ($matches == 0) {
                            $tuples->remove_index($position);
                            $position--;
                        }
                        $matches++;
                    }
                    else if ($tuple[0] == ')') {
                        $matches--;
                        if ($matches == 0) {
                            $tuples->remove_index($position);
                            break;
                        }
                    }
                    else if (($matches == 1) && ($tuple[0] == ',')) {
                        $tuples->set_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                        $position++;
                        $tuples->insert_element($position, array('||', SQLTokenizer::OTHER_TOKEN));
                    }
                }
                $index--;
            }
            else if (preg_match('/^CONVERT$/', $tuple[0])) {
                $tuples->set_element($index, array('CAST', SQLTokenizer::OTHER_TOKEN));
                $matches = 0;
                for ($position = $index + 1; $position < $tuples->count(); $position++) {
                    $tuple = $tuples->get_element($position);
                    if ($tuple[0] == '(') {
                        $matches++;
                    }
                    else if ($tuple[0] == ')') {
                        $matches--;
                        if ($matches == 0) {
                            break;
                        }
                    }
                    else if (($matches == 1) && ($tuple[0] == ',')) {
                        $tuples->set_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                        $position++;
                        $tuples->insert_element($position, array('AS', SQLTokenizer::OTHER_TOKEN));
                    }
                }
            }
            else if (preg_match('/^IF$/', $tuple[0])) { // http://www.firebirdsql.org/manual/nullguide-conditionals-loops.html
                $tuples->set_element($index, array('CASE', SQLTokenizer::OTHER_TOKEN));
                $matches = 0;
                $commas = 0;
                for ($position = $index + 1; $position < $tuples->count(); $position++) {
                    $tuple = $tuples->get_element($position);
                    if ($tuple[0] == '(') {
                        if ($matches == 0) {
                            $tuple = $tuples->get_element($position - 1);
                            if ($tuple[1] != SQLTokenizer::WHITESPACE_TOKEN) {
                                $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                                $position++;
                            }
                            $tuples->set_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                            $position++;
                            $tuples->insert_element($position, array('WHEN', SQLTokenizer::OTHER_TOKEN));
                            $position++;
                            $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                        }
                        $matches++;
                    }
                    else if ($tuple[0] == ')') {
                        $matches--;
                        if ($matches == 0) {
                            $tuples->set_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                            $position++;
                            $tuples->insert_element($position, array('END', SQLTokenizer::OTHER_TOKEN));
                            $next = $position + 1;
                            if ($next < $tuples->count()) {
                                $tuple = $tuples->get_element($next);
                                if ($tuple[1] != SQLTokenizer::WHITESPACE_TOKEN) {
                                    $position++;
                                    $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                                }
                            }
                            break;
                        }
                    }
                    else if (($matches == 1) && ($tuple[0] == ',')) {
                        if ($commas == 0) {
                            $tuples->set_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                            $position++;
                            $tuples->insert_element($position, array('THEN', SQLTokenizer::OTHER_TOKEN));
                            $next = $position + 1;
                            if ($next < $tuples->count()) {
                                $tuple = $tuples->get_element($next);
                                if ($tuple[1] != SQLTokenizer::WHITESPACE_TOKEN) {
                                    $position++;
                                    $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                                }
                            }
                        }
                        else if ($commas == 1) {
                            $tuples->set_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                            $position++;
                            $tuples->insert_element($position, array('ELSE', SQLTokenizer::OTHER_TOKEN));
                            $next = $position + 1;
                            if ($next < $tuples->count()) {
                                $tuple = $tuples->get_element($next);
                                if ($tuple[1] != SQLTokenizer::WHITESPACE_TOKEN) {
                                    $position++;
                                    $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                                }
                            }
                        }
                        $commas++;
                    }
                }
            }
            else if ($start >= 0) {
                if (preg_match('/^LIMIT$/', $tuple[0])) {
                    $limit = $tuples->get_element($index + 2);
                    $tuples->remove_range($index - 1, $index + 2);
                    $position = $start + 1;
                    $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                    $position++;
                    $tuples->insert_element($position, array('FIRST', SQLTokenizer::OTHER_TOKEN));
                    $position++;
                    $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                    $position++;
                    $tuples->insert_element($position, $limit);
                    $start = $position;
                    $index += 2;
                }
                else if (preg_match('/^OFFSET$/', $tuple[0])) {
                    $offset = $tuples->get_element($index + 2);
                    $tuples->remove_range($index - 1, $index + 2);
                    $position = $start + 1;
                    $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                    $position++;
                    $tuples->insert_element($position, array('SKIP', SQLTokenizer::OTHER_TOKEN));
                    $position++;
                    $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                    $position++;
                    $tuples->insert_element($position, $offset);
                    $start = $position;
                    $index += 2;
                }
            }
            else if (preg_match('/^(SELECT|INSERT|DELETE|UPDATE)$/', $tuple[0])) {
                $start = $index;
            }
            $index++;
        }
        return $index;
    }

    /**
    * This function parses the tokens in the specified Vector of tuples and translates
    * them so that tuples can be reconstructed to build an SQL statement for a MS SQL
    * database.
    *
    * @access public
    * @static
    * @param Vector &$tuples            a collection of tuples
    * @param integer $index             the index to be begin parsing at
    * @return integer                   the last index processed
    */
    protected static function mysql_to_mssql(Vector &$tuples, $index = 0) {
        $start = -1;
        while ($index < $tuples->count()) {
            $tuple = $tuples->get_element($index);
            $tuple[0] = strtoupper($tuple[0]);
            if ($tuple[0] == '(') {
                $index++;
                $index = self::mysql_to_mssql($tuples, $index);
            }
            else if ($tuple[0] == ')') {
                return $index;
            }
            else if ($tuple[0] == '||') {
                $tuple[0] = '+';
                $tuples->set_element($index, $tuple);
            }
            else if (isset(self::$mssql_conversions[$tuple[0]])) {
                $tuples->set_element($index, array(self::$mssql_conversions[$tuple[0]], SQLTokenizer::OTHER_TOKEN));
            }
            else if (preg_match('/^CONCAT$/', $tuple[0])) {
                $tuples->remove_index($index);
                $matches = 0;
                for ($position = $index; $position < $tuples->count(); $position++) {
                    $tuple = $tuples->get_element($position);
                    if ($tuple[0] == '(') {
                        if ($matches == 0) {
                            $tuples->remove_index($position);
                            $position--;
                        }
                        $matches++;
                    }
                    else if ($tuple[0] == ')') {
                        $matches--;
                        if ($matches == 0) {
                            $tuples->remove_index($position);
                            break;
                        }
                    }
                    else if (($matches == 1) && ($tuple[0] == ',')) {
                        $tuples->set_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                        $position++;
                        $tuples->insert_element($position, array('+', SQLTokenizer::OTHER_TOKEN));
                    }
                }
                $index--;
            }
            else if (preg_match('/^TRIM$/', $tuple[0])) { // http://stackoverflow.com/questions/179625/in-a-select-statementms-sql-how-do-you-trim-a-string
                $tuples->set_element($index, array('LTRIM', SQLTokenizer::OTHER_TOKEN));
                $index++;
                $tuples->insert_element($index, array('(', SQLTokenizer::PARENTHESIS_TOKEN));
                $index++;
                $tuples->insert_element($index, array('RTRIM', SQLTokenizer::OTHER_TOKEN));
                $matches = 0;
                for ($position = $index + 1; $position < $tuples->count(); $position++) {
                    $tuple = $tuples->get_element($position);
                    if ($tuple[0] == '(') {
                        $matches++;
                    }
                    else if ($tuple[0] == ')') {
                        $matches--;
                        if ($matches == 0) {
                            $tuples->insert_element($position, array(')', SQLTokenizer::PARENTHESIS_TOKEN));
                            break;
                        }
                    }
                }
            }
            else if (preg_match('/^(MD5|SHA1)$/', $tuple[0])) { // http://msdn.microsoft.com/en-us/library/ms174415.aspx
                $hash = "'" . strtoupper($tuple[0]) . "'";
                $tuples->set_element($index, array('HASHBYTES', SQLTokenizer::OTHER_TOKEN));
                for ($position = $index + 1; $position < $tuples->count(); $position++) {
                    $tuple = $tuples->get_element($position);
                    if ($tuple[0] == '(') {
                        $tuples->insert_element($position, array($hash, SQLTokenizer::LITERAL_TOKEN));
                        $position++;
                        $tuples->insert_element($position, array(',', SQLTokenizer::COMMA_TOKEN));
                        $position++;
                        $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                        break;
                    }
                }
            }
            else if (preg_match('/^IF$/', $tuple[0])) { // http://sqltutorials.blogspot.com/2007/06/sql-ifelse-statement.html
                $matches = 0;
                $commas = 0;
                for ($position = $index + 1; $position < $tuples->count(); $position++) {
                    $tuple = $tuples->get_element($position);
                    if ($tuple[0] == '(') {
                        if ($matches == 0) {
                            $tuples->remove_index($position);
                            $tuple = $tuples->get_element($position - 1);
                            if ($tuple[1] != SQLTokenizer::WHITESPACE_TOKEN) {
                                $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                            }
                        }
                        $matches++;
                    }
                    else if ($tuple[0] == ')') {
                        $matches--;
                        if ($matches == 0) {
                            $tuples->set_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                            $position++;
                            $tuples->insert_element($position, array('END', SQLTokenizer::OTHER_TOKEN));
                            $next = $position + 1;
                            if ($next < $tuples->count()) {
                                $tuple = $tuples->get_element($next);
                                if ($tuple[1] != SQLTokenizer::WHITESPACE_TOKEN) {
                                    $position++;
                                    $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                                }
                            }
                            break;
                        }
                    }
                    else if (($matches == 1) && ($tuple[0] == ',')) {
                        if ($commas == 0) {
                            $tuples->set_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                            $position++;
                            $tuples->insert_element($position, array('BEGIN', SQLTokenizer::OTHER_TOKEN));
                            $next = $position + 1;
                            if ($next < $tuples->count()) {
                                $tuple = $tuples->get_element($next);
                                if ($tuple[1] != SQLTokenizer::WHITESPACE_TOKEN) {
                                    $position++;
                                    $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                                }
                            }
                        }
                        else if ($commas == 1) {
                            $tuples->set_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                            $position++;
                            $tuples->insert_element($position, array('END', SQLTokenizer::OTHER_TOKEN));
                            $position++;
                            $tuples->set_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                            $position++;
                            $tuples->insert_element($position, array('ELSE', SQLTokenizer::OTHER_TOKEN));
                            $position++;
                            $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                            $position++;
                            $tuples->insert_element($position, array('BEGIN', SQLTokenizer::OTHER_TOKEN));
                            $next = $position + 1;
                            if ($next < $tuples->count()) {
                                $tuple = $tuples->get_element($next);
                                if ($tuple[1] != SQLTokenizer::WHITESPACE_TOKEN) {
                                    $position++;
                                    $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                                }
                            }
                        }
                        $commas++;
                    }
                }
            }
            else if (preg_match('/^SUBSTR(ING)?$/', $tuple[0])) {
                $tuples->set_element($index, array('SUBSTRING', SQLTokenizer::OTHER_TOKEN));
                $matches = 0;
                for ($position = $index + 1; $position < $tuples->count(); $position++) {
                    $tuple = $tuples->get_element($position);
                    if ($tuple[0] == '(') {
                        $matches++;
                    }
                    else if ($tuple[0] == ')') {
                        $matches--;
                        if ($matches == 0) {
                            break;
                        }
                    }
                    else if (($matches == 1) && preg_match('/^(FROM|FOR)$/i', $tuple[0])) {
                        $tuples->set_element($position - 1, array(',', SQLTokenizer::COMMA_TOKEN));
                        $tuples->remove_index($position);
                    }
                }
            }
            else if ($start >= 0) {
                if (preg_match('/^LIMIT$/', $tuple[0])) {
                    $limit = $tuples->get_element($index + 2);
                    $tuples->remove_range($index - 1, $index + 2);
                    $position = $start + 1;
                    $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                    $position++;
                    $tuples->insert_element($position, array('TOP', SQLTokenizer::OTHER_TOKEN));
                    $position++;
                    $tuples->insert_element($position, array(' ', SQLTokenizer::WHITESPACE_TOKEN));
                    $position++;
                    $tuples->insert_element($position, $limit);
                    $index += 2;
                }
                else if (preg_match('/^OFFSET$/i', $tuple[0])) {
                    
                }
            }
            else if (preg_match('/^(SELECT|INSERT|DELETE|UPDATE)$/', $tuple[0])) {
                $start = $index;
            }
            $index++;
        }
        return $index;
    }

}
?>