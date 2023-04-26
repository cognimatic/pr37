/**
 *Viewer library.
 */

(function( root, factory ) {
  if ( typeof define === 'function' && define.amd ) {
    define( factory );
  } else if ( typeof exports === 'object' ) {
    module.exports = factory();
  } else {
    root.Viewer = factory();
  }
}( this, function() {

  "use strict";

  function Viewer() {};

  /**
   * Load Viewer Sources.
   */
  Viewer.prototype.load = function(uuid, wrapper, callback) {
    var markup = '<div class="viewer-loader hidden"><div class="overlay"></div>'
      + '<img src="' + drupalSettings.viewer.path + '/assets/loader.svg" border="0" />'
      + '<div class="loading-taking-time"></div></div>';
    wrapper.append(markup);
    wrapper.find('.viewer-loader').removeClass('hidden');

    setTimeout(function() {
      wrapper.find('.loading-taking-time').html('Loading is taking longer than expected...');
    }, 15000);

    jQuery.ajax({url: getDomainWithPort() + '/get/viewer/' + uuid + '?_format=json'})
      .done(function(data) {
        callback(data);
        wrapper.find('.viewer-loader').addClass('hidden');
        // Make hidden elements visible.
        wrapper.find('.viewer-hidden').removeClass('viewer-hidden');
    });
  };

  /**
   * Helper function to get current domain.
   */
  function getDomainWithPort() {
    if (!window.location.origin) {
      window.location.origin = window.location.protocol + "//" + window.location.hostname
      + (window.location.port ? ':' + window.location.port: '');
    }
    return window.location.origin + drupalSettings.path.baseUrl;
  };

  return Viewer;

}));
