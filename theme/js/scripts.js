/**
 * JavaScript for Loggix
 *
 * @since   5.6.7
 * @version 9.6.2
 */
 /*

/**
 * onload event
 */
$(document).ready(function(){
 
    /**
     * Monthly Archives
     */
    $('#more-archives').click(function () {
        if ($("#arcvhies-by-year").is(":hidden")) {
            $("#arcvhies-by-year").slideDown("slow");
            return false;
        } else {
            $("#arcvhies-by-year").slideUp("slow");
            return false;
        }
    });


    /**
     * qTip Settings
     */
    $('a[title]').qtip(
        { 
            style: { 
                name: 'blue', tip: true
            }, 
            
            show: { 
                effect: { type: 'slide', length: 200 } 
            },

            hide: { 
                effect: { type: 'slide', length: 200 } 
            },
            
            position: {
                target: 'mouse',
                adjust: { x: 8, y: 5 },
                corner: {
                    target: 'bottomRight',
                    type: 'relative',
                    tooltip: 'topLeft'
                }
            }

            
        }
    );

    $('abbr[title]').qtip(
        { 
            style: { 
                name: 'blue', tip: true 
            }, 
            
            show: { 
                effect: { type: 'slide', length: 200 } 
            },

            hide: { 
                effect: { type: 'slide', length: 200 } 
            }
        }
    );

    /**
     * Toggle switches
     */
    $("#toggle-options").click(function () {
        if ($("#options").is(":hidden")) {
            $("#options").slideDown("slow");
        } else {
            $("#options").slideUp("slow");
        }
    });
    
    $("#toggle-communication").click(function () {
        if ($("#communication").is(":hidden")) {
            $("#communication").slideDown("slow");
        } else {
            $("#communication").hide("slow");
        }
    });
    
    $("#toggle-excerpt").click(function () {
        if ($("#excerpt").is(":hidden")) {
            $("#excerpt").slideDown("slow");
        } else {
            $("#excerpt").hide("slow");
        }
    });

    $("#toggle-trackback").click(function () {
        if ($("#trackback").is(":hidden")) {
            $("#trackback").slideDown("slow");
        } else {
            $("#trackback").hide("slow");
        }
    });
    
    $("#toggle-monthly-archives").click(function () {
        if ($("#monthly-archives").is(":hidden")) {
            $("#monthly-archives").slideDown("slow");
        } else {
            $("#monthly-archives").slideUp("slow");
        }
    });
    
    /**
     * Validate visitor's comment
     */
    $("#addform").submit(function() {
        if ($("#title").val() == "") {
            $("#title").addClass("notice").focus();
            $("#title-label").addClass("notice");
            return false;
        } if ($("#user-name").val() == "") {
            $("#user-name").addClass("notice").focus();
            $("#user-name-label").addClass("notice");
            return false;
        } else if ($("#comment-title").val() == "") {
            $("#comment-title").addClass("notice").focus();
            $("#comment-title-label").addClass("notice");
            return false;
        } else if ($("textarea#comment").val() == "") {
            $("#comment").addClass("notice").focus();
            $("#comment-label").addClass("notice");
            return false;
        } else if ($("#user-pass").val() == "") {
            $("#user-pass").addClass("notice").focus();
            $("#user-pass-label").addClass("notice");
            return false;
        } else if ($("#userInputCaptchaPhrase").val() == "") {
            $("#userInputCaptchaPhrase").addClass("notice").focus();
            return false;
        }
        return true;
    });
    
    /**
     * Login form
     */
    $("#login-form").submit(function() {
        if ($("#user-name").val() == "") {
            $("#user-name").addClass("notice").focus();
            $("#user-name-label").addClass("notice");
            return false;
        } else if ($("#user-pass").val() == "") {
            $("#user-pass").addClass("notice").focus();
            $("#user-pass-label").addClass("notice");
            return false;
        }
        return true;
    });
	
    /**
     * Entry Option Tabs
     */
    $('#options').tabs();
    
    /**
     * System Information
     */
    $('#system-info').tabs();
    
    /**
     * Colorbox settings
     */
    $.fn.colorbox.settings.transition = "elastic";
    $.fn.colorbox.settings.bgOpacity = "0.8";
    $.fn.colorbox.settings.contentCurrent = "image {current} of {total}";
    $("a[rel^='lightbox']").colorbox();
    $(".colorbox").colorbox();
    $(".youtube").colorbox({contentWidth:"445px", contentHeight:"364px", contentIframe:true});
});

