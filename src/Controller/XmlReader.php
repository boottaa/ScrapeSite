<?php
/**
 * @author Сафронова Ольга
 * @copyright Фабрикант.ру
 * @category
 * @package
 */

namespace Fabrikant\Tool;

class XmlReader
{
	public function readString($xml)
	{
		$dom = new \DomDocument('1.0', 'utf8');
		if (!$dom->loadXML($xml, LIBXML_PARSEHUGE))
		{
			throw new \Exception(libxml_get_last_error()->message);
		}

		return $this->readDom($dom);
	}

	/**
	 * @param \DomDocument $dom
	 * @return array
	 */
	public function readDom($dom)
	{
		$result = [$dom->documentElement->localName => $this->_readNode($dom->documentElement)];

		return $result;
	}

	protected function _readNode(\DOMNode $node)
	{
		if ($node->nodeType == XML_TEXT_NODE)
		{
			return $this->_getValue($node->nodeValue);
		}

		$result = array();
		$isNil = false;

		if ($node->hasAttributes())
		{
			foreach ($node->attributes as $attr)
			{
				if ($attr->name == 'nil')
				{
					$isNil = true;
				}
				else
				{
					$result["!{$attr->name}"] = $this->_getValue($attr->value);
				}
			}
		}


		$curNodeValue = '';
		$curNodeValueIsSet = false;
		foreach($node->childNodes as $child)
		{
			/** @var $child \DomNode */

			$childValue = $this->_getValue($child->nodeValue);

			if ($child->nodeType == XML_ELEMENT_NODE)
			{
				if (array_key_exists($child->localName, $result))
				{
					if (!is_array($result[$child->localName]) || !isset($result[$child->localName][0]))
					{
						$result[$child->localName] = array($result[$child->localName]);
					}
					$result[$child->localName][] = $this->_readNode($child);

				}
				else
				{
					$result[$child->localName] = $this->_readNode($child);
				}
			}
			elseif (($child->nodeType == XML_TEXT_NODE || $child->nodeType == XML_CDATA_SECTION_NODE) && $childValue !== '')
			{
				$curNodeValue = $childValue;
				$curNodeValueIsSet = true;
			}
		}

		if ($curNodeValueIsSet)
		{
			if (!count($result))
			{
				$result = $curNodeValue;
			}
			else
			{
				$result['_'] = $curNodeValue;
			}
		}

		if ($isNil)
		{
			$result = null;
		}
		elseif (is_array($result) && empty($result))
		{
			$result = '';
		}


		return $result;
	}

	protected function _getValue($value)
	{
		$value = trim($value);
		if ($value === 'false')
		{
			return false;
		}
		if ($value === 'true')
		{
			return true;
		}
		return $value;
	}
}