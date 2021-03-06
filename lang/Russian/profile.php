<?php

// Language definitions used in profile.php
$lang_profile = [
    // Navigation and sections
    'Profile menu' => 'Профиль',
    'Section essentials' => 'Основной',
    'Section personal' => 'Персональный',
    'Section messaging' => 'Пейджеры',
    'Section personality' => 'Аватар и подпись',
    'Section display' => 'Внешний вид',
    'Section privacy' => 'Личный',
    'Section admin' => 'Администрирование',

    // Miscellaneous
    'Username and pass legend' => 'Имя и пароль',
    'Personal details legend' => 'Ваши персональные данные',
    'Contact details legend' => 'Укажите данные для общения',
    'Options display' => 'Укажите данные для отображения',
    'Options post' => 'Укажите данные для просмотра сообщений',
    'User activity' => 'Активность пользователя',
    'Paginate info' => 'Укажите число тем и сообщений отображаемых на каждой странице.',

    // Password stuff
    'Pass key bad' => 'Ключ активизации данного пароля неправилен или просрочен. Пожалуйста, запросите новый пароль еще раз. Если не получится, обратитесь к администратору форума',
    'Pass updated' => 'Ваш пароль изменён. Вы можете войти, используя новый пароль.',
    'Pass updated redirect' => 'Пароль изменён. Переадресация...',
    'Wrong pass' => 'Старый пароль неверен.',
    'Change pass' => 'Изменить пароль',
    'Change pass legend' => 'Введите и подтвердите ваш новый пароль',
    'Old pass' => 'Старый пароль',
    'New pass' => 'Новый пароль',
    'Confirm new pass' => 'Подтвердите новый пароль',

    // E-mail stuff
    'E-mail key bad' => 'Ключ активизации данного e-mail адреса неправилен или просрочен. Пожалуйста, запросите изменение e-mail адреса еще раз. Если не получится, обратитесь к администратору форума',
    'E-mail updated' => 'Ваш e-mail адрес изменён.',
    'Activate e-mail sent' => 'По указанному адресу было выслано письмо с инструкцией, как активировать новый e-mail адрес. В случае возникновения каких либо проблем обратитесь к администратору',
    'E-mail legend' => 'Введите ваш новый e-mail адрес',
    'E-mail instructions' => 'Введите новый e-mail адрес, на который придёт сообщение со ссылкой для подтверждения изменений. Для подтверждения изменения e-mail адреса, Вам нужно будет зайти по присланной ссылке.',
    'Change e-mail' => 'Изменить e-mail адрес',
    'New e-mail' => 'Новый e-mail',

    // Avatar upload stuff
    'Avatars disabled' => 'Использование аватаров отключено администратором.',
    'Too large ini' => 'Выбранный файл слишком велик для загрузки.',
    'Partial upload' => 'Выбранный файл был загружен не полностью. Пожалуйста, попробуйте еще раз.',
    'No tmp directory' => 'PHP не смог сохранить загруженный файл во временной директории.',
    'No file' => 'Вы не выбрали файл для загрузки.',
    'Bad type' => 'Тип файла, выбранного вами, не разрешен. Допустимые типы это - gif, jpeg и png.',
    'Too wide or high' => 'Файл, который вы загружаете, больше максимально допустимой ширины и/или высоты',
    'Too large' => 'Файл, который вы загружаете, больше максимально допустимого размера',
    'pixels' => 'пикселей',
    'bytes' => 'байт',
    'Move failed' => 'Не удалось сохранить загружаемый на сервер файл. Пожалуйста, обратитесь к администратору',
    'Unknown failure' => 'Неизвестная ошибка. Пожалуйста, попробуйте еще раз.',
    'Avatar upload redirect' => 'Аватар загружен. Переадресация...',
    'Avatar deleted redirect' => 'Аватар удалён. Переадресация...',
    'Avatar desc' => 'Аватар - это маленькое изображение, которое будет отображаться под Вашим именем в сообщениях на форуме. Оно не может быть больше чем',
    'Upload avatar' => 'Загрузить аватар',
    'Upload avatar legend' => 'Укажите файл с аватаром для загрузки',
    'Delete avatar' => 'Удалить аватар',	// only for admins
    'File' => 'Файл',
    'Upload' => 'Загрузить',	// submit button

    // Form validation stuff
    'Dupe username' => 'Кто-то уже зарегистрирован с таким именем. Пожалуйста, вернитесь и попробуйте другое имя.',
    'Forbidden title' => 'Название, введенное Вами, содержит запрещенное слово. Вы должны выбрать другое название.',
    'Profile redirect' => 'Профиль обновлен. Переадресация...',

    // Profile display stuff
    'Not activated' => 'Этот пользователь еще не активировал свой аккаунт (учетную запись). Аккаунт активируется при первом логине на форум.',
    'Unknown' => '(Неизвестно)',	// This is displayed when a user hasn't filled out profile field (e.g. Location)
    'Private' => '(Скрыт)',	// This is displayed when a user does not want to receive e-mails
    'No avatar' => '(Нет аватара)',
    'Show posts' => 'Показать все сообщения',
    'Show files' => 'Показать все файлы',
    'Realname' => 'Настоящее имя',
    'Location' => 'Местонахождение',
    'Website' => 'Вебсайт',
    'Jabber' => 'Jabber',
    'ICQ' => 'ICQ',
    'MSN' => 'MSN Messenger',
    'AOL IM' => 'AOL IM',
    'Yahoo' => 'Yahoo! Messenger',
    'Avatar' => 'Аватар',
    'Signature' => 'Подпись',
    'Sig max length' => 'Макс. символов',
    'Sig max lines' => 'Макс. строк',
    'Avatar legend' => 'Настройка отображения аватара',
    'Avatar info' => 'Аватар - это маленькое изображение, которое будет отображаться под Вашим именем в сообщениях на форуме. Вы можете загрузить аватар, нажав на ссылку ниже. Для того чтобы аватар был виден в ваших сообщениях, отметьте галочкой поле "Использовать аватар".',
    'Change avatar' => 'Изменить аватар',
    'Use avatar' => 'Использовать аватар.',
    'Signature legend' => 'Составьте вашу подпись',
    'Signature info' => 'Подпись - это небольшая приписка, прилагаемая к вашим сообщениям. Это может быть все, что вам нравится. Например, ваша любимая цитата. В подписи можно использовать встроенные теги BBCode и/или HTML, если это разрешено администратором. Что разрешено, можно увидеть слева, при редактировании подписи.',
    'Sig preview' => 'Предварительный просмотр текущей подписи:',
    'No sig' => 'Нет подписи.',
    'Topics per page' => 'Тем',
    'Topics per page info' => 'Эта опция устанавливает количество тем, показываемых на одной странице, при просмотре форума. Если вы не определились, использовать ее или нет, то оставьте пустой, будут использованы значения установленные по умолчанию.',
    'Posts per page' => 'Сообщений',
    'Posts per page info' => 'Эта опция контролирует количество сообщений, показываемых на одной странице, при просмотре темы. Если вы не определились, использовать ее или нет, то оставьте пустой, будут использованы значения установленные по умолчанию.',
    'Leave blank' => 'Оставьте поле пустым, для использования настроек форума, по умолчанию.',
    'Notify full' => 'Включать сообщение целиком, при подписке об уведомлениях о новых сообщениях на форуме на e-mail.',
    'Notify full info' => 'При включении этой опции, в тело уведомлений о новых сообщениях на e-mail, будет включаться текст самих сообщений.',
    'Show smilies' => 'Преобразовывать смайлики в изображения, по умолчанию.',
    'Show smilies info' => 'Отметьте, если Вы хотите, чтобы, по умолчанию, вместо текстовых смайликов использовались маленькие иконки. Также Вы сможете изменять эту опцию непосредственно перед написанием каждого сообщения.',
    'Show images' => 'Показывать изображения в сообщениях.',
    'Show images info' => 'Снимите отметку, если вы не хотите видеть изображения и иконки в сообщениях (включая смайлики и изображения отображаемые при помощи тега img).',
    'Show images sigs' => 'Показывать изображения в подписи.',
    'Show images sigs info' => 'Снимите отметку, если вы не хотите видеть изображения в подписях отображаемые при помощи тега img.',
    'Show avatars' => 'Показывать аватары пользователей в сообщениях.',
    'Show avatars info' => 'Установите эту опцию для отображения аватаров в просматриваемых сообщениях на форуме.',
    'Show sigs' => 'Показывать подписи пользователей.',
    'Show sigs info' => 'Отметьте, если вы хотите видеть подписи пользователей.',
    'Style legend' => 'Выберите стиль отображения',
    'Style info' => 'При желании, вы можете использовать другой визуальный стиль для этого форума.',
    'Admin note' => 'Прим. администратора',
    'Pagination legend' => 'Выбор отображения страниц',
    'Post display legend' => 'Выбор отображения сообщений',
    'Post display info' => 'Если вы используете медленное соединение, то вы можете запретить отображение изображений в  сообщениях и подписях для убыстрения загрузки страниц.',
    'Instructions' => 'После обновления профиля, вы будете перенаправлены назад на эту страницу.',

    // Extra personal detail stuff
    'Preview' => 'Просмотр',

    // Administration stuff
    'Group membership legend' => 'Выберите группу пользователей',
    'Save' => 'Сохранить',
    'Set mods legend' => 'Установить права модератора',
    'Moderator in' => 'Модератор в',
    'Moderator in info' => 'Выберете, какие форумы будет модерировать этот пользователь. Примечание: Это применимо только к модераторам. Администраторы всегда имеют полный доступ ко всем форумам.',
    'Update forums' => 'Обновить форумы',
    'Delete ban legend' => 'Удалить (только для администраторов) или забанить пользователя',
    'Delete user' => 'Удалить пользователя',
    'Ban user' => 'Забанить пользователя',
    'Confirm delete legend' => 'Важно: прочтите перед удалением пользователя',
    'Confirm delete user' => 'Подтвердите удаление пользователя',
    'Confirmation info' => 'Пожалуйста, подтвердите, что вы хотите удалить пользователя',	// the username will be appended to this string
    'Delete warning' => 'Внимание! Удаление пользователя или сообщений не может быть восстановлено. Если вы выберите не удалять сообщения, сделанные пользователем, то эти сообщения можно будет удалить только вручную позже.',
    'Delete posts' => 'Удаление всех сообщений и тем сделанных пользователем.',
    'Delete' => 'Удалить',		// submit button (confirm user delete)
    'User delete redirect' => 'Пользователь удален. Переадресация...',
    'Group membership redirect' => 'Изменение группы сохранено. Переадресация...',
    'Update forums redirect' => 'Права модерирования форума обновлены. Переадресация...',

    // REAL MARK TOPIC AS READ MOD BEGIN
    'Mark as read legend' => 'Пометка форумов и тем прочтёнными',
    'Mark as read after' => 'Пометить прочтенной после (дней)',

    // REAL MARK TOPIC AS READ MOD END

    'Show bb-panel legend' => 'ББ-панель в быстром ответе',
    'Show bb-panel' => 'Показывать ББ-панель в быстром ответе. Отключенная панель сокращает время загрузки страницы, за счет уменьшения кода страницы.',

    'Ban redirect' => 'Переадресация...',

    'sex' => 'Пол',
    'm' => 'М',
    'w' => 'Ж',
    'birthday' => 'День рождения',
    'day' => 'День',
    'month' => 'Месяц',
    'year' => 'Год',
];
