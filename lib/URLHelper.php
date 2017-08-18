<?php
////////////////////////////////////////////////////////////////
/*

This class give you access to the http header information and 
provides some help for retrieving and parsing urls.


For the lastest version go to:
http://www.phpclasses.org/browse.html/package/803.html


FUNCTIONS:
    function isURLAvailable($url)
    function isValidURLFormat($url, $strict=false)
    function addHTTPtoURL($url)
    function getHTTPStatusCode($url)
    function getRealURL ($url, $simple = true, $method = "HEAD")
    function getHTTPHeader($url)
    function getMD5FromURL($url, $estFilesize=500000)
    function getTitle($url)

    function _openHTTPConnection($url, $method = "HEAD")

////////////////////////////////////////////////////////////////

For HTTP Status Codes see:
    http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html

////////////////////////////////////////////////////////////////

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

////////////////////////////////////////////////////////////////
*/
/**
* Class for accessing URLs and the HTTP data
*
*
* @author	    Lennart Groetzbach <lennartg_at_web_dot_de>
* @copyright	Lennart Groetzbach <lennartg_at_web_dot_de> - distributed under the LGPL
* @version 	    1.2 - 2002/12/30
* <p>
* History / Changes<br>
* <table border="1" width="100%" cellpadding="3"><tr>
*       <th>Version</th>    <th>Reported By</th>        <th>File / Function Changed</th>    <th>Date of Change</th> <th>Commment</th>
* </tr><tr>
*       <td>1.2</td>        <td>-</td>        <td>-</td>            <td>2002/12/30</td>     <td>now all functions are static, so you methods can be called 'URLHelper::name()'</td>
* </tr><tr>
*       <td>1.2</td>        <td>-</td>        <td>getTitle()</td>            <td>2002/12/30</td>     <td>new method to extract the page title</td>
* </tr><tr>
*       <td>1.1</td>        <td>N.Narayanan</td>        <td>getHTTPHeader()</td>            <td>2002/10/14</td>     <td>removed warning for variable</td>
* </tr><tr>
*       <td>1.1</td>        <td>M.Oelke</td>            <td>_openHTTPConnection()</td>      <td>2002/10/14</td>     <td>added possibility to access all ports</td>
* </tr></table>
*
* @access       public
*/
class URLHelper {

////////////////////////////////////////////////////////////////
/**
* Adds "http://" to url if needed
* 
* @access   public
* @param	String      $url    the url
*
* @return   String      the updated url
*/
function addHTTPtoURL($url) {
	if ($url != "") {
		$pos = strpos(strtoupper($url), "HTTP");
		if ($pos === false) {
			$url = "http://" . $url ;
		}
	}
	return $url;
}

////////////////////////////////////////////////////////////////
/**
* Checks if url is in valid format
* 
* @access   public
* @param	String      $url    the url
* @param	boolean     $strict    stricter checking?
*
* @return   boolean     is it valid?
*/

function isValidURLFormat($url, $strict=false) {
    $str="";
	if ($strict == true) {
		$str .= "/^http:\\/\\/([A-Za-z-\\.]*)\\//";
	} else {
		$str .= "/^http:\\/\\/([A-Za-z-\\.]*)/";
	}
	return @preg_match($str, $url);
}

////////////////////////////////////////////////////////////////
/**
* Checks if url is in valid format
* 
* @access   public
* @param	String      $url    the url
*
* @return   boolean     does it exist?
*/

function isURLAvailable($url) {
    $fd = @fopen($url, "rb");
    @fclose($fd);
    return ($fd != "");
}

////////////////////////////////////////////////////////////////
/**
* Checks if url is in valid format
* 
* @access   private
* @param	String      $url    the url
* @param	String      $method what type of HTTP method
*
* @return   integer         file pointegerer
*/

function _openHTTPConnection($url, $method = "HEAD") {
		set_time_limit(30);
    $info = parse_url($url); 
    if (!array_key_exists('port', $info)) { 
        $info["port"] = 80;
    }
    $path = ($info["path"]) ? $info["path"] : "/"; 
    if (@$info["query"]) {
        $path = $path . "?" . $info["query"]; 
    }
    // open connection
    $conn = fsockopen(@$info["host"], $info["port"], $errno, $errstr,5); 
    if ($conn) { 
    	$host = $info["host"];
        // send request
    	fwrite ($conn, "$method $path HTTP/1.0\r\nHost: $host\r\n\r\n"); 
    }
    return $conn;
}

////////////////////////////////////////////////////////////////
/**
* Returns the HTTP status code
* 
* @access   public
* @param	String      $url    the url
*
* @return   integer         the status code
* @link     http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html  Status Code Definition
*/


function getHTTPStatusCode($url) {
	
    $count = 0;
    $conn = URLHelper::_openHTTPConnection($url);
    if ($conn) {
    	$buffer = fgets($conn, 1028);
        // extract code
        $temp = explode(" ", $buffer, 3);
        $count = $temp[1];
        fclose($conn);
    }
    else {
        return $count;
    }
    return $count;
}

////////////////////////////////////////////////////////////////
/**
* Returns the "real" URL, if the status code 302 or 405 were sent
* 
* @access   public
* @param	String      $url    the url
* @param	boolean     $simple try several times to get url?
* @param	String      $method what type of HTTP method?
*
* @return   String      the url
*/

function getRealURL ($url, $simple = true, $method = "HEAD") {
    $count = 0;
    $conn = URLHelper::_openHTTPConnection($url, $method);
    if ($conn) {
    	$buffer = fgets($conn, 1028);
        // extract code
        $temp = explode(" ", $buffer);
        $count = $temp[1];
        // is there a redirect?
        switch ($count) {
            case '302':
                do {
                    // find new location
                   	$buffer = fgets($conn, 4028);
                    if (eregi("LOCATION:", $buffer)) {
                        $tmp = substr(strstr($buffer, ":"), 1);
                        // is it relative?
                        if (strpos($tmp, '/') == 1)
                            $tmp = $url . substr($tmp, 2);
                        $url = URLHelper::getRealURL(trim($tmp), $simple);
                        break;
                    }
                } while ($buffer);
                break;
            case '405':
                if (!$simple) {
                    do {
                       	$buffer = fgets($conn, 4028);
                        if (eregi("ALLOW:", $buffer)) {
                            $tmp = trim(substr(strstr($buffer, ":"), 1));
                            $allowed = explode(",", $tmp);
                            $url = URLHelper::getRealURL($url, $simple, $allowed[0]);
                            break;
                        }
                    } while ($buffer);
                }
                break;
        }
        fclose($conn);
        return $url;
    }
    else {
        echo "getRealURL(): Cannot open connection!<br>\n";
        return -1;
    }
}

////////////////////////////////////////////////////////////////
/**
* Returns the complete header
* 
* @access   public
* @param	String      $url    the url
*
* @return   String      the header
*/

function getHTTPHeader($url) {
    $header = '';
    $conn = URLHelper::_openHTTPConnection($url);
    if ($conn) {
        do {
            $buffer = fgets($conn, 1028);
            $header .= $buffer;
        } while ($buffer);
        fclose($conn);
    }
    return $header;
}

////////////////////////////////////////////////////////////////
/**
* Returns the MD5 hash code of an url
* 
* @access   public
* @param	String      $url    the url
* @param	integer         $estFilesize    the approximate file size
*
* @return   String      the hash code
*/

function getMD5FromURL($url, $estFilesize=500000){
    $fd = @fopen($url, "rb");
    if ($fd){
        $fileContents = fread($fd, $estFilesize);
        return md5($fileContents);
        @fclose($fd);
    } else {
        return false;
    }
}

////////////////////////////////////////////////////////////////
/**
* Returns the page title
* 
* @access   public
* @param	String      $url    the url
*
* @return   mixed      title string or false;
*/
function getTitle($url) {
    $fp = @fopen ($url, 'r'); 
    if ($fp) {
        $page = '';
        while (!feof($fp)) { 
            $page .= fgets ($fp, 1024); 
            if (stristr($page, '<\title>')) { 
                 fclose();
                 break; 
            } 
        } 
        if (eregi("<title>(.*)</title>", $page, $out)) { 
            return $out[1]; 
        } 
    return false; 
    }
}

////////////////////////////////////////////////////////////////
}
////////////////////////////////////////////////////////////////
?>
