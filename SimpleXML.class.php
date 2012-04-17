<?php
/**
 * XML class
 *
 * This class extends SimpleXML
 *
 * @version 1.0
 * @author Mihai Zaharie <avataru@gmail.com>
 * @date 15 February 2011
 */

class SimpleXMLExtended extends SimpleXMLElement
{
	// Add CData section
	public function addCData($cdata_text)
	{
		$node= dom_import_simplexml($this);
		$no = $node->ownerDocument;
		$node->appendChild($no->createCDATASection($cdata_text));
	}
}