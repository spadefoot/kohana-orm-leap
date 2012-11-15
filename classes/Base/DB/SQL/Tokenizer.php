<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Copyright 2011-2012 Spadefoot
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
 * This class allows an application to tokenize an SQL statement.
 *
 * @package Leap
 * @category SQL
 * @version 2012-11-14
 *
 * @see http://www.sqlite.org/c3ref/complete.html
 * @see http://www.opensource.apple.com/source/SQLite/SQLite-74/public_source/src/complete.c
 *
 * @abstract
 */
abstract class Base_DB_SQL_Tokenizer extends Core_Object implements ArrayAccess, Countable, Iterator, SeekableIterator {

	/**
	 * This constant represents an error token.
	 *
	 * @access public
	 * @const string
	 */
	const ERROR_TOKEN = 'ERROR';

	/**
	 * This constant represents a hexadecimal token.
	 *
	 * @access public
	 * @const string
	 */
	const HEXADECIMAL_TOKEN = 'HEXADECIMAL';

	/**
	 * This constant represents an identifier token.
	 *
	 * @access public
	 * @const string
	 */
	const IDENTIFIER_TOKEN = 'IDENTIFIER';

	/**
	 * This constant represents an integer token.
	 *
	 * @access public
	 * @const string
	 */
	const INTEGER_TOKEN = 'NUMBER:INTEGER';

	/**
	 * This constant represents a keyword token.
	 *
	 * @access public
	 * @const string
	 */
	const KEYWORD_TOKEN = 'KEYWORD';

	/**
	 * This constant represents a literal token.
	 *
	 * @access public
	 * @const string
	 */
	const LITERAL_TOKEN = 'LITERAL';

	/**
	 * This constant represents an operator token.
	 *
	 * @access public
	 * @const string
	 */
	const OPERATOR_TOKEN = 'OPERATOR';

	/**
	 * This constant represents a parameter token.
	 *
	 * @access public
	 * @const string
	 */
	const PARAMETER_TOKEN = 'PARAMETER';

	/**
	 * This constant represents a real number token.
	 *
	 * @access public
	 * @const string
	 */
	const REAL_TOKEN = 'NUMBER:REAL';

	/**
	 * This constant represents a terminal character token.
	 *
	 * @access public
	 * @const string
	 */
	const TERMINAL_TOKEN = 'TERMINAL';

	/**
	 * This constant represents a whitespace token.
	 *
	 * @access public
	 * @const string
	 */
	const WHITESPACE_TOKEN = 'WHITESPACE';

	/**
	 * This variable stores the SQL statement being tokenized.
	 *
	 * @access protected
	 * @var string
	 */
	protected $statement = '';

	/**
	 * This variable stores the tuples discovered by the lexical analyzer.
	 *
	 * @access protected
	 * @var array
	 */
	protected $tuples = array();

	/**
	 * This variable stores the head position of lexical analyzer.
	 *
	 * @access protected
	 * @var integer
	 */
	protected $position = 0;

	/**
	 * This variable stores the number of tuples.
	 *
	 * @access protected
	 * @var integer
	 */
	protected $size = 0;

