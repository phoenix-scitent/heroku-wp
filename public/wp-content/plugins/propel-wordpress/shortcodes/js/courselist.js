// window onblur and onvisible functionality
!function(){function n(n){var o="visible",e="hidden",d={focus:o,focusin:o,pageshow:o,blur:e,focusout:e,pagehide:e};n=n||window.event,n.type in d?document.body.setAttribute('data-focusstatus',d[n.type]):document.body.setAttribute('data-focusstatus',(this[i]?"hidden":"visible")),window["on"+document.body.getAttribute('data-focusstatus')]()}var i="hidden";i in document?document.addEventListener("visibilitychange",n):(i="mozHidden")in document?document.addEventListener("mozvisibilitychange",n):(i="webkitHidden")in document?document.addEventListener("webkitvisibilitychange",n):(i="msHidden")in document?document.addEventListener("msvisibilitychange",n):"onfocusin"in document?document.onfocusin=document.onfocusout=n:window.onpageshow=window.onpagehide=window.onfocus=window.onblur=n,window.onvisible=function(){console.log("window is visible, no functionality")},window.onhidden=function(){console.log("window is hidden, no functionality")},void 0!==document[i]&&n({type:document[i]?"blur":"focus"})}();

jQuery(document).ready(function() {
  var $ = jQuery;
  $(".ellipsis").dotdotdot({
    watch: "window"
  });
  setupCourseHitBoxes($);
  setupMyCoursesSearchFilterSort($);
  $('.badge').popup({ 
      transition: 'scale',
      inline: true });
  
  setupCertificateModalHiding($);
  $(".grassblade_launch_link").click(function() {
    window.onvisible = function() {
      window.location.reload(true);
    };
  });
});


function clickToAccessCourse(obj) {
  var uri = jQuery(obj).attr('href');
  if (uri == undefined) {
    return false;
  }
  var targ = jQuery(obj).attr('target');
  if (targ === "_blank") {
    var win = window.open(uri, targ);
    if (win) {
      //Browser has allowed it to be opened
      win.focus();
    } else {
      //Browser has blocked it
      alert('Please allow popups for this website');
    }    
  } else {
    window.location.href = uri;
  }
}

function setupCourseHitBoxes($) {
  $('.course-image-link').on('click', function(e) {
    e.preventDefault();
    clickToAccessCourse($(this).parent().find('.course-access'));
  });
  $('.courselist-course-info').on('click', function(e) {
    e.preventDefault();
    clickToAccessCourse($(this).find('.course-access'));
  });
}

function setupMyCoursesSearchFilterSort($) {
  var fuzzyOptions = {
  searchClass: "fuzzy-search",
  location: 0,
  distance: 100,
  threshold: 0.4,
  multiSearch: true
  };
  var options = {
    valueNames: [ {name: 'course_title', attr: 'data-title'}, 
                  'authors', 
                  {name: 'progress', attr: 'data-progress'}, 
                  {name: 'course_active', attr: 'data-active'}, 
                  {name: 'activation_key', attr: "data-key"}, 
                  {name: 'course_started', attr: 'data-started'} 
                ],
    plugins: [
      ListFuzzySearch()
    ]
  };

  var coursesList = new List('courselist-container', options);
  console.log(coursesList.items);
  var filters = {
    "incomplete": function(item) {
      if (item.values().progress != "100" && item.values().course_active != null) {
        return true
      }
      return false;
    },
    "completed": function(item) {
      if (item.values().progress == "100" && item.values().course_active != null) {
        return true
      }
      return false;
    },
    "expired": function(item) {
      if (item.values().course_active == null) {
        return true;
      }
      return false;
    },
    "active": function(item) {
      if (item.values().course_active != null) {
        return true;
      }
      return false;
    }
  }
  coursesList.filter(filters["active"]);

  $('#filter-courselist-list').on('change', function() {
    var wat = $(this).val();
    coursesList.filter(filters[wat]);
    Claimer.refresh();
  });

  $('#sort-courselist-list').on('change', function() {
    var wat = $(this).val().split("|");
    coursesList.sort(wat[0], {order: wat[1]});
    Claimer.refresh();
  });
}

function setupCertificateModalHiding($) {
  window.certificate_modal_hidden = function() {
    console.log("certificate_modal_hidden");
    $('#header-secondary-outer').removeClass( 'hideme' );
    $('#header-outer').removeClass( 'hideme' );
    $('.page-header-no-bg').removeClass( 'hideme' );
    $("#footer-outer").removeClass('hideme');
  };
  window.certificate_modal_shown = function() {
    console.log("certificate_modal_shown");
    $('#header-secondary-outer').addClass( 'hideme' );
    $('#header-outer').addClass( 'hideme' );
    $('.page-header-no-bg').addClass( 'hideme' );
    $("#footer-outer").addClass('hideme');
  };
}