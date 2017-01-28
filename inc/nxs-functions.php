<?php
## Part I. General Functions
if (!function_exists('prr')){ function prr($str) { echo "<pre>"; print_r($str); echo "</pre>\r\n"; }}
if (!function_exists('nsTrnc')){ function nsTrnc($string, $limit, $break=" ", $pad=" ...") { if(strlen($string) <= $limit) return $string; $string = substr($string, 0, $limit-strlen($pad)); 
  $brLoc = strripos($string, $break);  if ($brLoc===false) return $string.$pad; else return substr($string, 0, $brLoc).$pad; 
}}
if (!function_exists('CutFromTo')){ function CutFromTo($string, $from, $to){$fstart = stripos($string, $from); $tmp = substr($string,$fstart+strlen($from)); $flen = stripos($tmp, $to);  return substr($tmp,0, $flen);}}
if (!function_exists('nsx_doEncode')){ function nsx_doEncode($string,$key='NSX') { $key = sha1($key); $strLen = strlen($string);$keyLen = strlen($key); $j = 0; $hash = '';
  for ($i = 0; $i < $strLen; $i++) { $ordStr = ord(substr($string,$i,1)); if ($j == $keyLen) $j = 0; $ordKey = ord(substr($key,$j,1)); $j++; $hash .= strrev(base_convert(dechex($ordStr + $ordKey),16,36));} return $hash;
}}
if (!function_exists('nsx_doDecode')){ function nsx_doDecode($string,$key='NSX') { $key = sha1($key); $strLen = strlen($string); $keyLen = strlen($key); $j = 0; $hash = ''; $strA = str_split($string, 2);
  foreach($strA as $ordStr){ $ordStr = hexdec(base_convert(strrev($ordStr),36,16)); if ($j == $keyLen) $j = 0; $ordKey = ord(substr($key,$j,1)); $j++; $hash .= chr($ordStr - $ordKey);} return $hash;
}}
if (!function_exists('nxs_strLen')){ function nxs_strLen($str) { return count(str_split(utf8_decode($str))); }}
if (!function_exists('nxs_currPageURL')){ function nxs_currPageURL() {
	if (empty($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'].'?'.$_SERVER['argv'][0]; $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
  if ($_SERVER["SERVER_PORT"] != "80"){ $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];} else { $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];} 
  $pageURL=strtok($pageURL,'?'); $ggt = $_GET; if (isset($ggt['auth'])) unset($ggt['auth']); if (isset($ggt['acc'])) unset($ggt['acc']); if (!empty($ggt)) $pageURL .= '?'.http_build_query($ggt); /*prr($pageURL); */ return $pageURL;
}}
//## Part II. SNAP Functions
if (!function_exists('nxs_settings_open')){ function nxs_settings_open() {  
	$fileData = trim(file_get_contents(dirname(__DIR__).'/nx-snap-settings.txt'));  $options = nxs_maybe_unserialize($fileData);  return $options;
}}
if (!function_exists('nxs_settings_save')){ function nxs_settings_save($options) {  
	$options = serialize($options);  file_put_contents(dirname(__DIR__).'/nx-snap-settings.txt', $options);
}}
if (!function_exists("nxs_save_glbNtwrks")) { function nxs_save_glbNtwrks($nt, $ii, $ntOptsOrVal, $field='', $networks='')  { if (empty($networks)) { if ($field=='*') {$field=''; $merge = true;} else $merge = false;
    if (function_exists("nxs_settings_open")) $networks = nxs_settings_open(); else { if (class_exists('nxs_SNAP')) { global $nxs_SNAP; if (!isset($nxs_SNAP)) $nxs_SNAP = new nxs_SNAP(); $networks = $nxs_SNAP->nxs_accts; }
      if (class_exists('NS_SNAutoPoster')) { global $plgn_NS_SNAutoPoster; if (!isset($plgn_NS_SNAutoPoster)) $plgn_NS_SNAutoPoster = new NS_SNAutoPoster(); $networks = $plgn_NS_SNAutoPoster->nxs_options; }       
    }
  } if(!empty($field)) $networks[$nt][$ii][$field] = $ntOptsOrVal; else $networks[$nt][$ii] = $merge?(array_merge($networks[$nt][$ii],$ntOptsOrVal)):$ntOptsOrVal; nxs_save_ntwrksOpts($networks);   
  if (isset($plgn_NS_SNAutoPoster)) $plgn_NS_SNAutoPoster->nxs_options = $networks; if (isset($nxs_SNAP))  $nxs_SNAP->nxs_accts = $networks; // prr($networks[$nt]); var_dump($merge);
}}