	/**
	 * This construct initializes the class.
	 *
	 * @access public
	 * @param string $statement                     the SQL statement to be tokenized
	 * @param string $dialect                       the SQL dialect
	 */
	public function __construct($statement, $dialect) {
		$position = 0;
		$strlen = strlen($statement);
		$length = $strlen - 1;

		$whitespace = array(' ', "\t");
		$eol = array("\n", "\r", ''); // TODO add PHP's equivalent to "\f"
		$quote = array('`', '"');

		while ($position <= $length) {
			$char = static::char_at($statement, $position, $strlen);
			if ($char == '|') { // "operator" token
				$lookahead = $position + 1;
				$next = static::char_at($statement, $lookahead, $strlen);
				if ($next == '|') {
					$lookahead++;
					$size = $lookahead - $position;
					$token = substr($statement, $position, $size);
					$this->tuples[] = array(
						'type' => static::OPERATOR_TOKEN,
						'token' => $token,
					);
					$this->size++;
					// echo Debug::vars($token);
				}
				else {
					$this->tuples[] = array(
						'type' => static::OPERATOR_TOKEN,
						'token' => $char,
					);
					$this->size++;
					// echo Debug::vars($char);
				}
				$position = $lookahead;
			}
			else if (($char == '!') OR ($char == '=')) { // "operator" token
				$lookahead = $position + 1;
				$next = static::char_at($statement, $lookahead, $strlen);
				if ($next == '=') {
					$lookahead++;
					$size = $lookahead - $position;
					$token = substr($statement, $position, $size);
					$this->tuples[] = array(
						'type' => static::OPERATOR_TOKEN,
						'token' => $token,
					);
					$this->size++;
					// echo Debug::vars($token);
				}
				else {
					$this->tuples[] = array(
						'type' => static::OPERATOR_TOKEN,
						'token' => $char,
					);
					$this->size++;
					// echo Debug::vars($char);
				}
				$position = $lookahead;
			}
			else if (($char == '<') OR ($char == '>')) { // "operator" token
				$lookahead = $position + 1;
				$next = static::char_at($statement, $lookahead, $strlen);
				if (($next == '=') OR ($next == $char) OR (($next == '>') AND ($char == '<'))) {
					$lookahead++;
					$size = $lookahead - $position;
					$token = substr($statement, $position, $size);
					$this->tuples[] = array(
						'type' => static::OPERATOR_TOKEN,
						'token' => $token,
					);
					$this->size++;
					// echo Debug::vars($token);
				}
				else {
					$this->tuples[] = array(
						'type' => static::OPERATOR_TOKEN,
						'token' => $char,
					);
					$this->size++;
					// echo Debug::vars($char);
				}
				$position = $lookahead;
			}
			else if (in_array($char, $whitespace) OR in_array($char, $eol)) { // "whitespace" token
				$start = $position;
				$next = '';
				do {
					$position++;
					$next = static::char_at($statement, $position, $strlen);
				} while(($next != '') AND (in_array($next, $whitespace) OR in_array($next, $eol)));
				$size = $position - $start;
				$token = substr($statement, $start, $size);
				$this->tuples[] = array(
					'type' => static::WHITESPACE_TOKEN,
					'token' => $token,
				);
				$this->size++;
				// echo Debug::vars($token);
			}
			else if ($char == '#') { // "whitespace" token (i.e. MySQL-style comment)
				$start = $position;
				do {
					$position++;
				} while( ! in_array(static::char_at($statement, $position, $strlen), $eol));
				$position++;
				$size = $position - $start;
				$token = substr($statement, $start, $size);
				$this->tuples[] = array(
					'type' => static::WHITESPACE_TOKEN,
					'token' => $token,
				);
				$this->size++;
				// echo Debug::vars($token);
			}
			else if ($char == '-') { // "whitespace" token (i.e. SQL-style comment) or "operator" token
				$lookahead = $position + 1;
				if (($lookahead > $length) OR (static::char_at($statement, $lookahead, $strlen) != '-')) {
					$this->tuples[] = array(
						'type' => static::OPERATOR_TOKEN,
						'token' => $char,
					);
					$this->size++;
					// echo Debug::vars($char);
				}
				else {
					while ( ! in_array(static::char_at($statement, $lookahead, $strlen), $eol)) {
						$lookahead++;
					}
					$lookahead++;
					$size = min($lookahead, $strlen) - $position;
					$token = substr($statement, $position, $size);
					$this->tuples[] = array(
						'type' => static::WHITESPACE_TOKEN,
						'token' => $token,
					);
					$this->size++;
					// echo Debug::vars($token);
				}
				$position = $lookahead;
			}
			else if ($char == '/') { // "whitespace" token (i.e. C-style comment) or "operator" token
				$lookahead = $position + 1;
				$next = static::char_at($statement, $lookahead, $strlen);
				if ($next != '*') {
					$this->tuples[] = array(
						'type' => static::OPERATOR_TOKEN,
						'token' => $char,
					);
					$this->size++;
					// echo Debug::vars($char);
				}
				else {
					$lookahead += 2;
					while ( ! ((static::char_at($statement, $lookahead - 1, $strlen) == '*') AND (static::char_at($statement, $lookahead, $strlen) == '/'))) {
						$lookahead++;
					}
					$lookahead++;
					$size = min($lookahead, $length + 1) - $position;
					$token = substr($statement, $position, $size);
					$this->tuples[] = array(
						'type' => static::WHITESPACE_TOKEN,
						'token' => $token,
					);
					$this->size++;
					// echo Debug::vars($token);
				}
				$position = $lookahead;
			}
			else if ($char == '[') { // "identifier" token (Microsoft-style)
				$start = $position;
				do {
					$position++;
				} while(($position < $length) AND (static::char_at($statement, $position, $strlen) != ']'));
				$position++;
				$size = $position - $start;
				$token = substr($statement, $start, $size);
				$this->tuples[] = array(
					'type' => static::IDENTIFIER_TOKEN,
					'token' => $token,
				);
				$this->size++;
				// echo Debug::vars($token);
			}
			else if (in_array($char, $quote)) { // "identifier" token (SQL-style)
				$start = $position;
				do {
					$position++;
				} while(($position < $length) AND (static::char_at($statement, $position, $strlen) != $char));
				$position++;
				$size = $position - $start;
				$token = substr($statement, $start, $size);
				$this->tuples[] = array(
					'type' => static::IDENTIFIER_TOKEN,
					'token' => $token,
				);
				$this->size++;
				// echo Debug::vars($token);
			}
			else if ($char == '\'') { // "literal" token
				$lookahead = $position + 1;
				while ($lookahead <= $length) {
					if (static::char_at($statement, $lookahead, $strlen) == '\'') {
						if (($lookahead == $length) OR (static::char_at($statement, $lookahead + 1, $strlen) != '\'')) {
							$lookahead++;
							break;
						}
						$lookahead++;
					}
					$lookahead++;
				}
				$size = $lookahead - $position;
				$token = substr($statement, $position, $size);
				$this->tuples[] = array(
					'type' => static::LITERAL_TOKEN,
					'token' => $token,
				);
				$this->size++;
				// echo Debug::vars($token);
				$position = $lookahead;
			}
			else if (($char >= '0') AND ($char <= '9')) { // "integer" token, "real" token, or "hexadecimal" token
				$type = '';
				$start = $position;
				$next = '';
				if ($char == '0') {
					$position++;
					$next = static::char_at($statement, $position, $strlen);
					if (($next == 'x') OR ($next == 'X')) {
						do {
							$position++;
							$next = static::char_at($statement, $position, $strlen);
						} while (($next >= '0') AND ($next <= '9'));
						$type = static::HEXADECIMAL_TOKEN;
					}
					else if ($next == '.') {
						do {
							$position++;
							$next = static::char_at($statement, $position, $strlen);
						} while (($next >= '0') AND ($next <= '9'));
						$type = static::REAL_TOKEN;
					}
					else {
						$type = static::INTEGER_TOKEN;
					}
				}
				else {
					do {
						$position++;
						$next = static::char_at($statement, $position, $strlen);
					} while (($next >= '0') AND ($next <= '9'));
					if ($next == '.') {
						do {
							$position++;
							$next = static::char_at($statement, $position, $strlen);
						} while (($next >= '0') AND ($next <= '9'));
						$type = static::REAL_TOKEN;
					}
					else {
						$type = static::INTEGER_TOKEN;
					}
				}
				$size = $position - $start;
				$token = substr($statement, $start, $size);
				$this->tuples[] = array(
					'type' => $type,
					'token' => $token,
				);
				$this->size++;
				// echo Debug::vars($token);
			}
			else if ((($char >= 'a') AND ($char <= 'z')) OR (($char >= 'A') AND ($char <= 'Z')) OR ($char == '_')) { // "keyword" token or "identifier" token
				$start = $position;
				$next = '';
				do {
					$position++;
					$next = static::char_at($statement, $position, $strlen);
				} while(($position <= $length) AND ((($next >= 'a') AND ($next <= 'z')) OR (($next >= 'A') AND ($next <= 'Z')) OR ($next == '_') OR (($next >= '0') AND ($next <= '9'))));
				$size = $position - $start;
				$token = substr($statement, $start, $size);
				$type = (static::is_keyword($token, $dialect)) ? static::KEYWORD_TOKEN : static::IDENTIFIER_TOKEN;
				$this->tuples[] = array(
					'type' => $type,
					'token' => $token,
				);
				$this->size++;
				// echo Debug::vars($token);
			}
			else { // miscellaneous token
				$token = $char;
				$type = '';
				switch ($char) {
					case '+':
					case '*':
					case '%':
					case '&':
					case '~':
						$type = static::OPERATOR_TOKEN;
					break;
					case '?':
						$type = static::PARAMETER_TOKEN;
					break;
					case ';':
						$type = static::TERMINAL_TOKEN;
					break;
					default:
						$type = $token;
					break;
				}
				$this->tuples[] = array(
					'type' => $type,
					'token' => $token,
				);
				$this->size++;
				// echo Debug::vars($token);
				$position++;
			}
		}
	}

