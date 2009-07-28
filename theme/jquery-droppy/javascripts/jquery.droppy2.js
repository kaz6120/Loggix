/*
 * Droppy 0.1.2
 * (c) 2008 Jason Frame (jason@onehackoranother.com)
 * 
 * @author  Customized by Loggix Project
 * @since   9.5.24
 * @version 9.5.26
 */
$.fn.droppy = function(options) {
    
  options = $.extend({speed: 250}, options || {});
  
  this.each(function() {
    
    var root = this, zIndex = 1000;
    
    function getSubnav(ele) {
      if (ele.nodeName.toLowerCase() == 'li') {
        var subnav = $('> ul', ele);
        return subnav.length ? subnav[0] : null;
      } else {
        return ele;
      }
    }
    
    function getActuator(ele) {
      if (ele.nodeName.toLowerCase() == 'ul') {
        return $(ele).parents('li')[0];
      } else {
        return ele;
      }
    }
    
    function hide() {
      var subnav = getSubnav(this);
      if (!subnav) return;
      $.data(subnav, 'cancelHide', false);
      setTimeout(function() {
        if (!$.data(subnav, 'cancelHide')) {
          $(subnav).slideUp(options.speed);
        }
      }, 500);
      return false;
    }
  
    function show() {
      var subnav = getSubnav(this);
      if (!subnav) return;
      $.data(subnav, 'cancelHide', true);
      $(subnav).css({zIndex: zIndex++}).slideDown(options.speed);
      if (this.nodeName.toLowerCase() == 'ul') {
        var li = getActuator(this);
        $(li).addClass('hover');
        $('> a', li).addClass('hover');
      }
      return false;
    }

    /* Settings */
    $('#current-month', this).click(function () {
        if ($("#arcvhies-by-year").is(":hidden")) {
            $("#arcvhies-by-year").slideDown("slow");
            return false;
        } else {
            $("#arcvhies-by-year").slideUp("slow");
            return false;
        }
    });

    $('.archive-by-year', this).hover(show, hide);
    
  });
  
};
