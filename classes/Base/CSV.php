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
 * This class creates and parses CSV documents.
 *
 * @package Leap
 * @category CSV
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_CSV extends Core_Object implements ArrayAccess, Countable, Iterator, SeekableIterator {

	/**
	 * This variable stores the file name for the CSV, which will only be used
	 * when saving to disk.
	 *
	 * @access protected
	 * @var string
	 */
	protected $file_name;

	/**
	 * This variable stores the data to be included in the CSV file.
	 *
	 * @access protected
	 * @var array
	 */
	protected $data;

	/**
	 * This variable determines if default headers are used
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $default_headers;

	/**
	 * This variable stores the delimiter to be used when generating the CSV file.
	 *
	 * @access protected
	 * @var char
	 */
	protected $delimiter;

	/**
	 * This variable stores the headers to be included at the beginning of the CSV file.
	 *
	 * @access protected
	 * @var array
	 */
	protected $header;

	/**
	 * This variable stores the mime type of the CSV.  It changes only
	 * when the delimiter is set.
	 *
	 * @access protected
	 * @var string
	 */
	protected $mime;

	/**
	 * This variable stores the character that will use to enclose string data.
	 *
	 * @access protected
	 * @var char
	 */
	protected $enclosure;

	/**
	 * This variable stores the EOL (i.e. end of line character).
	 *
	 * @access protected
	 * @var char
	 */
	protected $eol;

	/**
	 * This variable stores the current position in the records array.
	 *
	 * @access protected
	 * @var integer
	 */
	protected $position;

	/**
	 * This constructor creates an instance of this class.
	 *
	 * @access public
	 * @param array $config                         the configuration array
	 */
	public function __construct(Array $config = array()) {
		$this->file_name = (isset($config['file_name']) AND is_string($config['file_name'])) ? $config['file_name'] : '';
		$this->data = array();
		$this->default_headers = (isset($config['default_headers'])) ? (bool) $config['default_headers'] : FALSE;
		$this->delimiter = (isset($config['delimiter']) AND is_string($config['delimiter'])) ? $config['delimiter'] : ',';
		$this->header = (isset($config['header']) AND is_array($config['header'])) ? $config['header'] : array() ;
		$this->mime = ($this->delimiter == "\t") ? 'text/tab-separated-values' : 'text/csv';
		$this->enclosure = (isset($config['enclosure']) AND is_string($config['enclosure'])) ? $config['enclosure'] : '"';
		$this->eol = (isset($config['eol']) AND is_string($config['eol'])) ? $config['eol'] : chr(10); // PHP_EOL
		$this->position = 0;

		if (isset($config['data']) AND (is_array($config['data']) OR ($config['data'] instanceof Iterator))) {
			foreach ($config['data'] as $row) {
				$this->add_row($row);
			}
		}
	}

	/**
	 * This function gets the value of the specified property.
	 *
	 * @access public
	 * @param string $key                         	the name of the property
	 * @return mixed                                the value of the property
	 * @throws Throwable_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __get($key) {
		switch ($key) {
			case 'file_name':
				return $this->file_name;
			case 'default_headers':
				return $this->default_headers;
			case 'delimiter':
				return $this->delimiter;
			case 'header':
				return $this->header;
			case 'mime':
				return $this->mime;
			case 'enclosure':
				return $this->enclosure;
			case 'eol':
				return $this->eol;
			default:
				throw new Throwable_InvalidProperty_Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key));
			break;
		}
	}

	/**
	 * This function sets the value for the specified key.
	 *
	 * @access public
	 * @param string $key                           the name of the property
	 * @param mixed $value                          the value of the property
	 * @throws Throwable_InvalidProperty_Exception     indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __set($key, $value) {
		switch ($key) {
			case 'file_name':
				$this->file_name = (is_string($value)) ? $value : '';
			break;
			case 'default_headers':
				$this->default_headers = (bool) $value;
			break;
			case 'delimiter':
				$this->delimiter = (is_string($value)) ? $value : ',';
				$this->mime = ($this->delimiter == "\t") ? 'text/tab-separated-values' : 'text/csv'; // 'text/plain'
			break;
			case 'header':
				$this->header = (isset($value) AND is_array($value)) ? $value : array() ;
			break;
			case 'enclosure':
				$this->enclosure = (is_string($value)) ? $value : '"';
			break;
			case 'eol':
				$this->eol = (is_string($value)) ? $value : chr(10);
			break;
			default:
				throw new Throwable_InvalidProperty_Exception('Message: Unable to set the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key, ':value' => $value));
			break;
		}
	}

	/**
	 * This function adds a row to the data array.
	 *
	 * @access public
	 * @param array $row                            the row to be appended
	 */
	public function add_row(Array $row) {
		if ( ! empty($row)) {
			$this->data[] = $row;
		}
	}

	/**
	 * This function returns the contents as an array.
	 *
	 * @access public
	 * @return array                                an array of the contents
	 */
	 public function as_array() {
		 return $this->data;
	 }

	/**
	 * This function removes all rows from the data array.
	 *
	 * @access public
	 */
	public function clear() {
		$this->data = array();
		$this->position = 0;
	}

	/**
	 * This function returns a count of the number of rows in the data set.
	 *
	 * @access public
	 * @return integer                              the number of rows
	 */
	public function count() {
		return count($this->data);
	}

	/**
	 * This function checks whether the data array is empty.
	 *
	 * @access public
	 * @return boolean                              whether the data array is empty
	 */
	public function is_empty() {
		return empty($this->data);
	}

	/**
	 * This function outputs the CVS file.
	 *
	 * @access public
	 * @param boolean $as_file                      whether to output the data as a file
	 *                                              or just echo it
	 *
	 * @see http://www.rfc-editor.org/rfc/rfc4180.txt
	 */
	public function output($as_file = FALSE) {
		$output = $this->render();
		if ($as_file) {
			if (empty($this->file_name)) {
				$this->file_name  = date('YmdHis');
				$this->file_name .= ($this->mime == 'text/tab-separated-values') ? '.txt' : '.csv';
			}
			$uri = preg_split('!(\?.*|/)!', $this->file_name, -1, PREG_SPLIT_NO_EMPTY);
			$file_name = $uri[count($uri) - 1];
			header("Content-Disposition: attachment; filename=\"{$file_name}\"");
		}
		header("Content-Type: {$this->mime}");
		header('Cache-Control: no-store, no-cache');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		echo $output;
		exit();
	}

	/**
	 * This function renders the data as a string.
	 *
	 * @access public
	 * @return string                               the string of imploded data
	 */
	public function render() {
		$buffer = '';

		if ( ! empty($this->header)) {
			$buffer .= $this->implode($this->header);
			$buffer .= $this->eol;
		}
		else if ($this->default_headers AND ! empty($this->data)) {
			$header = array_keys($this->current());
			$buffer .= $this->implode($header);
			$buffer .= $this->eol;
		}

		foreach ($this->data as $row) {
			$buffer .= $this->implode($row);
			$buffer .= $this->eol;
		}

		$buffer = trim($buffer);
		return $buffer;
	}

	/**
	 * This function saves the CSV file to disk.
	 *
	 * @access public
	 * @param string $file_name                     the URI for where the CSV file will be stored
	 * @return boolean                              whether the CSV file was saved
	 */
	public function save($file_name = NULL) {
		if ($file_name !== NULL) {
			$this->file_name = $file_name;
		}
		$result = @file_put_contents($this->file_name, $this->render());
		if ($result === FALSE) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * This function returns the current record.
	 *
	 * @access public
	 * @return mixed						      the current record
	 */
	public function current() {
		return $this->data[$this->position];
	}

	/**
	 * This function returns the position to the current record.
	 *
	 * @access public
	 * @return integer					        the position of the current record
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * This function moves forward the position to the next record, lazy loading only
	 * when necessary.
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
		return isset($this->data[$offset]);
	}

	/**
	 * This functions gets value at the specified offset.
	 *
	 * @access public
	 * @param integer $offset                       the offset to be fetched
	 * @return mixed                                the value at the specified offset
	 */
	public function offsetGet($offset) {
		return isset($this->data[$offset]) ? $this->data[$offset] : NULL;
	}

	/**
	 * This functions sets the specified value at the specified offset.
	 *
	 * @access public
	 * @param integer $offset                       the offset to be set
	 * @param mixed $value                          the value to be set
	 */
	public function offsetSet($offset, $value) {
		if ( ! is_array($value)) {
			throw new Throwable_InvalidArgument_Exception('Message: Unable to set value. Reason: Value must be an array.', array(':type' => gettype($value)));
		}
		else if ($offset === NULL) {
			$this->data[] = $value;
		}
		else {
			$this->data[$offset] = $value;
		}
	}

	/**
	 * This functions allows for the specified offset to be unset.
	 *
	 * @access public
	 * @param integer $offset                       the offset to be unset
	 * @throws Throwable_UnimplementedMethod_Exception indicates the result cannot be modified
	 */
	public function offsetUnset($offset) {
		throw new Throwable_UnimplementedMethod_Exception('Message: Invalid call to member function. Reason: CSV class cannot be modified.', array());
	}

	/**
	 * This function returns the current iterator position.
	 *
	 * @access public
	 * @return integer					        the current iterator position
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
		if ( ! isset($this->data[$position])) {
			throw new Throwable_OutOfBounds_Exception('Message: Invalid array position. Reason: The specified position is out of bounds.', array(':position' => $position, ':count' => $this->count()));
		}
		$this->position = $position;
	}

	/**
	 * This function checks if the current iterator position is valid.
	 *
	 * @access public
	 * @return boolean					        whether the current iterator position is valid
	 */
	public function valid() {
		return isset($this->data[$this->position]);
	}

	/**
	 * This function is an alias for CSV::render() and will renders the data as a string when
	 * the object is treated like a string, e.g. with PHP's echo and print commands.
	 *
	 * @access public
	 * @return string                               the string of imploded data
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * This function will create an instance of the CSV class.
	 *
	 * @access public
	 * @static
	 * @param array $config                         the configuration array
	 * @return CSV                                  an instance of the CSV class
	 */
	public static function factory(Array $config = array()) {
		return new CSV($config);
	}

	/**
	 * This function will load a CSV file.
	 *
	 * @access public
	 * @static
	 * @param array $config                         the configuration array
	 * @return CSV                                  an instance of the CSV class containing
	 *                                              the contents of the file.
	 *
	 * @see http://www.php.net/manual/en/function.fgetcsv.php
	 */
	public static function load($config = array()) {
		$csv = new CSV($config);
		if (file_exists($csv->file_name)) {
		   if (($fp = fopen($csv->file_name, 'r')) !== FALSE) {
				$eol = ($csv->eol == "\r\n") ? array(13, 10) : array(ord($csv->eol)); // 13 => cr, 10 => lf
				$buffer = '';
				while (($char = fgetc($fp)) !== FALSE) { // load char by char, to replace line endings
					if (in_array(ord($char), $eol)) {
						$buffer .= "\r\n";
					}
					else {
						$buffer .= $char;
					}
				}
				fclose($fp);
				$rows = explode("\r\n", $buffer);
				$enclosure = $csv->enclosure;
				$delimiter = $enclosure . $csv->delimiter . $enclosure;
				if (empty($enclosure)) {
					$enclosure = " \t\n\r\0\x0B";
				}
				$regex = '/' . $delimiter . '/';
				foreach ($rows as $row) {
					$row = trim($row, $enclosure);
					//$columns = explode($delimiter, $row);
					$columns = preg_split($regex, $row);
					$csv->add_row($columns);
				}
			}
		}
		return $csv;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function implodes a row using the proper syntax.
	 *
	 * @access private
	 * @static
	 * @param array $row                            the row to be imploded
	 * @return string                               the string of the imploded row
	 */
	protected function implode($row) {
		$buffer = '';
		$pattern = '/' . addslashes($this->enclosure) . '/';
		$replace = addslashes($this->enclosure);
		foreach ($row as $column) {
			$buffer .= $this->delimiter . $this->enclosure . preg_replace($pattern, $replace, $column) . $this->enclosure;
		}
		if ( ! empty($buffer)) {
			$buffer = substr($buffer, strlen($this->delimiter));
		}
		return $buffer;
	}

}
?>