	/**
	 * This function returns an array of the found tuples.
	 *
	 * @access public
	 * @return array                                an array of tuples
	 */
	public function as_array() {
		return $this->tuples;
	}

	/**
	 * This function returns the total number of tuples found.
	 *
	 * @access public
	 * @return integer                              the total number of tuples found
	 */
	public function count() {
		return $this->size;
	}

	/**
	 * This function returns the current tuple.
	 *
	 * @access public
	 * @return array							    the current tuple
	 */
	public function current() {
		return $this->tuples[$this->position];
	}

	/**
	 * This function returns a tuple either at the current position or
	 * the specified position.
	 *
	 * @access public
	 * @param integer $index                        the tuple's index
	 * @return mixed                                the tuple
	 */
	public function fetch($index = -1) {
		settype($index, 'integer');
		if ($index < 0) {
			$index = $this->position;
			$this->position++;
		}

		if (isset($this->tuples[$index])) {
			return $this->tuples[$index];
		}

		return FALSE;
	}

	/**
	 * This function returns the index to the current tuple.
	 *
	 * @access public
	 * @return integer							    the index of the current tuple
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * This function moves forward the index to the next tuple.
	 *
	 * @access public
	 */
	public function next() {
		$this->position++;
	}

	/**
	 * This function determines whether an offset exists.
	 *
	 * @access public
	 * @param integer $offset                       the offset to be evaluated
	 * @return boolean                              whether the requested offset exists
	 */
	public function offsetExists($offset) {
		return isset($this->tuples[$offset]);
	}

