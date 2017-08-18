<?php

#############################################################################
#                                                                           #
# GXLink XH Version 1.5                                                     #
# Copyright 2007-2013 Gerd Xhonneux                                         #
# http://xtc.xhonneux.com                                                   #
#                                                                           #
# (X)HTML validation by Gert Ebersbach http://www.ge-webdesign.de           #
#                                                                           #
# This is a modified and enhanced version of original "LinkMix":            #
# Lars Eriksson                                                             #
#	Copyright 2004 LarsE                                                      #
#	http://www.siteferret.com                                                 #
#                                                                           #
# This program is free software; you can redistribute it and/or modify      #
# it under the terms of the GNU General Public License as published by      #
# the Free Software Foundation; either version 2 of the License, or         #
# (at your option) any later version.                                       #
#                                                                           #
# This program is distributed in the hope that it will be useful,           #
# but WITHOUT ANY WARRANTY; without even the implied warranty of            #
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             #
# GNU General Public License for more details.                              #
#                                                                           #
# You should have received a copy of the GNU General Public License         #
# along with this program; if not, write to the Free Software               #
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA   #
#                                                                           #
#############################################################################

//error_reporting(E_ALL);

$gxlink_plugin            = basename(dirname(__FILE__),'/');
$gxlink_pluginfolder      = $pth['folder']['plugins'].$gxlink_plugin;         # folder where the plugin resides
$API_HOME_DIR         = $gxlink_pluginfolder.'/txt-db-api/';              # folder where the txt-db-api resides
$DB_DIR               = $gxlink_pluginfolder.'/';                         # folder where the db resides

include_once($API_HOME_DIR.'txt-db-api.php');

