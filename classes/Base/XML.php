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
 * This class adds additional functionality to the underlying SimpleXMLElement
 * class.
 *
 * @package Leap
 * @category XML
 * @version 2012-11-14
 *
 * @abstract
 */
abstract class Base_XML extends SimpleXMLElement {

	/**
	 * This function converts an associated array to either a SimpleXMLElement or an XML formatted
	 * string depending on the second parameter.
	 *
	 * @access public
	 * @static
	 * @param array $array                          the associated array to be converted
	 * @param boolean $as_string                    whether to return a string
	 * @return mixed                                either a SimpleXMLElement or an XML
	 *                                              formatted string
	 */
	public static function encode(Array $array, $as_string = FALSE) {
		$content = static::convert_to_xml($array);
		if ($as_string) {
			return $content;
		}
		$XML = new XML($content);
		return $XML;
	}

	/**
	 * This function returns an instance of the class with the contents of the specified
	 * XML file.
	 *
	 * @access public
	 * @static
	 * @param string $file                          the file name
	 * @return XML                               	an instance of this class
	 * @throws Throwable_InvalidArgument_Exception		indicates that the an argument is of the
	 * 												wrong data type
	 * @throws Throwable_FileNotFound_Exception        indicates that the file does not exist
	 */
	public static function load($file) {
		if ( ! is_string($file)) {
			throw new Throwable_InvalidArgument_Exception('Message: Wrong data type specified. Reason: Argument must be a string.', array(':type', gettype($file)));
		}

		$source = static::find_file($file);

		$content = file_get_contents($source);

		$XML = new XML($content);
		return $XML;
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function converts an associated array to an XML string.
	 *
	 * @access protected
	 * @static
	 * @param array $array                          the associated array to be converted
	 * @param DOMElement $domElement                the XML DOM element
	 * @param DOMDocument $document                 the XML DOM document
	 * @return string                               a string formatted with XML
	 *
	 * @see http://darklaunch.com/2009/05/23/php-xml-encode-using-domdocument-convert-array-to-xml-json-encode
	 */
	protected static function convert_to_xml($array, $domElement = NULL, $document = NULL) {
		if ($document === NULL) {
			$document = new DOMDocument();
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
	 * This function searches for the file that first matches the specified file
	 * name and returns its path.
	 *
	 * @access protected
	 * @param string $file                          the file name
	 * @return string                               the file path
	 * @throws Throwable_FileNotFound_Exception        indicates that the file does not exist
	 */
	protected static function find_file($file) {
		if (file_exists($file)) {
			return $file;
		}

		$uri = APPPATH . $file;
		if (file_exists($uri)) {
			return $uri;
		}

		$modules = Kohana::modules();
		foreach($modules as $module) {
			$uri = $module . $file;
			if (file_exists($uri)) {
				return $uri;
			}
		}

		$uri = SYSPATH . $file;
		if (file_exists($uri)) {
			return $uri;
		}

		throw new Throwable_FileNotFound_Exception('Message: Unable to locate file. Reason: No file exists with the specified file name.', array(':file', $file));
	}

}
?>