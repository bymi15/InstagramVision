<?php //## NextScripts SNAP API 2.24.2
//================================GOOGLE===========================================
if (!class_exists('nxsAPI_GP')){ class nxsAPI_GP{ var $ck = array(); var $debug = false; var $proxy = array(); var $at=''; var $pig='';
    function headers($ref, $org='', $type='GET', $aj=false){  $hdrsArr = array();
      $hdrsArr['Cache-Control']='max-age=0'; $hdrsArr['Connection']='keep-alive'; $hdrsArr['Referer']=$ref;
      $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.54 Safari/537.36';
      if($type=='JSON') $hdrsArr['Content-Type']='application/json;charset=UTF-8'; elseif($type=='POST') $hdrsArr['Content-Type']='application/x-www-form-urlencoded';
        elseif($type=='JS') $hdrsArr['Content-Type']='application/javascript; charset=UTF-8'; elseif($type=='PUT') $hdrsArr['Content-Type']='application/octet-stream';
      if($aj===true) $hdrsArr['X-Requested-With']='XMLHttpRequest';  if ($org!='') $hdrsArr['Origin']=$org;
      if ($type=='GET') $hdrsArr['Accept']='text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'; else $hdrsArr['Accept']='*/*';
      if (function_exists('gzdeflate')) $hdrsArr['Accept-Encoding']='deflate,sdch';
      $hdrsArr['Accept-Language']='en-US,en;q=0.8'; return $hdrsArr;
    }
    function prcGSON($gson){ $json = substr($gson, 5); $json = str_replace("\r",'',$json); $json = str_replace("\n",'',$json); $json = str_replace(',{',',{"',$json); $json = str_replace(':[','":[',$json); $json = str_replace(',{""',',{"',$json); $json = str_replace('"":[','":[',$json);
      $json = str_replace('[,','["",',$json); $json = str_replace(',,',',"",',$json); $json = str_replace(',,',',"",',$json); return $json;
    }
    function check($srv, $u){ $ck = $this->ck;  if (!empty($ck) && is_array($ck)) {  if ($this->debug) echo "[G] Checking ".$srv." ;<br/>\r\n";  // prr($ck); //die();
        if ($srv=='GP') { $hdrsArr = $this->headers('https://plus.google.com/'); $url = 'https://plus.google.com/';}
        if ($srv=='YT') { $hdrsArr = $this->headers('https://www.youtube.com/'); $url = 'https://www.youtube.com/feed/subscriptions';}
        if ($srv=='BG') { $hdrsArr = $this->headers('https://www.blogger.com/'); $url = 'https://www.blogger.com/user-settings.g';}
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($url, $advSet); //prr($rep);
        if (is_nxs_error($rep)) return false; if ($rep['response']['code']=='302' && stripos($rep['headers']['location'], 'accounts.google.com')!==false) return false;
        if (stripos($rep['body'], $u)===false) return false; return true;
    } return false; }
    function connect($u,$p,$srv='GP'){ $sslverify = true;
      if (!$this->check($srv, $u)){ if ($this->debug) echo "[GP] NO Saved Data; Logging in...<br/>\r\n"; if ($this->debug) echo "[".$srv."] L to: ".$srv."<br/>\r\n";
        $err = nxsCheckSSLCurl('https://www.google.com'); if ($err!==false && $err['errNo']=='60') $sslverify = false;
        if ($srv == 'GP') $lpURL = 'https://accounts.google.com/ServiceLogin?service=oz&continue=https://plus.google.com/?gpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3Dc7578f19&hl=en-US';
        if ($srv == 'YT') $lpURL = 'https://accounts.google.com/ServiceLogin?service=oz&checkedDomains=youtube&checkConnection=youtube%3A271%3A1%2Cyoutube%3A69%3A1&continue=https://www.youtube.com/&hl=en-US';
        if ($srv == 'BG') $lpURL = 'https://accounts.google.com/ServiceLogin?service=blogger&passive=1209600&continue=https://www.blogger.com/home&followup=https://www.blogger.com/home&ltmpl=start';
        $hdrsArr = $this->headers('https://accounts.google.com/'); $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); $rep = nxs_remote_get($lpURL, $advSet);
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR X ="; return $badOut; } $ck = $rep['cookies']; $contents = $rep['body']; //if ($this->debug) prr($contents);
        //## GET HIDDEN FIELDS
        $md = array(); $flds  = array();
        while (stripos($contents, '<input')!==false){ $inpField = trim(CutFromTo($contents,'<input', '>')); $name = trim(CutFromTo($inpField,'name="', '"'));
          if ( stripos($inpField, '"hidden"')!==false && $name!='' && !in_array($name, $md)) { $md[] = $name; $val = trim(CutFromTo($inpField,'value="', '"')); $flds[$name]= $val;}
          $contents = substr($contents, stripos($contents, '<input')+8);
        } $flds['Email'] = $u; $flds['Passwd'] = $p;  $flds['signIn'] = 'Sign%20in'; $flds['PersistentCookie'] = 'yes'; $flds['rmShown'] = '1'; $flds['pstMsg'] = '1'; // $flds['bgresponse'] = $bg;
        //if ($srv == 'GP' || $srv == 'BG') $advSettings['cdomain']='google.com';
        //## ACTUAL LOGIN
        $hdrsArr = $this->headers($lpURL, 'https://accounts.google.com', 'POST');  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy);
        $rep = nxs_remote_post('https://accounts.google.com/ServiceLoginAuth', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 3="; return $badOut; } $ck = $rep['cookies']; // prr($rep);
        $unlockCaptchaMsg = "Your Google+ account is locked for the new applications to connect. Please follow this instructions to unlock it: <a href='http://www.nextscripts.com/blog/nxsfaq/how-to-unlock-google/' target='_blank'>http://www.nextscripts.com/blog/nxsfaq/how-to-unlock-google/</a>";
        $twoStepVerMsg = '<b style="color:#800000;">2-Step Verification is on. </b>2-Step Verification is not compatible with auto-posting. Please see more here:<br/> <a href="http://www.nextscripts.com/blog/google-2-step-verification-and-auto-posting" target="_blank">Google+, 2-step verification and auto-posting</a><br/>';
        if ($rep['response']['code']=='200' && !empty($rep['body'])) { $rep['body'] = str_ireplace('\'CREATE_CHANNEL_DIALOG_TITLE_IDV_CHALLENGE\': "Verify your identity"', "", $rep['body']);
          if (stripos($rep['body'],'class="error-msg"')!==false) { $ret = strip_tags(CutFromTo(CutFromTo($rep['body'],'class="error-msg"','</span>').'||', '>', '||'));
            if (trim($ret)==""){$rep['body']=CutFromTo($rep['body'],'class="error-msg"','</html>'); return strip_tags(CutFromTo(CutFromTo($rep['body'],'class="error-msg"','</span>').'||', '>', '||'));}
          } if (stripos($rep['body'],'class="captcha-box"')!==false || stripos($rep['body'],'is that really you')!==false || stripos($rep['body'],'Verify your identity')!==false) return $unlockCaptchaMsg;
        }
        if ($rep['response']['code']=='302' && !empty($rep['headers']['location'])){ $repLoc = $rep['headers']['location']; if(stripos($repLoc, 'ServiceLoginAuth')!==false || stripos($repLoc, 'ServiceLogin')!==false) return 'Incorrect Username/Password ';
          if((stripos($repLoc, 'LoginVerification')!==false || stripos($repLoc, '/selectchallenge')!==false)) return $unlockCaptchaMsg;
          if(( stripos($repLoc, '/SmsAuth')!==false || stripos($repLoc, '/SecondFactor')!==false)) return $twoStepVerMsg;
          if(( stripos($repLoc, '/challenge')!==false)) return "Can't login. Two possible reasons: <br/>1. ".$twoStepVerMsg."<br/>2. ".$unlockCaptchaMsg;
          if ($srv == 'BG') $repLoc = 'https://accounts.google.com/CheckCookie?checkedDomains=youtube&checkConnection=youtube%3A170%3A1&pstMsg=1&chtml=LoginDoneHtml&service=blogger&continue=https%3A%2F%2Fwww.blogger.com%2Fhome&gidl=CAA';
          if ($srv == 'YT') $repLoc = 'https://accounts.google.com/CheckCookie?hl=en-US&checkedDomains=youtube&checkConnection=youtube%3A271%3A1%2Cyoutube%3A69%3A1&pstMsg=1&chtml=LoginDoneHtml&service=oz&continue=https%3A%2F%2Fwww.youtube.com%2F&gidl=CAA';
          if ($srv == 'GP') $repLoc = 'https://accounts.google.com/CheckCookie?hl=en-US&checkedDomains=youtube&checkConnection=youtube%3A179%3A1&pstMsg=1&chtml=LoginDoneHtml&service=oz&continue=https%3A%2F%2Fplus.google.com%2F%3Fgpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3Dc7578f19&gidl=CAA';
          if ($this->debug) echo "[".$srv."] R to: ".$repLoc."<br/>\r\n";  $hdrsArr = $this->headers($lpURL, 'https://accounts.google.com');
          $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($repLoc, $advSet);
          if (!is_nxs_error($rep) && $srv == 'YT' && $rep['response']['code']=='302' && !empty($rep['headers']['location'])) { $repLoc = $rep['headers']['location'];
            $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($repLoc, $advSet); $ck = $rep['cookies'];
          } if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 4="; return $badOut; } $contents = $rep['body']; $rep['body'] = '';
          //## BG Auth redirect
          if ($srv != 'GP' && stripos($contents, 'meta http-equiv="refresh"')!==false) {$rURL = htmlspecialchars_decode(CutFromTo($contents,';url=','"'));
            if ($this->debug) echo "[".$srv."] R to: ".$rURL."<br/>\r\n";  $hdrsArr = $this->headers($repLoc); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($rURL, $advSet);//  prr($rep);
            if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 5="; return $badOut; } $ck = $rep['cookies'];
            if (!empty($rep['headers']['location'])) { $rURL = $rep['headers']['location']; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($rURL, $advSet);
              if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 6="; return $badOut; }
              if (!empty($rep['headers']['location'])) { $rURL = $rep['headers']['location'];  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($rURL, $advSet);
                if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 7="; return $badOut; }
              } if (!empty($rep['headers']['location'])) $ck = $rep['cookies']; else $rep['cookies'] = $ck;
            } $ck = $rep['cookies'];
          } $this->ck = $ck; return false;
        } return 'Unexpected Error, Please contact support';
      } else { if ($this->debug) echo "[GP] Saved Data is OK;<br/>\r\n"; return false; }
    }
    function getAt() { if (!empty($this->at)) return true; $ck = $this->ck;
      $hdrsArr = $this->headers('');  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy, 2); $rep = nxs_remote_get('https://plus.google.com/', $advSet);
      if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR CSI"; return $badOut; } $contents = $rep['body'];
      if (stripos($contents,'window.IJ_values = ')!==false) { $pig = CutFromTo($contents, 'window.IJ_values = ', ',];').']'; $pig = str_replace("'",'"',$pig); $pig = str_replace('\x2','',$pig);
        $pig = str_replace('\x3','',$pig); $pig = json_decode($pig, true); for ($k = 31; $k<45; $k++) if (!empty($pig[$k]) && is_numeric($pig[$k]) && $pig[$k]>1177680286367) { $this->pig = $pig[$k]; break;}
      }

      if (stripos($contents,'"SNlM0e":"')!==false) $at = CutFromTo($contents, '"SNlM0e":"', '",'); else return "Error (NXS): Lost Login info. Please see FAQ #3.4 or contact support";
      $this->at = $at; return true;
    }
    function urlInfo($url){ $out['link'] = $url; $url = urlencode($url); $at="623482169132-88"; $sslverify = false; $ck = $this->ck; $res = $this->getAt(); if ($res!==true) return $res; else $at = $this->at;
      $spar='f.req=%5B%5B%5B92371866%2C%5B%7B%2292371866%22%3A%5B%22'.$url.'%22%2C%5B%5B73046798%5D%2C%5B%5D%5D%2C1%5D%7D%5D%2Cnull%2Cnull%2C0%5D%5D%5D&at='.urlencode($at)."&";
      $gurl='https://plus.google.com/_/PlusAppUi/data?ds.extension=92371866&hl=en&soc-app=199&soc-platform=1&soc-device=1&_reqid=7372229&rt=c';
      $hdrsArr = $this->headers('https://plus.google.com/', 'https://plus.google.com', 'POST', true); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $spar, $this->proxy);
      $rep = nxs_remote_post($gurl, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $contents = $rep['body'];
      if (stripos($contents,',[["')!==false)  $out['img'] = CutFromTo($contents, ',[["', '",');
      return $out;
    }
    function getCCatsGP($commPageID){ $items = '';   $sslverify = false; $ck = $this->ck;
      $hdrsArr = $this->headers('https://plus.google.com/'); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://plus.google.com/communities/'.$commPageID, $advSet);
      if (is_nxs_error($rep)) return false; if (!empty($rep['cookies'])) $ck = $rep['cookies']; $contents = $rep['body']; //prr($rep);
      $tmps = CutFromTo($contents,"key: 'ds:7'",'}});'); $commPageID2 = '['.stripslashes(str_replace('\n', '', CutFromTo($tmps, '",[', "]\n]\n]"))); if (substr($commPageID2, -1)=='"') $commPageID2.="]]"; else $commPageID2.="]]]";
      $commPageID2 = str_replace('\u0026','&',$commPageID2); $commPageID2 = json_decode($commPageID2);
      if (is_array($commPageID2)) foreach ($commPageID2 as $cpiItem) if (is_array($cpiItem)) { $val = $cpiItem[0]; $name = $cpiItem[1]; $items .= '<option value="'.$val.'">'.$name.'</option>'; }
      return $items;
    }
    function chckForCpt($content, $ck){ if(stripos($content, 'action="/das_captcha"')!==false) { global $nxs_plurl;
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $ca = nxs_remote_get('https://www.google.com/recaptcha/api/noscript?k=6LcV18ESAAAAAI1Z5NnSAUBz4pTj3hYJQ_6NLgFN', $advSet);
      if (is_nxs_error($ca)) {  $badOut = print_r($ca, true)." - [captcha] ERROR"; return $badOut; } $img = CutFromTo($ca['body'], 'src="image?c=', '"');
      $formcode = '<form '.CutFromTo($content, '<form method="post" action="/das_captcha"', '</form>');  $formcode = str_ireplace('</iframe>', '', $formcode);
      $formcode = str_ireplace('<iframe src="//www.google.com/recaptcha/api/noscript?k=6LcV18ESAAAAAI1Z5NnSAUBz4pTj3hYJQ_6NLgFN" height="300" width="500" frameborder="0"', $ca['body'], $formcode);
      $img = '<img style="display:block;" alt="reCAPTCHA challenge image" height="57" width="300" src="'.$nxs_snapSetPgURL.'?pg=nxs&ca='.$img.'"/>';
      echo "Google asked you to enter Captcha. Please type the two words separated by a space (not case sensitive) and click \"Continue\"";
      echo $img; echo '<br/><input value="" style="width: 30%;" id="nxs_cpt_val" name="nxs_cpt" /><input type="hidden" id="nxsLiNum" name="nxsLiNum" value="'.$iidb.'" /><input type="button" value="Continue" onclick="doCtpSave(); return false;" id="results_ok_button" name="nxs_go" class="button" />'; ?><script type="text/javascript">
        function doCtpSave(){ var u = jQuery('#nxs_cpt_val').val(); var ii = jQuery('#nxsLiNum').val(); //alert(ii);
          var style = "position: fixed; display: none; z-index: 1000; top: 50%; left: 50%; background-color: #E8E8E8; border: 1px solid #555; padding: 15px; width: 350px; min-height: 80px; margin-left: -175px; margin-top: -40px; text-align: center; vertical-align: middle;";
          jQuery('body').append("<div id='test_results' style='" + style + "'></div>");
          jQuery.post(ajaxurl,{c:u, i:ii, action: 'nxs_snap_aj',"nxsact":'nxsCptCheckGP', id: 0, _wpnonce: jQuery('#nxsSsPageWPN_wpnonce').val()}, function(j){
            jQuery('#test_results').html('<p> ' + j + '</p>' +'<input type="button" class="button" onclick="jQuery(\'#test_results\').hide();" name="results_ok_button" id="results_ok_button" value="OK" />'); jQuery('#test_results').show();
          }, "html")
        }</script> <?php echo "||".$formcode."||";
        while (stripos($formcode, '"hidden"')!==false){$formcode = substr($formcode, stripos($formcode, '"hidden"')+8); $name = trim(CutFromTo($formcode,'name="', '"')); $md = array();
          if (!in_array($name, $md)) { $md[] = $name; $val = trim(CutFromTo($formcode,'value="', '"')); $flds[$name]= $val;      $mids .= "&".$name."=".$val;}
        } $ser = array(); $ser['c'] = $ck; $ser['f'] = $flds; $seForDB = serialize($ser); session_id("nxs-temp-gpcpt"); session_start(); $_SESSION["nxs-temp-gpcpt"] = $seForDB;
      }
    }
    function getPgsCmns($pgID){ $items = '';   $sslverify = false; $ck = $this->ck; $hdrsArr = $this->headers('https://plus.google.com/');
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://plus.google.com/dashboard?ppsrc=gpnv0', $advSet); //prr($advSet);
      if (is_nxs_error($rep)) return false; if (!empty($rep['cookies'])) $ck = $rep['cookies']; $contents = $rep['body'];
      $code = CutFromTo(CutFromTo($contents, "<script>AF_initDataCallback({key: '104'",'});</script>')."WWW=|=|=WWW", ', data:', 'WWW=|=|=WWW'); $code = json_decode($this->prcGSON('-----'.$code), true);
      if (!empty($code) && is_array($code) && !empty($code[1]) && is_array($code[1]) && !empty($code[1][1]) && is_array($code[1][1]) && !empty($code[1][1][0]) && is_array($code[1][1][0])) $code = $code[1][1][0];
      if (!empty($code)) { $items .= '<option disabled>Pages</option>'; foreach ($code as $cd) {
          $name = $cd[0][4][1]; $url= $cd[0][2]; $id = $cd[0][30]; $items .= '<option class="nxsBlue" '.($pgID==$id?'selected="selected"':'').' value="'.$id.'">&nbsp;&nbsp;&nbsp;'.$name.'</option>';
      }}
      if (stripos($contents,'csi.gstatic.com/csi","')!==false) $at = CutFromTo($contents, 'csi.gstatic.com/csi","', '",'); else {
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://plus.google.com/', $advSet);
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR CSI"; return $badOut; } /* if (!empty($rep['cookies'])) $ck = $rep['cookies']; */ $contents = $rep['body']; // prr($rep);
        if (stripos($contents,'csi.gstatic.com/csi","')!==false) $at = CutFromTo($contents, 'csi.gstatic.com/csi","', '",');  else return "Error (NXS): Lost Login info. Please contact support";
      } $ck = $rep['cookies'];
      $hdrsArr = $this->headers('https://plus.google.com', 'https://plus.google.com', 'POST', true); $hdrsArr['X-Same-Domain']='1'; $spar = 'f.req=%5Bfalse%2Cfalse%5D&at='.$at;
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $spar, $this->proxy); $rep = nxs_remote_post('https://plus.google.com/u/0/_/communities/gethome?soc-app=1&cid=0&soc-platform=1&hl=en&rt=j', $advSet); //prr($advSet);
      if (is_nxs_error($rep)) return false; if (!empty($rep['cookies'])) $ck = $rep['cookies']; $contents = $rep['body'];     //  prr($rep); die();
      $code = json_decode($this->prcGSON($contents), true); $mk = $code[0][0][9];
      if (!empty($mk)) { $items .= '<option disabled>Communities you moderate</option>'; foreach ($mk as $cd) {
           $name = $cd[0][0][1][0]; $id = $cd[0][0][0]; $items .= '<option class="nxsGreen" '.($pgID==$id?'selected="selected"':'').' value="'.$id.'">&nbsp;&nbsp;&nbsp;'.$name.'</option>';
      }} $mk = $code[0][0][10]; if (!empty($mk)) { $items .= '<option disabled>Communities you\'ve joined</option>'; foreach ($mk as $cd) {
           $name = $cd[0][0][1][0]; $id = $cd[0][0][0]; $items .= '<option class="nxsGreen" '.($pgID==$id?'selected="selected"':'').' value="'.$id.'">&nbsp;&nbsp;&nbsp;'.$name.'</option>';
      }} return $items;
    }
    function postGP($msg, $lnk='', $pageID='', $commPageID='', $commPageCatID=''){ $rnds = rndString(12); $sslverify = false; $ck = $this->ck; $hdrsArr = $this->headers('');
      $pageID = trim($pageID); $commPageID = trim($commPageID); $ownerID = ''; $bigCode = '';  $isPostToPage = $pageID!=''; $isPostToComm = $commPageID!='';
      if (function_exists('nxs_decodeEntitiesFull')) $msg = nxs_decodeEntitiesFull($msg); if (function_exists('nxs_html_to_utf8')) $msg = nxs_html_to_utf8($msg);
      $msg = str_replace('<br>', "_NXSZZNXS_5Cn", $msg); $msg = str_replace('<br/>', "_NXSZZNXS_5Cn", $msg); $msg = str_replace('<br />', "_NXSZZNXS_5Cn", $msg);
      $msg = str_replace("\r\n", "\n", $msg); $msg = str_replace("\n\r", "\n", $msg); $msg = str_replace("\r", "\n", $msg); $msg = str_replace("\n", "_NXSZZNXS_5Cn", $msg);  $msg = str_replace('"', '\"', $msg);
      $msg = urlencode(strip_tags($msg)); $msg = str_replace("_NXSZZNXS_5Cn", "%5Cn", $msg);
      $msg = str_replace('+', '%20', $msg); $msg = str_replace('%0A%0A', '%20', $msg); $msg = str_replace('%0A', '', $msg); $msg = str_replace('%0D', '%5C', $msg);
      if (!empty($lnk) && !is_array($lnk)) $lnk = $this->urlInfo($lnk); if ($lnk=='') $lnk = array('img'=>'', 'link'=>'', 'fav'=>'', 'domain'=>'', 'title'=>'', 'txt'=>'');
      if (!isset($lnk['link']) && !empty($lnk['img'])) { $hdrsArr = $this->headers(''); unset($hdrsArr['Connection']);  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($lnk['img'], $advSet);
        if (is_nxs_error($rep)) $lnk['img']=''; elseif ($rep['response']['code']=='200' && !empty($rep['headers']['content-type']) && stripos($rep['headers']['content-type'],'text/html')===false) {
          if (!empty($rep['headers']['content-length']))  $imgdSize = $rep['headers']['content-length'];
          if ((empty($imgdSize) || $imgdSize == '-1') && !empty($rep['headers']['size_download'])) $imgdSize = $rep['headers']['size_download'];
          if ((empty($imgdSize) || $imgdSize == '-1')){ $ch = curl_init($lnk['img']); curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); curl_setopt($ch, CURLOPT_HEADER, TRUE); curl_setopt($ch, CURLOPT_NOBODY, TRUE);
            $data = curl_exec($ch);  $imgdSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD); curl_close($ch);
          }
          if ((empty($imgdSize) || $imgdSize == '-1')) $imgdSize =  strlen($rep['body']);
          $urlParced = pathinfo($lnk['img']); $remImgURL = $lnk['img']; $remImgURLFilename = nxs_mkImgNm(nxs_clFN($urlParced['basename']), $rep['headers']['content-type']);  $imgData = $rep['body'];
        } else $lnk['img']='';
      }
      if (isset($lnk['img'])) $lnk['img'] = urlencode($lnk['img']); if (isset($lnk['link'])) $lnk['link'] = urlencode($lnk['link']);
      $refPage = 'https://plus.google.com/b/'.$pageID.'/'; $rndReqID = rand(1203718, 647379);
      $pgInf = (!empty($pageID))?'b/'.$pageID.'/':''; $gpp = 'https://plus.google.com/'.$pgInf.'_/PlusAppUi/mutate?ds.extension=79255737&hl=en&soc-app=199&soc-platform=1&soc-device=1&_reqid='.$rndReqID.'&rt=c';
      $res = $this->getAt(); if ($res!==true) return $res; else $at = $this->at; $gNum = '94316911';
      $comOrPg = empty($commPageID)?'%5B%5Bnull%2Cnull%2C1%5D%2C%22Public%22%5D':'%5B%5Bnull%2Cnull%2Cnull%2C%5B%22'.$commPageID.'%22%2Cnull%2C%22'.$commPageCatID.'%22%5D%5D%2C%22!%22%2Cnull%2Cnull%2Cnull%2C%22!%22%5D';

      if (!empty($lnk['link'])) //## URL
        $spar="f.req=%5B%22af.maf%22%2C%5B%5B%22af.add%22%2C79255737%2C%5B%7B%2279255737%22%3A%5B%5B%5B%5D%2C%5B%5D%2C%5B".$comOrPg."%5D%5D%2C%5B%5B%5B0%2C%22".$msg."%22%2Cnull%5D%5D%5D%2Cnull%2Cfalse%2Cnull%2C%5B%7B%2294515327%22%3A%5B%22".$lnk['link']."%22%2C%22".$lnk['img']."%22%5D%7D%5D%2C%5B%5D%2Cnull%2C199%2Cfalse%2Cfalse%2C%22".time().$rnds."%22%5D%7D%5D%5D%5D%5D&at=".$at."&";
      elseif(!empty($lnk['img']) && !empty($imgData)) { //## Image
       $pageIDX = !empty($pageID)?$pageID:$this->pig; //$imgdSize =  strlen(urlencode($imgData));
       $iflds = '{"protocolVersion":"0.8","createSessionRequest":{"fields":[{"external":{"name":"file","filename":"'.$remImgURLFilename.'","put":{},"size":'.$imgdSize.'}},{"inlined":{"name":"batchid","content":"'.time().'97","contentType":"text/plain"}},{"inlined":{"name":"client","content":"google-plus","contentType":"text/plain"}},{"inlined":{"name":"disable_asbe_notification","content":"true","contentType":"text/plain"}},{"inlined":{"name":"effective_id","content":"'.$pageIDX.'","contentType":"text/plain"}},{"inlined":{"name":"owner_name","content":"'.$pageIDX.'","contentType":"text/plain"}},{"inlined":{"name":"album_mode","content":"temporary","contentType":"text/plain"}}]}}';
       $hdrsArr = $this->headers('', 'https://plus.google.com', 'POST', true); $hdrsArr['X-GUploader-Client-Info']='mechanism=scotty xhr resumable; clientVersion=58505203';  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $iflds, $this->proxy);
       $imgReqCnt = nxs_remote_post('https://plus.google.com/_/upload/photos/resumable?authuser=0', $advSet); if (is_nxs_error($imgReqCnt)) {  $badOut = print_r($imgReqCnt, true)." - ERROR IMG"; return $badOut; }
       $gUplURL = str_replace('\u0026', '&', CutFromTo($imgReqCnt['body'], 'putInfo":{"url":"', '"'));  $gUplID = CutFromTo($imgReqCnt['body'], 'upload_id":"', '"');
       $hdrsArr = $this->headers('https://plus.google.com/', 'https://plus.google.com', 'PUT'); $hdrsArr['X-Goog-Upload-Offset']='0';  $hdrsArr['X-Goog-Upload-Command']='upload, finalize';  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $imgData, $this->proxy);
       $rep = nxs_remote_post($gUplURL, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR IMG Upl (Upl URL: ".$gUplURL.", IMG URL: ".urldecode($lnk['img']).", FileName: ".$remImgURLFilename.", FIlesize: ".$imgdSize.")"; return $badOut; }
       $imgUplCnt = json_decode($rep['body'], true);   if (empty($imgUplCnt)) return "Can't upload image: ".$remImgURL."  |  ".print_r($rep, true); // prr($imgUplCnt);
       if (is_array($imgUplCnt) && isset($imgUplCnt['errorMessage']) && is_array($imgUplCnt['errorMessage']) ) return "Error *NXS Upload* : ".print_r($imgUplCnt['errorMessage'], true);
       $infoArray = $imgUplCnt['sessionStatus']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo'];
       $albumID = $infoArray['albumid']; $photoid =  $infoArray['photoid']; $mk =  urlencode($infoArray['photoMediaKey']); $imgUrl = urlencode($infoArray['url']); $imgTitie = $infoArray['title'];
       $imgUrlX = str_ireplace('https:', '', $infoArray['url']); $imgUrlX = str_ireplace('//lh4.', '//lh3.', $imgUrlX); $imgUrlX = urlencode(str_ireplace('http:', '', $imgUrlX));
       $width = $infoArray['width']; $height = $infoArray['height']; $userID = $infoArray['username'];
       $intID = $infoArray['albumPageUrl'];  $intID = str_replace('https://picasaweb.google.com/','', $intID);  $intID = str_replace($userID,'', $intID); $intID = str_replace('/','', $intID); $tmm = time();
       $spar="f.req=%5B%22af.maf%22%2C%5B%5B%22af.add%22%2C79255737%2C%5B%7B%2279255737%22%3A%5B%5B%5B%5D%2C%5B%5D%2C%5B".$comOrPg."%5D%5D%2C%5B%5B%5B0%2C%22".$msg."%22%2Cnull%5D%5D%5D%2Cnull%2Cfalse%2Cnull%2C%5B%7B%22".$gNum."%22%3A%5B%5B%5B%22".$mk."%22%2C%22".$imgUrl."%22%2C".$width."%2C".$height."%5D%5D%5D%7D%5D%2C%5B%5D%2Cnull%2C199%2Cfalse%2Cfalse%2C%22".$tmm.'666'.$rnds."%22%5D%7D%5D%5D%5D%5D&at=".$at."&";
      } else //## Just text
        $spar="f.req=%5B%22af.maf%22%2C%5B%5B%22af.add%22%2C79255737%2C%5B%7B%2279255737%22%3A%5B%5B%5B%5D%2C%5B%5D%2C%5B".$comOrPg."%5D%5D%2C%5B%5B%5B0%2C%22".$msg."%22%2Cnull%5D%5D%5D%2Cnull%2Cfalse%2Cnull%2Cnull%2C%5B%5D%2Cnull%2C199%2Cfalse%2Cfalse%2C%22".time().$rnds."%22%5D%7D%5D%5D%5D%5D&at=".$at."&";
      $spar = str_ireplace('+','%20',$spar); $spar = str_ireplace(':','%3A',$spar);  $hdrsArr = $this->headers($refPage, 'https://plus.google.com', 'POST'); $hdrsArr['X-Same-Domain']='1';  $hdrsArr['X-Client-Data']='CKC1yQEIhbbJAQiltskBCPyYygE=';
      //$ckt = $ck; $ck = array(); $no = array("LSID", "ACCOUNT_CHOOSER", "GoogleAccountsLocale_session", "GAPS", "GALX"); foreach ($ckt as $c) {if (!in_array($c->name, $no)) $ck[]=$c;}
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $spar, $this->proxy); $rep = nxs_remote_post($gpp, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR POST"; return $badOut; }  $contents = $rep['body'];
      //prr($gpp); prr($spar); prr(urldecode($spar));  prr($advSet);    prr($rep);
      if ($rep['response']['code']=='403') return "Error: You are not authorized to publish to this page. Are you sure this is even a page? (".$pageID.")";
      if ($rep['response']['code']=='404') return "Error: Page you are posting is not found.<br/><br/> If you have entered your page ID as 117008619877691455570/117008619877691455570, please remove the second copy. It should be one number only - 117008619877691455570";
      if ($rep['response']['code']=='400') return "Error (400): Something is wrong, please contact support";
      if ($rep['response']['code']=='500' && stripos($rep['body'], 'RpcClientException')!==false) return "Error (500): Google Server is overloaded or temporary out of service. Message: ".CutFromTo($rep['body'],'RpcClientException',']');
      if ($rep['response']['code']=='500') return "Error (500): Something is wrong, please contact support";
      if ($rep['response']['code']=='200') { $ret = $rep['body']; if (stripos($ret,'"https://plus.google.com/')!==false)  $ret = CutFromTo($contents, '"https://plus.google.com/', '",');
        return array('isPosted'=>'1', 'postID'=>$ret, 'postURL'=>'https://plus.google.com/'.$ret, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
      } return print_r($contents, true);
    }

    function postBG($blogID, $title, $msg, $tags=''){ $sslverify = false; $rnds = rndString(35); $blogID = trim($blogID); $ck = $this->ck;
      $gpp = "https://www.blogger.com/blogger.g?blogID=".$blogID; $refPage = "https://www.blogger.com/home";
      $hdrsArr = $this->headers($refPage); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($gpp, $advSet); //prr($ck); prr($rep);// die();
      if (is_nxs_error($rep)) return false; /*if (!empty($rep['cookies'])) $ck = $rep['cookies']; */ $contents = $rep['body']; if ( stripos($contents, 'Error 404')!==false) return "Error: Invalid Blog ID - Blog with ID ".$blogID." Not Found";
      $jjs = CutFromTo($contents, 'BloggerClientFlags=','_layoutOnLoadHandler'); $j69 = ''; // prr($jjs); //  prr($contents); echo "\r\n"; echo "\r\n";
      for ($i = 54; $i <= 169; $i++) { if ($j69=='' && strpos($jjs, $i.':"')!==false){ $j69 = CutFromTo($jjs, $i.':"','"');
        if (strpos($j69, ':')===false || (strpos($j69, '/')!==false) || (strpos($j69, ' ')!==false) || (strpos($j69, '\\')!==false)) $j69 = '';}
      } $gpp = "https://www.blogger.com/blogger_rpc?blogID=".$blogID; $refPage = "https://www.blogger.com/blogger.g?blogID=".$blogID;
      $spar = '{"method":"editPost","params":{"1":1,"2":"","3":"","5":0,"6":0,"7":1,"8":3,"9":0,"10":2,"11":1,"13":0,"14":{"6":""},"15":"en","16":0,"17":{"1":'.date("Y").',"2":'.date("n").',"3":'.date("j").',"4":'.date("G").',"5":'.date("i").'},"20":0,"21":"","22":{"1":1,"2":{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0,"10":"0"}},"23":1},"xsrf":"'.$j69.'"}';
      $hdrsArr = $this->headers($refPage, 'https://www.blogger.com', 'JS', false);
      $hdrsArr['X-GWT-Module-Base']='https://www.blogger.com/static/v1/gwt/'; $hdrsArr['X-GWT-Permutation']='906B796BACD31B64BA497BEE3824B344';
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $spar, $this->proxy);$rep = nxs_remote_post($gpp, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR BG"; return $badOut; }  $contents = $rep['body']; //  prr($rep);
      $newpostID = CutFromTo($contents, '"result":[null,"', '"');
      if ($tags!='') $pTags = '["'.$tags.'"]'; else $pTags = ''; $pTags = str_replace('!','',$pTags); $pTags = str_replace('.','',$pTags);
      if (class_exists('DOMDocument')) { $doc = new DOMDocument();  @$doc->loadXML("<QAZX>".$msg."</QAZX>"); $styles = $doc->getElementsByTagName('style');
        if ($styles->length>0) {  foreach ($styles as $style)  $style->nodeValue = str_ireplace("<br/>", "", $style->nodeValue);
          $msg = $doc->saveXML($doc->documentElement, LIBXML_NOEMPTYTAG); $msg = str_ireplace("<QAZX>", "", str_ireplace("</QAZX>", "", $msg));
        }
      } $msg = str_replace("'",'"',$msg); $msg = addslashes($msg); $msg = str_replace("\r\n","\n",$msg); $msg = str_replace("\n\r","\n",$msg); $msg = str_replace("\r","\n",$msg); $msg = str_replace("\n",'\n',$msg);
      $title = strip_tags($title); $title = str_replace("'",'"',$title); $title = addslashes($title); $title = str_replace("\r\n","\n",$title);
      $title = str_replace("\n\r","\n",$title); $title = str_replace("\r","\n",$title); $title = str_replace("\n",'\n',$title); //echo "~~~~~";  prr($title);

      $spar = '{"method":"editPost","params":{"1":1,"2":"'.$title.'","3":"'.$msg.'","4":"'.$newpostID.'","5":0,"6":0,"7":1,"8":3,"9":0,"10":2,"11":2,'.($pTags!=''?'"12":'.$pTags.',':'').'"13":0,"14":{},"15":"en","16":1,"17":{"1":'.date("Y").',"2":'.date("n").',"3":'.date("j").',"4":'.date("G").',"5":'.date("i").'},"20":0,"21":"","22":{"1":1,"2":{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0,"10":"0"}},"23":3,"26":"","27":1,"28":0},"xsrf":"'.$j69.'"}';
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $spar, $this->proxy); $rep = nxs_remote_post($gpp, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR BG2"; return $badOut; }  $contents = $rep['body'];
      $retJ = json_decode($contents, true); if (is_array($retJ) && !empty($retJ['result']) && is_array($retJ['result']) ) $postID = $retJ['result'][6]; else $postID = '';
      if ( stripos($contents, '"error":')!==false) { return "Error: ".print_r($contents, true); }
      if ($rep['response']['code']=='200') return array('isPosted'=>'1', 'postID'=>$postID, 'postURL'=>$postID, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck); else return print_r($contents, true);
    }
    function postYT($msg, $ytUrl, $vURL = '', $ytGPPageID='') { $ck = $this->ck; $sslverify = false;
      $ytUrl = str_ireplace('/feed','',$ytUrl); if (substr($ytUrl, -1)=='/') $ytUrl = substr($ytUrl, 0, -1); $ytUrl .= '/feed'; $hdrsArr = $this->headers('http://www.youtube.com/');
      if ($ytGPPageID!=''){ $pgURL = 'https://www.youtube.com/signin?authuser=0&action_handle_signin=true&pageid='.$ytGPPageID;      if ($this->debug) echo "[YT] G SW to page: ".$ytGPPageID."<br/>\r\n";
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy);$rep = nxs_remote_get($pgURL, $advSet); if (is_nxs_error($rep)) return "ERROR: ".print_r($rep, true);
        if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }
      } $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($ytUrl, $advSet); if (is_nxs_error($rep)) return "ERROR: ".print_r($rep, true);
      //## Merge CK
      if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }
      $this->chckForCpt($rep['body'], $ck); // prr($rep);
      $contents = $rep['body']; $gpPageMsg = "Either BAD YouTube USER/PASS or you are trying to post from the wrong account/page. Make sure you have Google+ page ID if your YouTube account belongs to the page.";
      $actFormCode='channel_ajax';
      if (stripos($contents,'action="/channels_feed_ajax?')!==false) $actFormCode='channels_feed_ajax'; elseif (stripos($contents,'action="/c4_feed_ajax?')!==false)$actFormCode = 'c4_feed_ajax';
      if (stripos($contents, 'action="/'.$actFormCode.'?')) $frmData = CutFromTo($contents, 'action="/'.$actFormCode.'?', '</form>'); else {
        if (stripos($contents, 'property="og:url"')) {  $ytUrl = CutFromTo($contents, 'property="og:url" content="', '"').'/feed';
          $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($ytUrl, $advSet);
          if (is_nxs_error($rep)) return "ERROR: ".print_r($rep, true); if (!empty($rep['cookies'])) $ck = $rep['cookies'];  $contents = $rep['body'];
          if (stripos($contents, 'action="/'.$actFormCode.'?')) $frmData = CutFromTo($contents, 'action="/'.$actFormCode.'?', '</form>'); else return 'OG - Form not found. - '. $gpPageMsg;
        } else { $eMsg = "No Form/No OG - ". $gpPageMsg; return $eMsg; }
      } $md = array(); $flds = array(); if (!empty($vURL) && stripos($vURL, 'http')===false && strlen($vURL)!=11) $vURL = '';
      if ($vURL!='' && stripos($vURL, 'http')===false) $vURL = 'https://www.youtube.com/watch?v='.$vURL; $msg = strip_tags($msg); $msg = nsTrnc($msg, 500);
      while (stripos($frmData, '"hidden"')!==false){$frmData = substr($frmData, stripos($frmData, '"hidden"')+8); $name = trim(CutFromTo($frmData,'name="', '"'));
        if (!in_array($name, $md)) {$md[] = $name; $val = trim(CutFromTo($frmData,'value="', '"')); $flds[$name]= $val;}
      } $flds['message'] = $msg; $flds['video_url'] = $vURL; $flds['session_token'] = trim(CutFromTo($contents,'XSRF_TOKEN\': "', '"'));
      $flds['params'] = 'CAE%3D'; $flds['video_id'] = ''; $flds['playlist_id'] = ''; //prr($flds);
      $ytGPPageID = 'https://www.youtube.com/channel/'.$ytGPPageID; $hdrsArr = $this->headers($ytGPPageID, 'https://www.youtube.com/', 'POST', false);
      $hdrsArr['X-YouTube-Page-CL'] = '67741289'; $hdrsArr['X-YouTube-Page-Timestamp'] = date("D M j H:i:s Y", time()-54000)." (".time().")"; //'Thu May 22 00:31:51 2014 (1400743911)';
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post('https://www.youtube.com/'.$actFormCode.'?action_create_channel_post=1', $advSet); //prr($rep); prr($advSet);
      if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR YT"; return $badOut; }  $contents = $rep['body']; //prr($contents);
      if ($rep['response']['code']=='200' && ( $contents == '{"code": "SUCCESS"}' || stripos($contents,'"feed_entry_html":')!==false )) return array("isPosted"=>"1", "postID"=>'', 'postURL'=>'', 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck); else return $rep['response']['code']."|".$contents;
    }
}}
//================================Pinterest===========================================
if (!class_exists('nxsAPI_PN')){class nxsAPI_PN{ var $ck = array(); var $tk=''; var $boards = ''; var $apVer=''; var $u=''; var $debug = false; var $loc = ''; var $proxy = array();
    function headers($ref, $org='', $type='GET', $aj=false){  $hdrsArr = array();
      $hdrsArr['Cache-Control']='max-age=0'; $hdrsArr['Connection']='keep-alive'; $hdrsArr['Referer']=$ref;
      $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.54 Safari/537.36';
      if($type=='JSON') $hdrsArr['Content-Type']='application/json;charset=UTF-8'; elseif($type=='POST') $hdrsArr['Content-Type']='application/x-www-form-urlencoded';
      if($aj===true) $hdrsArr['X-Requested-With']='XMLHttpRequest';  if ($org!='') $hdrsArr['Origin']=$org;
      if ($type=='GET') $hdrsArr['Accept']='text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'; else $hdrsArr['Accept']='*/*';
      if (function_exists('gzdeflate')) $hdrsArr['Accept-Encoding']='deflate,sdch';
      $hdrsArr['Accept-Language']='en-US,en;q=0.8'; return $hdrsArr;
    }
    function check($u=''){ $ck = $this->ck; if (!empty($ck) && is_array($ck)) { if (empty($this->loc)) $this->getLoc(); $hdrsArr = $this->headers($brdURL = $this->loc.'settings/'); if ($this->debug) echo "[PN] Checking....;<br/>\r\n";
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($brdURL = $this->loc.'settings/', $advSet);
        if (is_nxs_error($rep)) return false; $ck = $rep['cookies']; $contents = $rep['body']; //if ($this->debug) prr($contents);
        $ret = stripos($contents, 'href="#accountBasics"')!==false; $usr = CutFromTo($contents, '"email": "', '"'); if ($ret & $this->debug) echo "[PN] Logged as:".$usr."<br/>\r\n";
        $apVer = trim(CutFromTo($contents,'"app_version": "', '"'));  $this->apVer = $apVer;
        if (empty($u) || $u==$usr) return $ret; else return false;
      } else return false;
    }
    function connect($u,$p){ $badOut = 'Error: '; // $this->debug = true;
      //## Check if alrady IN
      if (!$this->check($u)){ if ($this->debug) echo "[PN] NO Saved Data; Logging in...<br/>\r\n"; if (empty($this->loc)) $this->getLoc();
        $hdrsArr = $this->headers($this->loc.'login/'); $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); $rep = nxs_remote_get($this->loc.'login/', $advSet);
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR -01-"; return $badOut; } $ck = $rep['cookies']; $contents = $rep['body']; $apVer = trim(CutFromTo($contents,'"app_version": "', '"'));
        $fldsTxt = 'data=%7B%22options%22%3A%7B%22username_or_email%22%3A%22'.urlencode($u).'%22%2C%22password%22%3A%22'.str_replace('%5C','%5C%5C',urlencode($p)).'%22%7D%2C%22context%22%3A%7B%22app_version%22%3A%22'.$apVer.
    '%22%7D%7D&source_url=%2Flogin%2F&module_path=App()%3ELoginPage()%3ELogin()%3EButton(class_name%3Dprimary%2C+text%3DLog+in%2C+type%3Dsubmit%2C+tagName%3Dbutton%2C+size%3Dlarge)';
        foreach ($ck as $c) if ($c->name=='csrftoken') $xftkn = $c->value;
        //## ACTUAL LOGIN
        $hdrsArr = $this->headers($this->loc.'login/', $this->loc, 'POST', true); $hdrsArr['X-NEW-APP']='1'; $hdrsArr['X-APP-VERSION']=$apVer; $hdrsArr['X-CSRFToken']=$xftkn;
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $fldsTxt, $this->proxy); $rep = nxs_remote_post($this->loc.'resource/UserSessionResource/create/', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR -02-"; return $badOut; }
        if (!empty($rep['headers']['location'])) { $loc = CutFromTo($rep['headers']['location'], 'https://','.pinterest');
          $hdrsArr = $this->headers('https://'.$loc.'.pinterest.com/login/', 'https://'.$loc.'.pinterest.com', 'POST', true); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $fldsTxt, $this->proxy);
          $rep = nxs_remote_post('https://'.$loc.'.pinterest.com/resource/UserSessionResource/create/', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR -02-"; return $badOut; }
        } else $loc = 'www';
        if (!empty($rep['body'])) { $contents = $rep['body']; $resp = json_decode($contents, true); } else { $badOut = print_r($rep, true)." - ERROR -03-"; return $badOut; }
          if (is_array($resp) && empty($resp['resource_response']['error'])) { $ck = $rep['cookies'];  foreach ($ck as $ci=>$cc) $ck[$ci]->value = str_replace(' ','+', $cc->value);
            $hdrsArr = $this->headers('https://'.$loc.'.pinterest.com/login'); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy);
            $rep=nxs_remote_get('https://'.$loc.'.pinterest.com/', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR -02.1-"; return $badOut; }
            if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }
            foreach ($ck as $ci=>$cc) $ck[$ci]->value = str_replace(' ','+', $cc->value); $this->tk = $xftkn; $this->ck = $ck;  $this->apVer = $apVer;  $this->getLoc();
            if ($this->debug) echo "[PN] You are IN;<br/>\r\n"; return false; // echo "You are IN";
          } elseif (is_array($resp) && isset($resp['resource_response']['error'])) return "ERROR -04-: ".$resp['resource_response']['error']['http_status']." | ".$resp['resource_response']['error']['message'];
          elseif (stripos($contents, 'CSRF verification failed')!==false) { $retText = trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
            return "CSRF verification failed - Please contact NextScripts Support | Pinterest Message:".$retText;
          } elseif (stripos($contents, 'IP because of suspicious activity')!==false) return 'Pinterest blocked logins from this IP because of suspicious activity';
          elseif (stripos($contents, 've detected a bot!')!==false) return 'Pinterest has your IP ('.CutFromTo($contents, 'ess: <b>','<').') blocked. Please <a target="_blank" class="link" href="//help.pinterest.com/entries/22914692">Contact Pinterest</a> and ask them to unblock your IP. ';
          elseif (stripos($contents, 'bot running on your network')!==false) return 'Pinterest has your IP ('.CutFromTo($contents, 'Your IP is:','<').') blocked. Please <a target="_blank" class="link" href="//help.pinterest.com/entries/22914692">Contact Pinterest</a> and ask them to unblock your IP. ';
          else return 'Pinterest login failed. Unknown Error. Please contact support.';
          return 'Pinterest login failed. Unknown Error #2. Please contact support.';
      } else { if ($this->debug) echo "[PN] Saved Data is OK;<br/>\r\n"; return false; }
    }
    function getBoardsOLD() { if (!$this->check()){ if ($this->debug) echo "[PN] NO Saved Data;<br/>\r\n"; return 'Not logged IN';} $boards = ''; $ck = $this->ck; $apVer = $this->apVer; $brdsArr = array();
        $iu = 'http://memory.loc.gov/award/ndfa/ndfahult/c200/c240r.jpg'; $su = '/pin/find/?url='.urlencode($iu);
        $hdrsArr = $this->headers('http://www.pinterest.com/pin/find/?url='.urlencode($iu),'','JSON', true); $hdrsArr['X-NEW-APP']='1'; $hdrsArr['X-APP-VERSION']=$apVer;
        $hdrsArr['Accept'] = 'application/json, text/javascript, */*; q=0.01';
        $dt = '{"options":{},"context":{},"module":{"name":"PinCreate","options":{"image_url":"'.$iu.'","action":"create","method":"scraped","link":"'.$iu.'","transparent_modal":false}},"append":false,"error_strategy":0}';
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('http://www.pinterest.com/resource/NoopResource/get/?source_url='.urlencode($su).'&data='.urlencode($dt), $advSet);
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $ck = $rep['cookies']; $contents = $rep['body'];   $k = json_decode($contents, true);
        if (!empty($k['module']['tree']) && !empty($k['module']['tree']['children'][0]) && !empty($k['module']['tree']['children'][0]['children'])) $brdsA = $k['module']['tree']['children'][0]['children'];
          if (!empty($brdsA)&& is_array($brdsA)) foreach ($brdsA as $ab) { if (!empty($ab) && !empty($ab['data']['all_boards'])) { $ba = $ab['data']['all_boards'];
            foreach ($ba as $kh) { $boards .= '<option value="'.$kh['id'].'">'.$kh['name'].'</option>'; $brdsArr[] = array('id'=>$kh['id'], 'n'=>$kh['name']); } $this->boards = $brdsArr; return $boards;
          } $khtml = CutFromTo($k['module']['html'], "boardPickerInnerWrapper", "</ul>"); $khA = explode('<li', $khtml);
        }
        if (!empty($khA)&& is_array($khA)) foreach ($khA as $kh) if (stripos($kh, 'data-id')!==false) { $bid = CutFromTo($kh, 'data-id="', '"'); $bname = trim(CutFromTo($kh, '</div>', '</li>'));
          if (isset($bid)) { $boards .= '<option value="'.$bid.'">'.trim($bname).'</option>'; $brdsArr[] = array('id'=>$bid, 'n'=>trim($bname)); }
        } $this->boards = $brdsArr; return $boards;
    }
    function getLoc(){ $ck = $this->ck; $hdrsArr = $this->headers('https://www.pinterest.com/');
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.pinterest.com/', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; }
       if ($rep['response']['code']=='200') $this->loc = 'https://www.pinterest.com/'; elseif ($rep['response']['code']=='302' && !empty($rep['headers']['location'])) $this->loc = $rep['headers']['location'];
    }
    function getBoards() { $boards = ''; $ck = $this->ck; $apVer = $this->apVer; $brdsArr = array(); if (empty($this->loc)) $this->getLoc();
        $iu = 'http://memory.loc.gov/award/ndfa/ndfahult/c200/c240r.jpg'; $su = '/pin/find/?url='.urlencode($iu); $iuu = urlencode($iu); $hdrsArr = $this->headers($this->loc,'','JSON', true);
        $hdrsArr['X-NEW-APP']='1'; $hdrsArr['X-APP-VERSION']=$apVer; $hdrsArr['X-Pinterest-AppState']='active'; $hdrsArr['Accept'] = 'application/json, text/javascript, */*; q=0.01';
        $brdURL = $this->loc.'resource/BoardPickerBoardsResource/get/?source_url=%2Fpin%2Ffind%2F%3Furl%'.$iuu.'&data=%7B%22options%22%3A%7B%22filter%22%3A%22all%22%2C%22field_set_key%22%3A%22board_picker%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App()%3EImagesFeedPage(resource%3DFindPinImagesResource(url%'.$iuu.'))%3EGrid()%3EGridItems()%3EPinnable()%3EShowModalButton(module%3DPinCreate)';$advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get($brdURL, $advSet);
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $ck = $rep['cookies']; $contents = $rep['body'];   $k = json_decode($contents, true);      //   prr($k);
        if (!empty($k['resource_data_cache']) || !empty($k['resource_response'])) { if (!empty($k['resource_data_cache'])) $brdsA = $k['resource_data_cache']; else {$brdsA = array(); $brdsA[] = $k['resource_response']; }
          foreach ($brdsA as $ab) if (!empty($ab) && !empty($ab['data']['all_boards'])) { $ba = $ab['data']['all_boards'];
            foreach ($ba as $kh) { $boards .= '<option value="'.$kh['id'].'">'.$kh['name'].'</option>'; $brdsArr[] = array('id'=>$kh['id'], 'n'=>$kh['name']); } $this->boards = $brdsArr; return $boards;
          }
        } return $this->getBoardsOLD(); //## Remove it in couple months
    }
    function post($msg, $imgURL, $lnk, $boardID, $title = '', $price='', $via=''){
      $tk = $this->tk; $ck = $this->ck; $apVer = $this->apVer; if (empty($this->loc))  $this->getLoc(); if ($this->debug) echo "[PN] Posting to ...".$boardID."<br/>\r\n";
      foreach ($ck as $c) if ( is_object($c) && $c->name=='csrftoken') $tk = $c->value; $msg = strip_tags($msg); $msg = substr($msg, 0, 480); $tgs = ''; $this->tk = $tk;
      if ($msg=='') $msg = '&nbsp;';  if (trim($boardID)=='') return "Board is not Set";  if (trim($imgURL)=='') return "Image is not Set";   $msg = str_ireplace(array("\r\n", "\n", "\r"), " ", $msg);
      $msg = strip_tags($msg); if (function_exists('nxs_decodeEntitiesFull')) $msg = nxs_decodeEntitiesFull($msg, ENT_QUOTES);
      $mgsOut = urlencode($msg); $mgsOut = str_ireplace(array('%28', '%29', '%27', '%21', '%22', '%09'), array("(", ")", "'", "!", "%5C%22", '%5Ct'), $mgsOut);
      $fldsTxt = 'source_url=%2Fpin%2Ffind%2F%3Furl%3D'.urlencode(urlencode($lnk)).'&data=%7B%22options%22%3A%7B%22board_id%22%3A%22'.$boardID.'%22%2C%22description%22%3A%22'.$mgsOut.'%22%2C%22link%22%3A%22'.urlencode($lnk).'%22%2C%22share_twitter%22%3Afalse%2C%22image_url%22%3A%22'.urlencode($imgURL).'%22%2C%22method%22%3A%22scraped%22%7D%2C%22context%22%3A%7B%7D%7D';
      $hdrsArr = $this->headers($brdURL = $this->loc.'resource/PinResource/create/ ', $brdURL = $this->loc, 'POST', true);
      $hdrsArr['X-NEW-APP']='1'; $hdrsArr['X-APP-VERSION']=$apVer; $hdrsArr['X-CSRFToken']=$tk; $hdrsArr['X-Pinterest-AppState']='active';  $hdrsArr['Accept'] = 'application/json, text/javascript, */*; q=0.01';
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $fldsTxt, $this->proxy); $rep = nxs_remote_post($brdURL = $this->loc.'resource/PinResource/create/', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; }
      $contents = $rep['body']; $resp = json_decode($contents, true); //  prr($advSet);  prr($resp);   prr($fldsTxt); // prr($contents);
      if (is_array($resp)) {
        if (isset($resp['resource_response']) && isset($resp['resource_response']['error']) && $resp['resource_response']['error']!='' ) return print_r($resp['resource_response']['error'], true);
        elseif (isset($resp['resource_response']) && isset($resp['resource_response']['data']) && $resp['resource_response']['data']['id']!=''){ // gor JSON
          if (isset($resp['resource_response']) && isset($resp['resource_response']['error']) && $resp['resource_response']['error']!='') return print_r($resp['resource_response']['error'], true);
          else return array("isPosted"=>"1", "postID"=>$resp['resource_response']['data']['id'], 'pDate'=>date('Y-m-d H:i:s'), "postURL"=>$brdURL = $this->loc.'pin/'.$resp['resource_response']['data']['id'], 'ck'=> base64_encode(serialize($ck)));
        }
      }elseif (stripos($contents, 'blocked this')!==false) { $retText = trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
        return "Pinterest ERROR: 'The Source is blocked'. Please see https://support.pinterest.com/entries/21436306-why-is-my-pin-or-site-blocked-for-spam-or-inappropriate-content/ for more info | Pinterest Message:".$retText;
      }
      elseif (stripos($contents, 'image you tried to pin is too small')!==false) { $retText = trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
        return "Image you tried to pin is too small | Pinterest Message:".$retText;
      }
      elseif (stripos($contents, 'CSRF verification failed')!==false) { $retText = trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
        return "CSRF verification failed - Please contact NextScripts Support | Pinterest Message:".$retText;
      }
      elseif (stripos($contents, 'Oops')!==false && stripos($contents, '<body>')!==false ) return 'Pinterest ERROR MESSAGE : '.trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
      else return "Somethig is Wrong - Pinterest Returned Error 502";
    }
}}
//================================LinkedIn===========================================
if (!class_exists('nxsAPI_LI')){class nxsAPI_LI{ var $ck = array();  var $debug = false; var $proxy = array();
    function headers($ref, $org='', $type='GET', $aj=false){  $hdrsArr = array();
      $hdrsArr['Cache-Control']='max-age=0'; $hdrsArr['Connection']='keep-alive'; $hdrsArr['Referer']=$ref;
      $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.54 Safari/537.36';
      if($type=='JSON') $hdrsArr['Content-Type']='application/json;charset=UTF-8'; elseif($type=='POST') $hdrsArr['Content-Type']='application/x-www-form-urlencoded';
      if($aj===true) $hdrsArr['X-Requested-With']='XMLHttpRequest';  if ($org!='') $hdrsArr['Origin']=$org;
      if ($type=='GET') $hdrsArr['Accept']='text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'; else $hdrsArr['Accept']='*/*';
      if (function_exists('gzdeflate')) $hdrsArr['Accept-Encoding']='deflate,sdch';
      $hdrsArr['Accept-Language']='en-US,en;q=0.8'; return $hdrsArr;
    }
    function createFile($imgURL) {
      $remImgURL = urldecode($imgURL); $urlParced = pathinfo($remImgURL); $remImgURLFilename = $urlParced['basename'];
      $imgData = wp_remote_get($remImgURL, array('timeout' => 45)); if (is_nxs_error($imgData)) { $badOut['Error'] = print_r($imgData, true)." - ERROR"; return $badOut; }
      if (isset($imgData['content-type'])) $cType = $imgData['content-type']; $imgData = $imgData['body']; if (empty($cType)) $cType = 'image/png';
      $tmp=array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()]=tmpfile())));
      if (!is_writable($tmp))  { $badOut['Error'] = "Your temporary folder or file (file - ".$tmp.") is not writable. Can't upload images to Flickr"; return $badOut; }
      rename($tmp, $tmp.='.png'); register_shutdown_function(create_function('', "unlink('{$tmp}');"));
      file_put_contents($tmp, $imgData); if (!$tmp) { $badOut['Error'] = 'You must specify a path to a file'; return $badOut; }
      if (!file_exists($tmp)) { $badOut['Error'] = 'File path specified does not exist'; return $badOut; }
      if (!is_readable($tmp)) { $badOut['Error'] = 'File path specified is not readable'; return $badOut; }
      $cfile = curl_file_create($tmp,$cType,'nxstmp.png'); return $cfile;
    }
    function check(){ $ck = $this->ck;  if (!empty($ck) && is_array($ck)) { $hdrsArr = $this->headers('https://www.linkedin.com'); if ($this->debug) echo "[LI] Checking....;<br/>\r\n";
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://www.linkedin.com/profile/edit?trk=tab_pro', $advSet);// prr($rep);
        if (is_nxs_error($rep)) return false; $ck = $rep['cookies']; $contents = $rep['body']; //if ($this->debug) prr($contents);
        return stripos($contents, 'href="/profile/edit?trk=nav_responsive_sub_nav_edit_profile"')!==false;
      } else return false;
    }
    function connect($u,$p){ $badOut = 'Connect Error: ';
        //## Check if alrady IN
        if (!$this->check()){ if ($this->debug) echo "[LI] NO Saved Data;<br/>\r\n";
        $hdrsArr = $this->headers('https://www.linkedin.com'); $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); $rep = nxs_remote_get('https://www.linkedin.com/uas/login?goback=&trk=hb_signin', $advSet); // prr($rep);
        if (is_nxs_error($rep)) {  $badOut = "AUTH ERROR #1". print_r($rep, true); return $badOut; } $ck = $rep['cookies']; $contents = $rep['body'];
        //## GET HIDDEN FIELDS
        $md = array(); $flds  = array(); $treeID = trim(CutFromTo($contents,'name="treeID" content="', '"'));
        while (stripos($contents, '<input')!==false){ $inpField = trim(CutFromTo($contents,'<input', '>')); $name = trim(CutFromTo($inpField,'name="', '"'));
          if ( stripos($inpField, '"hidden"')!==false && $name!='' && !in_array($name, $md)) { $md[] = $name; $val = trim(CutFromTo($inpField,'value="', '"')); $flds[$name]= $val; }
          $contents = substr($contents, stripos($contents, '<input')+8);
        } $flds['session_key'] = $u; $flds['session_password'] = $p;  $flds['signin'] = 'Sign%20In';
        //## ACTUAL LOGIN
        $hdrsArr = $this->headers('https://www.linkedin.com/uas/login?goback=&trk=hb_signin', 'https://www.linkedin.com', 'POST', true); $hdrsArr['X-IsAJAXForm']='1';
        $hdrsArr['X-LinkedIn-traceDataContext']='X-LI-ORIGIN-UUID='.$treeID; $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy);
        $rep = nxs_remote_post('https://www.linkedin.com/uas/login-submit', $advSet); if (is_nxs_error($rep)) {  $badOut = "AUTH ERROR #2". print_r($rep, true); return $badOut; }
        if ($rep['response']['code']=='200') { $content = $rep['body'];
           if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }
           if (stripos($content, '"status":"ok"')!==false) { if (stripos($content, 'redirectUrl')!==false) { if ($this->debug) echo "[LI] Login REDIR;<br/>\r\n";
             $content = str_ireplace('/uas/','https://www.linkedin.com/uas/',$content); $rJson = json_decode($content, true);
             if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }
             $hdrsArr = $this->headers('https://www.linkedin.com/uas/login-submit'); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy,1); $rep = nxs_remote_get($rJson['redirectUrl'], $advSet); $content = $rep['body'];
           } else { if ($this->debug) echo "[LI] Login was OK;<br/>\r\n"; $this->ck = $ck; return false; }}
           if (stripos($content, 'ou have exceeded the maximum number of code requests')!==false) { return "You have exceeded the maximum number of code requests. Please try again later.";}
           if (stripos($content, '"submitRequired":true')!==false) { unset($hdrsArr['X-IsAJAXForm']);  unset($hdrsArr['X-LinkedIn-traceDataContext']); unset($hdrsArr['X-Requested-With']);
             $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post('https://www.linkedin.com/uas/login-submit', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; }  $content = $rep['body'];
           }
           if ( stripos($content, 'name="PinVerificationForm_pinParam"')!==false) { //## Code
               if ( stripos($content, '<div id="uas-consumer-two-step-verification" class="two-step-verification">')!==false) {
                 $text = CutFromTo($content, '<div id="uas-consumer-two-step-verification" class="two-step-verification">', '<script id="').'</li></ul></form></div></div>';
                 $formcode = '<form '.CutFromTo($content, '<div id="uas-consumer-two-step-verification" class="two-step-verification">', '</form>');
               } else { $text = CutFromTo($content, '<div id="uas-consumer-ato-pin-challenge" class="two-step-verification">', '<script id="').'</li></ul></form></div></div>';
                 $formcode = '<form '.CutFromTo($content, '<div id="uas-consumer-ato-pin-challenge" class="two-step-verification">', '</form>');
               }
               while (stripos($formcode, '"hidden"')!==false){$formcode = substr($formcode, stripos($formcode, '"hidden"')+8); $name = trim(CutFromTo($formcode,'name="', '"'));
                 if (!in_array($name, $md)) { $md[] = $name; $val = trim(CutFromTo($formcode,'value="', '"')); $flds[$name]= $val; }
               } $flds['session_key'] = $u; $flds['session_password'] = $p;  $flds['signin'] = 'Sign%20In'; // prr($flds); prr($nxs_gCookiesArr);
               $ser = array(); $ser['c'] = $ck; $ser['f'] = $flds; $seForDB = serialize($ser); return array('out' => $text, 'ser'=>$seForDB);
           }
           if (stripos($content, 'captcha recaptcha')!==false) {//## Captcha
             $ca = nxs_remote_get('https://www.google.com/recaptcha/api/noscript?k=6LcnacMSAAAAADoIuYvLUHSNLXdgUcq-jjqjBo5n');
             if (is_nxs_error($ca)) {  $badOut = print_r($ca, true)." - [captcha] ERROR"; return $badOut; } $img = CutFromTo($ca['body'], 'src="image?c=', '"');
             $formcode = '<form '.CutFromTo($content, '<form action="https://www.linkedin.com/uas/captcha-submit" ', '</form>');  $formcode = str_ireplace('</iframe>', '', $formcode);
             $formcode = str_ireplace('<iframe src="https://www.google.com/recaptcha/api/noscript?k=6LcnacMSAAAAADoIuYvLUHSNLXdgUcq-jjqjBo5n" height="300" width="500" frameborder="0">', $ca['body'], $formcode);
             return array('cimg' => $img, 'ck'=>$ck, 'formcode'=>$formcode);
           }
           if (stripos($content, '"status":"fail"')!==false) { if ($this->debug) echo "[LI] Login failed;<br/>\r\n";
             $content = str_ireplace('href="/uas/','href="https://www.linkedin.com/uas/',$content); $rJson = json_decode($content, true); $badOut = "LOGIN ERROR: ".print_r($rJson, true); return $badOut;
           }
           if (stripos($content, 'textarea name="postText"')!==false || stripos($content, 'id="sharebox-container"')!==false) { if ($this->debug) echo "[LI] Login OK; Got Form; <br/>\r\n"; $this->ck = $ck; return false;}
        } return $badOut.print_r($rep, true);
      } else { if ($this->debug) echo "[LI] Saved Data is OK;<br/>\r\n"; return false; }
    }
    function post($msg, $lnkArr, $to){ global $nxs_plurl; $postFormType = 0; $isGrp = false; $ck = $this->ck; $to = utf8_encode($to); $parts = parse_url($to);
      $to = $parts['scheme'].'://'.$parts['host'].str_replace('%2F','/',urlencode($parts['path'])).((isset($parts['query']) && $parts['query']!='')?'?'.$parts['query']:'');
      $to = str_replace('%25', '%', $to); $hdrsArr = $this->headers('https://www.linkedin.com/company/home?trk=nav_responsive_sub_nav_companies'); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy);
      $rep = nxs_remote_get($to, $advSet);   if ($this->debug) echo "[LI] Posting to: ".$to."<br/>\r\n"; // prr($rep); die();
      if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; }
      if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }
      $contents = $rep['body']; $contents = str_ireplace('https://www.linkedin.com','',str_ireplace('http://www.linkedin.com','',$contents));
      $prfx = stripos($contents,'X-Progress-ID=')!==false?CutFromTo($contents,'X-Progress-ID=', '"'):'ss333698448'; $ck = nxsClnCookies($ck);
      if (stripos($contents, '<form action="/share?submitPost="')!==false) $contents = CutFromTo($contents, '<form action="/share?submitPost="','</form>');
        elseif (stripos($contents, 'name="pageKey" content="d_grp_community_feed_bootstrap"')!==false ){ $postFormType=5; }
        elseif (stripos($contents, '<form action="/nhome/submit-post"')!==false ) { $contents = CutFromTo($contents, '<form action="/nhome/submit-post"','</form>'); $postFormType = 1;  }
        elseif (stripos($contents, '<form action="/nhome/submit&#45;post"')!==false ) { $contents = CutFromTo($contents, '<form action="/nhome/submit&#45;post"','</form>'); $postFormType = 1;  }
        elseif ( ($to=='http://www.linkedin.com/home' || $to=='https://www.linkedin.com/home') && stripos($contents, '/uas/logout')!==false ) { $postFormType = 4;  }
        elseif (stripos($contents, '<form action="/groups"')!==false ){$contents=CutFromTo($contents,'<form action="/groups"','</form>'); $postFormType=2; $isGrp=true; }
        elseif (stripos($contents, 'action="/grp/postForm/submit"')!==false ){$contents=CutFromTo($contents,'action="/grp/postForm/submit"','</form>'); $postFormType=3; $isGrp=true; }
        else { $msg = ''; if (stripos($contents, '<div role="alert" class="alert error">')!==false ) $msg = strip_tags(CutFromTo($contents,'<div role="alert" class="alert error">','</div>'));
          return "Error: No posting form found on ".$to.". ". (!empty($msg)?$msg:'You are either not logged in or have no posting privileges on this page.');
        }
      //## GET HIDDEN FIELDS
      $md = array(); $flds  = array(); $imgCid = '';
      if ($postFormType!=5){ if ($postFormType == 4) {  $flds['csrfToken'] = CutFromTo($contents, 'csrfToken":"', '"'); }
        else while (stripos($contents, '<input')!==false){ $inpField = trim(CutFromTo($contents,'<input', '>')); $name = trim(CutFromTo($inpField,'name="', '"'));
          if ( stripos($inpField, '"hidden"')!==false && $name!='' && !in_array($name, $md)) { $md[] = $name; $val = trim(CutFromTo($inpField,'value="', '"')); $flds[$name]= $val; }
          $contents = substr($contents, stripos($contents, '<input')+8);
        }
        if (!empty($lnkArr) && $lnkArr['postType']!='T'){ $flds['contentImageCount'] = '2'; $flds['contentImageIndex'] = '0'; $flds['contentEntityID'] = ($postFormType > 0?'ARTC_':'').'5681815750';
          if (isset($lnkArr['img']) && $lnkArr['img']!=''){ $flds['contentImageIncluded'] = 'true'; $flds['contentImage'] = $lnkArr['img']; } $flds['contentSummary'] = isset($lnkArr['url'])?$lnkArr['desc']:'';
          $flds['contentUrl'] = isset($lnkArr['url'])?$lnkArr['url']:''; $flds['contentTitle'] = isset($lnkArr['title'])?strip_tags($lnkArr['title']):'';
        } $flds['postText'] = strip_tags($msg); $flds['shareText'] = strip_tags($msg);  $flds['ajax'] = 'true'; $flds['postVisibility'] = 'EVERYONE';
        if ($isGrp) { $flds['postTitle'] = strip_tags($lnkArr['postTitle']); $flds['title'] = strip_tags($lnkArr['postTitle']); $flds['details'] = strip_tags($msg);  $flds['displayCategory'] = 'DISCUSSION'; } else {
          if ($postFormType==1 && $lnkArr['postType']=='T') { foreach ($ck as $ci=>$cc) { if($cc->name =='JSESSIONID') $kkk = str_replace('"','',$cc->value);}
            $fldsT = array('ajax'=>'true'); $fldsT['postText'] = $flds['postText'];  $fldsT['companyId'] = $flds['companyId'];  $fldsT['csrfToken'] = $kkk; $flds = $fldsT;
          }
        }
        if ($postFormType==4) { $flds['mentions'] = '[]'; $flds['dist.networks[0]'] = 'PUBLIC';
          if (!empty($lnkArr) && $lnkArr['postType']!='T') {  $flds['content.id'] = $flds['contentEntityID']; $flds['content.url'] = $flds['contentUrl'];
            $flds['content.resolvedUrl'] = $flds['contentUrl']; $flds['content.title'] = $flds['contentTitle']; $flds['content.description'] =  $flds['contentSummary'];
            $flds['content.image.url'] = $lnkArr['img']; $flds['content.image.width'] = ''; $flds['content.image.height'] = ''; $flds['content.image.size'] = '';
          }
        } if ($flds['csrfToken']=='delete me') { foreach ($ck as $c) if ($c->name=='JSESSIONID') $flds['csrfToken'] = substr($c->value, 1, -1);}
        if ($postFormType==0) $pURL = 'http://www.linkedin.com/share?submitPost='; elseif ($postFormType==1) $pURL = 'https://www.linkedin.com/nhome/submit-post';   elseif ($postFormType==3) $pURL = 'https://www.linkedin.com/grp/postForm/submit'; elseif ($postFormType==4) $pURL = 'https://www.linkedin.com/sharing/share?trk=ONSITE_OZ_SHAREBOX'; else $pURL = 'http://www.linkedin.com/groups';
      }
      //## IMG
      if ( (!$isGrp ) && !empty($lnkArr['postType']) && $lnkArr['postType']=='I' && !empty($lnkArr['img'])) {
          $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.54 Safari/537.36';
          $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); $imgData = wp_remote_get($lnkArr['img'], $advSet); //prr($lnkArr['img']);
          if(is_nxs_error($imgData) || empty($imgData['body']) || (!empty($imgData['headers']['content-length']) && (int)$imgData['headers']['content-length']<200)) { $options['attchImg'] = 0;
            $badOut[] = 'Image Error: Could not get image ('.$lnkArr['img'].'), will post without it - Error:'.print_r($imgData, true);
          } else $imgData = $imgData['body'];
          $params  = "------WebKitFormFQc7dbZE\r\nContent-Disposition: form-data; name=\"file_name\"; filename=\"image.jpg\"\r\nContent-Type: image/jpg\r\n\r\n".$imgData."\r\n------WebKitFormFQc7dbZE--";
          $iurl = 'https://slideshare.www.linkedin.com/upload?X-Progress-ID='.$prfx.'14302495287920.01809079945087433&iframe_jsonp=true&window_post=true&post_window=parent&jsonp_callback=LI.JSONP.LI62297f57_9c63_6792_97c2_5dda196c8e3a';
          $hdrsArr = $this->headers($to, 'http://www.linkedin.com', 'POST');  unset($hdrsArr['Content-Type']);  $hdrsArr['Content-Type']='multipart/form-data; boundary=----WebKitFormFQc7dbZE';
          $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $params, $this->proxy); $rep = nxs_remote_post($iurl, $advSet); if (is_nxs_error($rep)) {  $badOut[] = 'Image Error: '.print_r($rep, true); } $imgID = ''; // prr($rep); die();
          if (stripos($rep['body'], '"file_key":"')===false) {  $badOut[] = 'Image Error: '.print_r($rep, true); } else { $imgID = CutFromTo($rep['body'], '"file_key":"', '"'); //prr($imgID);
            $flds['fileShareFileId']=$imgID; $imgID = str_ireplace('.','-large.',$imgID); $flds['contentImageCount']=1; $flds['fileShareFileType']='jpg'; $flds['contentTitle']='image.jpg';
            $flds['contentImage']='http://image-store.slidesharecdn.com/'.$imgID; $flds['contentUrl']='http://image-store.slidesharecdn.com/'.$imgID;
            $flds['postVisibility2']='all-followers'; $flds['pageKey']='biz-overview-internal'; unset($flds['shareText']);  $flds['contentEntityID']='FSHR_38';
            if ($postFormType==4) {$flds['content.image.url']=$flds['contentImage']; $flds['content.url']=$flds['contentImage']; $flds['content.fileEx']='jpg'; $flds['content.fileId']=$imgID; }
          } $imgCid = ',"contentId":"'.$flds['fileShareFileId'].'"';
      }
      if ($isGrp){ $flds['shareImageUrl']=$flds['contentImage']; $flds['shareTitle']=$flds['contentTitle']; $flds['shareUrl']=$flds['contentUrl'];
        $flds['shareDescription']=$flds['contentSummary']; $flds['shareId']='ARTC_7088818443089997666';
      } $hdrsArr = $this->headers($to, 'https://www.linkedin.com', 'POST', true);

      if ($postFormType==5){ $cID = preg_replace("/[^0-9]/","",$to); $pURL = 'https://www.linkedin.com/communities-api/v1/discussion?groupId='.$cID; $msg = str_replace("\n",'\\n', str_replace("\r",'',str_replace("\r\n","\n",$msg)));
        $hdrsArr = $this->headers('https://www.linkedin.com', 'https://www.linkedin.com', 'JSON', true); foreach ($ck as $ci=>$cc) { if($cc->name =='JSESSIONID') $hdrsArr['Csrf-Token'] = str_replace('"','',$cc->value);} $hdrsArr['Accept'] = 'application/json, text/javascript, */*; q=0.01';
        if ($lnkArr['postType']=='A') { $UU = 'https://www.linkedin.com/communities-api/v1/url-preview/'.urlencode($lnkArr['url']); $pURL = 'https://www.linkedin.com/communities-api/v1/discussion?groupId='.$cID;
          $advSetU = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $repU = nxs_remote_get($UU, $advSetU); if (is_nxs_error($repU)) { $badOut = "ERROR (Preview) ".print_r($repU, true); return $badOut; }  $ctU = $repU['body'];
          while (stripos($ctU,'"status":"KEEP_POLLING"')!==false) { sleep(5); $repU = nxs_remote_get($UU, $advSetU); $ctU = $repU['body']; } //prr($ctU);
          if (stripos($ctU,'"status":"FAILED"')!==false) { $flds = '{"communityId":"'.$cID.'","contentType":"TEXT","comments":[],"mentions":[],"activityType":"DISCUSSION","title":"'.$lnkArr['postTitle'].'","body":"'.strip_tags($msg).'\\n\\n'.$lnkArr['url'].'"}'; }
          elseif (stripos($ctU,'"status":"SUCCEEDED"')!==false) { $lid = CutFromTo($ctU,'"urn:li:ingestedContent:','"');  $ctU = str_replace('\"','ZZZ!==X==!ZZZ',$ctU);  $cdesc = CutFromTo($ctU,'"description":"','"'); $cttl = CutFromTo($ctU,'"title":"','"');
            $flds = '{"communityId":"'.$cID.'","contentType":"LINK_SHARE","comments":[],"mentions":[],"activityType":"DISCUSSION","title":"'.$lnkArr['postTitle'].'","body":"'.strip_tags($msg).'","contentId":"urn:li:ingestedContent:'.$lid.'","contentTitle":"'.$cttl.'","contentBody":"'.$cdesc.'"}';
            $flds = str_replace('ZZZ!==X==!ZZZ', '\"',$flds);
          }
        } else $flds = '{"communityId":"'.$cID.'","contentType":"'.(empty($imgCid)?'TEXT':'RICH_MEDIA_SHARE').'","comments":[],"mentions":[],"activityType":"DISCUSSION","title":"'.$lnkArr['postTitle'].'","body":"'.strip_tags($msg).'"'.$imgCid.'}';
      }
      //## POST
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $flds, $this->proxy); $rep = nxs_remote_post($pURL, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $contents = $rep['body']; //prr($pURL); prr($advSet);  prr($rep);  die();
      if (stripos($contents, '"responseStatus":"CREATED"')!==false ) { $pid = CutFromTo($contents,'"activityId":"','"');  $to = 'https://www.linkedin.com/groups/'.$cID.'/'.$pid;
        return array('isPosted'=>'1', 'postID'=>$pid, 'postURL'=>$to, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);;
      }
      if (stripos($contents, '"errorType"')!==false ) { return "Group Post Failure: ".$contents; }
      if ((!empty($rep['headers']['location']) && stripos($rep['headers']['location'], 'success=')!==false)) return array('isPosted'=>'1', 'postID'=>'', 'postURL'=>$to, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
      if ((!empty($rep['headers']['location']) && stripos($rep['headers']['location'], 'failure=')!==false) || stripos($contents, 'formErrors')!==false ) { return "Post Failure: ".CutFromTo($contents,'<formErrors>', '</formErrors>'); }
      if ($rep['response']['code']=='302' && !empty($rep['headers']['location'])) { $hdrsArr = $this->headers($pURL);
         $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy);  $rep = nxs_remote_get($rep['headers']['location'], $advSet);
         if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } /* $ck = $rep['cookies']; */ $contents = $rep['body']; sleep(1);//  prr($rep);
      }   if ($this->debug) prr($rep);
      if (stripos($contents, '"status":"SUCCESS"')!==false ) {
         if (stripos($contents, '"url":"')!==false ) return array('isPosted'=>'1', 'postID'=>CutFromTo($contents,'&item=','"'), 'postURL'=>'https://www.linkedin.com'.CutFromTo($contents,'"url":"','"'), 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
         if (stripos($contents, '"activityId":"')!==false ) return array('isPosted'=>'1', 'postID'=>CutFromTo($contents,'"activityId":"','"'), 'postURL'=>'http://www.linkedin.com/nhome/updates?activity='.CutFromTo($contents,'"activityId":"','"'), 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
      }
      if (stripos($contents, '"status":"PENDING_APPROVAL"')!==false ) return array('isPosted'=>'1', 'postID'=>'PENDING_APPROVAL', 'postURL'=>$to, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
      if (stripos($contents, '"status":"NON_DISCUSSION"')!==false ) return array('isPosted'=>'1', 'postID'=>'LINKEDIN MOVED POST TO PROMOTIONS', 'postURL'=>$to, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
      if (stripos($contents, '<responseInfo>SUCCESS</responseInfo>')!==false ) { $outURL = json_decode(str_replace('&quot;', '"', CutFromTo($contents, '<jsonPayLoad>', '</jsonPayLoad>')), true);
        if (!empty($outURL['isPremoderated']) && $outURL['isPremoderated'] == 'true') return array('isPosted'=>'1', 'postID'=>'closedGroupNoID', 'postURL'=>'closedGroupNoURL', 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
        $outURL = $outURL['sharingUpdateUrl'];
        if (stripos($outURL, '_internal/mappers/shareUscpActivity')!==false && stripos($outURL, 'companyId')!==false && stripos($outURL, 'updateId')!==false) { $hdrsArr = $this->headers('https://www.linkedin.com');
            $outURL = str_replace('&amp;','&',$outURL); $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $repJS = nxs_remote_get($outURL, $advSet );
            if (is_nxs_error($repJS)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $contents = $repJS['body'];
            if (stripos($contents, '"link_permalink_url":"')!==false ) $outURL = "https://www.linkedin.com".CutFromTo($contents, '"link_permalink_url":"','&goback=');
        } if ($outURL!='') return array('isPosted'=>'1', 'postID'=>$outURL, 'postURL'=>$outURL, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
      }
      if (stripos($contents, 'Request Error')!==false ) { return  "Post Failure: Request Error"; }
      if (stripos($contents, '<responseInfo>FAILURE</responseInfo>')!==false ) { return  "Post Failure: ".CutFromTo($contents,'<responseMsg>', '</responseMsg>'); }
      if (stripos($contents, '<responseInfo>')!==false ) { return  "Post Problem: ".CutFromTo($contents,'<responseMsg>', '</responseMsg>'); }
      return false;
    }

} }
//================================Flipboard===========================================
if (!class_exists('nxsAPI_FP')){class nxsAPI_FP{ var $ck = array(); var $tk=''; var $u=''; var $debug = false; var $proxy = array();
    function headers($ref, $org='', $post=false, $aj=false){ $hdrsArr = array();
      $hdrsArr['Cache-Control']='max-age=0'; $hdrsArr['Connection']='keep-alive'; $hdrsArr['Referer']=$ref;
      $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.22 Safari/537.36';
      if($post==='j') $hdrsArr['Content-Type']='application/json;charset=UTF-8'; elseif($post===true) $hdrsArr['Content-Type']='application/x-www-form-urlencoded';
      if($aj===true) $hdrsArr['X-Requested-With']='XMLHttpRequest';  if ($org!='') $hdrsArr['Origin']=$org;
      $hdrsArr['Accept']='text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';// $hdrsArr['DNT']='1';
      if (function_exists('gzdeflate')) $hdrsArr['Accept-Encoding']='gzip,deflate,sdch';
      $hdrsArr['Accept-Language']='en-US,en;q=0.8'; return $hdrsArr;
    }
    function check($u=''){ $ck = $this->ck; if (!empty($ck) && is_array($ck)) { $usr = 'hre'; if ($this->debug) echo "[FP] Checking user ".$u."...<br/>\r\n";
      $hdrsArr = $this->headers('https://flipboard.com/profile');  $advSet = nxs_mkRemOptsArr($hdrsArr,$ck); $rep = nxs_remote_get( 'https://flipboard.com/profile', $advSet);
      if (is_nxs_error($rep)) return false; if (stripos($rep['body'],'"authorUsername":"')!==false) { $usr = trim(strip_tags(CutFromTo($rep['body'], '"authorUsername":"', '"'))); } else return false;
        if (empty($u) || $u==$usr) return true; else return false;
      } else return false;
    }
    function connect($u,$p){ $badOut = 'Error: '; // $this->debug = true;
      //## Check if alrady IN
      if (!$this->check($u)){ if ($this->debug) echo "[FP] NO Saved Data; Logging in...<br/>\r\n";  $url = "";  $hdrsArr = $this->headers('');
        $advSet = nxs_mkRemOptsArr($hdrsArr); $rep = nxs_remote_get('https://flipboard.com/signin', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - =1= ERROR"; return $badOut; }
        $ck = $rep['cookies']; $rTok = CutFromTo($rep['body'], 'id="_csrf" type="hidden" value="', '"');// $rTok = str_replace('&#x2f;','/',$rTok);
        $hdrsArr = $this->headers('https://flipboard.com/', 'https://flipboard.com', true,true);  $flds = array('username' => $u, 'password' => $p, '_csrf' => $rTok); $flds = http_build_query($flds);
        $advSet = nxs_mkRemOptsArr($hdrsArr,$ck,$flds); $response = nxs_remote_post('https://flipboard.com/api/flipboard/login', $advSet); //prr($advSet);  prr($response); die();
        if (is_nxs_error($response)) {  $badOut = print_r($response, true)." - ERROR"; return $badOut; } $ck =  $response['cookies'];
        if (!empty($response['body']) && stripos($response['body'], 'id="errormessage"')!==false) { $errMsg = CutFromTo($response['body'],'id="errormessage"','/p>'); $errMsg = CutFromTo($errMsg,'>','<'); return $errMsg; }
        if (stripos($response['body'], '"success":true')!==false) { $this->ck = $ck; return false; }
        if (isset($response['headers']['location']) && ( $response['headers']['location']=='https://editor.flipboard.com/' || $response['headers']['location']=='/')) {
        $hdrsArr = $this->headers('https://editor.flipboard.com/'); $advSet = nxs_mkRemOptsArr($hdrsArr,$ck); $rep = nxs_remote_get( 'https://flipboard.com/profile/', $advSet);
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $mh = trim(strip_tags(CutFromTo($rep['body'], '<a href="/account">', '</a>'))); $this->ck = $ck; return false;
      } else  $badOut = print_r($response, true)." - ERROR"; return $badOut;
    }}

    function post($post){ $ck = $this->ck; $hdrsArr = $this->headers('https://editor.flipboard.com/'); $badOut = array();
      $advSet = nxs_mkRemOptsArr($hdrsArr,$ck); $rep = nxs_remote_get('https://share.flipboard.com/bookmarklet/popout?v=2&title=&url='.urlencode($post['url']).'&t=', $advSet);  //prr($rep);
      if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 1"; return $badOut; } $rTok = CutFromTo($rep['body'], 'id="fl-csrf">&quot;', '&quot;');
      $rTok = str_replace('&#x2f;','/',$rTok); if (empty($rTok)) return "Error: ".strip_tags($rep['body']);// $ck =   $rep['cookies'];
      if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }
      $flds = array("url"=>$post['url'], "_csrf"=>$rTok); $flds = json_encode($flds, JSON_UNESCAPED_SLASHES); $hdrsArr = $this->headers('https://share.flipboard.com/bookmarklet/popout', 'https://share.flipboard.com', 'j');
      $hdrsArr['Accept'] = 'application/json, text/plain, */*'; $advSet = nxs_mkRemOptsArr($hdrsArr,$ck,$flds); $response = nxs_remote_post( 'https://share.flipboard.com/bookmarklet/flip', $advSet);  //prr($advSet); prr($response);
      if (is_nxs_error($rep)) {  $badOut = print_r($response, true)." - ERROR 2"; return $badOut; }
      if (stripos($response['body'], '"success":true')!==false) { $txtArr = json_decode($response['body'], true);
        if (stripos($post['mgzURL'],'@')!==false){ $advSet = nxs_mkRemOptsArr($hdrsArr,$ck); $rep = nxs_remote_get($post['mgzURL'],$advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR 01"; return $badOut; }
          $mgzURL = CutFromTo($rep['body'], '"magazineTarget":"', '"');  $sccID = 'auth/'.CutFromTo($rep['body'], '"remoteidToShare":"', '"');
        }elseif (stripos($post['mgzURL'],'auth/flipboard/curator')!==false) {
          $mgzURL = $post['mgzURL']; $mgzURL = 'flipboard/mag-'.str_replace('-','%252D', urldecode(CutFromTo($mgzURL."|||", 'magazine%252F', '|||')));
          $sccID = $post['mgzURL']; $sccID = urldecode(CutFromTo($sccID."|||", 'section?sections=', '|||'));
        } else return "Incorrect Flipboard URL";
        $flds = array("url"=>$post['url'],"sig"=>$txtArr['sig'],"image"=>$post['imgURL'],"price"=>null,"currency"=>'$',"title"=>'',"text"=>$post['text'],"target"=>$mgzURL,"services"=>"","_csrf"=>$rTok); // prr($flds);
        $flds = json_encode($flds); $flds = str_replace('\/','/',$flds); $hdrsArr = $this->headers('https://share.flipboard.com/bookmarklet/popout', 'https://share.flipboard.com', 'j');
        $advSet = nxs_mkRemOptsArr($hdrsArr,$ck,$flds); $response = nxs_remote_post('https://share.flipboard.com/bookmarklet/save', $advSet);
        if (stripos($response['body'], '"success":true')!==false) { sleep(2);
          $flds = array("sectionid"=>$sccID, "title"=>'', "imageURL"=>$post['imgURL'], "_csrf"=>$rTok); $flds = json_encode($flds); $flds = str_replace('\/','/',$flds); //prr($flds);
          $advSet = nxs_mkRemOptsArr($hdrsArr,$ck,$flds); $resp2 = nxs_remote_post( 'https://share.flipboard.com/v1/social/shortenSection', $advSet);// prr($resp2);
          $respLink  = json_decode($resp2['body'], true); $respLink = $respLink['result']; $respID = str_replace('http://flip.it/', '', $respLink);
          return array('postID'=>$respID, 'isPosted'=>1, 'postURL'=> $respLink, 'pDate'=>date('Y-m-d H:i:s'), 'ck'=>$ck);
        } else { $badOut['Error'] .= print_r($response, true); return $badOut; }
      } else return "Error: ".strip_tags($response['body']);
    }
}}
//================================Instagram===========================================
if (!class_exists('nxsAPI_IG')){class nxsAPI_IG{ var $ck = array(); var $agent=''; var $guid=''; var $phid='';  var $dId=''; var $debug = false; var $loc = ''; var $proxy = array(); var $advSet = array();
    function __construct() { $this->agent = 'Instagram 8.0.0 Android (16/4.1.2; 480dpi; 1080x1920; LGE/lge; LG-E980; geefhd; geefhd; en_US)';
      $this->guid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',  mt_rand(0, 65535),  mt_rand(0, 65535),  mt_rand(0, 65535),  mt_rand(16384, 20479),  mt_rand(32768, 49151),  mt_rand(0, 65535),  mt_rand(0, 65535),  mt_rand(0, 65535));
      $this->phid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',  mt_rand(0, 65535),  mt_rand(0, 65535),  mt_rand(0, 65535),  mt_rand(16384, 20479),  mt_rand(32768, 49151),  mt_rand(0, 65535),  mt_rand(0, 65535),  mt_rand(0, 65535));
      $this->dId = "android-".$this->guid;
    }
    function headers($ref, $org='', $type='GET', $aj=false){$hdrsArr = array(); $hdrsArr['User-Agent']='Instagram 7.10.0 Android (23/6.0; 515dpi; 1440x2416; huawei/google; Nexus 6P; angler; angler; en_US)';
      $hdrsArr['connection']='keep-alive'; $hdrsArr['Accept-Language']='en-US'; $hdrsArr['Accept-Encoding']='gzip, deflate';
    return $hdrsArr;}
    function doSig($data) { return hash_hmac('sha256', $data, '9b3b9e55988c954e51477da115c58ae82dcae7ac01c735b4443a3c5923cb593a'); }
    function makeSQExtend( $imgSrc, $thumbFile, $thumbSize=1000 ){ $type = substr( $imgSrc , strrpos( $imgSrc , '.' )+1 );  // prr($type);      prr($filename); die();
      switch( $type ){ case 'jpg' : case 'jpeg' : $src = imagecreatefromjpeg( $imgSrc ); break; case 'png' : $src = imagecreatefrompng( $imgSrc ); break; case 'gif' : $src = imagecreatefromgif( $imgSrc ); break; }
      list($w, $h) = getimagesize($imgSrc); if ($w > $h)  $bgSide = $w; else { $bgSide = $h; } if ($thumbSize<$bgSide) $sqSize = $thumbSize; else $sqSize = $bgSide; //$width = imagesx( $src ); $height = imagesy( $src );
      if($w> $h) { $width_t=$sqSize; $height_t=round($h/$w*$sqSize); $off_y=ceil(($width_t-$height_t)/2); $off_x=0; }
        elseif($h> $w) { $height_t=$sqSize; $width_t=round($w/$h*$sqSize); $off_x=ceil(($height_t-$width_t)/2); $off_y=0; } else { $width_t=$height_t=$sqSize; $off_x=$off_y=0; }
      $new = imagecreatetruecolor( $sqSize , $sqSize ); $bg = imagecolorallocate ( $new, 255, 255, 255 ); imagefill ( $new, 0, 0, $bg ); imagecopyresampled( $new , $src , $off_x, $off_y, 0, 0, $width_t, $height_t, $w, $h );
      $res = imagejpeg( $new , $thumbFile, 100); @imagedestroy( $new ); @imagedestroy( $src );
    }
    function makeSQCrop( $imgSrc, $thumbFile, $thumbSize=1000 ){ list($width, $height) = getimagesize($imgSrc); $type = substr( $imgSrc , strrpos( $imgSrc , '.' )+1 );  // prr($type);      prr($filename); die();
      switch( $type ){ case 'jpg' : case 'jpeg' : $src = imagecreatefromjpeg( $imgSrc ); break; case 'png' : $src = imagecreatefrompng( $imgSrc ); break; case 'gif' : $src = imagecreatefromgif( $imgSrc ); break; }
      if ($width > $height) { $y = 0; $x = ($width - $height) / 2;} else { $x = 0; $y = ($height - $width) / 2;} $minSide = min($width,$height);
      $thumb = imagecreatetruecolor($minSide, $minSide); imagecopyresampled($thumb, $src, 0, 0, $x, $y, $minSide, $minSide, $minSide, $minSide);
      unlink($imgSrc); imagejpeg($thumb,$thumbFile); @imagedestroy($src); @imagedestroy($thumb);
    }
    /*function check(){ $ck = $this->ck; if (!empty($ck) && is_array($ck)) { $hdrsArr = $this->headers(''); if ($this->debug) echo "[IG] Checking....;<br/>\r\n";
        $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, '', $this->proxy); $rep = nxs_remote_get('https://i.instagram.com/api/v1/accounts/current_user/', $advSet);
        if (is_nxs_error($rep)) return $rep['body']; $ck = $rep['cookies']; $contents = $rep['body']; //if ($this->debug) prr($contents);
        $ret = stripos($contents, '"status": "ok"')!==false; $usr = CutFromTo($contents, '"username": "', '"'); if ($ret & $this->debug) echo "[PN] Logged as:".$usr."<br/>\r\n";
        $apVer = trim(CutFromTo($contents,'"pk": "', '"'));  $this->apVer = $apVer;
        $status = trim(CutFromTo($contents, '"status": "', '"'));
        if ($status=="ok") return $ret; else return print_r($contents, true);
      } else return 'asd';
    }*/
    function altCurlIG( $ch, $r ){ $tmp = $r['body']; if (function_exists('curl_file_create')) { $file  = curl_file_create($tmp); $flds = array('device_timestamp' => time(), 'photo' => $file); } else $flds = array('device_timestamp' => time(), 'photo' => '@'.$tmp);
      if ( !empty( $r['headers'] ) ) { $headers = array(); foreach ( $r['headers'] as $name => $value ) if ($name!=='Content-Length')  $headers[] = "{$name}: $value"; curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );}
      curl_setopt($ch, CURLOPT_POST, TRUE); curl_setopt($ch, CURLOPT_POSTFIELDS, $flds);
    }
    function altCurlIGX( $ch, $r ){ $flds = $r['body']; //prr($flds);
      if ( !empty( $r['headers'] ) ) { $headers = array(); foreach ( $r['headers'] as $name => $value ) if ($name!=='Content-Length')  $headers[] = "{$name}: $value"; //else  $headers[] = "Content-Length: ".strlen($flds); prr($headers);
      curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );} curl_setopt($ch, CURLOPT_POST, TRUE); curl_setopt($ch, CURLOPT_POSTFIELDS, $flds);
    }
    function bldBody($arr){ $body = "";
      foreach($arr as $b){ $body .= "--".$this->guid."\r\n"; $body .= "Content-Disposition: ".$b["type"]."; name=\"".$b["name"]."\"";
        if(isset($b["filename"])) { $ext = pathinfo($b["filename"], PATHINFO_EXTENSION); $body .= "; filename=\"".substr(bin2hex($b["filename"]),0,48).".".$ext."\""; }
        if(isset($b["headers"]) && is_array($b["headers"])) foreach($b["headers"] as $header)$body.= "\r\n".$header; $body.= "\r\n\r\n".$b["data"]."\r\n";
      } $body .= "--".$this->guid."--"; return $body;
    }
    function setCK($ck){
      $this->ck = $ck;
    }
    function getCK(){
      return $this->ck;
    }
    function getAdvSet(){
      return $this->advSet;
    }
    function directLogin($advSet){
      if(!$advSet){
        return false;
      }

      $rep = nxs_remote_post('https://i.instagram.com/api/v1/accounts/login/', $advSet);
        if (is_nxs_error($rep)) {
          $badOut = print_r($rep, true)." - ERROR -02-";
          return $badOut;
        }
        if (empty($rep['body'])) {
          $badOut = print_r($rep, true)." - ERROR -03-";
          return $badOut;
        }
        $obj = @json_decode($rep['body'], true); if (empty($obj) || !is_array($obj) || empty($obj['status'])) {  $badOut = "ERROR -04- ".print_r($rep, true); return $badOut; }
        if ($obj['status']!='ok' && !empty($obj['message'])) { return "ERROR -LOGIN- ".print_r($obj, true); } if ( empty($obj['logged_in_user']) || empty($obj['logged_in_user']['username'])) {  $badOut = "ERROR -04- ".print_r($rep, true); return $badOut; }
        if ($obj['status']=='ok') {
          $ck = $rep['cookies'];
          foreach ($ck as $ci=>$cc) $ck[$ci]->value = urlencode($cc->value);
          $this->ck = $ck;
          $this->advSet = $advSet;
          return false;
        } else return "ERROR -POST- ".print_r($obj, true);
    }
    function connect($u,$p){ $badOut = 'Error: '; // $this->debug = true;
        $flds = '{"device_id":"'.$this->dId.'","guid":"'.$this->guid.'","username":"'.$u.'","password":"'.$p.'","Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"}';
        $flds = 'signed_body='.$this->doSig($flds).'.'.urlencode($flds).'&ig_sig_key_version=4';
        //## ACTUAL LOGIN
        $hdrsArr = $this->headers('', '', 'POST');
        $hdrsArr['User-Agent']=$this->agent;
        $advSet = nxs_mkRemOptsArr($hdrsArr, '', $flds, $this->proxy);
        $rep = nxs_remote_post('https://i.instagram.com/api/v1/accounts/login/', $advSet);
        if (is_nxs_error($rep)) {
          $badOut = print_r($rep, true)." - ERROR -02-";
          return $badOut;
        }
        if (empty($rep['body'])) {
          $badOut = print_r($rep, true)." - ERROR -03-";
          return $badOut;
        }
        $obj = @json_decode($rep['body'], true); if (empty($obj) || !is_array($obj) || empty($obj['status'])) {  $badOut = "ERROR -04- ".print_r($rep, true); return $badOut; }
        if ($obj['status']!='ok' && !empty($obj['message'])) { return "ERROR -LOGIN- ".print_r($obj, true); } if ( empty($obj['logged_in_user']) || empty($obj['logged_in_user']['username'])) {  $badOut = "ERROR -04- ".print_r($rep, true); return $badOut; }
        if ($obj['status']=='ok') {
          $ck = $rep['cookies'];
          foreach ($ck as $ci=>$cc) $ck[$ci]->value = urlencode($cc->value);
          $this->ck = $ck;
          $this->advSet = $advSet;
          return false;
        } else return "ERROR -POST- ".print_r($obj, true);
    }
    function post($msg, $imgURL, $style='E'){ $ck = $this->ck; if ($this->debug) echo "[IG] Posting to ...".$imgURL."<br/>\r\n"; $badOut = '';  $msg = str_replace("\n",'\n', str_replace("\r",'', strip_tags($msg)));
      //## Get image
      $remImgURL = urldecode($imgURL); $urlParced = pathinfo($remImgURL); $remImgURLFilename = $urlParced['basename']; $imgType = substr(  $remImgURL, strrpos( $remImgURL , '.' )+1 );
      $hdrsArr = $this->headers($remImgURL); $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.54 Safari/537.36';
      $advSet = nxs_mkRemOptsArr($hdrsArr, '', '', $this->proxy); $imgData = wp_remote_get($remImgURL, $advSet);// prr($remImgURL);  // prr($imgData);
      if(is_nxs_error($imgData) || empty($imgData['body']) || (!empty($imgData['headers']['content-length']) && (int)$imgData['headers']['content-length']<200) ||
          $imgData['headers']['content-type'] == 'text/html' ||  $imgData['response']['code'] == '403' ) { $options['attchImg'] = 0;
            nxs_addToLogN('E','Error','IG','Could not get image ( '.$remImgURL.' ), will post without it - ', print_r($imgData, true)); return 'Image Upload Error, please see log';
      } $imgData = $imgData['body'];
      $tmpX=array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()]=tmpfile()))); if (!is_writable($tmpX)) return "Your temporary folder or file (file - ".$tmpX.") is not writable. Can't upload image to IG";
      rename($tmpX, $tmpX.='.'.$imgType);  register_shutdown_function(create_function('', "@unlink('{$tmpX}');")); file_put_contents($tmpX, $imgData);
      $tmp=array_search('uri', @array_flip(stream_get_meta_data($GLOBALS[mt_rand()]=tmpfile()))); if (!is_writable($tmp)) return "Your temporary folder or file (file - ".$tmp.") is not writable. Can't upload image to IG";
      rename($tmp, $tmp.='.'.$imgType); register_shutdown_function(create_function('', "@unlink('{$tmp}');")); if(($style=='E' || $style=='C') && !function_exists('imagecreatefromjpeg')) { $badOut .= "GD is not available; Can't resize;\r\n<br/>"; $style='D'; }
      if($style=='E') $this->makeSQExtend($tmpX, $tmp, 1080);  elseif ($style=='C') $this->makeSQCrop($tmpX, $tmp, 1080); else $tmp = $tmpX;
      foreach ($ck as $c) { if ($c->name=='csrftoken') $xftkn = $c->value;  if ($c->name=='ds_user_id') $uid = $c->value;} $ddt = date("Y:m:d H:i:s");
      $octStreamArr = array(array('type' => 'form-data', 'name' => 'upload_id', 'data' => (time()+100)),array('type' => 'form-data','name' => '_uuid','data' => $this->guid),array('type' => 'form-data','name' => '_csrftoken','data' => $xftkn),
        array("type"=>"form-data","name"=>"image_compression","data"=>'{"lib_name":"jt","lib_version":"1.3.0","quality":"85"}'),
        array('type' => 'form-data','name' => 'photo','data' => file_get_contents($tmp),'filename' => basename($tmp),'headers' =>array("Content-type: application/octet-stream\nContent-Transfer-Encoding: binary"))
      ); $data = $this->bldBody($octStreamArr); //prr($data);

            $hdrsArr = $this->headers('', '', 'POST'); $hdrsArr['User-Agent']=$this->agent; $hdrsArr['Content-Type']= 'multipart/form-data; boundary='.$this->guid;  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $data, $this->proxy);   //  prr($advSet);
            if (function_exists('add_action')) add_action( 'http_api_curl', array( $this, 'altCurlIGX' ), 10, 2); else $advSet['usearray'] = '1';
            $rep = nxs_remote_post('https://i.instagram.com/api/v1/upload/photo/', $advSet);
            if (function_exists('add_action')) remove_action( 'http_api_curl', array( $this, 'altCurlIGX' ));

      if (is_nxs_error($rep)) {  $badOut .= print_r($rep, true)." - ERROR -02I-"; return $badOut; }    if (empty($rep['body'])) {  $badOut .= print_r($rep, true)." - ERROR -03I-"; return $badOut; }
      $obj = @json_decode($rep['body'], true); if (empty($obj) || !is_array($obj) || empty($obj['status']) || $obj['status']!='ok'){ $badOut .= "ERROR -04I- ".print_r($rep, true); return $badOut; }
      //$geturl = 'https://i.instagram.com/api/v1/friendships/'.$uid.'/following/'; $hdrsArr = $this->headers('', '', 'GET'); $hdrsArr['User-Agent']=$this->agent;  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck); $rep = nxs_remote_get($geturl, $advSet); prr($rep);  sleep(1);
      //$geturl = 'https://i.instagram.com/api/v1/tags/search/?count=50&client_time=1456516833&q=Ttstts'; $hdrsArr = $this->headers('', '', 'GET'); $hdrsArr['User-Agent']=$this->agent;  $advSet = nxs_mkRemOptsArr($hdrsArr, $ck); $rep = nxs_remote_get($geturl, $advSet); prr($rep);  sleep(6);
      $data = '{"_csrftoken":"'.$xftkn.'","source_type":"4","_uid":"'.$uid.'","_uuid":"'.$this->guid.'","caption":"'.$msg.' ","upload_id":"'.$obj['upload_id'].'","device":{"manufacturer":"LGE","model":"LG-E980","android_version":16,"android_release":"4.1.2"},"edits":{"crop_original_size":[900.0,900.0],"crop_center":[0.0,-0.0],"crop_zoom":1.0},"extra":{"source_width":900,"source_height":900}}'; // prr($data);
      $data = 'signed_body='.$this->doSig($data).'.'.urlencode($data).'&ig_sig_key_version=4';// prr($data); //die();
      $hdrsArr = $this->headers('', '', 'POST'); $hdrsArr['User-Agent']=$this->agent; $hdrsArr['X-IG-Connection-Type']='WIFI'; $hdrsArr['X-IG-Capabilities']='3Q==';
      $advSet = nxs_mkRemOptsArr($hdrsArr, $ck, $data, $this->proxy); $rep = nxs_remote_post('https://i.instagram.com/api/v1/media/configure/', $advSet); //prr($hdrsArr); prr($rep);
      if (is_nxs_error($rep)) {  $badOut .= print_r($rep, true)." - ERROR -02I-"; return $badOut; }    if (empty($rep['body'])) {  $badOut .= print_r($rep, true)." - ERROR -03I-"; return $badOut; }
      $obj = @json_decode($rep['body'], true); if (empty($obj) || !is_array($obj) || empty($obj['status'])){ $badOut .= "ERROR -04I- ".print_r($rep, true); return $badOut; }
      if ($obj['status']!='ok' && $obj['message']=='checkpoint_required') { return "You got checkpoint! Please login to Instagram from your phone and confirm the login or action."; }
      if ($obj['status']!='ok' && !empty($obj['message'])) { $badOut .= "ERROR -POST- ".print_r($obj, true); return $badOut; }
      if ($obj['status']=='ok') { return array("isPosted"=>"1", "postID"=>$obj['media']['code'], 'pDate'=>date('Y-m-d H:i:s'), "postURL"=>'https://www.instagram.com/p/'.$obj['media']['code'], 'msg'=>$badOut, 'ck'=>$ck); } else {  $badOut .= print_r($rep, true)." - ERROR -05I-"; return $badOut; }
    }
}}