function GXLink($catname = null,$database = null) {

global $su, $sn, $sl, $plugin_cf, $plugin_tx, $pth, $plugin, $hjs;

$gxlink_version = "XH 1.5";
$gxlink_plugin            = basename(dirname(__FILE__),'/');
$gxlink_pluginfolder      = $pth['folder']['plugins'].$gxlink_plugin;         # folder where the plugin resides
$refbase              = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$su;   # base for the references
$badwordsfile         = $gxlink_pluginfolder."/config/".$plugin_cf['gxlink']['BadWordsFile'];   # file that contains the bad words 

// Some variables

if (!isset($catname)) {
  $catname = isset($_POST['catname']) ? $_POST['catname'] : (isset($_GET['catname']) ? $_GET['catname'] : null);
} else {
  if ( empty($catname) ) {
    $catname = isset($_POST['catname']) ? $_POST['catname'] : (isset($_GET['catname']) ? $_GET['catname'] : null);
  } else {
    $plugin_cf['gxlink']['showCats'] = 0;  
  }
}

if (!isset($q)) {
  $q = isset($_POST['q']) ? $_POST['q'] : (isset($_GET['q']) ? $_GET['q'] : null);
}

if (!isset($database)) {
  $database = 'DBlink';
}

if (!isset($url)) {
  $url = isset($_POST['url']) ? $_POST['url'] : (isset($_GET['url']) ? $_GET['url'] : null);
}

if (!isset($linkname)) {
  $linkname = isset($_POST['linkname']) ? $_POST['linkname'] : (isset($_GET['linkname']) ? $_GET['linkname'] : null);
}

if (!isset($categ)) {
  $categ = isset($_POST['categ']) ? $_POST['categ'] : (isset($_GET['categ']) ? $_GET['categ'] : null);
}

if (!isset($linkdescr)) {
  $linkdescr = isset($_POST['linkdescr']) ? $_POST['linkdescr'] : (isset($_GET['linkdescr']) ? $_GET['linkdescr'] : null);
}

if (!isset($country)) {
  $country = isset($_POST['country']) ? $_POST['country'] : (isset($_GET['country']) ? $_GET['country'] : null);
}

// Variables who serves fight against spam 
if (isset($_POST['spamdate']) && is_numeric($_POST['spamdate'])) {
   $posted = intval($_POST['spamdate']);
   $sendtime = (time() - $posted);
}   

$tmp = "<!-- Code start - inserted by GXLink ".$gxlink_version." from http://xtc.xhonneux.com -->\r\n";
$tmp .= '<div id="GXLink">';


$db=new Database($database);

$cats=$db->executeQuery('SELECT * FROM cats ORDER BY name');
$userDetails = $_SERVER['REMOTE_ADDR'];
if ($plugin_cf['gxlink']['showApprovedLinksOnly'] == 1) {
	if ($plugin_cf['gxlink']['doNotShowBroken'] == 1) {
			$totallinks=$db->executeQuery('SELECT * FROM links WHERE approved = 1 AND statuscode > 0 AND statuscode < 400');
	} else {
			$totallinks=$db->executeQuery('SELECT * FROM links WHERE approved = 1');
	}
} else {
	if ($plugin_cf['gxlink']['doNotShowBroken'] == 1) {
			$totallinks=$db->executeQuery('SELECT * FROM links WHERE statuscode > 0 AND statuscode < 400 OR statuscode = ""');
	} else {
			$totallinks=$db->executeQuery('SELECT * FROM links');
	}
}

$hjs = '<script type="text/javascript">
<!--
	function checkUrl(theUrl)
	{
	var is_protocol_ok=theUrl.indexOf(\'http://\');
	var is_protocol_ftp_ok=theUrl.indexOf(\'ftp://\');
	var is_protocol_https_ok=theUrl.indexOf(\'https://\');
	var is_dot_ok=theUrl.indexOf(\'.\');
	if ((is_protocol_ok==-1) && (is_protocol_ftp_ok==-1) && (is_protocol_https_ok==-1) || (is_dot_ok==-1))
	 { 
	  alert(\''.$plugin_tx['gxlink']['descErrorNoHttp'].'\');
	  return false;
	 }
	else
	 return true;
	}
// -->
</script>'.'<script type="text/javascript" src="'.$gxlink_pluginfolder.'/titles.js"></script>'.$hjs;

//$tmp .= '<div align="center">';
$tmp .= '<table cellpadding="0" cellspacing="10" style="width: '.$plugin_cf['gxlink']['pageWidth'].'; border: 0;">';
$tmp .= '<tr>';

if ($plugin_cf['gxlink']['showCats'] == 4) {
  $tmp .= '<td style="vertical-align:top;">';
  	if ($catname == '' && $q == '') { 
  		} 
  		while($cats->next()){   
  		  list($nr,$name,$descr)=$cats->getCurrentValues(); 
  		  $tmp .= '<a href="'.$refbase.'&amp;catname='.urlencode($name).'" title="'.$descr.'" class="menu">'.$name.'</a>'.tag('br');
      }
  
  $tmp .= '<a href="'.$refbase.'&amp;catname=all" class="menu">'.$plugin_tx['gxlink']['descAllLinks'].'</a>';
  if ($plugin_cf['gxlink']['showNewLinks'] == 1) {  
    $tmp .= '<p>'.tag('img src="'.$gxlink_pluginfolder.'/images/new_link.gif" style="width: 10px; height: 10px; border: 0; vertical-align:middle;" alt="'.$plugin_tx['gxlink']['descNew'].'" title= "'.$plugin_tx['gxlink']['descNew'].'"').' = '.$plugin_tx['gxlink']['descNew'];
  }  
  $tmp .= '</td>';
}

$tmp .= '<td style="vertical-align:top;">';

///***
//MOD by Tata www.cmsimple.sk 18.04.2008 
//for special case in some east europe languages
/* 
if ($plugin_cf['gxlink']['showTitle'] == 1) {
  $tmp .= '<div align="center">';
  $tmp .=	'<h1>'.$plugin_tx['gxlink']['descLinksInDb1'].$totallinks->getRowCount().' '.$plugin_tx['gxlink']['descLinksInDb2'].'</h1>';
  $tmp .=	'</div>';
} 
///***
*/
if ($plugin_cf['gxlink']['showTitle'] == 1) {
  $tmp .= '<div class="title">';
  $tmp .=	'<h1>';
  $tmp .= $plugin_tx['gxlink']['descLinksInDb1'].$totallinks->getRowCount().' '; 
///*** added switcher ***
switch($totallinks->getRowCount()){
case 1:
   $tmp .= $plugin_tx['gxlink']['descLinksInDb2a'];
   break;
case 2:
   $tmp .= $plugin_tx['gxlink']['descLinksInDb2b'];
   break;
case 3:
   $tmp .= $plugin_tx['gxlink']['descLinksInDb2b'];
   break;
case 4:
   $tmp .= $plugin_tx['gxlink']['descLinksInDb2b'];
   break;
default:
   $tmp .= $plugin_tx['gxlink']['descLinksInDb2c'];
}
//*** end of switcher***      
  $tmp .= '</h1>';
  $tmp .=	'</div>';
}  
///*** end of mod

if ($plugin_cf['gxlink']['showCats'] == 1 || $plugin_cf['gxlink']['showCats'] == 3) {
  $tmp .=	'<div class="cats">';
	if ($catname == '' && $q == '') { 
	} 
	$li = 1;	
	while($cats->next()){   
		list($nr,$name,$descr)=$cats->getCurrentValues(); 
  	$tmp .= '<a href="'.$refbase.'&amp;catname='.urlencode($name).'" title="'.$descr.'" class="menu">'.$name.'</a> | ';
		if ($li == $plugin_cf['gxlink']['numCatRow']) {
			$tmp .= tag('br');
			$li = 0;
		}
		$li++;
	}
	$tmp .= '<a href="'.$refbase.'&amp;catname=all" class="menu">'.$plugin_tx['gxlink']['descAllLinks'].'</a>';
  if ($plugin_cf['gxlink']['showNewLinks'] == 1) {  
    $tmp .= '<p>'.tag('img src="'.$gxlink_pluginfolder.'/images/new_link.gif" style="width: 10px; height: 10px; border: 0;vertical-align:middle;" alt="'.$plugin_tx['gxlink']['descNew'].'" title= "'.$plugin_tx['gxlink']['descNew'].'"').' = '.$plugin_tx['gxlink']['descNew'].'</p>';
  }  
  $tmp .=	'</div>';	
}


if ($plugin_cf['gxlink']['showSearch'] == 1) {
  $tmp .=	'<div class="search">';
  $tmp .=	'<form action="'.$refbase.'" method="post">';
  $tmp .=	tag('input type="text" size="20" name="q"').tag('input type="submit" value="'.$plugin_tx['gxlink']['descSearch'].'"');
  $tmp .=	'</form>';
  $tmp .=	'</div>';
}

$tmp .= '<p>&nbsp;</p>';
$tmp .=	'<table border="0" cellpadding="2" cellspacing="0" width="'.$plugin_cf['gxlink']['pageWidth'].'" class="linkTable" style="text-aglin:center">';

// Check if the time for filling out the add form is greater then the configured value in $plugin_cf['gxlink']['minFillOutTime'] (to fight against spam robots)
if (isset($sendtime) && $sendtime > $plugin_cf['gxlink']['minFillOutTime'] ) {
  // add link
  if ($url != '' && $linkname != '' && $categ != '') {
  	if ($linkdescr == '') {
  		$linkdescr = $descNotGiven;
  	}

    // Bad word filter
    $linkdescr = filterBadWords($linkdescr,$badwordsfile,$plugin_cf['gxlink']['BadWordreplaceChar'],$plugin_cf['gxlink']['BadWordshowLetters'],$plugin_cf['gxlink']['BadWordRating']); 
  
  	//check if link is on page already
  	$linkcheck=$db->executeQuery('SELECT * FROM links WHERE url="'.$url.'"');
  	$linkcheck=$db->executeQuery('SELECT * FROM links WHERE url="'.$url.'"');
  	$lc = 0;
  	while($linkcheck->next()){ 
  		list($nr,$name,$url,$descr,$countryiso,$ip,$app,$cat,$statuscode)=$linkcheck->getCurrentValues();
  		$lc++;
  	}
  	
  	if ($lc > 0) {
  		
  		//HI $tmp .= '<script>alert(\''.$plugin_tx['gxlink']['descOnPageAlready'].'\');</script>';
  		$tmp .= '<p style="text-align:center;font-weight:bold;">'.$plugin_tx['gxlink']['descOnPageAlready'];
  		$tmp .= '</p>';
      			
  	} else {
  		
  		$db->executeQuery('INSERT INTO links (name, url, descr, country, ip, approved, category) VALUES("'.strip_tags($linkname).'","'.strip_tags($url).'","'.trim(preg_replace('/\r\n/'," ",nl2br(strip_tags(addslashes($linkdescr))))).'","'.strip_tags($country).'","'.$userDetails.'","0","'.strip_tags($categ).'")');
                  //Write a Message when a new link was added successfully or already exists
                  $tmp .= '<p class="linkadded">'.$plugin_tx['gxlink']['linkadded'].tag('br').strip_tags($url);
                  
                  if ($plugin_cf['gxlink']['showApprovedLinksOnly'] == 1) {
      	           $tmp .= tag('br').$plugin_tx['gxlink']['link2bapproved'];
                  }
                  
                  $tmp .= '';
                  // Send a mail to admin
                  if ($plugin_cf['gxlink']['notifyMail'] == 1) {
  	            mail($plugin_cf['gxlink']['mailTo'], $plugin_tx['gxlink']['descNew'], strip_tags($url).chr(13).strip_tags($linkname).chr(13).strip_tags($linkdescr).chr(13).strip_tags($categ));			
  		}
  	
  	}
  	    
  } 
}
// Checking last links
if ($plugin_cf['gxlink']['showApprovedLinksOnly'] == 1) {
		$lastlinks=$db->executeQuery('SELECT * FROM links WHERE approved = 1 ORDER BY nr DESC LIMIT '.$plugin_cf['gxlink']['iNewLinks'].'');
} else {
    $lastlinks=$db->executeQuery('SELECT * FROM links ORDER BY nr DESC LIMIT '.$plugin_cf['gxlink']['iNewLinks'].'');    
}

$numrows = $lastlinks->getRowCount();
$prevnr = '##';

$row_count = 0; 

if ($plugin_cf['gxlink']['showNewLinks'] == 1) {
    if (trim($catname) == '' && trim($q) == '' && $plugin_cf['gxlink']['iNewLinks'] != 0) {
				$tmp .= '<tr class="catDesc"><td colspan="4">'.$plugin_tx['gxlink']['descNewLinks'].'</td></tr>';
		}
				
		$newLCount = 0;
		
		while($lastlinks->next()){ 

			list($nr,$name,$url,$descr,$countryiso,$ip,$app,$cat,$statuscode)=$lastlinks->getCurrentValues();
      
      // Detect country
      $acountry=$db->executeQuery('SELECT * FROM countries WHERE iso="'.$countryiso.'"');
      while($acountry->next()) {
      list($nr, $iso, $pic, $desc_en)=$acountry->getCurrentValues();
        // Decide if country will be shown as picture or text
        if ($plugin_cf['gxlink']['showCountryPic'] == 1) {
          $country = tag('img src="'.$gxlink_pluginfolder.'/images/countries/'.$pic.'" title="'.$desc_en.'" alt="'.$iso.'" style="vertical-align: middle;"');
        } else {
          $country = $iso;
        }  
      }	
						
			$row_color = ($row_count % 2) ? $plugin_cf['gxlink']['color1'] : $plugin_cf['gxlink']['color2'];
			if (trim($catname) == '' && trim($q) == '') { 
					if ($app == 0) 
					{ 
						if ($plugin_cf['gxlink']['showIP'] == 1) {
						  $validated = $ip; 
						} else {
							$validated = '';
						}
						
					} else {
						$validated = tag('img src="'.$gxlink_pluginfolder.'/images/check.gif" style="width: 10px; height: 10px; border: 0;" title="'.$plugin_tx['gxlink']['approved'].'" alt="'.$plugin_tx['gxlink']['approved'].'"');
					}		

					$descr = str_replace('"','&quot;',$descr);
					$descr = str_replace('\'','&acute;',$descr);
					if ($plugin_cf['gxlink']['doNotShowBroken'] == 1) {
							if ($statuscode	!= 0 && $statuscode < 400) {
									$newLCount++;	
									$tmp .= '<tr bgcolor="'.$row_color.'">';
									$tmp .= '<tr style="background-color:'.$row_color.'">';
									if ($plugin_cf['gxlink']['showDescr'] == 1) {
                    $tmp .= '<td class="count" width="16">'.$newLCount.'</td><td class="links" ><a href="'.$url.'" target="_blank">'.$name.'</a> '.$iso.'</td>'.'<td colspan="2" class="linkcat">'.$cat.' '.$validated.'</td>';
   									$tmp .= '</tr>';
  									$tmp .= '<tr class="links" style="background-color:'.$row_color.'">';
  									$tmp .= '<td></td><td class="links" colspan="3">'.$descr.'</td>';
                  } else {
                    $tmp .= '<td class="count" width="16">'.$newLCount.'</td><td class="links" ><a href="'.$url.'" title="'.$descr.'" target="_blank">'.$name.'</a> ('.$country.')</td>'.'<td colspan="2" class="linkcat">'.$cat.' '.$validated.'</td>';
                  }
                  $tmp .= '</tr>';
							}
					} else {
							$newLCount++;	
							$tmp .= '<tr style="background-color:'.$row_color.'">';
							if ($plugin_cf['gxlink']['showDescr'] == 1) {
                $tmp .= '<td class="count" width="16">'.$newLCount.'</td><td class="links" ><a href="'.$url.'" target="_blank">'.$name.'</a> ('.$country.')</td>'.'<td colspan="2" class="linkcat">'.$cat.' '.$validated.'</td>';
								$tmp .= '</tr>';
								$tmp .= '<tr class="links" style="background-color:'.$row_color.'">';
								$tmp .= '<td></td><td class="links" colspan="3">'.$descr.'</td>';
              } else {
                $tmp .= '<td class="count" width="16">'.$newLCount.'</td><td class="links" ><a href="'.$url.'" title="'.$descr.'" target="_blank">'.$name.'</a> ('.$country.')</td>'.'<td colspan="2" class="linkcat">'.$cat.' '.$validated.'</td>';
              }
              $tmp .= '</tr>';   
					}
			}
			$prevnr = $prevnr.$nr.'##';
			$row_count++;
		}
}

if ($catname == '') {
	if ($q != '') {
			$q = str_replace('*','%',$q);
			$do_search = parse_search($q);
		
			$incL = count($do_search['include']);
			$excL = count($do_search['exclude']);
			
			$dbStart = 'SELECT * FROM links WHERE ';
			
			// Make the SQL
			for($i = 0; $i < $incL; $i++) { 
			   	
			   	if ($plugin_cf['gxlink']['showApprovedLinksOnly'] == 1) {
					   	$dbAdd = $dbAdd.'
					   	name LIKE "%'.$do_search['include'][$i].'%" AND approved = 1 
					   	OR descr LIKE "%'.$do_search['include'][$i].'%" AND approved = 1 
					   	OR url LIKE "%'.$do_search['include'][$i].'%" AND approved = 1 
					   	OR country LIKE "%'.$do_search['include'][$i].'%" AND approved = 1 
					  	';
					  	if ($i != ($incL - 1)) {
					  		$dbAdd = $dbAdd.'OR ';
					  	}
					}	else {
					   	$dbAdd = $dbAdd.'
					   	name LIKE "%'.$do_search['include'][$i].'%" 
					   	OR descr LIKE "%'.$do_search['include'][$i].'%" 
					   	OR url LIKE "%'.$do_search['include'][$i].'%" 
					   	OR country LIKE "%'.$do_search['include'][$i].'%" 
					  	';
					  	if ($i != ($incL - 1)) {
					  		$dbAdd = $dbAdd.'OR ';
					  	}		
					}
			} 					
			$dbEnd = 'ORDER BY category, name';

			$SQL = $dbStart.$dbAdd.$dbEnd;
			$links=$db->executeQuery($SQL);
			
	} else {
			if ($plugin_cf['gxlink']['showApprovedLinksOnly'] == 1) {
					$links=$db->executeQuery('SELECT * FROM links WHERE approved = 1 ORDER BY category, name');
			} else {
					$links=$db->executeQuery('SELECT * FROM links ORDER BY category, name');
			}
	}
} else {
	if ($catname != 'all') {
		if ($plugin_cf['gxlink']['showApprovedLinksOnly'] == 1) {
				$links=$db->executeQuery('SELECT * FROM links WHERE category="'.$catname.'" AND approved = 1 ORDER BY name');
		} else {
				$links=$db->executeQuery('SELECT * FROM links WHERE category="'.$catname.'" ORDER BY name');
		}
	} else {
		if ($plugin_cf['gxlink']['showApprovedLinksOnly'] == 1) {
				$links=$db->executeQuery('SELECT * FROM links WHERE approved = 1 ORDER BY category, name');
		} else {
				$links=$db->executeQuery('SELECT * FROM links ORDER BY category, name');
		}
	}
}

$i=1;
$row_count = 0; 

if ($plugin_cf['gxlink']['showAllLinksDefault'] == 1) {
		$catname = 'all';
}

if ($catname != '' || $q != '') {
	while($links->next()){ 
		list($nr,$name,$url,$descr,$countryiso,$ip,$app,$cat,$statuscode)=$links->getCurrentValues();

      // Detect country
      $acountry=$db->executeQuery('SELECT * FROM countries WHERE iso="'.$countryiso.'"');
      while($acountry->next()) {
      list($nr, $iso, $pic, $desc_en)=$acountry->getCurrentValues();
        // Decide if country will be shown as picture or text
        if ($plugin_cf['gxlink']['showCountryPic'] == 1) {
          $country = tag('img src="'.$gxlink_pluginfolder.'/images/countries/'.$pic.'" title="'.$desc_en.'" alt="'.$iso.'" style="vertical-align: middle;"');
        } else {
          $country = $iso;
        }  
      }	

		$row_color = ($row_count % 2) ? $plugin_cf['gxlink']['color1'] : $plugin_cf['gxlink']['color2'];

		$new = strpos($prevnr, '##'.$nr.'##');				
		
				$descr = str_replace('"','&quot;',$descr);
				$descr = str_replace('\'','&acute;',$descr);
				
				if ($app == 0) 
				{ 
					if ($plugin_cf['gxlink']['showIP'] == 1) {
						$validated = $ip; 
					} else {
						$validated = '';
					}
				} else {
					$validated = tag('img src="'.$gxlink_pluginfolder.'/images/check.gif" style="width: 10px; height: 10px; border: 0;" title="'.$plugin_tx['gxlink']['approved'].'" alt="'.$plugin_tx['gxlink']['approved'].'"');					
				}	
				if ($plugin_cf['gxlink']['doNotShowBroken'] == 1) {	
						if ($statuscode	!= 0 && $statuscode < 400) {
								if ($tempCat == '' || $tempCat != $cat){
										$tmp .= '<tr class="catDesc"><td colspan="3" class="catDesc">'.$cat.'</td></tr>';
								}	
								$tmp .= '<tr class="links" style="background-color:'.$row_color.'">';
		
								if ($new === false) {
									if ($plugin_cf['gxlink']['showDescr'] == 1) {
			  						$tmp .= '<td class="links" width="16">'.$i.'</td><td class="links" ><a href="'.$url.'" target="_blank">'.$name.'</a> ('.$country.')</td>'.'<td style="text-align:right;">'.$validated.'</td>';                    
   									$tmp .= '</tr>';
  									$tmp .= '<tr class="links" style="background-color:'.$row_color.'">';
  									$tmp .= '<td></td><td class="links" colspan="3">'.$descr.'</td>';
                  } else {
         						$tmp .= '<td class="links" width="16">'.$i.'</td><td class="links" ><a href="'.$url.'" title="'.$descr.'" target="_blank">'.$name.'</a> ('.$country.')</td>'.'<td style="text-align:right;">'.$validated.'</td>';
                  }
								} else {
									if ($plugin_cf['gxlink']['showDescr'] == 1) {
									  $tmp .= '<td class="links" width="16">'.$i.'</td><td class="links" >';
                    if ($plugin_cf['gxlink']['showNewLinks'] == 1) {
                      $tmp .= tag('img src="'.$gxlink_pluginfolder.'/images/new_link.gif" style="width: 10px; height: 10px; border: 0; vertical-align:middle;" alt="'.$plugin_tx['gxlink']['descNew'].'"');
                    }
                    $tmp .= '<a href="'.$url.'" target="_blank">'.$name.'</a> ('.$country.')</td>'.'<td style="text-align:right">'.$validated.'</td>';
   									$tmp .= '</tr>';
  									$tmp .= '<tr class="links" style="background-color:'.$row_color.'">';
  									$tmp .= '<td></td><td class="links" colspan="3">'.$descr.'</td>';
                  } else {
									  $tmp .= '<td class="links" width="16">'.$i.'</td><td class="links" >';
                    if ($plugin_cf['gxlink']['showNewLinks'] == 1) {
                      $tmp .= tag('img src="'.$gxlink_pluginfolder.'/images/new_link.gif" style="width: 10px; height: 10px; border: 0; vertical-align:middle;" alt="'.$plugin_tx['gxlink']['descNew'].'"');
                    }
                    $tmp .= '<a href="'.$url.'" title="'.$descr.'" target="_blank">'.$name.'</a> ('.$country.')</td>'.'<td style="text-align:right">'.$validated.'</td>';
                  }
								}
                
                $tmp .= '</tr>';
								$tempCat = $cat;
								
								$i++;
								$row_count++; 							
						}
				} else {
						if ($tempCat == '' || $tempCat != $cat){
								$tmp .= '<tr class="catDesc"><td colspan="3" class="catDesc">'.$cat.'</td></tr>';
						}	
						$tmp .= '<tr class="links" style="background-color:'.$row_color.'">';

						if ($new === false) {
							if ($plugin_cf['gxlink']['showDescr'] == 1) {
	  						$tmp .= '<td class="links" width="16">'.$i.'</td><td class="links" ><a href="'.$url.'" target="_blank">'.$name.'</a> ('.$country.')</td>'.'<td style="text-align:right">'.$validated.'</td>';
								$tmp .= '</tr>';
								$tmp .= '<tr class="links" style="background-color:'.$row_color.'">';
								$tmp .= '<td></td><td class="links" colspan="3">'.$descr.'</td>';
              } else {
     						$tmp .= '<td class="links" width="16">'.$i.'</td><td class="links" ><a href="'.$url.'" title="'.$descr.'" target="_blank">'.$name.'</a> ('.$country.')</td>'.'<td style="text-align:right">'.$validated.'</td>';
              }
						} else {
							if ($plugin_cf['gxlink']['showDescr'] == 1) {
							  $tmp .= '<td class="links" width="16">'.$i.'</td><td class="links" >';
                if ($plugin_cf['gxlink']['showNewLinks'] == 1) {
                      $tmp .= tag('img src="'.$gxlink_pluginfolder.'/images/new_link.gif" style="width: 10px; height: 10px; border: 0; vertical-align:middle;" alt="'.$plugin_tx['gxlink']['descNew'].'"');
                } 
                $tmp .= '<a href="'.$url.'" target="_blank">'.$name.'</a> ('.$country.')</td>'.'<td style="text-align:right">'.$validated.'</td>';
								$tmp .= '</tr>';
								$tmp .= '<tr class="links" style="background-color:'.$row_color.'">';
								$tmp .= '<td></td><td class="links" colspan="3">'.$descr.'</td>';
              } else {
							  $tmp .= '<td class="links" width="16">'.$i.'</td><td class="links" >';
                if ($plugin_cf['gxlink']['showNewLinks'] == 1) {
                      $tmp .= tag('img src="'.$gxlink_pluginfolder.'/images/new_link.gif" style="width: 10px; height: 10px; border: 0; vertical-align:middle;" alt="'.$plugin_tx['gxlink']['descNew'].'"');
                } 
                $tmp .= '<a href="'.$url.'" title="'.$descr.'" target="_blank">'.$name.'</a> ('.$country.')</td>'.'<td style="text-align:right">'.$validated.'</td>';
              }
						}
						$tmp .= '</tr>';

						$tempCat = $cat;
						
						$i++;
						$row_count++; 								
				}
		}
}

$tmp .=	'</table>';
$tmp .=	'<p></p>';
if ($plugin_cf['gxlink']['showCats'] == 2 || $plugin_cf['gxlink']['showCats'] == 3) {
	$tmp .=	'<div style="text-align:center;">';
	$cats=$db->executeQuery('SELECT * FROM cats ORDER BY name');
	$li = 1;	
	while($cats->next()){   
		list($nr,$name,$descr)=$cats->getCurrentValues(); 
    $tmp .= '<a href="'.$refbase.'&amp;catname='.urlencode($name).'" title="'.$descr.'" class="menu">'.$name.'</a>'.' | ';		
    if ($li == $plugin_cf['gxlink']['numCatRow']) {
			$tmp .= tag('br');
			$li = 0;
		}
		$li++;
	}
	$tmp .= '<a href="'.$refbase.'&amp;catname=all" class="menu">'.$plugin_tx['gxlink']['descAllLinks'].'</a>';
	$tmp .= '</div>';	
	}

$tmp .= '<a name="bottom"> </a>';
if ($plugin_cf['gxlink']['allowLinkPosting'] == 1) {
	$tmp .= '<form name="test" action="'.$refbase.'" method="post" onsubmit="return checkUrl(document.test.url.value);">';
	$tmp .= '<table border="0" cellpadding="1" cellspacing="0" style="text-align:center">';
	$tmp .= '<tr>';
	$tmp .= '<td class="links"><h3>'.$plugin_tx['gxlink']['descAddYourOwn'].'</h3></td>';
	$tmp .= '</tr>';
	$tmp .= '<tr class="links">';
	$tmp .= '<td style="text-align:left;">'.$plugin_tx['gxlink']['descForm'].'</td>';
	$tmp .= '</tr>';			
	$tmp .= '<tr class="links">';
	$tmp .= '<td style="text-align:left;">'.$plugin_tx['gxlink']['descNameOfLink'].tag('br');
	$tmp .= tag('input type="text" name="linkname" size="40" maxlength="40"').'</td>';
	$tmp .= '</tr>';	
	$tmp .= '<tr class="links">';
	$tmp .= '<td style="text-align:left:">'.$plugin_tx['gxlink']['descUrlOfLink'].tag('br');
	$tmp .= tag('input type="text" name="url" size="40"').'</td>';
	$tmp .= '</tr>';
	$tmp .= '<tr>';
	$tmp .= '<td class="links" style="text-align:left;">'.$plugin_tx['gxlink']['descChooseCountry'].tag('br');
	$tmp .= '<select name="country">';
	$countries=$db->executeQuery('SELECT * FROM countries ORDER BY iso');
	while($countries->next()){   
		list($nr,$iso,$pic,$desc_en)=$countries->getCurrentValues() + array(null, null, null, null);
		if ($iso != '') {
			if ($plugin_cf['gxlink']['defaultCountry'] == $iso) {
				$tmp .= '<option value="'.$iso.'" selected>'.$iso.' ('.$desc_en.')'.'</option>'.chr(13);
			} else {
				$tmp .= '<option value="'.$iso.'">'.$iso.' ('.$desc_en.')'.'</option>'.chr(13);
			}
		}
	}
	$tmp .= '</select>';
	$tmp .= '</td>';
	$tmp .= '</tr>';		
	$tmp .= '<tr>';
	$tmp .= '<td class="links" style="text-align:left;" valign="top">'.$plugin_tx['gxlink']['descLinkDesc'].tag('br');
	$tmp .= '<textarea name="linkdescr" rows="4" cols="30"></textarea></td>';
	$tmp .= '</tr>';
	$tmp .= '<tr>';
	$tmp .= '<td class="links" style="text-align:left;">'.$plugin_tx['gxlink']['descChooseCat'].tag('br');
	$tmp .= '<select name="categ">';
	$cats=$db->executeQuery('SELECT * FROM cats ORDER BY name');
	while($cats->next()){   
		list($nr,$name,$descr)=$cats->getCurrentValues(); 
		if ($name != '') {
			$tmp .= '<option value="'.$name.'">'.$name.chr(13).'</option>';  
		}
	}
	$tmp .= '</select>';
	$tmp .= tag('input type="submit" value="'.$plugin_tx['gxlink']['descButtonAddYourOwn'].'"');
  // The following fields are only for fighting against spam entries
  $tmp .= tag('input name="spamdate" type="hidden" value="'.time().'"');		
	$tmp .= '</td>';
	$tmp .= '</tr>';
	$tmp .= '</table>';
	$tmp .= '</form>';
}
$tmp .= '</td>';
if ($plugin_cf['gxlink']['showCats'] == 5) {
	$cats=$db->executeQuery('SELECT * FROM cats ORDER BY name');
	$tmp .= '<td valign="top">';
	if ($catname == '' && $q == '') { 
	} 
	while($cats->next()){   
		list($nr,$name,$descr)=$cats->getCurrentValues(); 
	  $tmp .= '<a href="'.$refbase.'&amp;catname='.urlencode($name).'" title="'.$descr.'" class="menu">'.$name.'</a>'.tag('br');	
	}
	$tmp .= '<a href="'.$refbase.'&amp;catname=all" class="menu">'.$plugin_tx['gxlink']['descAllLinks'].'</a>';
  if ($plugin_cf['gxlink']['showNewLinks'] == 1) {  
    $tmp .= '<p>'.tag('img src="'.$gxlink_pluginfolder.'/images/new_link.gif" style="width: 10px; height: 10px; border: 0;vertical-align:middle;" alt="'.$plugin_tx['gxlink']['descNew'].'" title="'.$plugin_tx['gxlink']['descNew'].'"');
  }  
  $tmp .= '</td>';
}
$tmp .= '</tr>';
$tmp .= '</table>';
		
unset($catname);
unset($q); 

$tmp .= '</div>';
$tmp .= "<!-- Code end - inserted by GXLink ".$gxlink_version." from http://xtc.xhonneux.com -->\r\n"; 

// Back to CMSimple
return($tmp);
  	
}

function parse_search ($input) {
   	$c = count_chars($input, 0);
   	$c = ($c[34]/ 2);
  	if(!strstr($c, '.') && $c != '0'){
		   $input = explode('"', $input);
		   $include = array();
		   $exclude = array();
		   $switches = array('+', '-');
		     for($i=0; $i < count($input); $i++){
		       	$inst = $input[$i];
			       if($inst == '' && ($i == '0' || $i == (count($input) - 1)) ){ $inst = '+'; }
				         if(in_array($inst, $switches)){
				           	$lswitch = $inst;
				         } else {
					           if ($inst != ''){
					           if ($lswitch == '-'){
					               $exclude[] = $inst;
					           } elseif ($lswitch == '+'){
					               $include[] = $inst;
					           } else {
					               $include[] = $inst;
					           }
				         }
				           unset($lswitch);
				     }
	     	} // end loop
	   		$output = array('include' => $include, 'exclude' => $exclude);
	  }else{
	   		$output = array('include' => explode(' ', trim($input)));
	  }
	 	return $output;
} // end function				

function filterBadWords($str,$badWordsFile,$replaceChar="*",$showLetters=0,$rating=0){
    // check for the badwords file
    if(!is_file($badWordsFile)){
        echo 'ERROR: Could not find badwords file "'.$badWordsFile.'"';
        exit;
    }
    else{
            //open or file as resource $handle
        $handle =  fopen($badWordsFile,"r");
    }
        // while we're not at eof (End Of File) do this
    while(!feof($handle)){
        $badword = trim(fgets($handle)); // get the word from the file
        $word_rating = substr($badword,0,1); // get the badword "rating", word is in format 5myBadWord
        $badword = substr($badword,1);  //take the rating off of the badword so we have just the badword left
        
            //if my word rating is greater than my exceptable level bleep it out
        if($word_rating>$rating){
                // look for and take out our bad word
           $str = str_replace($badword, substr($badword,0,$showLetters).sprintf("%'".$replaceChar.(strlen($badword)-$showLetters)."s", NULL), $str);
        }
    }
    //return our formatted string
    return $str;
}// end function

?>
