<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Richard Heyes                                               |
   +----------------------------------------------------------------------+
   $Id$
*/

/**
* Based on PEAR::Net_UserAgent_Detect
*/

class browser
{
    /**
    * Array that stores all of the flags for the vendor and version of
    * the different browsers.  The flags key values are show in the array.
    * @var array $browser
    */
    var $browser = array('ns', 'ns2', 'ns3', 'ns4', 'ns4up', 'nav', 'ns6', 'ns6up', 'gecko', 'ie', 'ie3', 'ie4', 'ie4up', 'ie5', 'ie5_5', 'ie5up', 'ie6', 'ie6up', 'opera', 'opera2', 'opera3', 'opera4', 'opera5', 'opera5up', 'aol', 'aol3', 'aol4', 'aol5', 'aol6', 'aol7', 'webtv', 'aoltv', 'tvnavigator', 'hotjava', 'hotjava3', 'hotjava3up');

    /**
    * The leading identifier is the very first term in the user agent string, which is
    * used to identify clients which are not Mosaic-based browsers.
    * @var string $leadingIdentifier
    */
    var $leadingIdentifier = '';

    /**
    * The full version of the client as supplied by the very first numbers in the user agent
    * @var float $version
    */
    var $version = 0;

    /**
    * The major part of the client version, which is the integer value of the version.
    * @var integer $majorVersion
    */
    var $majorVersion = 0;

    /**
    * The minor part of the client version, which is the decimal parts of the version
    * @var float $subVersion
    */
    var $subVersion = 0;

    /**
    * Constructor
    *
    * @param
    */
    function browser($in_useragent = null)
    {
        $this->detect($in_useragent);
    }

