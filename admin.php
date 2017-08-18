<?php

if(function_exists('XH_wantsPluginAdministration') && XH_wantsPluginAdministration('gxlink')
			|| isset($gxlink)) {

  //check if register_globals is turned OFF
  if (!ini_get ("register_globals")){
      extract($HTTP_GET_VARS);
      extract($HTTP_POST_VARS);
      extract($HTTP_COOKIE_VARS);
      extract($HTTP_ENV_VARS);
      extract($HTTP_SERVER_VARS);
  }

/*
// Another possibility to
// Emulate register_globals on
if (!ini_get('register_globals')) {
    $superglobals = array($_SERVER, $_ENV,
        $_FILES, $_COOKIE, $_POST, $_GET);
    if (isset($_SESSION)) {
        array_unshift($superglobals, $_SESSION);
    }
    foreach ($superglobals as $superglobal) {
        extract($superglobal, EXTR_SKIP);
    }
    ini_set('register_globals', true);
}
*/

  $gxlink_version = "XH 1.5";
  $gxlink_plugin            = basename(dirname(__FILE__),'/');
  $gxlink_pluginfolder      = $pth['folder']['plugins'].$gxlink_plugin;      # folder where the plugin resides
  $API_HOME_DIR         = $gxlink_pluginfolder.'/txt-db-api/';           # folder where the txt-db-api resides
  $DB_DIR               = $gxlink_pluginfolder.'/';                      # folder where the db resides
  
  include_once($API_HOME_DIR.'txt-db-api.php');
  
  require $gxlink_pluginfolder."/lib/URLHelper.php";

  global $pth, $sl, $plugin;

  //$plugin            = basename(dirname(__FILE__),'/');
  $gxlink_plugin            = basename(dirname(__FILE__),'/');
  $gxlink_pluginfolder      = $pth['folder']['plugins'].$gxlink_plugin;         # folder where the plugin resides
  $refbase              = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.'&'.$gxlink_plugin.'&admin=plugin_main&action=plugin_text';   # base for the references 
  $admin= isset($_POST['admin']) ? $_POST['admin'] : $_GET['admin'];
  $action= isset($_POST['action']) ? $_POST['action'] : $_GET['action'];
  
  
  // check, if value for $database set in form getDB? (Warum erlaubst Du auch $GET?)
  if (!isset($database)) {
  	$database = isset($_POST['database']) ? $_POST['database'] : $_GET['database'];
  }
  
  // if even empty look for a session var
  if (!isset($database)) {
 
  	if (isset($_COOKIE["gxlink_database"])) {
		$database = $_COOKIE["gxlink_database"];
  	}
  }

  // no COOKIE and no $GET / $POST ?
  if (!isset($database)) {
    $database = 'DBlink'; //set default value and then set a cookie var
  }
  
  $_COOKIE["gxlink_database"] = $database;
	setcookie("gxlink_database", $database, 0, CMSIMPLE_ROOT);
   

  if (!isset($addcat)) {
  $addcat = isset($_POST['addcat']) ? $_POST['addcat'] : $_GET['addcat'];
  }

  if (!isset($edcatid)) {
  $edcatid = isset($_POST['edcatid']) ? $_POST['edcatid'] : $_GET['edcatid'];
  }

  if (!isset($edlink)) {
  $edlink = isset($_POST['edlink']) ? $_POST['edlink'] : $_GET['edlink'];
  }

  if (!isset($checklink)) {
  $checklink = isset($_POST['checklink']) ? $_POST['checklink'] : $_GET['checklink'];
  }

  if (!isset($checkAllLinks)) {
  $checkAllLinks = isset($_POST['checkAllLinks']) ? $_POST['checkAllLinks'] : $_GET['checkAllLinks'];
  }

  if (!isset($app)) {
  $app = isset($_POST['app']) ? $_POST['app'] : $_GET['app'];
  }

  if (!isset($linkstatus)) {
  $linkstatus = isset($_POST['linkstatus']) ? $_POST['linkstatus'] : $_GET['linkstatus'];
  }

  if (!isset($delcat)) {
  $delcat = isset($_POST['delcat']) ? $_POST['delcat'] : $_GET['delcat'];
  }

  if (!isset($delink)) {
  $delink = isset($_POST['delink']) ? $_POST['delink'] : $_GET['delink'];
  }

  if (!isset($edThisLink)) {
  $edThisLink = isset($_POST['edThisLink']) ? $_POST['edThisLink'] : $_GET['edThisLink'];
  }

  if (!isset($url)) {
    $url = isset($_POST['url']) ? $_POST['url'] : $_GET['url'];
  }
  
  if (!isset($name)) {
    $name = isset($_POST['name']) ? $_POST['name'] : $_GET['name'];
  }
  
  if (!isset($categ)) {
    $categ = isset($_POST['categ']) ? $_POST['categ'] : $_GET['categ'];
  }
  
  if (!isset($appAll)) {
    $appAll = isset($_POST['appAll']) ? $_POST['appAll'] : $_GET['appAll'];
  }

  if (!isset($catnr)) {
  $catnr = isset($_POST['catnr']) ? $_POST['catnr'] : $_GET['catnr'];
  }

  if (!isset($catname)) {
  $catname = isset($_POST['catname']) ? $_POST['catname'] : $_GET['catname'];
  $catname = addslashes($catname);
  }

  if (!isset($catdesc)) {
  $catdesc = isset($_POST['catdesc']) ? $_POST['catdesc'] : $_GET['catdesc'];
  $catdesc = addslashes($catdesc);
  }

  if (!isset($oldcat)) {
  $oldcat = isset($_POST['oldcat']) ? $_POST['oldcat'] : $_GET['oldcat'];
  }

  if (!isset($descr)) {
  $descr = isset($_POST['descr']) ? $_POST['descr'] : $_GET['descr'];
  }
  
  if (!isset($country)) {
  $country = isset($_POST['country']) ? $_POST['country'] : $_GET['country'];
  }

  // $dblist contains all directory names starting with 'DB' (database directories)
  if ($dir = @opendir($gxlink_pluginfolder)) {
    while (($dbdirs = readdir($dir)) !== false)
      {
        if($dbdirs != ".." && $dbdirs != ".") {
          if (substr($dbdirs,0,2) == "DB") {
            $dblist[] = $dbdirs;
            }        
          }
      }
    closedir($dir);
  }

  $hjs = '<script type="text/javascript">
  <!--
  		function confirmSubmit(message)
  		{
  		var agree=confirm(message);
  		if (agree)
  			return true ;
  		else
  			return false ;
  		}
  // -->
  </script>'.'<script type="text/javascript" src="'.$gxlink_pluginfolder.'/titles.js"></script>'.$hjs;


  $o.=print_plugin_admin('on');
  if($admin<>'plugin_main'){$o.=plugin_admin_common($action,$admin,$gxlink_plugin);}
  if($admin=='')$o.="<h4>GXLink</h4>Link plugin ver ".$gxlink_version." by Gerd Xhonneux (<a href=\"http://xtc.xhonneux.com\" target=\"_blank\">http://xtc.xhonneux.com</a>)";
  
  if ($admin == 'plugin_main') {
  
    $o .= '<div id="GXLink">';
    // show all available databases
    asort($dblist);
    $o .= '<form action="'.$refbase.'" method="post" name="getDB">';
			$o .= '<table border="0" cellpadding="1" cellspacing="0" align="center">';
			$o .= "<tr>";
				$o .= '<td class="links">'.$plugin_tx['gxlink']['descChooseDB'].'</td>';
				$o .= '<td class="links">';
        $o .= '<select name="database" onChange="document.getDB.submit();">';
        while (list ($key, $val) = each ($dblist)) {
					if ($val == $database) {
						$o .= '<OPTION value="'.$val.'" selected>'.$val.'</OPTION>'.chr(13);;
					} else {
						$o .= '<OPTION value="'.$val.'">'.$val.'</OPTION>'.chr(13);;
					}
        }
        $o .= '</select>';
			
				$o .= "</td>";
			$o .= "</tr>";
			$o .= "</table>";
    $o .= '</form>';

    $db=new Database($database);

    $error[0]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error0']."</span>";
    $error[100]   = "<span class=\"check\">".$plugin_tx['gxlink']['error100']."</span>";
    $error[101]   = "<span class=\"check\">".$plugin_tx['gxlink']['error101']."</span>";
    $error[200]   = "<span class=\"good\">".$plugin_tx['gxlink']['error200']."</span>";
    $error[201]   = "<span class=\"check\">".$plugin_tx['gxlink']['error201']."</span>";
    $error[202]   = "<span class=\"check\">".$plugin_tx['gxlink']['error202']."</span>";
    $error[203]   = "<span class=\"check\">".$plugin_tx['gxlink']['error203']."</span>";
    $error[204]   = "<span class=\"check\">".$plugin_tx['gxlink']['error204']."</span>";
    $error[205]   = "<span class=\"check\">".$plugin_tx['gxlink']['error205']."</span>";
    $error[206]   = "<span class=\"check\">".$plugin_tx['gxlink']['error206']."</span>";
    $error[300]   = "<span class=\"check\">".$plugin_tx['gxlink']['error300']."</span>";
    $error[301]   = "<span class=\"check\">".$plugin_tx['gxlink']['error301']."</span>";
    $error[302]   = "<span class=\"check\">".$plugin_tx['gxlink']['error302']."</span>";
    $error[303]   = "<span class=\"check\">".$plugin_tx['gxlink']['error303']."</span>";
    $error[304]   = "<span class=\"check\">".$plugin_tx['gxlink']['error304']."</span>";
    $error[305]   = "<span class=\"check\">".$plugin_tx['gxlink']['error305']."</span>";
    $error[307]   = "<span class=\"check\">".$plugin_tx['gxlink']['error307']."</span>";
    $error[400]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error400']."</span>";
    $error[401]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error401']."</span>";
    $error[402]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error402']."</span>";
    $error[403]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error403']."</span>";
    $error[404]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error404']."</span>";
    $error[405]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error405']."</span>";
    $error[406]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error406']."</span>";
    $error[407]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error407']."</span>";
    $error[408]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error408']."</span>";
    $error[409]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error409']."</span>";
    $error[410]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error410']."</span>";
    $error[411]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error411']."</span>";
    $error[412]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error412']."</span>";
    $error[413]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error413']."</span>";
    $error[414]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error414']."</span>";
    $error[415]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error415']."</span>";
    $error[416]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error416']."</span>";
    $error[417]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error417']."</span>";
    $error[500]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error500']."</span>";
    $error[501]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error501']."</span>";
    $error[502]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error502']."</span>";
    $error[503]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error503']."</span>";
    $error[504]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error504']."</span>";
    $error[505]   = "<span class=\"bad\">".$plugin_tx['gxlink']['error505']."</span>";

		if ($addcat != '') {
			$db->executeQuery("INSERT INTO cats (name, descr) VALUES('".$catname."','".$catdesc."')");
		}
		
    if ($edcatid != '') {
			$db->executeQuery("UPDATE cats SET name='".$catname."', descr='".$catdesc."' WHERE nr='".$edcatid."'");
			$db->executeQuery("UPDATE links SET category='".$catname."' WHERE category='".$oldcat."'");
		}
		if ($app != '') {
			$db->executeQuery("UPDATE links SET approved='".$linkstatus."' WHERE nr='".$app."'");
		}
		if ($delcat != '') {
			$getCatName=$db->executeQuery("SELECT name FROM cats WHERE nr='".$delcat."'");
			
			while($getCatName->next()){ 
						list($name)=$getCatName->getCurrentValues();
						$db->executeQuery("DELETE FROM links WHERE category='".$name."'");		
			}
					
			$db->executeQuery("DELETE FROM cats WHERE nr='".$delcat."'");		
		}
		if ($delink != '') {
			$db->executeQuery("DELETE FROM links WHERE nr='".$delink."'");
		}
		if ($edThisLink != '') {
			if ($url != '' && $name != '' && $categ != '') {
				if ($descr == '') {
					$descr = "N/A";
				}
				//nr#name#url#descr#country#ip#approved#category
				$descr = str_replace('"','&quot;',$descr);	
				$name = str_replace('"','&quot;',$name);
        $db->executeQuery("UPDATE links SET name='".strip_tags($name)."', url='".strip_tags($url)."', descr='".trim(preg_replace("/\r\n/",' ',strip_tags($descr)))."', country='".strip_tags($country)."', category='".strip_tags($categ)."' WHERE nr='".$edThisLink."'");
			}
		}

		$o .= '<div class="tool">';
		$o .= $plugin_tx['gxlink']['backup_txt'].' <a href="'.$gxlink_pluginfolder.'/'.$database.'/links.txt" class="links">[links]</a> - <a href="'.$gxlink_pluginfolder.'/'.$database.'/cats.txt" class="links">[categories]</a> - <a href="'.$gxlink_pluginfolder.'/'.$database.'/countries.txt" class="links">[countries]</a>';
		$o .= '</div>';
		$o .= '<div class="links" align="center">';
		$o .= '<p>';

    if ($appAll == "yes") {
    		$db->executeQuery("UPDATE links SET approved=1 WHERE approved=0");
    }	
    $newLc = 0;
    $notApp=$db->executeQuery("SELECT * FROM links WHERE approved = 0");
    while($notApp->next()){ 
    		$newLc++;
    		if ($newLc == 1) {
    				$o .= '<table border="0" cellpadding="2" cellspacing="0" style="border: 1px solid #999;" bgcolor="FFFFFF">';
    				$o .= '<tr><td style="border-bottom: 1px solid #999;" align="center"><h2>'.$plugin_tx['gxlink']['notapprovedlinks'].'</h2> <a href="'.$refbase.'&appAll=yes" class="links">['.$plugin_tx['gxlink']['approveall'].']</a></td></tr>';
    		}	
    		list($nr,$name,$url,$descr,$country,$ip,$app,$cat)=$notApp->getCurrentValues();
    		$o .= "<tr bgcolor=\"EFEFEF\"><td class=\"links\">".$plugin_tx['gxlink']['descNameOfLink']." <a href=\"".$url."\" title=\"".$descr."\" target=\"_blank\">".$name."</a> | ".$cat."<br>URL:<a href=\"".$url."\" target=\"_blank\" class=\"links\">".$url."</a> | <a href=\"".$refbase."&catnr=".$catnr."&app=".$nr."&linkstatus=1\" class=\"links\">[".$plugin_tx['gxlink']['approvelink']."]</a> <a href=\"".$refbase."&catnr=".$catnr."&edlink=".$nr."\" class=\"links\">[".$plugin_tx['gxlink']['editlink']."]</a> <a href=\"".$refbase."&catnr=".$catnr."&delink=".$nr."\" onClick=\"return confirmSubmit('".$plugin_tx['gxlink']['confirm_dellink']."');\" class=\"links\">[".$plugin_tx['gxlink']['dellink']."]</a></td></tr>";
    			
    }
    if ($newLc > 0) {
    		$o .= "</table><p>";
    }

		$o .= "</div>";
				$o .= '<form action="'.$refbase.'" method="post" name="getCat">';
				$o .= '<table border="0" cellpadding="1" cellspacing="0" align="center">';
				$o .= "<tr>";
					$o .= '<td class="links">'.$plugin_tx['gxlink']['descChooseCat'].'</td>';
					$o .= '<td class="links">';
					$o .= '<select name="catnr" onChange="document.getCat.submit();">';
					$o .= '<option value=""> '.$plugin_tx['gxlink']['descChooseCat'];
					$cats=$db->executeQuery("SELECT * FROM cats ORDER BY name");
					while($cats->next()){ 
						list($nr,$name)=$cats->getCurrentValues();
						if ($name != '') {
								if ($catnr == $nr) {
									$o .= "<option value=\"".$nr."\" selected>".$name;
								} else {
									$o .= "<option value=\"".$nr."\">".$name;
								}
						}
					}
			    $o .= "</select>";
					$o .= "</td>";
				$o .= "</tr>";
				$o .= "</table>";
				$o .= "</form>";
		$o .= '<table border="0" align="center" cellspacing="20">';
		$o .= "<tr>";
		$o .= "<td>";
		if ($catnr != '') {
			$catinfo=$db->executeQuery("SELECT * FROM cats WHERE nr='".$catnr."';");
			
				while($catinfo->next()){ 
					
					list($nr,$name,$descr)=$catinfo->getCurrentValues();
					$linksincat=$db->executeQuery("SELECT * FROM links WHERE category='".$name."';");
					
					$name = str_replace('"','&quot;',$name);
					$o .= '<form action="'.$refbase.'" method="post" name="edCat">';
					$o .= '<input type="hidden" name="master" value="vap">';
					$o .= '<table border="0" cellpadding="1" cellspacing="0" align="center">';
					$o .= "<tr>";
						$o .= '<td class="links"><h3>'.$plugin_tx['gxlink']['changecat'].'</h3></td>';
					$o .= "</tr>";
					$o .= '<tr class="links">';
						$o .= '<td align="left">'.$plugin_tx['gxlink']['namecat'].'<br>';
						$o .= '<input type="text" name="catname" size="40" value="'.$name.'"></td>';
					$o .= "</tr>";
					$o .= "<tr>";
						$o .= '<td class="links" align="left" valign="top">'.$plugin_tx['gxlink']['desccat'].'<br>';
						$o .= '<textarea name="catdesc" rows="4" cols="30">'.$descr.'</textarea></td>';
					$o .= "</tr>";
					$o .= "<tr>";
						$o .= '<td class="links" align="left">';
							$o .= '<input type="submit" value=" '.$plugin_tx['gxlink']['changecat'].' "> <a href="'.$refbase.'&delcat='.$nr.'" onClick="return confirmSubmit(\''.$plugin_tx['gxlink']['confirm_delcat'].'\');">'.$plugin_tx['gxlink']['delcat'].'</a>';
						$o .= "</td>";
					$o .= "</tr>";
					$o .= "</table>";
					$o .= '<input type="hidden" name="edcatid" value="'.$nr.'">';
					$o .= '<input type="hidden" name="oldcat" value="'.$name.'">';
					$o .= "</form>";
				}	
		}
		$o .= "</td>";
		$o .= "<td>";
				$o .= '<form action="'.$refbase.'" method="post">';
				$o .= '<input type="hidden" name="master" value="vap">';
				$o .= '<table border="0" cellpadding="1" cellspacing="0" align="center">';
				$o .= '<tr>';
					$o .= '<td class="links"><h3>'.$plugin_tx['gxlink']['addcat'].'</h3></td>';
				$o .= '</tr>';
				$o .= '<tr class="links">';
					$o .= '<td align="left">'.$plugin_tx['gxlink']['namecat'].':<br>';
					$o .= '<input type="text" name="catname" size="40"></td>';
				$o .= '</tr>';
				$o .= '<tr>';
					$o .= '<td class="links" align="left" valign="top">'.$plugin_tx['gxlink']['desccat'].':<br>';
					$o .= '<textarea name="catdesc" rows="4" cols="30"></textarea></td>';
				$o .= '</tr>';
				$o .= '<tr>';
					$o .= '<td class="links" align="left">';
						$o .= '<input type="submit" value=" '.$plugin_tx['gxlink']['addcat'].' ">';	
					$o .= '</td>';
				$o .= '</tr>';
				$o .= '</table>';
				$o .= '<input type="hidden" name="addcat" value="1">';
				$o .= '</form>';
		$o .= '</td>';
		$o .= '</tr>';
		$o .= '</table>';
		if ($edlink != '') {
			$linkinfo=$db->executeQuery("SELECT * FROM links WHERE nr='".$edlink."';");
			while($linkinfo->next()){ 
				list($nr,$name,$url,$descr,$country,$ip,$app,$cat)=$linkinfo->getCurrentValues();
				$linkdescr = str_replace('"','&quot;',$descr);	
				$name = str_replace('"','&quot;',$name);
				
				$o .= '<form action="'.$refbase.'" method="post">';
				$o .= '<input type="hidden" name="master" value="vap">';
				$o .= '<table border="0" cellpadding="1" cellspacing="0" align="center">';
				$o .= '<tr>';
					$o .= '<td class="links"><h3>'.$plugin_tx['gxlink']['editlink'].'</h3></td>';
				$o .= '</tr>';
				$o .= '<tr class="links">';
					$o .= '<td align="left">'.$plugin_tx['gxlink']['descNameOfLink'].'<br>';
						$o .= '<input type="text" name="name" size="40" maxlength="40" value="'.$name.'">';
					$o .= '</td>';
				$o .= '</tr>';		
				$o .= '<tr class="links">';
					$o .= '<td align="left">'.$plugin_tx['gxlink']['descUrlOfLink'].'<br>';
					$o .= '<input type="text" name="url" size="40" value="'.$url.'"> <a href="'.$url.'" target="_blank">['.$plugin_tx['gxlink']['visitlink'].']</a></td>';
				$o .= '</tr>'; 
				$o .= '<tr>';
					$o .= '<td class="links" align="left">'.$plugin_tx['gxlink']['descChooseCountry'].'<br>';
						$o .= '<select name="country">';
							$countries=$db->executeQuery("SELECT * FROM countries ORDER BY iso");
							while($countries->next()){   
								list($nr, $iso, $pic, $desc_en)=$countries->getCurrentValues(); 
								if ($iso == $country) {
									$o .= '<OPTION value="'.$iso.'" selected>'.$iso.' ('.$desc_en.')'.'</OPTION>'.chr(13);
								} else {
									$o .= '<OPTION value="'.$iso.'">'.$iso.' ('.$desc_en.')'.'</OPTION>'.chr(13);
								}
							}
						$o .= '</select>';
					$o .= '</td>';
				$o .= '</tr>';		
				$o .= '<tr>';
					$o .= '<td class="links" align="left" valign="top">'.$plugin_tx['gxlink']['descLinkDesc'].'<br>';
							$o .= '<textarea name="descr" rows="4" cols="30">'.$linkdescr.'</textarea>';
					$o .= '</td>';
				$o .= '</tr>';
				$o .= '<tr>';
					$o .= '<td class="links" align="left">'.$plugin_tx['gxlink']['descChooseCat'].'<br>';
						$o .= '<select name="categ">';
						$cats=$db->executeQuery("SELECT * FROM cats ORDER BY name");	
						while($cats->next()){   
							list($nr,$name,$descr)=$cats->getCurrentValues(); 
							if ($name != '') {
									if ($cat == $name) {
										$selected = "selected";
									} else {
										$selected = "";
									}
									
									$o .= '<option value="'.$name.'" '.$selected.'>'.$name.chr(13);  
							}
						}
						$o .= '</select>';
						$o .= '<input type="hidden" name="edThisLink" value="'.$edlink.'">';
						$o .= '<input type="hidden" name="catnr" value="'.$catnr.'">';
						$o .= '<input type="submit" value=" '.$plugin_tx['gxlink']['savelink'].' ">';	
					$o .= '</td>';
				$o .= '</tr>';
				$o .= '</table>';
				$o .= '</form>';
			}
		}
		$o .= '<table border="0" cellpadding="2" cellspacing="0" width="728" align="center" style="border: 1px solid Navy;">';
		if ($catnr != '') {
			
			$catinfo=$db->executeQuery("SELECT * FROM cats WHERE nr='".$catnr."';");
			while($catinfo->next()){ 
				list($nr,$name,$descr)=$catinfo->getCurrentValues();	
				$links=$db->executeQuery("SELECT * FROM links WHERE category = \"".$name."\"");
			}
		
			$i=1;
			$row_count = 0; 
			$color1 = "#EEEEEE";
			$color2 = "#FFFFFF";
			while($links->next()){ 
				//nr#name#url#descr#country#ip#approved#category
				list($nr,$name,$url,$descr,$country,$ip,$app,$cat,$statuscode)=$links->getCurrentValues();
				$row_color = ($row_count % 2) ? $color1 : $color2;
			
				if ($app == 0) {
					$infW = $plugin_tx['gxlink']['approvelink'];
					$linkstatus = 1;
				} else {
					$infW = $plugin_tx['gxlink']['disapprovelink'];
					$linkstatus = 0;
				}
				
				if ($app == 0) 
				{ 
					$validated = "<span class=\"bad\">".$plugin_tx['gxlink']['notapproved']."&nbsp;&nbsp;&nbsp;&nbsp;<br>IP: ".$ip."</span>"; 
				} else {
					$validated = "<span class=\"good\">".$plugin_tx['gxlink']['approved']."&nbsp;&nbsp;&nbsp;&nbsp;<br>IP: ".$ip."</span>";
				}	
				
				if ($tempCat == '' || $tempCat != $cat){
						$o .= "<tr class=\"catDesc\"><td colspan=\"3\" class=\"catDesc\">".$cat." | <a class=\"checkButton\" href=\"".$refbase."&catnr=".$catnr."&checkAllLinks=".$catnr."\" onClick=\"return confirmSubmit('".$plugin_tx['gxlink']['confirm_checkbrokenlinks']."')\">".$plugin_tx['gxlink']['checkcatbrokenlinks']." &raquo</a></td><td align=\"right\">".$plugin_tx['gxlink']['statposted']."</td></tr>";
				}	
				$o .= "<tr class=\"links\" bgcolor=\"$row_color\">";
				if (($checklink != '') && ($url == $checklink)) {
						$code = URLHelper::getHTTPStatusCode($url);
						$o .= "<td class=\"links\" width=\"10\">".$i."</td><td class=\"links\" width=\"150\"><a href=\"".$url."\" title=\"".$descr."\" target=\"_blank\">".$name."</a><br><a href=\"".$refbase."&catnr=".$catnr."&edlink=".$nr."\">[".$plugin_tx['gxlink']['editlink']."]</a> <a href=\"".$refbase."&catnr=".$catnr."&checklink=".urlencode($url)."\">[".$plugin_tx['gxlink']['checklink']."]</a><br><a href=\"".$refbase."&catnr=".$catnr."&app=".$nr."&linkstatus=".$linkstatus."\">[".$infW."]</a> <a href=\"".$refbase."&catnr=".$catnr."&delink=".$nr."\" onClick=\"return confirmSubmit('".$plugin_tx['gxlink']['confirm_dellink']."');\">[".$plugin_tx['gxlink']['dellink']."]</a> </td>"."<td><a href=\"".$url."\" target=\"_blank\" class=\"links\">".$url."</a></td>"."<td align=\"right\">".$error[$code]."</td>";
						$db->executeQuery("UPDATE links SET statuscode='$code' WHERE url='$url'");
				}	else {
						if ($checkAllLinks == $catnr) {
								$code = URLHelper::getHTTPStatusCode($url);
								$o .= "<td class=\"links\" width=\"10\">".$i."</td><td class=\"links\" width=\"150\"><a href=\"".$url."\" title=\"".$descr."\" target=\"_blank\">".$name."</a><br><a href=\"".$refbase."&catnr=".$catnr."&edlink=".$nr."\">[".$plugin_tx['gxlink']['editlink']."]</a> <a href=\"".$refbase."&catnr=".$catnr."&checklink=".urlencode($url)."\">[".$plugin_tx['gxlink']['checklink']."]</a><br><a href=\"".$refbase."&catnr=".$catnr."&app=".$nr."&linkstatus=".$linkstatus."\">[".$infW."]</a> <a href=\"".$refbase."&catnr=".$catnr."&delink=".$nr."\" onClick=\"return confirmSubmit('".$plugin_tx['gxlink']['confirm_dellink']."');\">[".$plugin_tx['gxlink']['dellink']."]</a> </td>"."<td><a href=\"".$url."\" target=\"_blank\" class=\"links\">".$url."</a></td>"."<td align=\"right\">".$error[$code]."</td>";
								$db->executeQuery("UPDATE links SET statuscode='$code' WHERE url='$url'");
								flush();
						} else {	
								if ($statuscode	== 0 || $statuscode >= 400) {
										$dispError = $error[$statuscode];
								} else {
										$dispError = '';
								}
								$o .= "<td class=\"links\" width=\"10\">".$i."</td><td class=\"links\" width=\"150\"><a href=\"".$url."\" title=\"".$descr."\" target=\"_blank\">".$name."</a><br><a href=\"".$refbase."&catnr=".$catnr."&edlink=".$nr."\">[".$plugin_tx['gxlink']['editlink']."]</a> <a href=\"".$refbase."&catnr=".$catnr."&checklink=".urlencode($url)."\">[".$plugin_tx['gxlink']['checklink']."]</a><br><a href=\"".$refbase."&catnr=".$catnr."&app=".$nr."&linkstatus=".$linkstatus."\">[".$infW."]</a> <a href=\"".$refbase."&catnr=".$catnr."&delink=".$nr."\" onClick=\"return confirmSubmit('".$plugin_tx['gxlink']['confirm_dellink']."');\">[".$plugin_tx['gxlink']['dellink']."]</a> </td>"."<td><a href=\"".$url."\" target=\"_blank\" class=\"links\">".$url."</a></td>"."<td align=\"right\">".$dispError."<br>".$validated."</td>";
						}
				}			
				
				$o .= "</tr>";
				$o .= "<tr class=\"links\" bgcolor=\"".$row_color."\">";
				$o .= "<td></td><td class=\"links\" width=\"350\" colspan=\"3\">".$country."</td>";
				$o .= "</tr>";
				$tempCat = $cat;
				$checkThese[] = $url;
				$i++;
				$row_count++; 			
			}
		} 
		$o .= "</table>";
    $o .= '</div>';
    //$o .= plugin_admin_common($action,$admin,$plugin);
  }

}
?>
