<?php
// Determine what locale to use
switch (PHP_OS) {
	case 'WINNT':
	case 'WIN32':
		$locale = 'english';
		break;

	case 'FreeBSD':
	case 'NetBSD':
	case 'OpenBSD':
		$locale = 'en_US.utf-8';
		break;

	default:
		$locale = 'en_US';
		break;
}

// Attempt to set the locale
setlocale(LC_CTYPE, $locale);

// Language definitions for frequently used strings
$lang_common = array(

// Text orientation and encoding
'lang_direction'		=>	'ltr',	// ltr (Left-To-Right) or rtl (Right-To-Left)

// Notices
'Bad request'			=>	'Bad request. The link you followed is incorrect or outdated.',
'No view'				=>	'You do not have permission to view these forums.',
'No permission'			=>	'You do not have permission to access this page.',
'Bad referrer'			=>	'Bad HTTP_REFERER. You were referred to this page from an unauthorized source. If the problem persists please make sure that "Base URL" is correctly set in Admin/Options and that you are visiting the forum by navigating to that URL. More information regarding the referrer check can be found in the PunBB documentation.',

// Topic/forum indicators
'New icon'				=>	'There are new posts',
'New icon_m'			=>	'new',
'Normal icon'			=>	'<!-- -->',
'Closed icon'			=>	'This topic is closed',
'Closed icon_m'			    =>	'#',
'Redirect icon'			=>	'Redirected forum',

// Miscellaneous
'Announcement'			=>	'Announcement',
'Options'				=>	'Options',
'Actions'				=>	'Actions',
'Submit'				=>	'Submit',	// "name" of submit buttons
'Ban message'			=>	'You are banned from this forum.',
'Ban message 2'			=>	'The ban expires at the end of',
'Ban message 3'			=>	'The administrator or moderator that banned you left the following message:',
'Ban message 4'			=>	'Please direct any inquiries to the forum administrator at',
'Never'					=>	'Never',
'Today'					=>	'Today',
'Yesterday'				=>	'Yesterday',
'Info'					=>	'Info',		// a common table header
'Go back'				=>	'Go back',
'Maintenance'			=>	'Maintenance',
'Redirecting'			=>	'Redirecting',
'Click redirect'		=>	'Click here if you do not want to wait any longer (or if your browser does not automatically forward you)',
'on'					=>	'on',		// as in "BBCode is on"
'off'					=>	'off',
'on_m'					=>	'on',		// as in "BBCode is on mobile"
'off_m'					=>	'off',
'Invalid e-mail'		=>	'The e-mail address you entered is invalid.',
'required field'		=>	'is a required field in this form.',	// for javascript form validation
'Last post'				=>	'Last post',
'by'					=>	'by',	// as in last post by someuser
'New posts'				=>	'New posts',	// the link that leads to the first new post (use &nbsp; for spaces)
'New posts info'		=>	'Go to the first new post in this topic.',	// the popup text for new posts links
'Show karma'			=>	'Show',
'Karma'					=>	'Karma',
'Vote'					=>	'Vote',
'Date'					=>	'Date',
'Username'				=>	'Username',
'Password'				=>	'Password',
'E-mail'				=>	'E-mail',
'Send e-mail'			=>	'Send e-mail',
'Moderated by'			=>	'Moderated by',
'Registered'			=>	'Registered',
'Subject'				=>	'Subject',
'Message'				=>	'Message',
'Topic'					=>	'Topic',
'Forum'					=>	'Forum',
'Posts'					=>	'Posts',
'Files'					=>	'Files',
'Bonus'					=>	'Bonus',
'Replies'				=>	'Replies',
'Author'				=>	'Author',
'Pages'					=>	'Pages',
'BBCode'				=>	'BBCode',	// You probably shouldn't change this
'img tag'				=>	'[img] tag',
'Smilies'				=>	'Smilies',
'and'					=>	'and',
'Image link'			=>	'image',	// This is displayed (i.e. <image>) instead of images when "Show images" is disabled in the profile
'wrote'					=>	'wrote',	// For [quote]'s
'Code'					=>	'Code',		// For [code]'s
'Mailer'				=>	'Mailer',	// As in "MyForums Mailer" in the signature of outgoing e-mails
'Important information'	=>	'Important information',
'Write message legend'	=>	'Write your message and submit',

// Title
'Title'					=>	'Title',
'Member'				=>	'Member',	// Default title
'Moderator'				=>	'Moderator',
'Administrator'			=>	'Administrator',
'Banned'				=>	'Banned',
'Deleted'				=>	'Deleted',
'Guest'					=>	'Guest',

// Stuff for include/parser.php
'BBCode error'			=>	'The BBCode syntax in the message is incorrect.',
'BBCode error 1'		=>	'Missing start tag for [/quote].',
'BBCode error 2'		=>	'Missing end tag for [code].',
'BBCode error 3'		=>	'Missing start tag for [/code].',
'BBCode error 4'		=>	'Missing one or more end tags for [quote].',
'BBCode error 5'		=>	'Missing one or more start tags for [/quote].',
'BBCode error 6'		=>	'Missing start tag for [/hide].',
'BBCode error 7'		=>	'Missing one or more end tags for [hide].',
'BBCode error 8'		=>	'Missing one or more start tags for [/hide].',


'BBCode posts'			=>	'Hidden text.<br/>You do not have enough posts (%num_posts%). Requires a minimum of %posts%',


// Stuff for the navigator (top of every page)
'WAP'                   =>	'Veris mobile',
'Uploader'			 =>	'Uploader',
'Index'					=>	'Index',
'User list'				=>	'User list',
'Rules'					=>  'Rules',
'Search'				=>  'Search',
'Register'				=>  'Register',
'Login'					=>  'Login',
'Not logged in'			=>  'You are not logged in.',
'Profile'				=>	'Profile',
'Site map'			=>	'Site map',
'Attachments'			=>	'Attachments',
'Logout'				=>	'Logout',
'Login required' => 'Login required',
'Logged in as'			=>	'Logged in as',
'Admin'					=>	'Administration',
'Admin_m'				=>	'Admin panel',
'Last visit'			=>	'Last visit',
'Show new posts'		=>	'Show new posts since last visit',
'Mark all as read'		=>	'Mark all as read',
'Link separator'		=>	'',	// The text that separates links in the navigator
'Link separator_m'		=>	'|',// The text that separates links in the navigator (wap)

// Stuff for the page footer
'Board footer'			=>	'Board footer',
'Search links'			=>	'Search links',
'Show recent posts'		=>	'Show recent posts',
'Show unanswered posts'	=>	'Show unanswered posts',
'Show your posts'		=>	'Show your posts',
'Show subscriptions'	=>	'Show your subscribed topics',
'Jump to'				=>	'Jump to',
'Go'					=>	' Go ',		// submit button in forum jump
'Move topic'			=>  'Move topic',
'Open topic'			=>  'Open topic',
'Close topic'			=>  'Close topic',
'Unstick topic'			=>  'Unstick topic',
'Stick topic'			=>  'Stick topic',
'Moderate forum'		=>	'Moderate forum',
'Delete posts'			=>	'Delete multiple posts',
'Debug table'			=>	'Debug information',

// ALL POST IN ONE PAGE

'All'					=>	'All',


//wap moderate IP
'Show IP'				=>	'Show more users for this IP',
'Find email in stop forum spam' => 'Find e-mail in StopForumSpam',

);
