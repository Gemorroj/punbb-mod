<?php

// Language definitions used in post.php and edit.php
$lang_post = [
    // Post validation stuff (many are similiar to those in edit.php)
    'No subject' => 'Topics must contain a subject.',
    'Too long subject' => 'Subjects cannot be longer than 70 characters.',
    'No message' => 'You must enter a message.',
    'Too long message' => 'Posts cannot be longer that 65535 characters (64 KB).',

    // Posting
    'Post errors' => 'Post errors',
    'Post errors info' => 'The following errors need to be corrected before the message can be posted:',
    'Post preview' => 'Post preview',
    'Guest name' => 'Name',	// For guests (instead of Username)
    'Post redirect' => 'Post entered. Redirecting &#x2026;',
    'Post a reply' => 'Post a reply',
    'Post new topic' => 'Post new topic',
    'Hide smilies' => 'Never show smilies as icons for this post',
    'Subscribe' => 'Subscribe to this topic',
    'Topic review' => 'Topic review (newest first)',
    'Flood start' => 'At least',
    'flood end' => 'seconds have to pass between posts. Please wait a little while and try posting again.',
    'Preview' => 'Preview',	// submit button to preview message
    'Quote' => 'Quote',

    // Edit post
    'Edit post legend' => 'Edit the post and submit changes',
    'Silent edit' => 'Silent edit (don\'t display "Edited by ..." in topic view)',
    'Edit post' => 'Edit post',
    'Edit redirect' => 'Post updated. Redirecting &#x2026;',
    'Added' => 'Added after ',
    'Merge posts' => 'Merge with previous if it yours',

    // Extra stuff for javascripts and popups
    'Smilies table' => 'Smilies table',
    'Smiley text' => 'Text',
    'Smiley image' => 'Smiley',
    'Image text' => 'Image Text',
    'Image info' => 'Please copy the text in the image to the text box above',
    'Text mismatch' => 'Please make sure the text and the image match',
];

function seconds_st($nm)
{
    switch ($nm) {
        case 1:
        case 21:
        case 31:
        case 41:
        case 51:
            $st = 'seconds';

        break;

        case 2:
        case 3:
        case 4:
        case 22:
        case 23:
        case 24:
        case 32:
        case 33:
        case 34:
        case 42:
        case 43:
        case 44:
        case 52:
        case 53:
        case 54:
            $st = 'seconds';

        break;

        default:
            $st = 'seconds';

        break;
    }

    return ' '.$nm.' '.$st;
}

function minutes_st($nm)
{
    switch ($nm) {
        case 1:
        case 21:
        case 31:
        case 41:
        case 51:
            $st = 'minuts';

        break;

        case 2:
        case 3:
        case 4:
        case 22:
        case 23:
        case 24:
        case 32:
        case 33:
        case 34:
        case 42:
        case 43:
        case 44:
        case 52:
        case 53:
        case 54:
            $st = 'minuts';

        break;

        default:
            $st = 'minuts';

        break;
    }

    return ' '.$nm.' '.$st;
}

function hours_st($nm)
{
    switch ($nm) {
        case 1:
        case 21:
            $st = 'hours';

        break;

        case 2:
        case 3:
        case 4:
        case 22:
        case 23:
            $st = 'hours';

        break;

        default:
            $st = 'hours';

        break;
    }

    return ' '.$nm.' '.$st;
}

function days_st($nm)
{
    switch ($nm) {
        case 1:
        case 21:
            $st = 'days';

        break;

        case 2:
        case 3:
        case 4:
        case 22:
        case 23:
            $st = 'days';

        break;

        default:
            $st = 'days';

        break;
    }

    return ' '.$nm.' '.$st;
}
