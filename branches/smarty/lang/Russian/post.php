<?php

// Language definitions used in post.php and edit.php
$lang_post = array(

// Post validation stuff (many are similiar to those in edit.php)
    'No subject'       => 'Тема должна содержать заголовок.',
    'Too long subject' => 'Заголовок не может быть длиннее 70 символов.',
    'No message'       => 'Вы должны ввести текст сообщения.',
    'Too long message' => 'Сообщение не может быть длиннее 65535 символов (64 kb).',
// Posting
    'Post errors'      => 'Ошибки',
    'Post errors info' => 'Следующие ошибки необходимо исправить перед отправкой сообщения:',
    'Post preview'     => 'Предварительный просмотр сообщения',
    'Guest name'       => 'Имя', // For guests (instead of Username)
    'Post redirect'    => 'Сообщение добавленно. Переадресация...',
    'Post a reply'     => 'Ответить',
    'Post new topic'   => 'Начать новую тему',
    'Hide smilies'     => 'Не показывать графические смайлики для этого сообщения',
    'Subscribe'        => 'Подписаться и следить за ответами в этой теме',
    'Topic review'     => 'Обзор темы (новые сверху)',
    'Flood start'      => 'Должно пройти, по крайней мере',
    'flood end'        => 'секунд, между отправкой сообщений. Пожалуйста, попробуйте отправить очередное сообщение немного позже.',
    'Preview'          => 'Посмотреть', // submit button to preview message
    'Quote'            => 'Цитировать',
// Edit post
    'Edit post legend' => 'Отредактируйте сообщение и нажмите отправить',
    'Silent edit'      => 'Не отображать сообщение о редактировании (не отображает "Отредактировано..." при просмотре темы с сообщениями)',
    'Edit post'        => 'Редактирование сообщения',
    'Edit redirect'    => 'Сообщение отредактировано. Переадресация...',
    'Added'            => 'Добавлено спустя',
    'Merge posts'      => 'Склеить с предыдущим сообщением, если оно ваше',
// Extra stuff for javascripts and popups
    'Smilies table'    => 'Таблица смайлов',
    'Smiley text'      => 'Текст',
    'Smiley image'     => 'Смайл',
    'Image text'       => 'Текст на картинке',
    'Image info'       => 'Введите текст, который Вы видите на картинке',
    'Text mismatch'    => 'Введённый Вами текст не правильный',

);


function seconds_st($nm)
{
    switch ($nm) {
        case 1:
        case 21:
        case 31:
        case 41:
        case 51:
            $st = 'секунду';
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
            $st = 'секунды';
            break;

        default:
            $st = 'секунд';
            break;
    }
    return ' ' . $nm . ' ' . $st;
}

function minutes_st($nm)
{
    switch ($nm) {
        case 1:
        case 21:
        case 31:
        case 41:
        case 51:
            $st = 'минуту';
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
            $st = 'минуты';
            break;

        default:
            $st = 'минут';
            break;
    }
    return ' ' . $nm . ' ' . $st;
}

function hours_st($nm)
{
    switch ($nm) {
        case 1:
        case 21:
            $st = 'час';
            break;

        case 2:
        case 3:
        case 4:
        case 22:
        case 23:
            $st = 'часа';
            break;

        default:
            $st = 'часов';
            break;
    }
    return ' ' . $nm . ' ' . $st;
}

function days_st($nm)
{
    switch ($nm) {
        case 1:
        case 21:
            $st = 'день';
            break;

        case 2:
        case 3:
        case 4:
        case 22:
        case 23:
            $st = 'дня';
            break;

        default:
            $st = 'дней';
            break;
    }
    return ' ' . $nm . ' ' . $st;
}
