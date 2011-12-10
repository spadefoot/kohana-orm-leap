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
 * This class allows an application to tokenize an SQL statement.
 *
 * -- Dependencies:
 *      collection
 *      exception
 *
 * @package Leap
 * @category SQL
 * @version 2011-12-04
 *
 * @see http://www.sqlite.org/c3ref/complete.html
 * @see http://www.opensource.apple.com/source/SQLite/SQLite-74/public_source/src/complete.c
 *
 * @abstract
 */
abstract class Base_DB_SQL_Tokenizer extends Kohana_Object implements Iterator {

	/**
	* This constant stores the token value for a comma character.
	*
	* @access public
	* @var integer
	*/
	const COMMA_TOKEN = 0;

	/**
	* This constant stores the token value for an error.
	*
	* @access public
	* @var integer
	*/
	const ERROR_TOKEN = 1;

	/**
	* This constant stores the token value for a literal.
	*
	* @access public
	* @var integer
	*/
	const LITERAL_TOKEN = 2;

	/**
	* This constant stores the token value for a number.
	*
	* @access public
	* @var integer
	*/
	const NUMBER_TOKEN = 3;

	/**
	* This constant stores the token value for an arbitrary sequence of characters.
	*
	* @access public
	* @var integer
	*/
	const OTHER_TOKEN = 4;

	/**
	* This constant stores the token value for a parenthesis character.
	*
	* @access public
	* @var integer
	*/
	const PARENTHESIS_TOKEN = 5;

	/**
	* This constant stores the token value for a period character.
	*
	* @access public
	* @var integer
	*/
	const PERIOD_TOKEN = 6;

	/**
	* This constant stores the token value for a semicolon character.
	*
	* @access public
	* @var integer
	*/
	const SEMICOLON_TOKEN = 7;

	/**
	* This constant stores the token value for a sequence of whitespace characters.
	*
	* @access public
	* @var integer
	*/
	const WHITESPACE_TOKEN = 8;

	/**
	* This variable stores the original SQL statement.
	*
	* @access protected
	* @var string
	*/
	protected $statement;

	/**
	* This variable stores the head position of lexical analyzer.
	*
	* @access protected
	* @var integer
	*/
	protected $position;

	/**
	* This variable stores the length of the original SQL statement.
	*
	* @access protected
	* @var integer
	*/
	protected $length;

	/**
	* This variable stores the tuples discovered by the lexical analyzer.
	*
	* @access protected
	* @var array
	*/
	protected $tuples;

	/**
	* This variable stores the current index in the tuple array.
	*
	* @access protected
	* @var integer
	*/
	protected $index;

	/**
	* This variable stores the length of the tuple array.
	*
	* @access protected
	* @var integer
	*/
	protected $size;

	/**
	* This variable stores whether to cleanse whitespace tokens.
	*
	* @access protected
	* @var boolean
	*/
	protected $cleanse;

	/**
	* This construct initializes the lexical analyzer with the SQL statement.
	*
	* @access public
	* @param string $statement                  the SQL statement to be tokenized
	* @param string $cleanse                    whether to cleanse whitespace tokens
	* @throws Kohana_InvalidArgumentException   indicates that there is a data type mismatch
	*/
	public function __construct($statement, $cleanse = FALSE) {
		if (!is_string($statement) && !empty($statement)) {
			throw new Kohana_InvalidArgument_Exception('Invalid argument has been specified.', array(':statement' => $statement, ':cleanse' => $cleanse));
		}
		$this->statement = $statement;
		$this->position = 0;
		$this->length = strlen($this->statement);
		$this->tuples = array();
		$this->index = 0;
		$this->size = 0;
		$this->cleanse = (boolean)$cleanse;
		$this->evaluate();
	}

	/**
	* This function returns the current tuple.
	*
	* @access public
	* @return array						        the current tuple
	*/
	public function current() {
		return $this->tuples[$this->index];
	}

	/**
	* This function returns the index to the current tuple.
	*
	* @access public
	* @return integer					        the index of the current tuple
	*/
	public function key() {
		return $this->index;
	}

	/**
	* This function moves forward the index to the next tuple.
	*
	* @access public
	*/
	public function next() {
		if ($this->index < $this->size) {
			$this->index++;
		}
		$this->evaluate();
	}

	/**
	* This function returns the current iterator position.
	*
	* @access public
	* @return integer					        the current iterator position
	*/
	public function position() {
		return $this->index;
	}

	/**
	* This function rewinds the iterator back to starting position.
	*
	* @access public
	*/
	public function rewind() {
		$this->index = 0;
	}

