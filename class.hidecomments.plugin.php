<?php if (!defined('APPLICATION')) exit();
/*
 * HideComments
 * 
 * File: class.hidecomments.plugin.php
 *
 * Allows users to hide individual comments inside discussions.
 * Support for hiding discussions partially implemented.
 * 
 * With techniques borrowed from the Ignore plugin by Tim Gunter.
 *
 * @version 1.1.0
 * @author Jonathan Walker <xorith@gmail.com>
 * @copyright 2015 Jonathan Walker
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL
 * @package Addons 
 */

// Define the plugin:
$PluginInfo['HideComments'] = array(
   'Name'                  => 'Hide Comments',
   'Description'           => 'Allows users to hide comments within a discussion.',
   'Version'               => '1.1.0',
   'RequiredApplications'  => FALSE,
   'RequiredTheme'         => FALSE, 
   'RequiredPlugins'       => FALSE,
   'HasLocale'             => TRUE,
   'Author'                => "Jonathan Walker",
   'AuthorEmail'           => 'xorith@gmail.com'
);

class HideCommentsPlugin extends Gdn_Plugin {

   /*
    * Load in our specific resources. These files handle the "magic" and display of this add-on.
    * Changes in how the visual aspect works should be made to them.
    * @param mixed $Sender The $Sender data structure.
    */
   public function DiscussionController_BeforeDiscussionRender_Handler($Sender) {
      $Sender->AddJsFile('hidecomments.js', 'plugins/HideComments');
      $Sender->AddCssFile('hidecomments.css', 'plugins/HideComments');
   }

   /*
    * Determine if an item is hidden.
    * @todo Support for discussions is supported but not implemented.
    * @param mixed $DiscussionID The ID of the discussion to be checked.
    * @param mixed $CommentID The ID of the comment to be checked. Default NULL if a discussion is being checked.
    * @return int|bool Either the ID of the record in the HideComment table or false if not found.
    */
   public function Hidden($DiscussionID, $CommentID = NULL) {
      // Only signed-in users can use this feature
      if( !($UserID = Gdn::Session()->UserID))
         return;
      
      //Fetch hidden content for this user
      $Result = Gdn::SQL()
                  ->Select('h.ID')
                  ->From('HideComment h')
                  ->Where('UserID', $UserID)
                  ->Where('DiscussionID', $DiscussionID)
                  ->Where('CommentID',$CommentID)
                  ->Get()
                  ->FirstRow();
      if( !$Result )
         return false; // Not Found
      return intval($Result->ID); // Found
   }

   /*
    * Generate a menu option for Hiding
    * @todo Support for hiding discussions is supported but not implemented.
    * @param mixed $Options The array of options to insert this option into.
    * @param mixed $DiscussionID The Discussion ID of the discussion the action takes place in.
    * @param mixed $CommentID The comment ID inside the discussion, or NULL if the target is the discussion.
    * @param mixed $Key Array key to use for this specific option.
    */  
   public function MenuOptions(&$Options, $DiscussionID, $CommentID = NULL, $Key = NULL) {
      // Set up our label to show current state of Hide preference.
      $Label = "Hide ";
      if($this->Hidden($DiscussionID, $CommentID))
         $Label = "Unhide ";
      $Label .= ($CommentID == NULL) ? "Discussion" : "Comment";
      
      // Build our CSS Class name based on the action
      $CssClass = 'Hide'.(($CommentID == NULL) ? "Discussion" : "Comment");
      
      // Create a unique CSS Class for live page updating.
      $UniqueCss = $CssClass.'_'.(($CommentID == NULL) ? $DiscussionID : $CommentID);
      
      // URL of our Ajax request. Use 'd' as a URL-safe alternative for Null
      $Url = "/discussion/hidecomment/{$DiscussionID}/" . (($CommentID == NULL) ? 'd' : $CommentID);
      
      // Set up our option entry
      $Options[$Key] = array(
          'Label' => T($Label),
          'Url' => $Url,
          'Class' => $CssClass.' Hijack '.$UniqueCss
      );
   }

/*
 * Discussion Hiding not supported.
 *
   public function DiscussionController_DiscussionOptions_Handler($Sender, $Args){
      if(!(Gdn::Session()->UserID))
         return;
      $this->MenuOptions($Args['DiscussionOptions'],$Args['Discussion']->DiscussionID);
   }
*/

   /*
    * Insert the Hide option in the comment options menu.
    * @param mixed $Sender The Sender data structure
    * @param mixed $Args The Event Arguments.
    */
   public function DiscussionController_CommentOptions_Handler($Sender, $Args){
      if(!($UserID = Gdn::Session()->UserID))
         return;
      if($UserID == $Args['Comment']->InsertUserID)
         return;
      $this->MenuOptions($Args['CommentOptions'],$Args['Discussion']->DiscussionID, $Args['Comment']->CommentID);
   }