/**
 * Admin Mode Table Status
 */
function selectTables(check){
    var i;
    for (i = 0; i < document.forms.dbtables.tables.length; i++) {
        document.forms.dbtables.tables[i].checked = check;
    }
}


/**
 * Insert text
 */
/* --- BASE FUNCTION ---- */
function insertAtCursor(comment, myValue) {
    //IE support
    if (document.selection) {
        comment.focus();
        sel = document.selection.createRange();
        sel.text = myValue;
    } else if(comment.selectionStart || comment.selectionStart == '0') { //MOZILLA/NETSCAPE support
        var startPos = comment.selectionStart;
        var endPos   = comment.selectionEnd;
        comment.value = comment.value.substring(0, startPos) 
                      + myValue 
                      + comment.value.substring(endPos, comment.value.length);
    } else {
        comment.value += myValue;
    }
}


function setFile(num, xmlLanguage) {
    var targetFile   = document.getElementById('img' + num);
    var targetButton = document.getElementById('button' + num);
    var fileValue    = document.getElementById('myfile' + num).value;    
    var filePointer  = fileValue.replace(/\\/g, '/').split('/');
    var fileNumber   = filePointer.length - 1;
    var file         = filePointer[fileNumber];
    
    if (file.match(/.jpg/i) || file.match(/.png/i) || file.match(/.gif/i)) {
        targetFile.src     = 'file:///' + fileValue;
        targetButton.value = (xmlLanguage == 'ja') ? '画像タグを挿入↑' : 'Insert Image Tag↑';
    } else if (file.match(/.mp3/i)) {
        targetFile.src     = '../theme/css/_shared/mp3.png';
        targetButton.value = (xmlLanguage == 'ja') ? 'ポッドキャストタグを挿入↑' : 'Insert Podcast Tag↑';
    } else if ((file.match(/.m4/i)) || file.match(/.mp4/i)) {
        targetFile.src     = '../theme/css/_shared/m4.png';
        targetButton.value = (xmlLanguage == 'ja') ? 'ポッドキャストタグを挿入↑' : 'Insert Podcast Tag↑';
    } else if (file.match(/.mov/i)) {
        targetFile.src     = '../theme/css/_shared/mov.png';
        targetButton.value = (xmlLanguage == 'ja') ? 'ポッドキャストタグを挿入↑' : 'Insert Podcast Tag↑';
    } else if (file.match(/.wav/i)) {
        targetFile.src     = '../theme/css/_shared/wav.png';
        targetButton.value = (xmlLanguage == 'ja') ? 'ポッドキャストタグを挿入↑' : 'Insert Podcast Tag↑';
    } else if (file.match(/.pdf/i)) {
        targetFile.src     = '../theme/css/_shared/pdf.png';
        targetButton.value = (xmlLanguage == 'ja') ? 'PDFへのリンクを挿入↑' : 'Insert a link to PDF↑';
    } else {
        targetFile.src     = '../theme/css/_shared/file-icon-large.png';
        targetButton.value = (xmlLanguage == 'ja') ? 'ファイルへのリンクを挿入↑' : 'Insert File Tag↑';
    }
}