    /**
    * Detect the user agent and prepare flags, features and quirks based on what is found
    *
    * This is the core of the Net_UserAgent_Detect class.  It moves its way through the user agent
    * string setting up the flags based on the vendors and versions of the browsers, determining
    * the OS and setting up the features and quirks owned by each of the relevant clients.
    *
    * @access public
    * @param  string (optional) user agent override
    * @return void
    */
    function detect($in_userAgent = null)
    {
        // Detemine what user agent we are using
        if (is_null($in_userAgent)) {
            if (isset($GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'])) {
                $this->userAgent = $GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'];
            } else {
                $this->userAgent = '';
            }
        } else {
            $this->userAgent = $in_userAgent;
        }

        // Get the lowercase version for case-insensitive searching
        $agt = strtolower($this->userAgent);

        // Initialize the flag arrays
        $brwsr =& $this->browser;
        $brwsr =  array_flip($brwsr);

        // Get the type and version of the client
        preg_match(";^([[:alpha:]]+)[ /\(]*[[:alpha:]]*([\d]*)\.([\d\.]*);", $agt, $matches);
        @list(, $this->leadingIdentifier, $this->majorVersion, $this->subVersion) = $matches;
        if (empty($this->leadingIdentifier)) {
            $this->leadingIdentifier = 'Unknown';
        }

        $this->version = $this->majorVersion . '.' . $this->subVersion;

        $brwsr['konq']    = (strpos($agt, 'konqueror') !== false);
        $brwsr['text']    = (strpos($agt, 'links') !== false) || (strpos($agt, 'lynx') !== false) || (strpos($agt, 'w3m') !== false);
        $brwsr['ns']      = (strpos($agt, 'mozilla') !== false) && !(strpos($agt, 'spoofer') !== false) && !(strpos($agt, 'compatible') !== false) && !(strpos($agt, 'hotjava') !== false) && !(strpos($agt, 'opera') !== false) && !(strpos($agt, 'webtv') !== false) ? 1 : 0;
        $brwsr['ns2']     = $brwsr['ns'] && $this->majorVersion == 2;
        $brwsr['ns3']     = $brwsr['ns'] && $this->majorVersion == 3;
        $brwsr['ns4']     = $brwsr['ns'] && $this->majorVersion == 4;
        $brwsr['ns4up']   = $brwsr['ns'] && $this->majorVersion >= 4;
        // determine if this is a Netscape Navigator
        $brwsr['nav']     = $brwsr['ns'] && ((strpos($agt, ';nav') !== false) || ((strpos($agt, '; nav') !== false)));
        $brwsr['ns6']     = !$brwsr['konq'] && $brwsr['ns'] && $this->majorVersion == 5;
        $brwsr['ns6up']   = $brwsr['ns6'] && $this->majorVersion >= 5;
        $brwsr['gecko']   = (strpos($agt, 'gecko') !== false);
        $brwsr['ie']      = (strpos($agt, 'msie') !== false) && !(strpos($agt, 'opera') !== false);
        $brwsr['ie3']     = $brwsr['ie'] && $this->majorVersion < 4;
        $brwsr['ie4']     = $brwsr['ie'] && $this->majorVersion == 4 && (strpos($agt, 'msie 4') !== false);
        $brwsr['ie4up']   = $brwsr['ie'] && $this->majorVersion >= 4;
        $brwsr['ie5']     = $brwsr['ie'] && $this->majorVersion == 4 && (strpos($agt, 'msie 5.0') !== false);
        $brwsr['ie5_5']   = $brwsr['ie'] && $this->majorVersion == 4 && (strpos($agt, 'msie 5.5') !== false);
        $brwsr['ie5up']   = $brwsr['ie'] && !$brwsr['ie3'] && !$brwsr['ie4'];
        $brwsr['ie5_5up'] = $brwsr['ie'] && !$brwsr['ie3'] && !$brwsr['ie4'] && !$brwsr['ie5'];
        $brwsr['ie6']     = $brwsr['ie'] && $this->majorVersion == 4 && (strpos($agt, 'msie 6.') !== false);
        $brwsr['ie6up']   = $brwsr['ie'] && !$brwsr['ie3'] && !$brwsr['ie4'] && !$brwsr['ie5'] && !$brwsr['ie5_5'];
        $brwsr['opera']   = (strpos($agt, 'opera') !== false);
        $brwsr['opera2']  = (strpos($agt, 'opera 2') !== false) || (strpos($agt, 'opera/2') !== false);
        $brwsr['opera3']  = (strpos($agt, 'opera 3') !== false) || (strpos($agt, 'opera/3') !== false);
        $brwsr['opera4']  = (strpos($agt, 'opera 4') !== false) || (strpos($agt, 'opera/4') !== false);
        $brwsr['opera5']  = (strpos($agt, 'opera 5') !== false) || (strpos($agt, 'opera/5') !== false);
        $brwsr['opera5up'] = $brwsr['opera'] && !$brwsr['opera2'] && !$brwsr['opera3'] && !$brwsr['opera4'];
        
        $brwsr['aol']   = (strpos($agt, 'aol') !== false);
        $brwsr['aol3']  = $brwsr['aol'] && $brwsr['ie3'];
        $brwsr['aol4']  = $brwsr['aol'] && $brwsr['ie4'];
        $brwsr['aol5']  = (strpos($agt, 'aol 5') !== false);
        $brwsr['aol6']  = (strpos($agt, 'aol 6') !== false);
        $brwsr['aol7']  = (strpos($agt, 'aol 7') !== false);
        $brwsr['webtv'] = (strpos($agt, 'webtv') !== false); 
        $brwsr['aoltv'] = $brwsr['tvnavigator'] = (strpos($agt, 'navio') !== false) || (strpos($agt, 'navio_aoltv') !== false); 
        $brwsr['hotjava'] = (strpos($agt, 'hotjava') !== false);
        $brwsr['hotjava3'] = $brwsr['hotjava'] && $this->majorVersion == 3;
        $brwsr['hotjava3up'] = $brwsr['hotjava'] && $this->majorVersion >= 3;

        /**
        * Setup easy access to the above variables
        */
        foreach ($brwsr as $key => $value) {
            $this->{'is_' . $key} = $value;
        }
    }
}
?>
