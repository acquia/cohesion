(function ($, Drupal) {
  "use strict";

  Drupal.behaviors.DX8YouTubeEmbed = {
        
    attach: function(context)   {
            
      // Reapply the src if it's been cached
      $.each($('.coh-youtube-embed > .coh-youtube-embed-inner > .coh-youtube-embed-item', context), function(){
                
        if($(this)[0].hasAttribute('data-src'))    {
          $(this).attr('src', $(this).data('src'));
        }
      });
    },
        
    detach: function(context)   {
            
      // Remove the src on unload so it doesn't play in the background
      $.each($('.coh-youtube-embed > .coh-youtube-embed-inner > .coh-youtube-embed-item', context), function(){
        $(this).attr('data-src', $(this).attr('src')).attr('src', '');
      });
    }
  };
    
})(jQuery, Drupal);