function Attach(num) {

    // Get image size
    var targetFile = document.getElementById('img' + num);
    var fileWidth  = targetFile.width;
    var fileHeight = targetFile.height;

    if (fileWidth  == 0) { fileWidth  = ''; }
    if (fileHeight == 0) { fileHeight = ''; }

    var comment     = document.getElementById('comment');
    var fileValue   = document.getElementById('myfile' + num).value;    
    var filePointer = fileValue.replace(/\\/g, '/').split('/');
    var fileNumber  = filePointer.length - 1;
    var file        = filePointer[fileNumber];
    var attachCode  = '<img src="./data/resources/'
                    + file + '" alt="' + file + '" />';
  
    if (file.match(/.jpg/i) || file.match(/.png/i) || file.match(/.gif/i)) {
        //attachCode  = '<img src="./data/resources/'
                    //+ file + '" alt="' + file + '" />';
        // This code will add a colorbox link automatically
        attachCode  = '<a href="./data/resources/' + file + '" class="colorbox">'
                    + '<img src="./data/resources/'
                    + file + '" alt="' + file + '" />'
                    + '</a>';
    } else if (file.match(/.mp3/i) || 
               file.match(/.m4/i)  || 
               file.match(/.m4v/i) || 
               file.match(/.mp4/i) || 
               file.match(/.mov/i) || 
               file.match(/.wav/i)) {
        attachCode = '<!-- PODCAST=' + file + ' -->';
    } else {
        attachCode = '<a href="./data/resources/' + file + '">' + file + '</a>';
    }
    
    if (fileValue != '') { // If file value is not empty...
        // for Mozilla and Safari 1.3 or greater
        if ((comment.selectionStart) && (!window.opera)) {
            var selLength = comment.textLength;
            var selStart  = comment.selectionStart;
            var selEnd    = comment.selectionEnd;
            if (selEnd == 1 || selEnd == 2) { selEnd = selLength; }
            var str1 = (comment.value).substring(0, selStart);
            var str2 = (comment.value).substring(selStart, selEnd);
            var str3 = (comment.value).substring(selEnd, selLength);
            comment.value = str1 + attachCode + str3;
            comment.focus();
        } else if (document.selection) { // for WinIE
            var str = document.selection.createRange().text;
            document.getElementById('comment').focus();
            var sel = document.selection.createRange();
            sel.text = attachCode;
            return;
        } else if (window.getSelection) { // for Old Safari
            var str = window.getSelection();
            comment.value += attachCode;
            comment.focus();
        } else {
            comment.value += attachCode;
        }
    } else { // If file is not selected...
        var defaultImageTag = '<img src="./data/resources/" alt="" />';
        insertAtCursor(comment, defaultImageTag);
    }
}



/** 
 * Insert Smiley Icon Code
 *
 * @author kaz
 * @author Hiro
 */ 
function smiley(icon) {
    comment = document.getElementById("comment");
    icon = ' ' + icon + ' ';
    insertAtCursor(comment, icon);
    return false;
}

// minmax.js: make IE5+/Win support CSS min/max-width/height
// version 1.0, 08-Aug-2003
// written by Andrew Clover <and@doxdesk.com>, use freely
// http://www.doxdesk.com/software/js/minmax.html

