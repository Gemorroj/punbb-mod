<?php
// Determine what locale to use
switch (PHP_OS) {
    case 'WINNT':
    case 'WIN32':
        $locale = 'russian';
        break;

    case 'FreeBSD':
    case 'NetBSD':
    case 'OpenBSD':
        $locale = 'ru_RU.utf-8';
        break;

    default:
        $locale = 'ru_RU';
        break;
}

// Attempt to set the locale
setlocale(LC_CTYPE, $locale);
setlocale(LC_TIME, $locale);

// FIX UTF REGULAR EXPRESSIONS BUG BEGIN
define('ALPHANUM', '[:punct:]а-яА-ЯёЁ\w'); //[:alnum:]
// FIX UTF REGULAR EXPRESSIONS BUG END


// Language definitions for frequently used strings
$lang_common = array(

// Text orientation and encoding
'lang_direction' => 'ltr',	// ltr (Left-To-Right) or rtl (Right-To-Left)

// Notices
'Bad request'			=>	'Неверный запрос. Ссылка, по которой вы пришли неверная или устаревшая.',
'No view'				=>	'Вы не имеете прав для просмотра этих форумов.',
'No permission'			=>	'Вы не имеете прав для доступа к этой странице.',
'Bad referrer'			=>	'Неверный источник. Вы попали на эту страницу из несанкционированного источника. Пожалуйста, вернитесь и попробуйте еще раз. Если проблема осталась, пожалуйста, убедитесь что "Начальный URL" правильно установлен в Администрирование/Свойства и, что, Вы попадаете на форум через этот URL.',

// Topic/forum indicators
'New icon'				=>	'Новые сообщения',
'New icon_m'			=>	'new',
'Normal icon'			=>	'',
'Closed icon'			=>	'Тема закрыта',
'Closed icon_m'			    =>	'#',
'Redirect icon'			=>	'Форум перенесен',

// Miscellaneous
'Announcement'			=>	'Объявление',
'Options'				=>	'Свойства',
'Actions'				=>	'Действие',
'Submit'				=>	'Отправить',	// "name" of submit buttons
'Ban message'			=>	'На этом форуме, Вы в черном списке (забанены).',
'Ban message 2'			=>	'Время действия Вашего бана истекает',
'Ban message 3'			=>	'Забанивший Вас администратор или модератор оставил следующее сообщение:',
'Ban message 4'			=>	'Если у вас есть какие-нибудь вопросы, вы можете обратиться к администратору',
'Never'					=>	'Никогда',
'Today'					=>	'Сегодня',
'Yesterday'				=>	'Вчера',
'Info'					=>	'Информация',		// a common table header
'Go back'				=>	'Вернуться назад',
'Maintenance'			=>	'Сервис',
'Redirecting'			=>	'Переадресация',
'Click redirect'		=>	'Нажмите сюда, если вы не хотите больше ждать (или если браузер не перенаправляет Вас автоматически)',
'on'					=>	'включено',		// as in "BBCode is on"
'off'					=>	'выключено',

'on_m'					=>	'вкл',		// as in "BBCode is on mobile"
'off_m'					=>	'выкл',

'Invalid e-mail'		=>	'E-mail адрес, который Вы ввели - неправильный',
'required field'		=>	'это поле обязательно для заполнения в этой форме.',	// for javascript form validation
'Last post'				=>	'Последнее сообщение',
'by'					=>	'-',	// as in last post by someuser
'New posts'				=>	'Новые сообщения',	// the link that leads to the first new post (use &#160; for spaces)
'New posts info'		=>	'Перейти к первому новому сообщению в этом топике.',	// the popup text for new posts links
'Show karma'			=>	'Показать историю',
'Karma'					=>	'Карма',
'Vote'					=>	'Отзыв',
'Date'					=>	'Дата',
'Username'				=>	'Имя',
'Password'				=>	'Пароль',
'E-mail'				=>	'E-mail',
'Send e-mail'			=>	'Послать e-mail',
'Moderated by'			=>	'Модераторы:',
'Registered'			=>	'Зарегистрирован',
'Subject'				=>	'Заголовок',
'Message'				=>	'Сообщение',
'Topic'					=>	'Тема',
'Forum'					=>	'Форум',
'Posts'					=>	'Сообщений',
'Files'					=>	'Файлов',
'Bonus'					=>	'Бонус',
'Replies'				=>	'Ответов',
'Author'				=>	'Автор',
'Pages'					=>	'Страниц',
'BBCode'				=>	'BBCode',	// You probably shouldn't change this
'img tag'				=>	'[img] тег',
'Smilies'				=>	'Смайлики',
'and'					=>	'и',
'Image link'			=>	'изображение',	// This is displayed (i.e. <image>) instead of images when "Show images" is disabled in the profile
'wrote'					=>	'написал',	// For [quote]'s
'Code'					=>	'Код',		// For [code]'s
'Mailer'				=>	'Почтовый робот',	// As in "MyForums Mailer" in the signature of outgoing e-mails
'Important information'	=>	'Важная информация',
'Write message legend'	=>	'Напишите ваше сообщение и нажмите отправить',

// Title
'Title'					=>	'Статус',
'Member'				=>	'Участник',	// Default title
'Moderator'				=>	'Модератор',
'Administrator'			=>	'Администратор',
'Banned'				=>	'Забанен',
'Deleted'				=>	'Удален',
'Guest'					=>	'Гость',

// Stuff for include/parser.php
'BBCode error'			=>	'Синтаксис тегов BBCode в сообщении, неправильный.',
'BBCode error 1'		=>	'Отсутствует начальный тег для [/quote].',
'BBCode error 2'		=>	'Отсутствует конечный тег для [code].',
'BBCode error 3'		=>	'Отсутствует начальный тег для [/code].',
'BBCode error 4'		=>	'Отсутствует один или более конечных тегов для [quote].',
'BBCode error 5'		=>	'Отсутствует один или более начальных тегов для [/quote].',
'BBCode error 6'		=>	'Отсутствует начальный тег для [/hide].',
'BBCode error 7'		=>	'Отсутствует один или более конечных тегов для [hide].',
'BBCode error 8'		=>	'Отсутствует один или более начальных тегов для [/hide].',

'BBCode posts'			=>	'Скрытый текст.<br/>У Вас недостаточно постов (%num_posts%). Требуется как минимум %posts%',


// Stuff for the navigator (top of every page)
'WAP'                   =>	'Мобильная версия',
'Uploader'				 =>	'Загрузки',
'Index'					=>	'Главная',
'User list'				=>	'Пользователи',
'Rules'					=> 'Правила',
'Search'				=> 'Поиск',
'Register'				=> 'Регистрация',
'Login'					=> 'Войти',
'Not logged in'			=> 'Вы не зашли.',
'Profile'				=>	'Профиль',
'Site map'				=>	'Карта сайта',
'Attachments'			=>	'Вложения',
'Logout'				=>	'Выйти',
'Logged in as'			=>	'Вы зашли как',
'Admin'					=>	'Администрирование',
'Admin_m'				=>	'Админ панель',
'Last visit'			=>	'Ваш последний визит',
'Show new posts'		=>	'Новые сообщения',
'Mark all as read'		=>	'Пометить все форумы как прочитанные',
'Link separator'		=>	'',	// The text that separates links in the navigator
'Link separator_m'		=>	'|',	// The text that separates links in the navigator (wap)

// Stuff for the page footer
'Board footer'			=>	'Дополнительно',
'Search links'			=>	'Сообщения',
'Show recent posts'		=>	'Показать последние сообщения',
'Show unanswered posts'	=>	'Показать сообщения, не имеющие ответов',
'Show your posts'		=>	'Показать Ваши сообщения',
'Show subscriptions'	=>	'Показать сообщения на которые Вы подписаны',
'Jump to'				=>	'Перейти',
'Go'					=>	' Перейти ',		// submit button in forum jump
'Move topic'			=> 'Перенести тему',
'Open topic'			=> 'Открыть тему',
'Close topic'			=> 'Закрыть тему',
'Unstick topic'			=> 'Снять выделение',
'Stick topic'			=> 'Выделить тему',
'Moderate forum'		=>	'Модерировать форум',
'Delete posts'			=>	'Удалить сообщения',
'Debug table'			=>	'Отладочная информация',

// ALL POST IN ONE PAGE

'All'					=>	'Все',

// MOD PRINTABLE TOPIC
// MOD Printable topic version string
'Print version'			=>	'Версия для печати',


//wap moderate IP
'Show IP'				=>	'Показать еще пользователем с этим IP'
);
