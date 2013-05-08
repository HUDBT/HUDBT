<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Stijn de Reede <sjr@gmx.co.uk>                               |
// +----------------------------------------------------------------------+
//
// $Id: Images.php,v 1.8 2007/07/02 17:44:47 cweiske Exp $
//

/**
* @package  HTML_BBCodeParser
* @author   Stijn de Reede  <sjr@gmx.co.uk>
*/
require_once 'HTML/BBCodeParser/Filter.php';

class HTML_BBCodeParser_Filter_Images extends HTML_BBCodeParser_Filter
{

    /**
    * An array of tags parsed by the engine
    *
    * @access   private
    * @var      array
    */
    var $_definedTags = array(
        'img' => array(
            /* 'htmlopen'  => 'img class="scalable"', */
            'htmlclose' => '',
            'allowed'   => 'none',
            'attributes'=> array(
                'img'   => 'src=%2$s%1$s%2$s',
                'w'     => 'width=%2$s%1$d%2$s',
                'h'     => 'height=%2$s%1$d%2$s',
                'alt'   => 'alt=%2$s%1$s%2$s',
		'full' => 'full=%2$s%1$s%2$s'
				 ),
	    'htmlopencallback' => array('HTML_BBCodeParser_Filter_Images', 'imgCallback')
        )
    );

    static function imgCallback() {
      $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
      $h = $options['imgMaxH'];
      $w = $options['imgMaxW'];
      $style = '';
      if ($h != 0) {
	$style .= 'max-height: '.$h.'px;';
      }
      if ($w != 0) {
	$style .= 'max-width: '.$w.'px;';
      }
      if ($style) {
	$style = ' style="' . $style . '"';
      }

      return 'img class="scalable"' . $style;
    }

    /**
    * Executes statements before the actual array building starts
    *
    * This method should be overwritten in a filter if you want to do
    * something before the parsing process starts. This can be useful to
    * allow certain short alternative tags which then can be converted into
    * proper tags with preg_replace() calls.
    * The main class walks through all the filters and and calls this
    * method if it exists. The filters should modify their private $_text
    * variable.
    *
    * @return   none
    * @access   private
    * @see      $_text
    * @author   Stijn de Reede  <sjr@gmx.co.uk>
    */
    function _preparse() {
        $options = PEAR::getStaticProperty('HTML_BBCodeParser','_options');
        $o  = $options['open'];
        $c  = $options['close'];
        $oe = $options['open_esc'];
        $ce = $options['close_esc'];
        $this->_preparsed = preg_replace(
			"!".$oe."img(\s?.*)".$ce."(.*)".$oe."/img".$ce."!Ui",
			$o."img=\"\$2\"\$1".$c.$o."/img".$c,
			$this->_text);

	global $possibleUrls;
	$regpre = '!'.$oe.'img=(["\']?)https?://(?:' . implode('|', $possibleUrls) . ')/!i';
	$this->_preparsed = preg_replace($regpre, $o.'img=$1/', $this->_preparsed);
    }
}