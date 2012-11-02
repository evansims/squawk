<?

	/*

		cXML, an XML processing library for PHP.
		Copyright (C) 2005 Evan Sims (hello@evansims.com)

		This library is free software; you can redistribute it and/or
		modify it under the terms of the GNU Lesser General Public
		License as published by the Free Software Foundation; either
		version 2.1 of the License, or (at your option) any later version.

		This library is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
		Lesser General Public License for more details.

		You should have received a copy of the GNU Lesser General Public
		License along with this library; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

		---

		This library was developed as a fast, flexible and dependant-less
		alternative to PHP's extensions and the oodles of other bloated
		XML parsers. It's simple and straightforward, and pretty speedy.

		For the latest releases, please visit: http://evansims.com/pub/code/php/xml

	*/

	class cXML {

		var $convTimestamps = true;
		var $stripCDATA = true;
		var $translateCondense = false;

		/*
			Function: Parse()
			 Accepts: 1 String or Array
			 Returns: 1 Array
			    Desc: This function will take one string of valid XML code and
			          return a native PHP array representing the data hierarchy
			          of that XML.
		*/

		function Parse($data) {

			if (is_array($data)) $data = implode("\n", $data);
			if (strlen($data) <= 5) return NULL;

			$out = array();

			static $nodeid = 0;

			for($i = 0; $i < strlen($data); $i++) {

				if ($data[$i] == '<') {

					if (($i + 1) > strlen($data)) continue;
					if ($data[$i+1] == '!') continue; // Comments

					$temp = ''; $subtemp = ''; $tag = '';
					$attr = ''; $attrs = array(); $value = '';
					$children = array(); $meta = false; $cdata = false;

					if ($data[$i+1] == '?') $i = $i + 1;

					$temp = substr($data, $i+1);
					$temp = substr($temp, 0, strpos($temp, '>'));

					$rawi = $i + strlen($temp) + 2;

					if ($temp[(strlen($temp)-1)] == '?') { $meta = true; $temp = substr($temp, 0, -1); }

					$tag = substr($temp, 0, strpos($temp, ' '));
					if (!$tag) $tag = $temp;
					$tag = trim($tag);

					if (substr($tag, 0, 1) == '/') continue;

					if ($tag) {

						if (!$meta) {
							if ($temp[(strlen($temp)-1)] != '/') {

								for($n = $rawi; $n < strlen($data); $n++) {

									if (ord($data[$n]) < 33 || ord($data[$n]) > 126) continue;

									$subtemp = substr($data, $n);

									for($s = 0; $s < strlen($subtemp); $s++) {

										if ($subtemp[$s] == '<') {

											for($s2 = ($s+1); $s2 < strlen($subtemp); $s2++) {

												if (ord($data[$n]) < 32 || ord($data[$n]) > 126) continue;
												if ($subtemp[$s2] == '/') {

													$nextblock = strpos($subtemp, '>', $s2);

													if (strlen($tag) < ($s2 - $nextblock)) continue 2;

													for($s3 = ($s2+1); $s3 < strlen($subtemp); $s3++) {

														if (ord($data[$n]) < 32 || ord($data[$n]) > 126) continue;

														if (substr($subtemp, $s3, strlen($tag)) == $tag) {
															$rawi = $rawi + $s3 + strlen($tag) + 1;
															$subtemp = substr($subtemp, 0, $s);
															break 3;
														} else { continue 2; }

													}

												} else { continue 2; }

											}

										}

									}

									break;

								}

								if (strlen($subtemp)) {

									if ($this->stripCDATA) {
										if (substr(strtoupper($subtemp), 0, 9) == '<![CDATA[') {
											$cdata = true;
											$subtemp = substr($subtemp, 9);
											$subtemp = substr($subtemp, 0, -3);
										}
									}

									if (!$cdata && $subtemp[0] == '<') {
										$children = $this->Parse($subtemp);
										$value = '';
									} else {
										if ($this->convTimestamps && strlen($subtemp) <= 64 &&  strlen($subtemp) > 5) {
											$time = strtotime($subtemp);
											if ($time != -1) {
												$subtemp = $time;
											}
										}
										$value = $subtemp;
									}

								}

							} else { $tag = substr($tag, 0, -1); }
						}

						$attr = substr($temp, (strlen($tag) + 1));
						$attr = explode(' ', $attr);
						if (count($attr)) {
							for($a = 0; $a < count($attr); $a++) {
								$attr_name = strtolower(substr($attr[$a], 0, strpos($attr[$a], '=')));
								if ($attr_name) $attrs[$attr_name] = str_replace('"', '', trim(substr($attr[$a], (strpos($attr[$a], '=') + 2), -1)));
							}
						}

						$out[] = array('name' => strtolower($tag), 'value' => trim($value), 'attributes' => $attrs, 'children' => $children, 'declaration' => $meta, 'encoded' => $cdata);
						$nodeid++;

					}

					$i = $rawi;

				}

			}

			return $out;

		}

		/*
			Function: Render()
			 Accepts: 1 Array
			 Returns: 1 String
			    Desc: This function will take an array of data matching that of the output of Parse() [see above],
			          and return a valid UTF-8 encoded XML structure representing the data in that array.
		*/

		function Render($array, $tier = 0) {

			$out = '';

			foreach($array as $node) {

				$attrs = '';
				if (count($node['attributes'])) {
					foreach($node['attributes'] as $attr => $val) {
						$attrs .= ' ' . $attr . '="' . $val . '"';
					}
				}

				if (!$this->translateCondense) $out .= str_repeat('  ', $tier);
				$out .= '<';
				if ($node['declaration']) $out .= '?';
				$out .= utf8_encode($node['name']) . utf8_encode($attrs);

				if (strlen($node['value']) == 0 && count($node['children']) == 0) {
					if ($node['declaration']) {
						$out .= '?';
					} else {
						$out .= '/';
					}
					$out .= '>';
					if (!$this->translateCondense) $out .= "\n";
					continue;
				}
				else if (strlen($node['value']) == 0 && count($node['children'])) { $out .= '>'; if (!$this->translateCondense) { $out .= "\n"; } $out .= $this->Render($node['children'], ($tier + 1)); if (!$this->translateCondense) { $out .= str_repeat('  ', $tier); } }
				else if (strlen($node['value'])) {
					$out .= '>';
					if ($node['encoded']) { $out .= '<![CDATA['; }
					$out .= utf8_encode($node['value']);
					if ($node['encoded']) { $out .= ']]>'; }
				}

				$out .=  '</' . $node['name'] . '>';
				if (!$this->translateCondense) $out .= "\n";

			}

			return $out;

		}

	}
