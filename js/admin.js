// @source: https://github.com/creativecommons/wordpress-plugin/
// @license: http://creativecommons.org/publicdomain/zero/1.0/

jQuery(document).ready(function license($) {
  // ensure that misc-pub-section-last is actually on the last one
  $('.misc-pub-section').removeClass('misc-pub-section-last');
  $('.misc-pub-section:last').addClass('misc-pub-section-last');

  if ($('#license').length) { // if there's a license block...

    var setLicenseImage = function setLicenseImage() {
      if ($('#hidden-license-deed').val() == '') {
        $('#license-display').html('<i>No license chosen</i>').show();
      } else {
        var img = $('<a target="_new"><img /></a>')
          .attr('href',$('#hidden-license-deed').val())
          .attr('alt',$('#hidden-license-name').val())
          .find('img')
            .attr('src',$('#hidden-license-image').val())
            .attr('title',$('#hidden-license-name').val())
            .end();
	  $('#license-display').html('').append(img).show();
      }
    }
    window.setLicenseImage = setLicenseImage;
  
    window.setLicense = function setLicense(obj) {
      $('#hidden-license-deed').val(obj.deed);
      $('#hidden-license-image').val(obj.button);
      $('#hidden-license-name').val(obj.name);
      setLicenseImage();
    }
    
    // setup license image for current license
    setLicenseImage();
    
  }

})

// for now need to integrate this better with the other js code
jQuery(document).ready( function() {
  jQuery('.license-attribution-options').change( function() {
    if( jQuery('#license-other').is(':checked') ) {
      jQuery('#attribute-other-data').removeClass('hidden');
    } else {
      jQuery('#attribute-other-data').addClass('hidden');
    }
  }) 
});

