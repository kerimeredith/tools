/*

Oxygen Webhelp plugin
Copyright (c) 1998-2014 Syncro Soft SRL, Romania.  All rights reserved.
Licensed under the terms stated in the license file EULA_Webhelp.txt 
available in the base directory of this Oxygen Webhelp plugin.

*/

/**
 * @description Get location used for "Link to this page"
 * @param currentUrl URL of current loaded page (frame)
 * @returns {string|*}
 */
function getPath(currentUrl) {
    //With Frames
    if (/MSIE (\d+\.\d+);/.test(navigator.userAgent) && location.hostname == '' && currentUrl.search("/") == '0') {
        currentUrl = currentUrl.substr(1);
    }
    path = prefix + "?q=" + currentUrl;
    return path;
}


/**
accord creates accordions for any section with outputclass of headerH that has a sectiondiv of class insideSection ignoring section title as first child. nancy 12/1/15
 */


 function makeAccordions(){
      headers=document.getElementsByClassName("headerH");
      for(var i=0;i < ;headers.length;i++){
        headers[i].addEventListener('click', accord, false);
        }
      }



function accord(){
listofnodes=this.children;
    for (var i=1; i<listofnodes.length; i++){
        //listofnodes=boxes[i].childNodes;
		if (listofnodes[i].style.display=="none"){
            listofnodes[i].style.display="block";
        }
        else {
            listofnodes[i].style.display="none";
        }
    }
}

/**
 * @description Highlight searched words
 * @param words {array} words to be highlighted
 */
function highlightSearchTerm(words) {
    if (top==self) {
        if (words != null) {
            // highlight each term in the content view
            $('#frm').contents().find('body').removeHighlight();
            for (i = 0; i < words.length; i++) {
                debug('highlight(' + words[i] + ');');
                $('#frm').contents().find('body').highlight(words[i]);
            }
        }
    } else {
        // For index with frames
        if (parent.termsToHighlight != null) {
            // highlight each term in the content view
            for (i = 0; i < parent.termsToHighlight.length; i++) {
                $('*', window.parent.contentwin.document).highlight(parent.termsToHighlight[i]);
            }
        }
    }
}

$(document).ready(function () {
    $('#permalink').show();
    if ($('#permalink').length > 0) {
        if (window.top !== window.self) {
            if (window.parent.location.protocol != 'file:' && typeof window.parent.location.protocol != 'undefined') {
                $('#permalink>a').attr('href', window.parent.location.pathname + '?q=' + window.location.pathname);
                $('#permalink>a').attr('target', '_blank');
            } else {
                $('#permalink').hide();
            }
        } else {
            $("<div class='frames'><div class='wFrames'><a href=" + getPath(location.pathname) + ">With Frames</a></div></div>").prependTo('.navheader');							        
            $('#permalink').hide();
        }
    }
    
    // Expand toc in case there are frames.
    if (top !== self && window.parent.tocwin) {
        if (typeof window.parent.tocwin.markSelectItem === 'function') {
            window.parent.tocwin.markSelectItem(window.location.href);
        }
    }
});