//================================vKontakte===========================================
if (!function_exists("nxs_doCheckVK")) {function nxs_doCheckVK(){ global $nxs_vkCkArray; $hdrsArr = nxs_getVKHeaders('https://vk.com/login.php'); $ckArr = nxsClnCookies($nxs_vkCkArray);
  $response = wp_remote_get('https://vk.com/settings', array( 'method' => 'GET', 'timeout' => 45, 'redirection' => 0,  'headers' => $hdrsArr, 'cookies' => $ckArr));  //  prr($response);
  if (isset($response['headers']['location']) && stripos($response['headers']['location'], 'login.php')!==false) return 'Bad Saved Login';
  if ( $response['response']['code']=='200' && stripos($response['body'], 'settings_new_pwd')!==false){  $nxs_vkCkArray = nxs_MergeCookieArr($ckArr, $response['cookies']);
      /*echo "You are IN"; */ return false;
  } else return 'No Saved Login';
  return false;
}}
if (!function_exists("nxs_doConnectToVK")) {  function nxs_doConnectToVK($u, $p, $ph=''){ global $nxs_vkCkArray; $hdrsArr = nxs_getVKHeaders('http://vk.com/login.php'); $mids = ''; //echo "LOG=";
    $response = wp_remote_get('http://vk.com/login.php', array( 'method' => 'POST', 'timeout' => 45, 'redirection' => 0,  'headers' => $hdrsArr));
    if (is_nxs_error($response)) {  $badOut = "Connection Error 1: ". print_r($response, true); return $badOut; }  $contents = $response['body'];
    $ckArr = $response['cookies']; $hdrsArr = nxs_getVKHeaders('http://vk.com/login.php', true);
    $frmTxt = CutFromTo($contents, 'action="https://login.vk.com/','</form>'); $md = array(); $flds  = array();
    while (stripos($frmTxt, '<input')!==false){ $inpField = trim(CutFromTo($frmTxt,'<input', '>')); $name = trim(CutFromTo($inpField,'name="', '"'));
     if ( stripos($inpField, '"hidden"')!==false && $name!='' && !in_array($name, $md)) { $md[] = $name; $val = trim(CutFromTo($inpField,'value="', '"')); $flds[$name]= $val; $mids .= "&".$name."=".$val;}
     $frmTxt = substr($frmTxt, stripos($frmTxt, '<input')+8);
    } $flds['email'] = $u; $flds['pass'] = $p;
    $r2 = wp_remote_post( 'https://login.vk.com/', array( 'method' => 'POST', 'timeout' => 45, 'redirection' => 0,  'headers' => $hdrsArr, 'body' => $flds, 'cookies' => $ckArr));
    if (is_nxs_error($r2)) {  $badOut = "Connection Error 2: ". print_r($r2, true); return $badOut; }  $ckArr = nxsMergeArraysOV($ckArr, $r2['cookies']);
    if ($r2['response']['code']=='302' && $r2['headers']['location']!='') $response = wp_remote_get( $r2['headers']['location'], array('timeout' => 45, 'redirection' => 0,  'headers' => $hdrsArr, 'cookies' => $ckArr));
    if (is_nxs_error($response)) {  $badOut = "Connection Error 3: ". print_r($response, true); return $badOut; }
    if ($response['response']['code']=='200' && $response['body']!='' && stripos($response['body'], 'message_text"')!==false) {$txt = CutFromTo($response['body'], 'message_text"','<ul'); return trim(strip_tags($txt)); }
    if ($response['response']['code']=='302' && $response['headers']['location']=='/') {  $ckArr = nxsMergeArraysOV($ckArr, $response['cookies']); $nxs_vkCkArray = $ckArr;
      $hdrsArr = nxs_getVKHeaders('http://vk.com/'); $response = wp_remote_get('http://vk.com/', array('redirection' => 0,  'headers' => $hdrsArr, 'cookies' => $ckArr));
      if (is_nxs_error($response)) {  $badOut = "Connection Error 4: ". print_r($response, true); return $badOut; }
      if ($response['response']['code']=='302' && $response['headers']['location']=='/login.php?act=security_check&to=&al_page=3') { //## PH Ver
        $hdrsArr = nxs_getVKHeaders('http://vk.com/'); $response = wp_remote_get('http://vk.com/login.php?act=security_check&to=&al_page=3', array('redirection' => 0,  'headers' => $hdrsArr, 'cookies' => $ckArr));
        if (is_nxs_error($response)) {  $badOut = "Connection Error 5: ". print_r($response, true);return $badOut; }
        $txt = $response['body']; if ($ph=='') { $txtF = CutFromTo($txt, 'form_table', '</tr>'); $ph1 = trim(CutFromTo($txtF, 'label ta_r">', '</div>')); $ph2 = trim(CutFromTo($txtF, 'phone_postfix">', '</span>'));
          return "Phone verification required: ".$ph1." ... ".$ph2;
        } else { $hash = CutFromTo($txt, "al_page: '3', hash: '", "'"); $flds  = array('act'=>'security_check', 'code'=> $ph, 'to'=>'', 'al'=>'1', 'al_page'=>'3', 'hash'=> $hash);
          $hdrsArr = nxs_getVKHeaders('http://vk.com/login.php?act=security_check&to=&al_page=3', true, true);
          $response = wp_remote_post('http://vk.com/login.php', array('redirection' => 0, 'body' => $flds,  'headers' => $hdrsArr, 'cookies' => $ckArr));
          if (is_nxs_error($response)) {  $badOut = "Connection Error 6: ". print_r($response, true);return $badOut; }
          if ($response['response']['code']=='200' && $response['body']!='' && stripos($response['body'], '4 hours')!==false) return "Invalid Phone verification number. You can try again in 4 hours";
          if ($response['response']['code']=='200' && $response['body']!='' && stripos($response['body'], 'incorrect')!==false) return "Invalid Phone verification number.";
          $hdrsArr = nxs_getVKHeaders('http://vk.com/'); $response = wp_remote_get('http://vk.com/', array('redirection' => 0,  'headers' => $hdrsArr, 'cookies' => $ckArr));
          if (is_nxs_error($response)) {  $badOut = "Connection Error 7: ". print_r($response, true);return $badOut; }
          if ($response['response']['code']=='302' && $response['headers']['location']=='/login.php?act=security_check&to=&al_page=3') return "Invalid verification number"; else {
            $ckArr = nxsMergeArraysOV($ckArr, $response['cookies']); $nxs_vkCkArray = $ckArr; return false;
          }
        }
      } else return false;
    } elseif (isset($response['_reason'])) { return $response['_reason']; } else return "UNKNOWN ERROR. Please contact support.".print_r($response, true);
}}
if (!function_exists("nxs_doPostToVK")) {  function nxs_doPostToVK($msg, $where, $msgOpts){ global $nxs_vkCkArray; $hdrsArr = nxs_getVKHeaders($where); $ckArr = nxsClnCookies($nxs_vkCkArray);
  $response = wp_remote_get($where, array( 'method' => 'GET', 'timeout' => 45, 'redirection' => 0,  'headers' => $hdrsArr, 'cookies' => $ckArr));
  $ckArr2 = nxs_MergeCookieArr($ckArr, $response['cookies']);  $contents = $response['body'];
  if (stripos($contents, '"post_hash":"')!==false) $hash =  CutFromTo($contents, '"post_hash":"', '"');
  if (stripos($contents, '"timehash":"')!==false) $timeHash =  CutFromTo($contents, '"timehash":"', '"');
  if (stripos($contents, '"rhash":"')!==false) $rHash =  CutFromTo($contents, '"rhash":"', '"');
  if (stripos($contents, '"public_id":')!==false) { $postTo =  '-'.CutFromTo($contents, '"public_id":', ','); $type='all'; }
  if (stripos($contents, '"user_id":')!==false) { $postTo =  CutFromTo($contents, '"user_id":', ','); $type='own'; }
  if (stripos($contents, '"group_id":')!==false) { $postTo =  '-'.CutFromTo($contents, '"group_id":', ','); $type='all'; }
  if (stripos($contents, '"id":')!==false) $uid =  CutFromTo($contents, '"id":', ',');
  $flds = array('Message'=> strip_tags($msg), 'act'=>'post', 'al'=>1, 'facebook_export'=>'', 'fixed'=>'', 'friends_only'=>'', 'from'=>'', 'hash'=>$hash, 'official'=>'', 'signed'=>'', 'status_export'=>'', 'to_id'=>$postTo, 'type'=>$type);
  if ($msgOpts['type']=='A' && $msgOpts['url']!=''){ $flds2 = array();
     $flds2['url'] = $msgOpts['url']; $flds2['act']='a_photo';  $flds2['image'] = $msgOpts['imgURL'];
    if (empty($msgOpts['vID'])) {  $flds2['index']='4'; $flds2['extra']='0'; } else { $flds['extra']='21'; $flds['extra_data']=$msgOpts['vID']; $flds2['extra'] = '21';  $flds2['index'] ='1'; }
    $hdrsArrP = nxs_getVKHeaders($where, true);     // prr($hdrsArrP); prr($flds2); //  prr($ckArr);
    //$postArr = array( 'method' => 'POST', 'timeout' => 45, 'redirection' => 0,  'headers' => $hdrsArrP, 'body' => $flds2, 'cookies' => $ckArr);
    //$r3 = wp_remote_post('http://vk.com/share.php', $postArr); $errMsg =  utf8_encode( strip_tags($r3['body']));// prr($r3);

    $flds3 = array();   $flds3['url'] = $msgOpts['url'];  $flds3['index'] ='3'; $flds3['to_mail'] = ''; $flds3['hash'] = '1445888975_a85b04a18ebbc5c76f';
    $postArr = array( 'method' => 'POST', 'timeout' => 45, 'redirection' => 0,  'headers' => $hdrsArrP, 'body' => $flds3, 'cookies' => $ckArr); //prr($postArr);

    $r3 = wp_remote_post('https://vk.com/share.php?act=url_attachment', $postArr); $errMsg =  utf8_encode( strip_tags($r3['body'])); // prr($r3); //die();
    if ((stripos($r3['body'], 'photo_id:')!==false)) {
       $attchID =  trim(CutFromTo($r3['body'], 'user_id:', ','))."_".trim(CutFromTo($r3['body'], 'photo_id:', '}'));
    } else {
      if ((stripos($r3['body'], '<div class="title">Error</div>')!==false)) { $errr =  strip_tags(CutFromTo($r3['body'], '<div class="body">', '</div>')); $errr = str_ireplace('back','',$errr);
        return "ERROR: R5: ".print_r($errr, true);
      }
      if ( ($r3['response']['code']=='302' || $r3['response']['code']=='303') && $r3['headers']['location']!='') { sleep(3);
        $r4 = wp_remote_get( $r3['headers']['location'], array('timeout' => 45, 'redirection' => 0,  'headers' => $hdrsArr, 'cookies' => $ckArr2));
        $hdrsArr2 = nxs_getVKHeaders($r3['headers']['location']); $ckArr2 = nxs_MergeCookieArr($ckArr, $r4['cookies']);
        if (($r4['response']['code']=='302' | $r4['response']['code']=='303') && $r4['headers']['location']!='') {  sleep(3);
          $r5 = wp_remote_get( $r4['headers']['location'], array('timeout' => 45, 'redirection' => 0,  'headers' => $hdrsArr2, 'cookies' => $ckArr2));
          if (stripos($r5['body'], '"photo_id"')!==false) { $attchID =  trim(CutFromTo($r5['body'], '"user_id":', ','))."_".trim(CutFromTo($r5['body'], '"photo_id":', '}')); }
        } else return "ERROR: R4: ".print_r($r3, true);
      } else return "ERROR: R3: ".print_r($r3, true);
    }   // prr($r5);
    $flds['attach1']=$attchID; $flds['attach1_type']='share'; $flds['description']=$msgOpts['urlDesc']; $flds['photo_url']=($msgOpts['imgURL']); $flds['title']=$msgOpts['urlTitle']; $flds['url']=$msgOpts['url'];
    $flds['official']=1;
  }   $hdrsArr = nxs_getVKHeaders($where, true, true); // prr($hdrsArr);    prr($flds);
  $r2 = wp_remote_post('http://vk.com/al_wall.php', array( 'method' => 'POST', 'timeout' => 45, 'httpversion'=>'1.1', 'redirection' => 0,  'headers' => $hdrsArr, 'body' => $flds, 'cookies' => $ckArr));
  if (stripos($r2['body'], 'page_wall_count_own')!==false && stripos($r2['body'], 'div id="post')!==false) { $pid = CutFromTo($r2['body'], 'div id="post', '"'); return array("code"=>"OK", "post_id"=>$pid); }
    else { $errMsg =  utf8_encode( strip_tags($r2['body'])); return "ERROR: ".print_r($errMsg, true); }
}}
//================================Reddit===========================================
if (!function_exists("doConnectToRD")) { function doConnectToRD($unm, $pass){ $url = "http://www.reddit.com/api/login/".$unm;  $hdrsArr = ''; global $nxs_gRDSubreddits;
  $flds = array('api_type' => 'json', 'user' => $unm, 'passwd' => $pass);
  $response = wp_remote_post( $url, array( 'method' => 'POST', 'timeout' => 45, 'redirection' => 0,  'headers' => $hdrsArr, 'body' => $flds));
  if (is_wp_error($response)) {  $badOut = print_r($response, true)." - ERROR"; return $badOut; }
  $ck =  $response['cookies']; $response = json_decode($response['body'], true); // prr($response);
  if (is_array($response['json']['errors']) && count($response['json']['errors'])>0 ) {  $badOut = print_r($response, true)." - ERROR"; return $badOut; }
  $data = $response['json']['data']; $mh = $data['modhash'];
  $response = wp_remote_get( 'https://www.reddit.com/subreddits/mine/moderator/', array('redirection' => 0,  'headers' => $hdrsArr, 'cookies' => $ck));
  $cnt = $response['body']; $cnt = CutFromTo($cnt, '<div id="siteTable"', '<div class="footer-parent">'); $srds = '';
  $cntArr = explode('<p class="titlerow">',$cnt); foreach ($cntArr as $txt) if (stripos($txt, 'class="title"')!==false) { $bid = CutFromTo($txt, '://www.reddit.com/r/', '/"'); $bname = trim(CutFromTo($txt, 'class="title" >', '</a>'));
        if (isset($bid)) $srds .= '<option value="'.$bid.'">'.trim($bname).'</option>';
    } $nxs_gRDSubreddits = $srds;
  return array('mh'=>$mh, 'ck'=>$ck);
}}
if (!function_exists("doGetSubredditsFromRD")) {function doGetSubredditsFromRD(){ global $nxs_gRDSubreddits;  return $nxs_gRDSubreddits; }}
//================================Flipboard===========================================
if (!function_exists("doCheckFlipboard")) { function doCheckFlipboard($ck){ return false; }}
if (!function_exists("doConnectToFlipboard")) { function doConnectToFlipboard($unm, $pass){
  $nt = new nxsAPI_FP(); $nt->debug = false; if (!empty($ck)) $nt->ck = $ck;  $loginErr = $nt->connect($unm, $pass); if ($loginErr===false && !empty($nt->ck)) return array('ck'=>$nt->ck); else return $loginErr;
}}
if (!function_exists("doPostToFlipboard")) { function doPostToFlipboard($ck, $post){ $nt = new nxsAPI_FP(); $nt->debug = false; if (!empty($ck)) $nt->ck = $ck; $ret = $nt->post($post); return $ret; }}
?>
