jQuery(document).ready(function() {
  var $ = jQuery;
  $(".ellipsis").dotdotdot({
    watch: "window"
  });
  setupCourseHitBoxes($);
  setupCatalogSearchFilterSort($);
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

function setupCatalogSearchFilterSort($) {
  var options = {
    valueNames: 
      [ {name: 'course_title', attr: 'data-title'},
        'authors','price_members','price_non_members','course_count','publish_date','excerpt','categories'
      ]
  };

  var coursesList = new List('course-catalog-container', options);
  console.log(coursesList.items);
  var filters = {
    "freeformembers": function(item) {
      if (item.values().price_members == "FREE") {
        return true
      }
      return false;
    },
    "bundles": function(item) {
      if (Number(item.values().course_count) > 1) {
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
    "all": function(item) {
      return true;
    }
  }
  // $('#filter-list').append($('<option>', {
  //     value: "---",
  //     text: "-------"
  //   }));
  $('.catalog-category-filters').append('<input type="radio" name="category-filters" id="radio-cat-all" value="all"> <label for="radio-cat-all">All Courses</label><br>');
  Object.keys(window.woo_cat).forEach(function(cat){
    var tersecat = cat.trim().replace(/([A-Z])/g, '-$1').replace(/[-_\s]+/g, '').toLowerCase();
    filters[tersecat] = function(item) {
      if (item.values().categories.includes(cat)) {
        return true;
      }
      return false;
    }
    // $('#filter-list').append($('<option>', {
    //   value: tersecat,
    //   text: cat
    // }));
    $('.catalog-category-filters').append('<input type="radio" name="category-filters" id="radio-cat-' + tersecat + '" value="' + tersecat + '"> <label for="radio-cat-' + tersecat + '">' + cat + '</label><br>');
  });
  $(document).on('click', '.catalog-category-filters input', function(e) {
    var checked = $(this);
    var wat = checked.attr('value');
    coursesList.filter(filters[wat]);
  })
  // coursesList.filter(filters["active"]);
  $('.search-catalog').on('keyup', function() {
    var wat = $(this).val();
    coursesList.search(wat);
  })

  $('.filter-list').on('change', function() {
    var wat = $(this).val();
    coursesList.filter(filters[wat]);
  });

  $('.sort-list').on('change', function() {
    var wat = $(this).val().split("|");
    coursesList.sort(wat[0], {order: wat[1]});
  });
}