if (!function_exists("nxs_save_ntwrksOpts")) { function nxs_save_ntwrksOpts($networks) { 
  if (function_exists('nxs_settings_save')) nxs_settings_save($networks); else if (function_exists('get_option') && !empty($networks)) update_option('NS_SNAutoPoster', $networks); 
}}
if (!function_exists("nxs_mkShortURL")) { function nxs_mkShortURL($url, $postID='') {return $url; }}
if (!function_exists("nxs_spinRecursion")) { function nxs_spinRecursion(&$txt, $startCh) { global $nxs_spin_lCh, $nxs_spin_rCh, $nxs_spin_splCh; $startPos = $startCh;
  while ($startCh++ < strlen($txt)) {
	if (substr($txt, $startCh, strlen($nxs_spin_lCh)) == $nxs_spin_lCh)  $txt = nxs_spinRecursion($txt, $startCh);
	elseif (substr($txt, $startCh, strlen($nxs_spin_rCh)) == $nxs_spin_rCh) {
	  $tmpTxt = substr($txt, $startPos+strlen($nxs_spin_lCh), ($startCh - $startPos)-strlen($nxs_spin_rCh));
	  $toRepl = nxs_spinReplace($tmpTxt); $txt = str_replace($nxs_spin_lCh.$tmpTxt.$nxs_spin_rCh, $toRepl, $txt);
	}
  } return $txt;
}}
if (!function_exists("nxs_spinReplace")) { function nxs_spinReplace($txt) { global $nxs_spin_splCh; $txt = explode($nxs_spin_splCh, $txt);  $out = $txt[mt_rand(0,count($txt)-1)]; return $out; }}
if (!function_exists("nxs_doSpin")) { function nxs_doSpin($msg){  global $nxs_spin_lCh, $nxs_spin_rCh, $nxs_spin_splCh;
	$nxs_spin_lCh = '{'; $nxs_spin_rCh='}'; $nxs_spin_splCh='|'; $msg = nxs_spinRecursion($msg, -1); return $msg;
}}
if (!function_exists("nxs_getImgfrOpt")) { function nxs_getImgfrOpt($imgOpts, $defSize=''){ if (!is_array($imgOpts)) return $imgOpts;// prr($imgOpts);
   if ($defSize!='' && isset($imgOpts[$defSize]) && trim($imgOpts[$defSize])!='') return $imgOpts[$defSize];
   if (isset($imgOpts['large']) && trim($imgOpts['large'])!='') return $imgOpts['large'];
   if (isset($imgOpts['original']) && trim($imgOpts['original'])!='') return $imgOpts['original'];
   if (isset($imgOpts['thumb']) && trim($imgOpts['thumb'])!='') return $imgOpts['thumb'];
   if (isset($imgOpts['medium']) && trim($imgOpts['medium'])!='') return $imgOpts['medium'];
}}
if (!function_exists('nxs_doFormatMsg')){ function nxs_doFormatMsg($format, $message, $addURLParams=''){ global $nxs_urlLen; $msg = nxs_doSpin($format);// prr($msg); prr($message);// Make "message default"
  if (preg_match('%URL%', $msg)) { $url = $message['url']; if($addURLParams!='') $url .= (strpos($url,'?')!==false?'&':'?').$addURLParams;  $nxs_urlLen = nxs_strLen($url); $msg = str_ireplace("%URL%", $url, $msg);}
  if (preg_match('%SURL%', $msg)) { 
	if (isset($message['surl']) && $message['surl']!='') $url = $message['surl']; else { $url = $message['url']; if($addURLParams!='') $url .= (strpos($url,'?')!==false?'&':'?').$addURLParams; $url = nxs_mkShortURL($url); } 
	$nxs_urlLen = nxs_strLen($url); $msg = str_ireplace("%SURL%", $url, $msg);
  }
  if (preg_match('%IMG%', $msg)) { if (isset($message['imgURL']) && is_array($message['imgURL'])) { $imgURL = trim($message['imgURL']['large']); if ($imgURL=='') $imgURL = trim($message['imgURL']['medium']);   
	  if ($imgURL=='') $imgURL = trim($message['imgURL']['original']); if ($imgURL=='') $imgURL = trim($message['imgURL']['thumb']);
	} elseif (!empty($message['imgURL'])) $imgURL = $message['imgURL']; else $imgURL = '';    $msg = str_ireplace("%IMG%", $imgURL, $msg); 
  }
  if (preg_match('%IMGLARGE%', $msg)) $msg = str_ireplace("%IMG%", trim($message['imgURL']['large'], $msg));  
  if (preg_match('%IMGMEDIUM%', $msg)) $msg = str_ireplace("%IMGMEDIUM%", trim($message['imgURL']['medium'], $msg));  
  if (preg_match('%IMGTHUMB%', $msg)) $msg = str_ireplace("%IMGTHUMB%", trim($message['imgURL']['thumb'], $msg));  
  if (preg_match('%IMGORIGINAL%', $msg)) $msg = str_ireplace("%IMGORIGINAL%", trim($message['imgURL']['original'], $msg));  
  
  if (preg_match('%TITLE%', $msg)) $msg = str_ireplace("%TITLE%", $message['title'], $msg);  
  if (preg_match('%STITLE%', $msg)) { $title = substr($message['title'], 0, 115); $msg = str_ireplace("%STITLE%", $title, $msg); }                    
  if (preg_match('%AUTHORNAME%', $msg)) $msg = str_ireplace("%AUTHORNAME%", $message['authorName'], $msg);
  if (preg_match('%SITENAME%', $msg)) $msg = str_ireplace("%SITENAME%", $message['siteName'], $msg); 
  
  if (preg_match('%ANNOUNCE%', $msg)) { $sText =  trim($message['announce'])!=''?$message['announce']:nsTrnc($message['text'], 300, " ", "...");  $msg = str_ireplace("%ANNOUNCE%", $sText, $msg); }
  if (preg_match('%EXCERPT%', $msg)) { $sText =  trim($message['announce'])!=''?$message['announce']:nsTrnc($message['text'], 300, " ", "...");  $msg = str_ireplace("%EXCERPT%", $sText, $msg); }
  if (preg_match('%RAWEXCERPT%', $msg)) { $sText =  trim($message['announce'])!=''?$message['announce']:nsTrnc($message['text'], 300, " ", "...");  $msg = str_ireplace("%RAWEXCERPT%", $sText, $msg); }
  
  if (preg_match('%TEXT%', $msg)) $msg = str_ireplace("%TEXT%", $message['text'], $msg);     
  if (preg_match('%FULLTEXT%', $msg)) $msg = str_ireplace("%FULLTEXT%", $message['text'], $msg);     
  if (preg_match('%RAWTEXT%', $msg)) $msg = str_ireplace("%RAWTEXT%", $message['text'], $msg);     
	  
  
  if (preg_match('%TAGS%', $msg)) { if (!empty($message['tags'])) $tags = nxs_doProcessTags($message['tags']); else $tags['tags'] = ''; $msg = str_ireplace("%TAGS%", $tags['tags'], $msg); }
  if (preg_match('%HTAGS%', $msg)) { if (!empty($message['tags'])) $tags = nxs_doProcessTags($message['tags']); else $tags['htags'] = ''; $msg = str_ireplace("%HTAGS%", $tags['htags'], $msg); }
  if (preg_match('%CATS%', $msg)) { if (!empty($message['cats'])) $tags = nxs_doProcessTags($message['cats']); else $tags['cats'] = '';  $msg = str_ireplace("%CATS%", $tags['tags'], $msg); }
  if (preg_match('%HCATS%', $msg)) { if (!empty($message['cats'])) $tags = nxs_doProcessTags($message['cats']); else $tags['hcats'] = ''; $msg = str_ireplace("%HCATS%", $tags['htags'], $msg); }
	
  if (preg_match('%CF-[a-zA-Z0-9]%', $msg)) { $msgA = explode('%CF', $msg); $mout = '';
	foreach ($msgA as $mms) { 
		if (substr($mms, 0, 1)=='-' && stripos($mms, '%')!==false) { $mGr = CutFromTo($mms, '-', '%'); $cfItem = $message[$mGr]; $mms = str_ireplace("-".$mGr."%", $cfItem, $mms); } $mout .= $mms; 
	} $msg = $mout; 
  }  
  return trim($msg);
}}
if (!function_exists('nxs_filterOutSettings')) {function nxs_filterOutSettings($ntArr, $allOptions){ $ntArrOut = array(); $arrNts = array_keys($ntArr); 
   foreach ($allOptions as $ntCode=>$nts) if (in_array($ntCode, $arrNts)) foreach ($nts as $ii=>$nt) if (in_array($ii, $ntArr[$ntCode])) $ntArrOut[$ntCode][$ii] = $nt;
   return $ntArrOut;
}}
if (!function_exists('nxs_decodeEntities')){function nxs_decodeEntities($text) {
	$text= html_entity_decode($text,ENT_QUOTES,"ISO-8859-1"); #NOTE: UTF-8 does not work!
	$text= preg_replace('/&#(\d+);/me',"chr(\\1)",$text); #decimal notation
	$text= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$text);  #hex notation
	return $text;
}}
if (!function_exists('nxs_decodeEntitiesFull')){ function nxs_decodeEntitiesFull($string, $quotes = ENT_COMPAT, $charset = 'utf-8') {
  return html_entity_decode(preg_replace_callback('/&([a-zA-Z][a-zA-Z0-9]+);/', 'nxs_convertEntity', $string), $quotes, $charset); 
}}
if (!function_exists('nxs_convertEntity')){ function nxs_convertEntity($matches, $destroy = true) {
  static $table = array('quot' => '&#34;','amp' => '&#38;','lt' => '&#60;','gt' => '&#62;','apos' => '&#39;','OElig' => '&#338;','oelig' => '&#339;','Scaron' => '&#352;','scaron' => '&#353;','Yuml' => '&#376;','circ' => '&#710;','tilde' => '&#732;','ensp' => '&#8194;','emsp' => '&#8195;','thinsp' => '&#8201;','zwnj' => '&#8204;','zwj' => '&#8205;','lrm' => '&#8206;','rlm' => '&#8207;','ndash' => '&#8211;','mdash' => '&#8212;','lsquo' => '&#8216;','rsquo' => '&#8217;','sbquo' => '&#8218;','ldquo' => '&#8220;','rdquo' => '&#8221;','bdquo' => '&#8222;','dagger' => '&#8224;','Dagger' => '&#8225;','permil' => '&#8240;','lsaquo' => '&#8249;','rsaquo' => '&#8250;','euro' => '&#8364;','fnof' => '&#402;','Alpha' => '&#913;','Beta' => '&#914;','Gamma' => '&#915;','Delta' => '&#916;','Epsilon' => '&#917;','Zeta' => '&#918;','Eta' => '&#919;','Theta' => '&#920;','Iota' => '&#921;','Kappa' => '&#922;','Lambda' => '&#923;','Mu' => '&#924;','Nu' => '&#925;','Xi' => '&#926;','Omicron' => '&#927;','Pi' => '&#928;','Rho' => '&#929;','Sigma' => '&#931;','Tau' => '&#932;','Upsilon' => '&#933;','Phi' => '&#934;','Chi' => '&#935;','Psi' => '&#936;','Omega' => '&#937;','alpha' => '&#945;','beta' => '&#946;','gamma' => '&#947;','delta' => '&#948;','epsilon' => '&#949;','zeta' => '&#950;','eta' => '&#951;','theta' => '&#952;','iota' => '&#953;','kappa' => '&#954;','lambda' => '&#955;','mu' => '&#956;','nu' => '&#957;','xi' => '&#958;','omicron' => '&#959;','pi' => '&#960;','rho' => '&#961;','sigmaf' => '&#962;','sigma' => '&#963;','tau' => '&#964;','upsilon' => '&#965;','phi' => '&#966;','chi' => '&#967;','psi' => '&#968;','omega' => '&#969;','thetasym' => '&#977;','upsih' => '&#978;','piv' => '&#982;','bull' => '&#8226;','hellip' => '&#8230;','prime' => '&#8242;','Prime' => '&#8243;','oline' => '&#8254;','frasl' => '&#8260;','weierp' => '&#8472;','image' => '&#8465;','real' => '&#8476;','trade' => '&#8482;','alefsym' => '&#8501;','larr' => '&#8592;','uarr' => '&#8593;','rarr' => '&#8594;','darr' => '&#8595;','harr' => '&#8596;','crarr' => '&#8629;','lArr' => '&#8656;','uArr' => '&#8657;','rArr' => '&#8658;','dArr' => '&#8659;','hArr' => '&#8660;','forall' => '&#8704;','part' => '&#8706;','exist' => '&#8707;','empty' => '&#8709;','nabla' => '&#8711;','isin' => '&#8712;','notin' => '&#8713;','ni' => '&#8715;','prod' => '&#8719;','sum' => '&#8721;','minus' => '&#8722;','lowast' => '&#8727;','radic' => '&#8730;','prop' => '&#8733;','infin' => '&#8734;','ang' => '&#8736;','and' => '&#8743;','or' => '&#8744;','cap' => '&#8745;','cup' => '&#8746;','int' => '&#8747;','there4' => '&#8756;','sim' => '&#8764;','cong' => '&#8773;','asymp' => '&#8776;','ne' => '&#8800;','equiv' => '&#8801;','le' => '&#8804;','ge' => '&#8805;','sub' => '&#8834;','sup' => '&#8835;','nsub' => '&#8836;','sube' => '&#8838;','supe' => '&#8839;','oplus' => '&#8853;','otimes' => '&#8855;','perp' => '&#8869;','sdot' => '&#8901;','lceil' => '&#8968;','rceil' => '&#8969;','lfloor' => '&#8970;','rfloor' => '&#8971;','lang' => '&#9001;','rang' => '&#9002;','loz' => '&#9674;','spades' => '&#9824;','clubs' => '&#9827;','hearts' => '&#9829;','diams' => '&#9830;','nbsp' => '&#160;','iexcl' => '&#161;','cent' => '&#162;','pound' => '&#163;','curren' => '&#164;','yen' => '&#165;','brvbar' => '&#166;','sect' => '&#167;','uml' => '&#168;','copy' => '&#169;','ordf' => '&#170;','laquo' => '&#171;','not' => '&#172;','shy' => '&#173;','reg' => '&#174;','macr' => '&#175;','deg' => '&#176;','plusmn' => '&#177;','sup2' => '&#178;','sup3' => '&#179;','acute' => '&#180;','micro' => '&#181;','para' => '&#182;','middot' => '&#183;','cedil' => '&#184;','sup1' => '&#185;','ordm' => '&#186;','raquo' => '&#187;','frac14' => '&#188;','frac12' => '&#189;','frac34' => '&#190;','iquest' => '&#191;','Agrave' => '&#192;','Aacute' => '&#193;','Acirc' => '&#194;','Atilde' => '&#195;','Auml' => '&#196;','Aring' => '&#197;','AElig' => '&#198;','Ccedil' => '&#199;','Egrave' => '&#200;','Eacute' => '&#201;','Ecirc' => '&#202;','Euml' => '&#203;','Igrave' => '&#204;','Iacute' => '&#205;','Icirc' => '&#206;','Iuml' => '&#207;','ETH' => '&#208;','Ntilde' => '&#209;','Ograve' => '&#210;','Oacute' => '&#211;','Ocirc' => '&#212;','Otilde' => '&#213;','Ouml' => '&#214;','times' => '&#215;','Oslash' => '&#216;','Ugrave' => '&#217;','Uacute' => '&#218;','Ucirc' => '&#219;','Uuml' => '&#220;','Yacute' => '&#221;','THORN' => '&#222;','szlig' => '&#223;','agrave' => '&#224;','aacute' => '&#225;','acirc' => '&#226;','atilde' => '&#227;','auml' => '&#228;','aring' => '&#229;','aelig' => '&#230;','ccedil' => '&#231;','egrave' => '&#232;','eacute' => '&#233;','ecirc' => '&#234;','euml' => '&#235;','igrave' => '&#236;','iacute' => '&#237;','icirc' => '&#238;','iuml' => '&#239;','eth' => '&#240;','ntilde' => '&#241;','ograve' => '&#242;','oacute' => '&#243;','ocirc' => '&#244;','otilde' => '&#245;','ouml' => '&#246;','divide' => '&#247;','oslash' => '&#248;','ugrave' => '&#249;','uacute' => '&#250;','ucirc' => '&#251;','uuml' => '&#252;','yacute' => '&#253;','thorn' => '&#254;','yuml' => '&#255;');
  if (isset($table[$matches[1]])) return $table[$matches[1]];
  // else 
  return $destroy ? '' : $matches[0];
}}
if (!function_exists('nxs_html_to_utf8')){ function nxs_html_to_utf8 ($data){return preg_replace_callback("/\\&\\#([0-9]{3,10})\\;/", create_function ('$matches', 'return nxs__html_to_utf8($matches[2]);'), $data); }}
if (!function_exists('nxs__html_to_utf8')){ function nxs__html_to_utf8 ($data){ if ($data > 127){ $i = 5; while (($i--) > 0){
  if ($data != ($a = $data % ($p = pow(64, $i)))){ 
    $ret = chr(base_convert(str_pad(str_repeat(1, $i + 1), 8, "0"), 2, 10) + (($data - $a) / $p)); for ($i; $i > 0; $i--) $ret .= chr(128 + ((($data % pow(64, $i)) - ($data % ($p = pow(64, $i - 1)))) / $p)); break; }
  }} else $ret = "&#$data;";
  return $ret;
}}
if (!function_exists("nxs_chArrVar")) { function nxs_chArrVar($arr, $varN, $varV){ return (isset($arr) && is_array($arr) && isset($arr[$varN]) && $arr[$varN]==$varV); }}
if (!function_exists('nxsMergeArraysOV')){function nxsMergeArraysOV($Arr1, $Arr2){
  foreach($Arr2 as $key => $value) { if(array_key_exists($key, $Arr1) && is_array($value)) $Arr1[$key] = nxsMergeArraysOV($Arr1[$key], $Arr2[$key]); else $Arr1[$key] = $value;} return $Arr1;
}}
if (!function_exists('nxs_MergeCookieArr')){function nxs_MergeCookieArr($ArrO, $ArrN){ $namesArr = array(); foreach($ArrO as $key => $value) { if (is_object($value)) $namesArr[$key] = $value->name; }             
  foreach($ArrN as $key => $value) { if (is_object($value) && $value->value!='deleted') { $isEx = array_search($value->name, $namesArr); if ($isEx===false) $ArrO[] = $value; else $ArrO[$isEx] = $value;}} return $ArrO;
}}
if (!function_exists('nxs_doProcessTags')){ function nxs_doProcessTags($tags){ $tagsA = array(); if (!is_array($tags)) { $tags = explode(',', $tags); 
  foreach ($tags as $tg) $tagsA[] = trim($tg); } else $tagsA = $tags; $tagsA = array_unique($tagsA);  $tags = array(); 
  foreach ($tagsA as $tg) { $tags['tagsA'][] = $tg; $tags['htagsA'][] = "#".trim(str_replace(' ', '', preg_replace('/[^a-zA-Z0-9\p{L}\p{N}\s]/u', '', trim(ucwords(str_ireplace('&', '', str_ireplace('&amp;','',$tg))))))); } 
  $tags['tags'] =  implode(', ', $tags['tagsA']); $tags['htags'] = implode(', ', $tags['htagsA']);
  return $tags;
}} 
if (!function_exists('nxs_replNRBack')){function nxs_replNRBack(&$v, $k) { if (is_string($v)) { $v = str_replace("҈", "\r",$v); $v = str_replace("҉", "\n",$v); }}}
if (!function_exists('nxs_showListRow')){function nxs_showListRow($ntParams) { $ntInfo = $ntParams['ntInfo']; $nxs_plurl = $ntParams['nxs_plurl']; $ntOpts = $ntParams['ntOpts'];  ?>
          <div class="nxs_box">
            <div class="nxs_box_header"> 
              <div class="nsx_iconedTitle" style="margin-bottom:1px;background-image:url(<?php echo $nxs_plurl;?>img/<?php echo $ntInfo['lcode']; ?>16.png);"><?php echo $ntInfo['name']; ?>
              <?php $cbo = count($ntOpts); ?> 
              <?php if ($cbo>1){ ?><div class="nsBigText"><?php echo "(".($cbo=='0'?'No':$cbo)." "; _e('accounts', 'social-networks-auto-poster-facebook-twitter-g'); echo ")"; ?></div><?php } ?>
              </div>
            </div>
            <div class="nxs_box_inside">            
            <?php  if(!empty($ntParams['checkFunc']) && !function_exists($ntParams['checkFunc']['funcName']) && !class_exists($ntParams['checkFunc']['funcName'])) echo $ntParams['checkFunc']['msg']; 
            else foreach ($ntOpts as $indx=>$pbo) if ($indx!=='') {  if (trim($pbo['nName']=='')) $pbo['nName'] = $ntInfo['name']; $pbo = nxs_FltrsV3toV4($pbo);              
              if (empty($pbo[$ntInfo['lcode'].'OK'])) $pbo[$ntInfo['lcode'].'OK'] = !empty($pbo[$ntParams['chkField']])?'1':''; ?>
              <?php if (function_exists('nxs_adminInitFunc')) { /* if standalone API - don't show checkbox */ ?> 
              <p style="margin:0px;margin-left:5px;"> <img id="<?php echo $ntInfo['code'].$indx;?>LoadingImg" style="display: none;" src='<?php echo $nxs_plurl; ?>img/ajax-loader-sm.gif' />
              <?php  if ((int)$pbo['do'.$ntInfo['code']]>0 && isset($pbo['fltrsOn']) && (int)$pbo['fltrsOn'] == 1) { 
                ?> <input type="radio" id="rbtn<?php echo $ntInfo['lcode'].$indx; ?>" value="2" name="<?php echo $ntInfo['lcode']; ?>[<?php echo $indx; ?>][apDo<?php echo $ntInfo['code']; ?>]" checked="checked" onmouseout="nxs_hidePopUpInfo('popOnlyCat');" onmouseover="nxs_showPopUpInfo('popOnlyCat', event);" /> <?php } else { ?>
                <input value="0" name="<?php echo $ntInfo['lcode']; ?>[<?php echo $indx; ?>][apDo<?php echo $ntInfo['code']; ?>]" type="hidden" />             
                <input value="1" name="<?php echo $ntInfo['lcode']; ?>[<?php echo $indx; ?>][apDo<?php echo $ntInfo['code']; ?>]" type="checkbox" <?php if ((int)$pbo['do'.$ntInfo['code']] > 1) echo "checked"; ?> />             
              <?php } ?>       
              
              <?php if (isset($pbo['rpstOn']) && (int)$pbo['rpstOn'] == 1) { ?> <span onmouseout="nxs_hidePopUpInfo('popReActive');" onmouseover="nxs_showPopUpInfo('popReActive', event);"><?php echo "*[R]*" ?></span><?php } ?>
              <?php }?>
              <strong><?php  _e('Auto-publish to', 'social-networks-auto-poster-facebook-twitter-g'); ?> <?php echo $ntInfo['name']; ?> <i style="color: #005800;"><?php if($pbo['nName']!='') echo "(".$pbo['nName'].")"; ?></i></strong>
              &nbsp;&nbsp;<?php if ($ntInfo['tstReq'] && (!isset($pbo[$ntInfo['lcode'].'OK']) || $pbo[$ntInfo['lcode'].'OK']=='')){ ?><b style="color: #800000"><?php  _e('Attention required. Unfinished setup', 'social-networks-auto-poster-facebook-twitter-g'); ?> ==&gt;</b><?php } ?>              
              <?php if ($ntInfo['lcode']=='li' && !empty($pbo['grpID'])){ ?><b style="color: #800000"><?php  _e('Attention required. Groups are no longer supported by LinkedIn Native API', 'social-networks-auto-poster-facebook-twitter-g'); ?> ==&gt;</b><?php } ?>              
              <a id="do<?php echo $ntInfo['code'].$indx; ?>AG" href="#" onclick="doGetHideNTBlock('<?php echo $ntInfo['code'];?>' , '<?php echo $indx; ?>');return false;">[<?php  _e('Show Settings', 'social-networks-auto-poster-facebook-twitter-g'); ?>]</a>&nbsp;&nbsp;          
              <a href="#" onclick="doDelAcct('<?php echo $ntInfo['lcode']; ?>', '<?php echo $indx; ?>', '<?php if (isset($pbo['bgBlogID'])) echo $pbo['nName']; ?>');return false;">[<?php  _e('Remove Account', 'social-networks-auto-poster-facebook-twitter-g'); ?>]</a>
              </p><div id="nxsNTSetDiv<?php echo $ntInfo['code'].$indx; ?>"></div>
            <?php } ?>
            </div>
          </div> <?php            
        }
}
if (!function_exists('nxs_addQTranslSel')){function nxs_addQTranslSel($nt, $ii, $selLng){  
  if (function_exists('nxs_doSMAS6')) return nxs_doSMAS6($nt, $ii, $selLng); else return '';  
}}
if (!function_exists('nxs_doShowHint')){ function nxs_doShowHint($t, $ex='', $wdth='79'){ ?>
<div id="<?php echo $t; ?>Hint" class="nxs_FRMTHint" style="font-size: 11px; margin: 2px; margin-top: 0px; padding:7px; border: 1px solid #C0C0C0; width: <?php echo $wdth; ?>%; background: #fff; display: none;"><span class="nxs_hili">%TITLE%</span> - <?php _e('Inserts the Title of the post', 'nxs_snap'); ?>, <span class="nxs_hili">%URL%</span> - <?php _e('Inserts the URL of the post', 'nxs_snap'); ?>, <span class="nxs_hili">%SURL%</span> - <?php _e('Inserts the <b>shortened URL</b> of your post', 'nxs_snap'); ?>, <span class="nxs_hili">%IMG%</span> - <?php _e('Inserts the featured image URL', 'nxs_snap'); ?>, <span class="nxs_hili">%EXCERPT%</span> - <?php _e('Inserts the excerpt of the post (processed)', 'nxs_snap'); ?>, <span class="nxs_hili">%RAWEXCERPT%</span> - <?php _e('Inserts the excerpt of the post (as typed)', 'nxs_snap'); ?>,  <span class="nxs_hili">%ANNOUNCE%</span> - <?php _e('Inserts the text till the &lt;!--more--&gt; tag or first N words of the post', 'nxs_snap'); ?>, <span class="nxs_hili">%FULLTEXT%</span> - <?php _e('Inserts the processed body(text) of the post', 'nxs_snap'); ?>, <span class="nxs_hili">%RAWTEXT%</span> - <?php _e('Inserts the body(text) of the post as typed', 'nxs_snap'); ?>, <span class="nxs_hili">%TAGS%</span> - <?php _e('Inserts post tags', 'nxs_snap'); ?>, <span class="nxs_hili">%CATS%</span> - <?php _e('Inserts post categories', 'nxs_snap'); ?>, <span class="nxs_hili">%HTAGS%</span> - <?php _e('Inserts post tags as hashtags', 'nxs_snap'); ?>, <span class="nxs_hili">%HCATS%</span> - <?php _e('Inserts post categories as hashtags', 'nxs_snap'); ?>, <span class="nxs_hili">%AUTHORNAME%</span> - <?php _e('Inserts the author\'s name', 'nxs_snap'); ?>, <span class="nxs_hili">%SITENAME%</span> - <?php _e('Inserts the the Blog/Site name', 'nxs_snap'); ?>. <?php echo $ex; ?></div>
<?php }}
if (!function_exists('nxs_adjRpst')) {function nxs_adjRpst($optionsii, $pval){ return $optionsii; }}
if (!function_exists('nxs_adjFilters')) {function nxs_adjFilters($pval, $optionsii){ return $optionsii; }}
if (!function_exists('nxs_FltrsV3toV4')){ function nxs_FltrsV3toV4($o) {return $o; }}
if (!function_exists('nsx_fixSlashes')){ function nsx_fixSlashes(&$value){ while (strpos($value, '\\\\')!==false) $value = str_replace('\\\\','\\',$value);
   if (strpos($value, "\\'")!==false) $value = str_replace("\\'","'",$value); if (strpos($value, '\\"')!==false) $value = str_replace('\\"','"',$value);
}}
//## Part III: WP Named Functions for compatibility
if (!function_exists('maybe_unserialize')){ function maybe_unserialize( $original ) { if ( is_serialized( $original ) ) return unserialize( $original ); return $original; }}
if (!function_exists('nxs_maybe_unserialize')){ function nxs_maybe_unserialize( $original ) {
  if ( is_serialized( $original ) ) { $__ret = $original; /* $__ret =str_replace("\r","҈",$original); $__ret =str_replace("\n","҉",$__ret); */ $retArr = unserialize( $__ret );   
	if (is_array($retArr)) { array_walk_recursive($retArr, 'nxs_replNRBack'); return $retArr;} else return "Incorrect configuration data";  
  } else return $original;
}}
if (!function_exists('is_serialized')){ function is_serialized( $data ) {
  if ( ! is_string( $data ) ) return false;  $data = trim( $data );
  if ( 'N;' == $data ) return true; $length = strlen( $data ); if ( $length < 4 ) return false;
  if ( ':' !== $data[1] ) return false;  $lastc = $data[$length-1];  if ( ';' !== $lastc && '}' !== $lastc ) return false;  $token = $data[0];
  switch ( $token ) {
	case 's' : if ( '"' !== $data[$length-2] ) return false;
	case 'a' :  case 'O' : return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
	case 'b' :  case 'i' : case 'd' : return (bool) preg_match( "/^{$token}:[0-9.E-]+;\$/", $data );
  }
  return false;
}}
if (!function_exists('__')){function __( $text, $domain = 'default' ) { return $text; }}
if (!function_exists('_e')){function _e( $text, $domain = 'default' ) { echo $text; }}
if (!function_exists('_x')){function _x( $text, $context, $domain = 'default' ) { return $text; }}
if (!function_exists('apply_filters')){function apply_filters( $tag, $value ) { return $value; }}
if (!function_exists('wp_parse_args')){function wp_parse_args( $args, $defaults = '' ) {
  if ( is_object( $args ) ) $r = get_object_vars( $args );
	elseif ( is_array( $args ) ) $r =& $args; else wp_parse_str( $args, $r );
  if ( is_array( $defaults ) ) return array_merge( $defaults, $r );  return $r;
}}
if (!function_exists('wp_parse_str')){function wp_parse_str( $string, &$array ) { parse_str( $string, $array ); if ( get_magic_quotes_gpc() ) $array = stripslashes_deep( $array ); }}
if (!function_exists('wp_remote_request')){function wp_remote_request($url, $args = array()) { return nxs_remote_request($url, $args); }}
if (!function_exists('wp_remote_get')){function wp_remote_get($url, $args = array()) { return nxs_remote_get($url, $args); }}
if (!function_exists('wp_remote_post')){function wp_remote_post($url, $args = array()) { return nxs_remote_post($url, $args); }}
if (!function_exists('wp_remote_head')){function wp_remote_head($url, $args = array()) { return nxs_remote_head($url, $args); }}
if (!function_exists('is_wp_error')){function is_wp_error($thing) { return is_nxs_error($thing); }}
//## DESRC Functions
if (!function_exists("nxsCheckSSLCurl")){function nxsCheckSSLCurl($url){
  $ch = curl_init($url); $headers = array(); $headers[] = 'Accept: text/html, application/xhtml+xml, */*'; $headers[] = 'Cache-Control: no-cache';
  $headers[] = 'Connection: Keep-Alive'; $headers[] = 'Accept-Language: en-us';  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)"); 
  $content = curl_exec($ch); $err = curl_errno($ch); $errmsg = curl_error($ch); if ($err!=0) return array('errNo'=>$err, 'errMsg'=>$errmsg); else return false;
}}
if (!function_exists("getUqID")) {function getUqID() {return mt_rand(0, 9999999);}}
if (!function_exists("rndString")) {function rndString($lngth){$str='';$chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";$size=strlen($chars);for($i=0;$i<$lngth;$i++){$str .= $chars[rand(0,$size-1)];} return $str;}}
if (!function_exists("nxs_clFN")){ function nxs_clFN($fn){$sch = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
  return trim(preg_replace('/[\s-]+/', '-', str_replace($sch, '', $fn)), '.-_');    
}}
if (!function_exists("nxs_mkImgNm")){ function nxs_mkImgNm($fn, $cType){ $iex = array(".png", ".jpg", ".gif", ".jpeg"); $map = array('image/gif'=>'.gif','image/jpeg'=>'.jpg','image/png'=>'.png');
  $fn = str_replace($iex, '', $fn); if (isset($map[$cType])){return $fn.$map[$cType];} else return $fn.".jpg";    
}}
if (!function_exists("nxs_jsonFix")) { function nxs_jsonFix(&$item, &$key){ $item = (substr($item, -4)=='E+12')?(number_format($item, 0, '', '')):$item; }}
if (!function_exists('nxsClnCookies')){ function nxsClnCookies($ck) { $ckOut = array(); $t =time(); foreach ($ck as $c) { if ($c->value!='deleted' && $c->value!='deleteMe' && $c->value!='delete me' && (empty($c->expires) || $c->expires>$t)) $ckOut[] = $c; } return $ckOut; }}
//## Classes
class NXS_HtmlFixer { public $dirtyhtml; public $fixedhtml; public $allowed_styles; private $matrix; public $debug; private $fixedhtmlDisplayCode;
	public function __construct() { $this->dirtyhtml = ""; $this->fixedhtml = ""; $this->debug = false; $this->fixedhtmlDisplayCode = ""; $this->allowed_styles = array();}
	public function getFixedHtml($dirtyhtml) { $c = 0; $this->dirtyhtml = $dirtyhtml; $this->fixedhtml = ""; $this->fixedhtmlDisplayCode = ""; if (is_array($this->matrix)) unset($this->matrix); $errorsFound=0;
	  while ($c<10) { if ($c>0) $this->dirtyhtml = $this->fixedxhtml; $errorsFound = $this->charByCharJob(); if (!$errorsFound) $c=10;  $this->fixedxhtml=str_replace('<root>','',$this->fixedxhtml); 
		$this->fixedxhtml=str_replace('</root>','',$this->fixedxhtml); $this->fixedxhtml = $this->removeSpacesAndBadTags($this->fixedxhtml); $c++;
	  } return $this->fixedxhtml;
	}
	private function fixStrToLower($m){ $right = strstr($m, '='); $left = str_replace($right,'',$m); return strtolower($left).$right;}
	private function fixQuotes($s){ $q = "\""; if (!stristr($s,"=")) return $s; $out = $s; preg_match_all("|=(.*)|",$s,$o,PREG_PATTERN_ORDER);
	  for ($i = 0; $i< count ($o[1]); $i++) { $t = trim ( $o[1][$i] ) ; $lc=""; if ($t!="") { if ($t[strlen($t)-1]==">") { $lc= ($t[strlen($t)-2].$t[strlen($t)-1])=="/>"  ?  "/>"  :  ">" ; $t=substr($t,0,-1);}
		if (($t[0]!="\"")&&($t[0]!="'")) $out = str_replace( $t, "\"".$t,$out); else $q=$t[0]; if (($t[strlen($t)-1]!="\"")&&($t[strlen($t)-1]!="'")) $out = str_replace( $t.$lc, $t.$q.$lc,$out);
	  }} return $out;
	}
	private function fixTag($t){  $t = preg_replace ( array( '/borderColor=([^ >])*/i', '/border=([^ >])*/i' ),  array('',''), $t);
		preg_match_all('/(?:"[^"]*"|\'[^\']*\'|[^"\'\s]+)+/', $t, $ar);  $ar = $ar[0];// prr($ar);
		$nt = ""; for ($i=0;$i<count($ar);$i++) { if (strpos($ar[$i], 'href=\\\\\\"')!==false) {$ar[$i] = str_replace('\\\\\\"','"',$ar[$i]);}
		  if (strpos($ar[$i], 'href=\\"')!==false) {$ar[$i] = str_replace('\\"','"',$ar[$i]);} if (strpos($ar[$i], 'href=\"')!==false) {$ar[$i] = str_replace('\"','"',$ar[$i]);}
		  $ar[$i]=$this->fixStrToLower($ar[$i]); if (stristr($ar[$i],"=")) $ar[$i] = $this->fixQuotes($ar[$i]); $nt.=$ar[$i]." ";   
		} $nt=preg_replace("/<( )*/i","<",$nt); $nt=preg_replace("/( )*>/i",">",$nt); return trim($nt);
	}
	private function extractChars($tag1,$tag2,$tutto) {  if (!stristr($tutto, $tag1)) return ''; $s=stristr($tutto,$tag1); $s=substr( $s,strlen($tag1)); if (!stristr($s,$tag2)) return '';
		$s1=stristr($s,$tag2); return substr($s,0,strlen($s)-strlen($s1));
	}
	private function mergeStyleAttributes($s) { $x = ""; $temp = ""; $c = 0;
		while(stristr($s,"style=\"")) {$temp = $this->extractChars("style=\"","\"",$s); if ($temp=="") { return preg_replace("/(\/)?>/i","\"\\1>",$s);}
			if ($c==0) $s = str_replace("style=\"".$temp."\"","##PUTITHERE##",$s); $s = str_replace("style=\"".$temp."\"","",$s); if (!preg_match("/;$/i",$temp)) $temp.=";"; $x.=$temp; $c++;
		}
		if (count($this->allowed_styles)>0) { $check=explode(';', $x); $x=""; foreach($check as $chk){ foreach($this->allowed_styles as $as) if(stripos($chk, $as) !== False) { $x.=$chk.';'; break; } }}
		if ($c>0) $s = str_replace("##PUTITHERE##","style=\"".$x."\"",$s);return $s;
	}
	private function fixAutoclosingTags($tag,$tipo=""){ if (in_array( $tipo, array ("img","input","br","hr")) ) { if (!stristr($tag,'/>')) $tag = str_replace('>','/>',$tag ); } return $tag; }
	private function getTypeOfTag($tag) { $tag = trim(preg_replace("/[\>\<\/]/i","",$tag)); $a = explode(" ",$tag); return $a[0];}
	private function checkTree() { $errorsCounter = 0; for ($i=1;$i<count($this->matrix);$i++) { $flag=false;
	  if ($this->matrix[$i]["tagType"]=="div") { $parentType = $this->matrix[$this->matrix[$i]["parentTag"]]["tagType"]; if (in_array($parentType, array("p","b","i","font","u","small","strong","em"))) $flag=true; }
	  if (in_array( $this->matrix[$i]["tagType"], array( "b", "strong" )) ) {  $parentType = $this->matrix[$this->matrix[$i]["parentTag"]]["tagType"]; if (in_array($parentType, array("b","strong"))) $flag=true; }
	  if (in_array( $this->matrix[$i]["tagType"], array ( "i", "em") )) {  $parentType = $this->matrix[$this->matrix[$i]["parentTag"]]["tagType"]; if (in_array($parentType, array("i","em"))) $flag=true; }
	  if ($this->matrix[$i]["tagType"]=="p") { $parentType = $this->matrix[$this->matrix[$i]["parentTag"]]["tagType"]; if (in_array($parentType, array("p","b","i","font","u","small","strong","em"))) $flag=true; }
	  if ($this->matrix[$i]["tagType"]=="table") { $parentType = $this->matrix[$this->matrix[$i]["parentTag"]]["tagType"]; if (in_array($parentType, array("p","b","i","font","u","small","strong","em","tr","table"))) $flag=true; }
	  if ($flag) { $errorsCounter++; if ($this->debug) echo "<div style='color:#ff0000'>Found a <b>".$this->matrix[$i]["tagType"]."</b> tag inside a <b>".htmlspecialchars($parentType)."</b> tag at node $i: MOVED</div>";                
		$swap = $this->matrix[$this->matrix[$i]["parentTag"]]["parentTag"]; if ($this->debug) echo "<div style='color:#ff0000'>Every node that has parent ".$this->matrix[$i]["parentTag"]." will have parent ".$swap."</div>";
		$this->matrix[$this->matrix[$i]["parentTag"]]["tag"]="<!-- T A G \"".$this->matrix[$this->matrix[$i]["parentTag"]]["tagType"]."\" R E M O V E D -->"; $this->matrix[$this->matrix[$i]["parentTag"]]["tagType"]="";
		$hoSpostato=0;for ($j=count($this->matrix)-1;$j>=$i;$j--) { if ($this->matrix[$j]["parentTag"]==$this->matrix[$i]["parentTag"]) { $this->matrix[$j]["parentTag"] = $swap; $hoSpostato=1; }}
	  }}return $errorsCounter;
	}
	private function findSonsOf($parentTag) { $out= "";
	  for ($i=1;$i<count($this->matrix);$i++) { if ($this->matrix[$i]["parentTag"]==$parentTag) {
		  if ($this->matrix[$i]["tag"]!="") { $out.=$this->matrix[$i]["pre"]; $out.=$this->matrix[$i]["tag"]; $out.=$this->matrix[$i]["post"]; } else { $out.=$this->matrix[$i]["pre"]; $out.=$this->matrix[$i]["post"];}
		  if ($this->matrix[$i]["tag"]!="") { $out.=$this->findSonsOf($i); if ($this->matrix[$i]["tagType"]!="") { if (!in_array($this->matrix[$i]["tagType"], array ( "br","img","hr","input"))) $out.="</". $this->matrix[$i]["tagType"].">";}}
	  }}return $out;
	}
	private function findSonsOfDisplayCode($parentTag) { $out= "";
		for ($i=1;$i<count($this->matrix);$i++) {
			if ($this->matrix[$i]["parentTag"]==$parentTag) { $out.= "<div style=\"padding-left:15\"><span style='float:left;background-color:#FFFF99;color:#000;'>{$i}:</span>";
				if ($this->matrix[$i]["tag"]!="") { if ($this->matrix[$i]["pre"]!="") $out.=htmlspecialchars($this->matrix[$i]["pre"])."<br>";
					$out.="".htmlspecialchars($this->matrix[$i]["tag"])."<span style='background-color:red; color:white'>{$i} <em>".$this->matrix[$i]["tagType"]."</em></span>";
					$out.=htmlspecialchars($this->matrix[$i]["post"]);
				} else { if ($this->matrix[$i]["pre"]!="") $out.=htmlspecialchars($this->matrix[$i]["pre"])."<br>"; $out.=htmlspecialchars($this->matrix[$i]["post"]);}
				if ($this->matrix[$i]["tag"]!="") { $out.="<div>".$this->findSonsOfDisplayCode($i)."</div>\n";
					if ($this->matrix[$i]["tagType"]!="") {
						if (($this->matrix[$i]["tagType"]!="br") && ($this->matrix[$i]["tagType"]!="img") && ($this->matrix[$i]["tagType"]!="hr")&& ($this->matrix[$i]["tagType"]!="input"))
							$out.="<div style='color:red'>".htmlspecialchars("</". $this->matrix[$i]["tagType"].">")."{$i} <em>".$this->matrix[$i]["tagType"]."</em></div>";
					}
				} $out.="</div>\n";
			}
		}return $out;
	}
	private function removeSpacesAndBadTags($s) { $i=0;
	  while ($i<10) { $i++; $s = preg_replace (
		array( '/  /i', '/<p([^>])*>(&nbsp;)*\s*<\/p>/i', '/<span([^>])*>(&nbsp;)*\s*<\/span>/i', '/<strong([^>])*>(&nbsp;)*\s*<\/strong>/i', '/<em([^>])*>(&nbsp;)*\s*<\/em>/i',
		  '/<font([^>])*>(&nbsp;)*\s*<\/font>/i', '/<small([^>])*>(&nbsp;)*\s*<\/small>/i', '/<\?xml:namespace([^>])*><\/\?xml:namespace>/i', '/<\?xml:namespace([^>])*\/>/i', '/class=\"MsoNormal\"/i',
		  '/<o:p><\/o:p>/i', '/<!DOCTYPE([^>])*>/i', '/<!--(.|\s)*?-->/', '/<\?(.|\s)*?\?>/'), 
		array(' ', ' ', '', '', '', '', '', '', '', '', '', ' ', '', '' ) , trim($s));
	  }return $s;
	}
	private function charByCharJob() { $s = $this->removeSpacesAndBadTags($this->dirtyhtml); if ($s=="") return; //echo "\r\n=!= ".$s." =!=\r\n<br/>\r\n";
		$s = "<root>".$s."</root>"; $contenuto = ""; $ns = ""; $i=0; $j=0; $ss=''; $indexparentTag=0; $padri=array(); array_push($padri,"0"); $this->matrix[$j]["tagType"]="";
		$this->matrix[$j]["tag"]=""; $this->matrix[$j]["parentTag"]="0"; $this->matrix[$j]["pre"]=""; $this->matrix[$j]["post"]=""; $tags=array();
		// echo "\r\n=#= ".$s." =#=\r\n<br/>\r\n";
		while($i<strlen($s)) {
			if ( $s[$i] =="<") { $contenuto = $ns; $ns = ""; $tag=""; while( $i<strlen($s) && $s[$i]!=">" ){ $tag.=$s[$i]; $i++;} $tag.=$s[$i]; if (stristr($tag,'<param') && stristr($tag,'/>')) $tag = str_replace('/>','></param>',$tag);
			$ss .= $tag;                 
		} else $ss .= $s[$i]; $i++; }
		$i=0; $s = $ss; //echo "\r\n== ".$s." ==\r\n<br/>\r\n";
		while($i<strlen($s)) {
			if ( $s[$i] =="<") { $contenuto = $ns; $ns = ""; $tag=""; while( $i<strlen($s) && $s[$i]!=">" ){ $tag.=$s[$i]; $i++;} $tag.=$s[$i];                
				if($s[$i]==">") { $tag = $this->fixTag($tag); $tagType = $this->getTypeOfTag($tag); $tag = $this->fixAutoclosingTags($tag,$tagType);
					$tag = $this->mergeStyleAttributes($tag); if (!isset($tags[$tagType])) $tags[$tagType]=0; $tagok=true;
					if (($tags[$tagType]==0)&&(stristr($tag,'/'.$tagType.'>'))&&(stristr($tag,'<'.$tagType)!==false)) { $tagok=false; if ($this->debug) echo "<div style='color:#ff0000'>Found a closing tag <b>".htmlspecialchars($tag)."</b> at char $i without open tag: REMOVED</div>";} else $tagok=true;
				}
				if ($tagok) { $j++; $this->matrix[$j]["pre"]=""; $this->matrix[$j]["post"]=""; $this->matrix[$j]["parentTag"]=""; $this->matrix[$j]["tag"]=""; $this->matrix[$j]["tagType"]="";
					if (stristr($tag,'/'.$tagType.'>')) { $ind = array_pop($padri); $this->matrix[$j]["post"]=$contenuto; $this->matrix[$j]["parentTag"]=$ind; $tags[$tagType]--;
					} else { if (@preg_match("/".$tagType."\/>$/i",$tag)||preg_match("/\/>/i",$tag)) { $this->matrix[$j]["tagType"]=$tagType; $this->matrix[$j]["tag"]=$tag;
					  $indexparentTag = array_pop($padri); array_push($padri,$indexparentTag); $this->matrix[$j]["parentTag"]=$indexparentTag; $this->matrix[$j]["pre"]=$contenuto; $this->matrix[$j]["post"]="";
					} else { $tags[$tagType]++; $this->matrix[$j]["tagType"]=$tagType; $this->matrix[$j]["tag"]=$tag; $indexparentTag = array_pop($padri); array_push($padri,$indexparentTag);
					  array_push($padri,$j); $this->matrix[$j]["parentTag"]=$indexparentTag; $this->matrix[$j]["pre"]=$contenuto; $this->matrix[$j]["post"]=""; }
					}
				}
			} else { $ns.=$s[$i]; } $i++;
		} for ($eli=$j+1;$eli<count($this->matrix);$eli++) { $this->matrix[$eli]["pre"]=""; $this->matrix[$eli]["post"]=""; $this->matrix[$eli]["parentTag"]=""; $this->matrix[$eli]["tag"]=""; $this->matrix[$eli]["tagType"]="";}
		$errorsCounter = $this->checkTree();  $this->fixedxhtml=$this->findSonsOf(0);return $errorsCounter;
	}
}
if (!function_exists("nxs_mkRemOptsArr")) {function nxs_mkRemOptsArr($hdrsArr, $ck='', $flds='', $p='', $rdr=0, $timt=45, $sslverify = false){ 
  $a = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => $timt, 'redirection' => $rdr, 'sslverify'=>$sslverify); if (!empty($flds)) $a['body'] = $flds; if (!empty($p)) $a['proxy'] = $p;  if (!empty($ck)) $a['cookies'] = $ck; return $a;
}}
if (!function_exists("nxs_show_noLibWrn")) {function nxs_show_noLibWrn($msg){ echo "API Lib not found"; }}
?>