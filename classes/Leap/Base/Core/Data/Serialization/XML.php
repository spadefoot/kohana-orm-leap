<?php

/**
 * Copyright © 2011–2013 Spadefoot Team.
 *
 * Unless otherwise noted, LEAP is licensed under the Apache License,
 * Version 2.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License
 * at:
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Leap\Base\Core\Data\Serialization {

	/**
	 * This class adds additional functionality to the underlying \SimpleXMLElement
	 * class.
	 *
	 * @package Leap
	 * @category XML
	 * @version 2013-01-28
	 *
	 * @abstract
	 */
	abstract class XML extends \SimpleXMLElement {

		/**
		 * This function converts an associated array to an XML string.
		 *
		 * @access protected
		 * @static
		 * @param array $array                          the associated array to be converted
		 * @param \DOMElement $domElement               the XML DOM element
		 * @param \DOMDocument $document                the XML DOM document
		 * @return string                               a string formatted with XML
		 *
		 * @see http://darklaunch.com/2009/05/23/php-xml-encode-using-domdocument-convert-array-to-xml-json-encode
		 */
		protected static function convert_to_xml($array, $domElement = NULL, $document = NULL) {
			if ($document === NULL) {
				$document = new \DOMDocument();
				$document->formatOutput = TRUE;
				static::convert_to_xml($array, $document, $document);
				return $document->asXML();
			}
			else {
				if (is_array($array)) {
					foreach ($array as $node => $value) {
						$element = NULL;
						if (is_integer($node)) {
							$element = $domElement;
						}
						else {
							$element = $document->createElement($node);
							$domElement->appendChild($element);
						}
						static::convert_to_xml($value, $element, $document);
					}
				}
				else {
					if (is_string($array) AND preg_match('/^<!CDATA\[.*\]\]>$/', $array)) {
						$array = substr($array, 8, strlen($array) - 11);
						$element = $document->createCDATASection($array);
						$domElement->appendChild($element);
					}
					else {
						$element = $document->createTextNode($array);
						$domElement->appendChild($element);
					}
				}
			}
		}

		/**
		 * This function converts an associated array to either a \SimpleXMLElement or an XML formatted
		 * string depending on the second parameter.
		 *
		 * @access public
		 * @static
		 * @param array $array                          the associated array to be converted
		 * @param boolean $as_string                    whether to return a string
		 * @return mixed                                either a \SimpleXMLElement or an XML
		 *                                              formatted string
		 */
		public static function encode(Array $array, $as_string = FALSE) {
			$contents = static::convert_to_xml($array);
			if ($as_string) {
				return $contents;
			}
			$XML = new Core\Data\Serialization\XML($contents);
			return $XML;
		}

		/**
		 * This function searches for the file that first matches the specified file
		 * name and returns its path.
		 *
		 * @access protected
		 * @static
		 * @param string $file                          the file name
		 * @return string                               the file path
		 * @throws Throwable\FileNotFound\Exception     indicates that the file does not exist
		 */
		protected static function find_file($file) {
			if (file_exists($file)) {
				return $file;
			}

			if (defined('APPPATH')) {
				$uri = APPPATH . $file;
				if (file_exists($uri)) {
					return $uri;
				}
			}

			if (class_exists('\\Kohana')) {
				$modules = \Kohana::modules();
				foreach($modules as $module) {
					$uri = $module . $file;
					if (file_exists($uri)) {
						return $uri;
					}
				}
			}

			if (defined('SYSPATH')) {
				$uri = SYSPATH . $file;
				if (file_exists($uri)) {
					return $uri;
				}
			}

			throw new Throwable\FileNotFound\Exception("Message: Unable to locate file. Reason: File ':file' does not exist.", array(':file', $file));
		}

		/**
		 * This function returns an instance of the class with the contents of the specified
		 * XML file.
		 *
		 * @access public
		 * @static
		 * @param string $file                          the file name
		 * @return Core\Data\Serialization\XML                        an instance of this class
		 * @throws Throwable\InvalidArgument\Exception  indicates a data type mismatch
		 * @throws Throwable\FileNotFound\Exception     indicates that the file does not exist
		 */
		public static function load($file) {
			if ( ! is_string($file)) {
				throw new Throwable\InvalidArgument\Exception('Message: Wrong data type specified. Reason: Argument must be a string.', array(':type', gettype($file)));
			}

			$uri = static::find_file($file);

			$contents = file_get_contents($uri);

			$XML = new Core\Data\Serialization\XML($contents);
			return $XML;
		}

	}

}