<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Mod Quizlet Import renderer.
 * @package   mod_quizletimport
 * @copyright 2014 Justin Hunt (poodllsupport@gmail.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_quizletimport_renderer extends plugin_renderer_base {	
    
    
    function show_popup_page($fullurl, $linktext){
        $ret ="";
        $jsfullurl = addslashes_js($fullurl->out());
        $options =Array();
        $width=800;
        $height=650;
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $attributes =array();
        $attributes['onclick'] = "window.open('$jsfullurl', '', '$wh'); return false;";
        //$extra = "onclick=\"window.open('$jsfullurl', '', '$wh'); return false;\"";

        $ret .= html_writer::start_div('urlworkaround');  
        $ret .= html_writer::link('#', $linktext, $attributes);
        //print_string('clicktoopen', 'url', "<a href=\"$fullurl\" $extra>$fullurl</a>");
        $ret .= html_writer::end_div();
        return $ret;
    }
    
    
    
    
    
    
    
    
    
    
	
	function display_error($qmessage) {
		 echo $qmessage;
	}//end of func

	function display_auth_link($authlink){
		return html_writer::link($authlink, get_string('quizletlogin', 'block_quizletquiz'));
	}

        function echo_quizlet_search_form($form){
		echo $this->output->heading(get_string('selectset','block_quizletquiz'), 3, 'main');
		echo $this->output->box_start('generalbox');
		$form->display();
		echo $this->output->box_end();
	}

	function echo_question_export_form($form, $exporttype, $message){
                if($message){
                    echo $this->output->heading($message, 3, 'badmessage'); 
                }
                switch($exporttype){
                    case 'qq':
                        // echo get_string('exporttofile', 'block_quizletquiz');
                         echo $this->output->heading(get_string('exportqqfile', 'block_quizletquiz'), 2, 'main');
                        break;
                    
                    case 'qq_direct':
                        // echo get_string('exporttofile', 'block_quizletquiz');
                        echo $this->output->heading(get_string('exportqqdirect', 'block_quizletquiz'), 2, 'main');
                        break;

                    case 'dd':
                        // echo get_string('exporttoddrop', 'block_quizletquiz');
                         echo $this->output->heading(get_string('exportddfile', 'block_quizletquiz'), 2, 'main');
                         break;
                    
                    case 'dd_direct':
                        // echo get_string('exporttoddrop', 'block_quizletquiz');
                        echo $this->output->heading(get_string('exportdddirect', 'block_quizletquiz'), 2, 'main');
                        break;
                        
                }
                //echo $this->output->heading(get_string('exporttofileheader', 'block_quizletquiz'), 3, 'main');
                echo $this->output->box_start('generalbox');
		$form->display();
		echo $this->output->box_end();
	}
	/*
	function echo_ddrop_export_form($form){

		echo $this->output->heading(get_string('exporttoddropheader', 'block_quizletquiz'), 3, 'main');
		echo get_string('exporttoddrop', 'block_quizletquiz');
		echo $this->output->box_start('generalbox');
		$form->display();
		echo $this->output->box_end();
	}
         */
        
        
        function display_preview_iframe($iframename){
            //add our preview iframe box
            //set up js
            //$iframename = "quizletimport_sampleset_flashcards";
            $opts = array();
            $opts['iframename'] = $iframename;
            $opts['width'] = 550;
            $opts['height'] = 350;
            
            $jsoptions = array($opts);
            $this->page->requires->js_init_call('M.block_quizletquiz.iframehelper.init', $jsoptions, false);
            //output the iframe
            $ret = $this->output->box_start('generalbox');
           // $ret.= "<iframe id='$iframename' name='$iframename' src=\"\" height=\"350\" width=\"550\" style=\"border:0;\"></iframe>";
            $iframe_atts = array();
            $iframe_atts['id']=$iframename;
            $iframe_atts['name']=$iframename;
            $iframe_atts['src']='';
            $iframe_atts['height']=1;
            $iframe_atts['width']=1;
            $iframe_atts['class']='block_quizletquiz_iframepreview';
            $iframe = html_writer::tag('iframe', '', $iframe_atts);
            $ret .= $iframe;
            $ret .= $this->output->box_end();
            return $ret;
        }
       
	
}