/*@cc_on
@if (@_win32 && @_jscript_version>4)

var minmax_elements;

minmax_props= new Array(
  new Array('min-width', 'minWidth'),
  new Array('max-width', 'maxWidth'),
  new Array('min-height','minHeight'),
  new Array('max-height','maxHeight')
);

// Binding. Called on all new elements. If <body>, initialise; check all
// elements for minmax properties

function minmax_bind(el) {
  var i, em, ms;
  var st= el.style, cs= el.currentStyle;

  if (minmax_elements==window.undefined) {
    // initialise when body element has turned up, but only on IE
    if (!document.body || !document.body.currentStyle) return;
    minmax_elements= new Array();
    window.attachEvent('onresize', minmax_delayout);
    // make font size listener
    em= document.createElement('div');
    em.setAttribute('id', 'minmax_em');
    em.style.position= 'absolute'; em.style.visibility= 'hidden';
    em.style.fontSize= 'xx-large'; em.style.height= '5em';
    em.style.top='-5em'; em.style.left= '0';
    if (em.style.setExpression) {
      em.style.setExpression('width', 'minmax_checkFont()');
      document.body.insertBefore(em, document.body.firstChild);
    }
  }

  // transform hyphenated properties the browser has not caught to camelCase
  for (i= minmax_props.length; i-->0;)
    if (cs[minmax_props[i][0]])
      st[minmax_props[i][1]]= cs[minmax_props[i][0]];
  // add element with properties to list, store optimal size values
  for (i= minmax_props.length; i-->0;) {
    ms= cs[minmax_props[i][1]];
    if (ms && ms!='auto' && ms!='none' && ms!='0' && ms!='') {
      st.minmaxWidth= cs.width; st.minmaxHeight= cs.height;
      minmax_elements[minmax_elements.length]= el;
      // will need a layout later
      minmax_delayout();
      break;
  } }
}

// check for font size changes

var minmax_fontsize= 0;
function minmax_checkFont() {
  var fs= document.getElementById('minmax_em').offsetHeight;
  if (minmax_fontsize!=fs && minmax_fontsize!=0)
    minmax_delayout();
  minmax_fontsize= fs;
  return '5em';
}

// Layout. Called after window and font size-change. Go through elements we
// picked out earlier and set their size to the minimum, maximum and optimum,
// choosing whichever is appropriate

// Request re-layout at next available moment
var minmax_delaying= false;
function minmax_delayout() {
  if (minmax_delaying) return;
  minmax_delaying= true;
  window.setTimeout(minmax_layout, 0);
}

function minmax_stopdelaying() {
  minmax_delaying= false;
}

function minmax_layout() {
  window.setTimeout(minmax_stopdelaying, 100);
  var i, el, st, cs, optimal, inrange;
  for (i= minmax_elements.length; i-->0;) {
    el= minmax_elements[i]; st= el.style; cs= el.currentStyle;

    // horizontal size bounding
    st.width= st.minmaxWidth; optimal= el.offsetWidth;
    inrange= true;
    if (inrange && cs.minWidth && cs.minWidth!='0' && cs.minWidth!='auto' && cs.minWidth!='') {
      st.width= cs.minWidth;
      inrange= (el.offsetWidth<optimal);
    }
    if (inrange && cs.maxWidth && cs.maxWidth!='none' && cs.maxWidth!='auto' && cs.maxWidth!='') {
      st.width= cs.maxWidth;
      inrange= (el.offsetWidth>optimal);
    }
    if (inrange) st.width= st.minmaxWidth;

    // vertical size bounding
    st.height= st.minmaxHeight; optimal= el.offsetHeight;
    inrange= true;
    if (inrange && cs.minHeight && cs.minHeight!='0' && cs.minHeight!='auto' && cs.minHeight!='') {
      st.height= cs.minHeight;
      inrange= (el.offsetHeight<optimal);
    }
    if (inrange && cs.maxHeight && cs.maxHeight!='none' && cs.maxHeight!='auto' && cs.maxHeight!='') {
      st.height= cs.maxHeight;
      inrange= (el.offsetHeight>optimal);
    }
    if (inrange) st.height= st.minmaxHeight;
  }
}

// Scanning. Check document every so often until it has finished loading. Do
// nothing until <body> arrives, then call main init. Pass any new elements
// found on each scan to be bound   

var minmax_SCANDELAY= 500;

function minmax_scan() {
  var el;
  for (var i= 0; i<document.all.length; i++) {
    el= document.all[i];
    if (!el.minmax_bound) {
      el.minmax_bound= true;
      minmax_bind(el);
  } }
}

var minmax_scanner;
function minmax_stop() {
  window.clearInterval(minmax_scanner);
  minmax_scan();
}

minmax_scan();
minmax_scanner= window.setInterval(minmax_scan, minmax_SCANDELAY);
window.attachEvent('onload', minmax_stop);

@end @*/
