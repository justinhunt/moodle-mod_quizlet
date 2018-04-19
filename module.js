// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JavaScript library for the quizlet module.
 *
 * @package    mod
 * @subpackage quizlet
 * @copyright  2014 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.mod_quizlet = M.mod_quizlet || {};

M.mod_quizlet.selectionhelper = {
    Y: null,
    qidbox: null,
    qnamebox: null,
    
    init: function(Y, opts) {
    	M.mod_quizlet.selectionhelper.Y = Y;
        M.mod_quizlet.selectionhelper.qidbox = Y.one('#' + opts['qidbox']);
        M.mod_quizlet.selectionhelper.qnamebox = Y.one('#' + opts['qnamebox']);
    },
    update: function(idvalue,namevalue){
        M.mod_quizlet.selectionhelper.qidbox.setAttribute('value',idvalue);
        M.mod_quizlet.selectionhelper.qnamebox.setAttribute('value',namevalue);
    }
};

M.mod_quizlet.selectformhelper = {
	IF: null,
	SB: null,
	
    /**
     * @param Y the YUI object
     * @param iframeref, the timer starting time, in seconds.
     * @param selectboxref, is this a quiz preview?
     */
    init: function(Y,iframeref, selectboxref) {
    	// console.log('quizlet:start:' + start +':countdown:' + showcountdown + ':showcompletion:' + showcompletion);
        M.mod_quizlet.selectformhelper.IF = Y.one('#' + iframeref);
        M.mod_quizlet.selectformhelper.SB = Y.one('#' + selectboxref);
   
    },
    getselectid: function(){
         
        var quizletset = M.mod_quizlet.selectformhelper.SB.get('value');
    	if(quizletset){
    		quizletset = quizletset.split('-')[0];
                //console.log ('qid:' + quizletset);
                return quizletset;
    	}else{
            return '';
        }
        
    },
    getselectname: function(){
        var quizletset = M.mod_quizlet.selectformhelper.SB.get('value');
    	if(quizletset){
    		quizletsetname = quizletset.split('-')[1];
                //console.log ('qname:' + quizletsetname);
                return quizletsetname;
    	}else{
            return '';
        }
        
    },
    justclose: function(){
        window.close();
        return false;
    },
    closeandupdate: function(){
         //console.log ('gothere:');
         var qid = this.getselectid();
         var qname = this.getselectname(); 
            try {
                window.opener.M.mod_quizlet.selectionhelper.update(qid,qname);
            }
            catch (err) {console.log(err);}
            window.close();
            return false;
    },
    updateiframe: function(){
    	var quizletset = M.mod_quizlet.selectformhelper.SB.get('value');
    	if(quizletset){
    		quizletset = quizletset.split('-')[0];
    	}
    	var newsrc = 'https://quizlet.com/' + quizletset + '/flashcards/embedv2';
    	M.mod_quizlet.selectformhelper.IF.setAttribute('src',newsrc);
    }

}; 

// Code for updating the countdown timer that is used on timed quizzes.
M.mod_quizlet.timer = {
    // YUI object.
    Y: null,

    // Timestamp at which time runs out, according to the student's computer's clock.
    endtime: 0,

    // This records the id of the timeout that updates the clock periodically,
    // so we can cancel.
    timeoutid: null,

    /**
     * @param Y the YUI object
     * @param start, the timer starting time, in seconds.
     * @param preview, is this a quiz preview?
     */
    init: function(Y, start,showcountdown, cmid, showcompletion, completed) {
    	// console.log('quizlet:start:' + start +':countdown:' + showcountdown + ':showcompletion:' + showcompletion);
        M.mod_quizlet.timer.Y = Y;
        M.mod_quizlet.timer.endtime = M.pageloadstarttime.getTime() + start*1000;
        M.mod_quizlet.timer.cmid = cmid;
        M.mod_quizlet.timer.showcompletion = showcompletion;
        M.mod_quizlet.timer.showcountdown = showcountdown;
        if(start>0 && !completed){
        	if(showcountdown){
                Y.one('#quizlet-timer').setStyle('display', 'block');
            }
            
            M.mod_quizlet.timer.update();
        	//console.log('quizlet:counting' + start + ":" + M.mod_quizlet.timer.cmid + ":" + $completed);
        }else if(completed){
          if(showcompletion){
          	Y.one('#quizlet-completed').setStyle('display', 'block');
          }
          //Y.one('#quizlet-timer').setStyle('display', 'none');
          //console.log('quizlet:completed');
        }
        //Y.one('#quizlet-timer').setStyle('display', 'block');
    },

    /**
     * Stop the timer, if it is running.
     */
    stop: function(e) {
        if (M.mod_quizlet.timer.timeoutid) {
            clearTimeout(M.mod_quizlet.timer.timeoutid);
        }
    },

    /**
     * Function to convert a number between 0 and 99 to a two-digit string.
     */
    two_digit: function(num) {
        if (num < 10) {
            return '0' + num;
        } else {
            return num;
        }
    },

    // Define a function to handle the AJAX response.
    complete: function(id,o,args) {
    	var id = id; // Transaction ID.
        var data = o.responseText; // Response data.
        console.log(data);
        var Y = M.mod_quizlet.timer.Y;
        Y.one('#quizlet-timer').setStyle('display', 'none');
        if(M.mod_quizlet.timer.showcompletion){
        	Y.one('#quizlet-completed').setStyle('display', 'block');
        }

    },

    // Function to update the clock with the current time left, and submit the quiz if necessary.
    update: function() {
        var Y = M.mod_quizlet.timer.Y;
        var secondsleft = Math.floor((M.mod_quizlet.timer.endtime - new Date().getTime())/1000);

        // If time has expired, set the hidden form field that says time has expired and submit
        if (secondsleft < 0) {
            M.mod_quizlet.timer.stop(null);
           	var uri  = 'ajaxcomplete.php?id=' +  M.mod_quizlet.timer.cmid;
           	Y.on('io:complete', M.mod_quizlet.timer.complete, Y,null);
           	//console.log('goingin');
           	Y.io(uri);
            return;
        }

        // If time has nearly expired, change the colour.
        if (secondsleft < 100) {
            Y.one('#quizlet-timer').removeClass('timeleft' + (secondsleft + 2))
                    .removeClass('timeleft' + (secondsleft + 1))
                    .addClass('timeleft' + secondsleft);
        }

        // Update the time display.
        var hours = Math.floor(secondsleft/3600);
        secondsleft -= hours*3600;
        var minutes = Math.floor(secondsleft/60);
        secondsleft -= minutes*60;
        var seconds = secondsleft;
        Y.one('#quizlet-time-left').setContent(hours + ':' +
                M.mod_quizlet.timer.two_digit(minutes) + ':' +
                M.mod_quizlet.timer.two_digit(seconds));
        

        // Arrange for this method to be called again soon.
        M.mod_quizlet.timer.timeoutid = setTimeout(M.mod_quizlet.timer.update, 100);
    }
};