	/**
	 * This functions gets value at the specified offset.
	 *
	 * @access public
	 * @param integer $offset                       the offset to be fetched
	 * @return mixed                                the value at the specified offset
	 */
	public function offsetGet($offset) {
		return isset($this->tuples[$offset]) ? $this->tuples[$offset] : NULL;
	}

	/**
	 * This functions sets the specified value at the specified offset.
	 *
	 * @access public
	 * @param integer $offset                       the offset to be set
	 * @param mixed $value                          the value to be set
	 * @throws Throwable_UnimplementedMethod_Exception indicates the result cannot be modified
	 */
	public function offsetSet($offset, $value) {
		throw new Throwable_UnimplementedMethod_Exception('Message: Invalid to call member function. Reason: Tokenizer cannot be modified.', array());
	}

	/**
	 * This functions allows for the specified offset to be unset.
	 *
	 * @access public
	 * @param integer $offset                       the offset to be unset
	 * @throws Throwable_UnimplementedMethod_Exception indicates the result cannot be modified
	 */
	public function offsetUnset($offset) {
		throw new Throwable_UnimplementedMethod_Exception('Message: Invalid to call member function. Reason: Tokenizer cannot be modified..', array());
	}

	/**
	 * This function returns the current iterator position.
	 *
	 * @access public
	 * @return integer							the current iterator position
	 */
	public function position() {
		return $this->position;
	}

	/**
	 * This function rewinds the iterator back to starting position.
	 *
	 * @access public
	 */
	public function rewind() {
		$this->position = 0;
	}

	/**
	 * This function sets the position pointer to the seeked position.
	 *
	 * @access public
	 * @param integer $position                     the seeked position
	 * @throws Throwable_OutOfBounds_Exception         indicates that the seeked position
	 *                                              is out of bounds
	 */
	public function seek($position) {
		if ( ! isset($this->tuples[$position])) {
			throw new Throwable_OutOfBounds_Exception('Message: Invalid array position. Reason: The specified position is out of bounds.', array(':position' => $position, ':count' => $this->size));
		}
		$this->position = $position;
	}

	/**
	 * This function checks if the current iterator position is valid.
	 *
	 * @access public
	 * @return boolean							whether the current iterator position is valid
	 */
	public function valid() {
		return isset($this->tuples[$this->position]);
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function returns the character at the specified position.
	 *
	 * @access protected
	 * @static
	 * @param string &$string                   the string to be used
	 * @param integer $index                    the character's index
	 * @param integer $length                   the string's length
	 * @return char                             the character at the specified index
	 */
	protected static function char_at(&$string, $index, $length) {
		return ($index < $length) ? $string[$index] : '';
	}

	/**
	 * This function checks whether the specified token is a reserved keyword.
	 *
	 * @access public
	 * @static
	 * @param string $token                     the token to be cross-referenced
	 * @return boolean                          whether the token is a reserved keyword
	 *
	 * @see http://drupal.org/node/141051
	 */
	public static function is_keyword($token, $dialect) {
		$compiler = 'DB_' . $dialect . '_Expression';
		$result = $compiler::is_keyword($token);
		return $result;
	}

}
?>