   /*
    * Toggles the Hide preference for the user
    * @todo Support for hiding discussions is supported but not implemented.
    * @param mixed $Sender The Sender data structure
    */
   public function DiscussionController_HideComment_Create($Sender) {
      // Signed-in users only.
      if (!($UserID = Gdn::Session()->UserID))
         return;

      // Make sure we have both the DiscussionID and the CommentID
      if (sizeof($Arguments = $Sender->RequestArgs) != 2)
         return;
      list($DiscussionID, $CommentID) = $Arguments;

      //Convert from the URL-friendly 'd' to denote a discussion-only.
      if($CommentID == 'd')
         $CommentID = NULL;

      // Set up our JSON response
      $Sender->DeliveryMethod(DELIVERY_METHOD_JSON);
      $Sender->DeliveryType(DELIVERY_TYPE_VIEW);

      // Check to see if this item is already hidden.
      if( ($Result = $this->Hidden($DiscussionID, $CommentID)) !== false ) {
         try {
            // Remove Hide preference
            Gdn::SQL()->Delete('HideComment', array(
               'ID'    => $Result
            ));

            // Handle the visual aspect of removing the Hide preference
            // Discussion support is disabled.
            if($CommentID == NULL) {
            //   $Sender->JsonTarget("#Discussion_{$DiscussionID}", 'HiddenDiscussion', 'RemoveClass');
            }
            else {
               $Sender->JsonTarget("#Comment_{$CommentID}", 'HiddenComment', 'RemoveClass'); // Remove the class
               $Sender->JsonTarget("#Comment_{$CommentID}", 'updateShown', 'Callback'); // Trigger updateShown() from hidecommments.js to update live page state.
            }
            $Sender->InformMessage(T('The '.(($CommentID == NULL) ? "discussion" : "comment").' will now be shown.'));
         } catch(Exception $e) {
            $Sender->InformMessage(T('Something went wrong. Please contact the administrator.'));
         }
      } else {
         try {
            // Set Hide preference
            Gdn::SQL()->Insert('HideComment', array(
               'DiscussionID'    => $DiscussionID,
               'CommentID'       => $CommentID,
               'UserID'          => $UserID,
               'Date'            => date('Y-m-d H:i:s')
            ));
            // Handle the visual aspect of Hiding
            // Discussions are disabled.
            if($CommentID == NULL) {
            //   $Sender->JsonTarget("#Discussion_{$DiscussionID}", 'HiddenDiscussion', 'AddClass');            
            }
            else {
               $Sender->JsonTarget("#Comment_{$CommentID}", 'HiddenComment', 'AddClass'); // Add the class
               $Sender->JsonTarget("#Comment_{$CommentID}", 'updateHidden', 'Callback'); // Trigger updateHidden() from hidecomments.js to update live page status.
            }
            $Sender->InformMessage(T('The '.(($CommentID == NULL) ? "discussion" : "comment").' will now be hidden.'));
         } catch(Exception $e) {
            $Sender->InformMessage(T('Something went wrong. Please contact the administrator.'));
         }
      }
      // Refresh view
      $Sender->Render('Blank', 'Utility', 'Dashboard');
   }

   /*
    * Apply Hide preferences to comments.
    * Functionality derived from Ignore plugin by Tim Gunter
    * @param mixed $Sender The Sender data structure.
    */
   public function DiscussionController_BeforeCommentDisplay_Handler($Sender) {
      // Signed-in users only
      if(!(Gdn::Session()->UserID))
         return;
      // Get our DiscussionID and our CommentID
      $DiscussionID = $Sender->EventArguments['Discussion']->DiscussionID;
      $CommentID = $Sender->EventArguments['Comment']->CommentID;
      
      // Check to see if this content should be hidden per the user's preference
      // If yes, then apply the HiddenComment class defined in hidecomments.css
      if(($Result = $this->Hidden($DiscussionID, $CommentID)) !== false) {
         $Classes = explode(" ",$Sender->EventArguments['CssClass']);
         $Classes[] = 'HiddenComment';
         $Classes = array_fill_keys($Classes, NULL);
         $Classes = implode(' ',array_keys($Classes));
         $Sender->EventArguments['CssClass'] = $Classes;
      }
   }

   /*
    * Stub for discussion hiding support.
    *//*   
   public function DiscussionController_BeforeDiscussionRender_Handler($Sender) {

   }
   */

   /*
    * Database changes needed for this plugin.
    * Using a table for this plugin as to not clutter UserMetaData
    */
   public function Structure() {
      $Structure = Gdn::Structure();
      $Structure
         ->Table('HideComment')
         ->PrimaryKey('ID')                           // Auto-Increment ID
         ->Column('DiscussionID', 'int(11)', FALSE)   // Required, not-null
         ->Column('CommentID', 'int(11)', TRUE)       // Null if the discussion is hidden
         ->Column('UserID', 'int(11)',FALSE, 'key')   // Owner of the hide preference
         ->Column('Date', 'datetime')                 // Date for reference only, possible future feature
         ->Set(FALSE, FALSE);      
   }

   public function Setup() {
      $this->Structure();
   }
}