	/**
	* This function checks if the current iterator position is valid.
	*
	* @access public
	* @return boolean					        whether the current iterator position is valid
	*/
	public function valid() {
		return (($this->index < $this->size) || ($this->position < $this->length));
	}

	/**
	* This function returns a collection of tuples.
	*
	* @access public
	* @return Vector                            a collection of tuples
	*/
	public function get_tuples() {
		while ($this->position < $this->length) {
			$this->evaluate();
		}
		$tuples = new Vector($this->tuples);
		return $tuples;
	}

	/**
	* This function evaluates the SQL statement for the next token.
	*
	* @access public
	*/
	protected function evaluate() {
		while ($this->position < $this->length) {
			$tuple = NULL;
			$token = $this->statement[$this->position];
			switch ($token) {
				case ',': // a comma
					$tuple = array($token, self::COMMA_TOKEN);
					$this->position++;
				break;
				case '(': // a parenthesis
				case ')':
					$tuple = array($token, self::PARENTHESIS_TOKEN);
					$this->position++;
				break;
				case '.': // a period
					$tuple = array($token, self::PERIOD_TOKEN);
					$this->position++;
				break;
				case ';': // a semicolon
					$tuple = array($token, self::SEMICOLON_TOKEN);
					$this->position++;
				break;
				case ' ': // white space
				case "\t": // a tab
				case "\r": // return
				case "\n": // new line
				case chr(10): // line feed
				case chr(12): // form feed
				case chr(13): // carriage return
					$tuple = array($token, self::WHITESPACE_TOKEN);
					$this->position++;
				break;
				case '/': // C-style comments i.e. /* ... */
					$lookahead = $this->position + 1;
					if (($lookahead >= $this->length) || ($this->statement[$lookahead] != '*')) {
						$tuple = array($token, self::OTHER_TOKEN);
					}
					else {
						$lookahead += 2;
						while (($lookahead < $this->length) && ($this->statement[$lookahead] != "*") && ($this->statement[$lookahead] != "/")) {
							$lookahead++;
						}
						$token = substr($this->statement, $this->position, ($lookahead - $this->position) + 2);
						$type = (preg_match('#/\*.*\*/#sm', $token)) ? self::WHITESPACE_TOKEN : self::ERROR_TOKEN;
						$tuple = array($token, $type);
					}
					$this->position += strlen($token);
				break;
				case '#': // MySQL-style rest of line comment
					$lookahead = $this->position + 1;
					$eol = array("\r", "\n", chr(10), chr(12), chr(13));
					while (($lookahead < $this->length) && !in_array($this->statement[$lookahead], $eol)) {
						$lookahead++;
					}
					$token = substr($this->statement, $this->position, ($lookahead - $this->position) + 1);
					$tuple = array($token, self::WHITESPACE_TOKEN);
					$this->position += strlen($token);
				break;
				case '-': // SQL-style (i.e. "--") rest of line comment
					$lookahead = $this->position + 1;
					if (($lookahead >= $this->length) || ($this->statement[$lookahead] != '-')) {
						$tuple = array($token, self::OTHER_TOKEN);
					}
					else {
						$eol = array("\r", "\n", chr(10), chr(12), chr(13));
						while (($lookahead < $this->length) && !in_array($this->statement[$lookahead], $eol)) {
							$lookahead++;
						}
						$token = substr($this->statement, $this->position, ($lookahead - $this->position) + 1);
						$tuple = array($token, self::WHITESPACE_TOKEN);
					}
					$this->position += strlen($token);
				break;
				case '[': // Microsoft-style identifiers, i.e. [...] -- see, http://msdn.microsoft.com/en-us/library/ms145572%28v=sql.90%29.aspx
					$lookahead = $this->position + 1;
					while (($lookahead < $this->length) && ($this->statement[$lookahead] != ']')) {
						$lookahead++;
					}
					$token = substr($this->statement, $this->position, ($lookahead - $this->position) + 1);
					$type = (preg_match('/^[.*]$/', $token)) ? self::OTHER_TOKEN : self::ERROR_TOKEN;
					$tuple = array($token, $type);
					$this->position += strlen($token);
				break;
				case '`': // grave-accent quote
				case '"': // single quote
				case "'": // double quote
					$quote = $token;
					$lookahead = $this->position + 1;
					$temp = '';
					while ($lookahead < $this->length) {
						$char = $this->statement[$lookahead];
						if (($char == $quote) && ((strlen($temp) % 2) == 0)) {
							break;
						}
						$temp = ($char == '\\') ? $temp . '\\' : '';
						$lookahead++;
					}
					$token = substr($this->statement, $this->position, ($lookahead - $this->position) + 1);
					$regex = '/^'. $quote. '.*'. $quote. '$/';
					$type = (preg_match($regex, $token)) ? self::LITERAL_TOKEN : self::ERROR_TOKEN;
					$tuple = array($token, $type);
					$this->position += strlen($token);
				break;
				case '|': // a pipe
					$lookahead = $this->position + 1;
					if (($lookahead < $this->length) && ($this->statement[$lookahead] == '|')) {
						$token .= $this->statement[$lookahead];
					}
					$tuple = array($token, self::OTHER_TOKEN);
					$this->position += strlen($token);
				break;
				case '!': // an exclaimation point
					$lookahead = $this->position + 1;
					if (($lookahead < $this->length) && ($this->statement[$lookahead] == '=')) {
						$token .= $this->statement[$lookahead];
					}
					$tuple = array($token, self::OTHER_TOKEN);
					$this->position += strlen($token);
				break;
				case '<':
					$lookahead = $this->position + 1;
					if ($lookahead < $this->length) {
						if ($this->statement[$lookahead] == '<') {
							$token .= $this->statement[$lookahead];
							$lookahead = $this->position + 1;
							if (($lookahead < $this->length) && ($this->statement[$lookahead] == '<')) {
								$token .= $this->statement[$lookahead];
							}
						}
						else if (($this->statement[$lookahead] == '=') || ($this->statement[$lookahead] == '>')) {
							$token .= $this->statement[$lookahead];
						}
					}
					$tuple = array($token, self::OTHER_TOKEN);
					$this->position += strlen($token);
				break;
				case '>':
					$lookahead = $this->position + 1;
					if ($lookahead < $this->length) {
						if ($this->statement[$lookahead] == '>') {
							$token .= $this->statement[$lookahead];
							$lookahead = $this->position + 1;
							if (($lookahead < $this->length) && ($this->statement[$lookahead] == '>')) {
								$token .= $this->statement[$lookahead];
							}
						}
						else if ($this->statement[$lookahead] == '=') {
							$token .= $this->statement[$lookahead];
						}
					}
					$tuple = array($token, self::OTHER_TOKEN);
					$this->position += strlen($token);
				break;
				case '0':
					$lookahead = $this->position + 2;
					if ($lookahead < $this->length) {
						$temp = substr($this->statement, $this->position, ($lookahead - $this->position) + 2);
						$regex = '/^0x[0-9]+$/';
						if (preg_match($regex, $temp)) {
							$token = $temp;
							$lookahead = $this->position + 1;
							while (($lookahead < $this->length) && preg_match($regex, substr($this->statement, $this->position, ($lookahead - $this->position) + 1))) {
								$token .= $this->statement[$lookahead];
								$lookahead++;
							}
							$token = trim($token); // to prevent an EOL character
							$tuple = array($token, self::OTHER_TOKEN);
							$this->position += strlen($token);
							break;
						}
					}
				case '1':
				case '2':
				case '3':
				case '4':
				case '5':
				case '6':
				case '7':
				case '8':
				case '9':
					$lookahead = $this->position + 1;
					while (($lookahead < $this->length) && preg_match('/^(0|[1-9][0-9]*)(\.[0-9]*)?$/', substr($this->statement, $this->position, ($lookahead - $this->position) + 1))) {
						$token .= $this->statement[$lookahead];
						$lookahead++;
					}
					$token = trim($token); // to prevent an EOL character
					$tuple = array($token, self::NUMBER_TOKEN);
					$this->position += strlen($token);
				break;
				default:
					$lookahead = $this->position + 1;
					while (($lookahead < $this->length) && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', substr($this->statement, $this->position, ($lookahead - $this->position) + 1))) {
						$token .= $this->statement[$lookahead];
						$lookahead++;
					}
					$token = trim($token); // to prevent an EOL character
					$tuple = array($token, self::OTHER_TOKEN);
					$this->position += strlen($token);
				break;
			}
			if (!$this->cleanse || ($tuple[1] != self::WHITESPACE_TOKEN)) {
				$this->tuples[] = $tuple;
				$this->size++;
				break;
			}
			else if (($this->size == 0) || ($this->tuples[$this->size - 1][1] != self::WHITESPACE_TOKEN)) {
				$this->tuples[] = array(' ', self::WHITESPACE_TOKEN);
				$this->size++;
				break;
			}
		}
	}

}
?>