/*
 * HideComments
 * 
 * File: hidecomments.js
 *
 * Allows users to hide individual comments inside discussions.
 * Support for hiding discussions partially implemented.
 * 
 * With techniques borrowed from the Ignore plugin by Tim Gunter.
 *
 * @version 1.0.0
 * @author Jonathan Walker <xorith@gmail.com>
 * @copyright 2015 Jonathan Walker
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL
 * @package Addons 
 */


// Set up visual magic for the Hide preference.
jQuery(document).ready(function($){

   // Collapse all hidden comments
   $('.HiddenComment').each(function(i,el){
      $(el).addClass('HiddenCommentHide');
   });

   // Expand the comment when the header is clicked. This does not toggle the Hide preference.
   $(document).on('click', '.HiddenComment', function(event) {
      var el = $(event.target);
      if (!el.hasClass('HiddenComment'))
         el = el.closest('.HiddenComment');

      if (el.hasClass('HiddenCommentHide'))
         el.removeClass('HiddenCommentHide');
      else
         el.addClass('HiddenCommentHide');
   });
   
   // Fix so that clicking the comment options link will show the comment as well.
   $(document).on('click', 'span.Arrow', function(event) {
      var el = $(event.target);
      if (!el.hasClass('HiddenComment'))
         el = el.closest('.HiddenComment');

      if (el.hasClass('HiddenCommentHide'))
         el.removeClass('HiddenCommentHide');
   });
});

// Note: I would prefer a way to update this from within the PHP code.
// @todo Localization support
// Function called by AJAX to update the state of the page when the Hide preference is applied
// See class.hidecomments.plugin.php, around Line 186
window.updateHidden = function() {
   var el = $(this);
   if (!el.hasClass('HiddenComment'))
      el = el.closest('.HiddenComment');
   el.addClass('HiddenCommentHide');
   // @todo Change from an ID to a unique class
   var opt = $('a.Hide' + $(this).attr('id'));
   opt.text("Unhide Comment");
}

// Function called by AJAX to update the state of the page when the Hide preference is removed.
// See class.hidecomments.plugin.php, around Line 164
window.updateShown = function() {
   var el = $(this);
   if (!el.hasClass('HiddenComment'))
      el = el.closest('.HiddenComment');
   el.removeClass('HiddenCommentHide');
   // @todo Change from an ID to a unique class
   var opt = $('a.Hide' + $(this).attr('id'));
   opt.text("Hide Comment");
}

/*
 * @todo Discussion Hiding is implemented but not yet supported.
 */
 
