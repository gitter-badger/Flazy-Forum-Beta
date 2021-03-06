<?php

$lang_admin_settings = array(

'Settings updated'				=>	'Settings updated.',

// Setup section
'Setup personal'				=>	'Personalize your Flazy installation',
'Setup personal legend'			=>	'Flazy installation',
'Board keywords label'			=>	'Board keywords',
'Board keywords help'			=>	'Keywords ',
'Board description label'		=>	'Board description',
'Board title label'				=>	'Board title',
'Default style label'			=>	'Default style',
'Forced style'					=>	'Force default style',
/*RUS*/'Forced style help'		=>	'Этот стиль будет отображаться у всех участников не зависимо от их выбора',
'Select'						=>	'Select',
/*RUS*/'User style'				=>	'Стиль участника',
/*RUS*/'User style help'		=>	'Разрешить участникам выбирать свой стиль оформления форума',
'Setup local'					=>	'Configure Flazy for your location',
'Setup local legend'			=>	'local settings',
'Default timezone label'		=>	'Default timezone',
'Adjust for DST'				=>	'Летнее время',
'DST label'						=>	'Daylight saving time (advance times by 1 hour).',
'Default language label'		=>	'Default language',
/*RUS*/'Default language help'	=>	'Если вы добавили или удалили языковой пакет, а его не видно, эту страницу нужно обновить',
'Forced language'				=>	'Force default language',
/*RUS*/'Forced language help'	=>	'Этот язык будет у всех участников не зависимо от их выбора',
'Time format label'				=>	'Time format',
'Date format label'				=>	'Date format',
'Current format'				=>	'[ Current format: %s ] %s',
'External format help'			=>	'See <a class="exthelp" href="http://www.php.net/manual/ru/function.date.php">here</a> for formatting options.',
'Setup timeouts'				=>	'Default timeouts and redirect delay',
'Setup timeouts legend'			=>	'Timeout defaults',
'Visit timeout label'			=>	'Visit timeout',
'Visit timeout help'			=>	'Seconds idle before user is logged out',
'Online timeout label'			=>	'Online timeout',
'Online timeout help'			=>	'Seconds idle before being removed from the online users list',
'Redirect time label'			=>	'Redirect wait',
'Redirect time help'			=>	'If set to 0 seconds, no redirect page will be displayed',
'Setup pagination'				=>	'Default pagination for topics, posts and post review',
'Setup pagination legend'		=>	'Pagination defaults',
'Topics per page label'			=>	'Topics per page',
'Posts per page label'			=>	'Posts per page',
'Topic review label'			=>	'Topic review',
'Topic review help'				=>	'Ordered newest first. 0 to disable.',
'Setup reports'					=>	'Method for receiving reports of posts and topics',
'Setup reports legend'			=>	'Receive reports',
/*RUS*/'Report enabled'				=>	'Включить жалобы',
/*RUS*/'Report enabled help'			=>	'Разрешить участникам оставлять жалобы на сообщения.',
'Reporting method'				=>	'Reporting method',
'Report internal label'			=>	'By internal report system.',
'Report both label'				=>	'Both by internal report system and by email to those on mailing list.',
'Report email label'			=>	'By email to those on mailing list.',
'Setup URL'						=>	'URL Scheme (<abbr title ="Search Engine Friendly">SEF</abbr> URLs) for your board\'s pages',
'Setup URL legend'				=>	'Select a scheme',
'URL scheme info'				=>	'<strong>WARNING!</strong> If you select any scheme other than the default scheme you must copy/rename the file <em>.htaccess.dist</em> to <em>.htaccess</em> in the forum root directory. The server that hosts the forums must be configured with mod_rewrite support and must allow the use of <em>.htaccess</em> files. For servers other than Apache, please refer to your servers documentation.',
'URL scheme label'				=>	'URL scheme',
'URL scheme help'				=>	'Make sure you have read and understood the information above.',
'Setup links'					=>	'Add your own links to the main navigation menu',
'Setup links info'				=>	'By entering HTML hyperlinks into this textbox, any number of items can be added to the navigation menu at the top of all pages. The format for adding new links is X = &lt;a href="URL"&gt;LINK&lt;/a&gt; where X is the position at which the link should be inserted (e.g. 0 to insert at the beginning and 2 to insert after "User list"). Separate entries with a linebreak.',
'Setup links legend'			=>	'Menu items',
'Enter links label'				=>	'Enter your links',
'Error no board title'			=>	'You must enter a board title.',
'Error timeout value'			=>	'The value of "Online timeout" must be smaller than the value of "Visit timeout".',


// Features section
'Features general'				=>	'General PunBB features which are optional',
'Features general legend'		=>	'General features',
'Searching'						=>	'Searching',
'Search all label'				=>	'Allow users to search all forums instead of one forum at a time. Disable if server load is high due to excessive searching.',
'Load server'					=>	'(Отключите, если нагрузка на сервер слишком высокая или ваш форум очень загружен.)',
'User ranks'					=>	'User rank',
'User ranks label'				=>	'Включить ранги участников основаные на колличестве сообщений.',
'Censor words'					=>	'Censor',
'Censor words label'			=>	'Включить цензуру на определенные слова.',
'Quick jump'					=>	'Меню быстрого перехода',
'Quick jump label'				=>	'Включить выпадающий список быстрого перехода (переход к разделу).',
'Show version'					=>	'Show forum version',
'Show version label'			=>	'Показывать номер версии Flazy внизу страницы.',
'Online list'					=>	'Список активности',
'Users online label'			=>	'Отображать список гостей и зарегистрированных участников, находящихся на форуме.',
'Today online list'				=>	'Сегодня были',
'Today online label'			=>	'Отображать список участников которые посетили форум в течении суток.',
'Record list'					=>	'Рекорд пользователей',
'Record label'					=>	'Отображать список рекорда пользователей на главной странице.',
'Stats list'					=>	'Статистика',
'Stats label'					=>	'Отображать ссылки на статистику на главной странице и разрешить участникам её просматривать.',
'Online ft list'				=>	'Форум\тему просматривают',
'Online ft label'				=>	'Показывать список участников которые просматривают форум\тему в данный момент.',
'Features posting'				=>	'Особенности тем и сообщений, а также информации об участнике',
'Features posting legend'		=>	'Возможности сообщений',
'Quick post'					=>	'Быстрый ответ',
'Quick post label'				=>	'Включить форму быстрого ответа внизу темы.',
'Subscriptions'					=>	'Подписки',
'Subscriptions label'			=>	'Разрешить участникам подписываться на темы (получать e-mail, когда кто-нибудь отвечает в теме).',
'Guest posting'					=>	'Сообщения гостей',
'Guest posting label'			=>	'Гости должны обязательно вводить e-mail адрес, оставляя сообщение.',
'User has posted'				=>	'Ответы участника',
'User has posted label'			=>	'Отображать точку перед индикатором состояния темы, если участник отвечал в ней ранее.',
'Topic views'					=>	'Количество просмотров темы',
'Topic views label'				=>	'Отслеживать количество просмотров темы.',
'User post count'				=>	'Счетчик сообщений',
'User post count label'			=>	'Показывать счетчик сообщений участника в сообщениях, в профиле и в списке участников.',
'User info'						=>	'Информация об участнике',
'User info label'				=>	'Отображать местонахождение, дату регистрации, колличество сообщений, адреса e-mail, вебсайт, контакты участников под сообщениями.',
'Merge info'					=>	'Объединение сообщений',
'Merge info label'				=>	'Время в секундах в течении которого последующие сообщения от одного участника будут объединяться. Поставте 0 чтобы отключить. Поставьте 1 чтобы объединять сообщения всегда.',
'Enable bb panel'				=>	'Панель ББ-кодов',
'Enable bb panel label'			=>	'Отображать панель ББ-кодов над формой ввода сообщения.',
'BB panel smilies'				=>	'Колличество смайлов',
'BB panel smilies label'		=>	'Колличество смайлов на панеле ББ-кодов.',
'Features posts'				=>	'Тема и содержание сообщения',
'Features posts legend'			=>	'Настройки темы и содержания сообщений',
'Post content group'			=>	'Настройки сообщений',
'Allow BBCode label'			=>	'Разрешить BB-коды в сообщениях (рекомендуется)',
'Allow img label'				=>	'Разрешить тег (BB-код ) [img] в сообщениях.',
'Smilies in posts label'		=>	'Преобразовывать текстовые смайлы в графические в сообщениях.',
'Make clickable links label'	=>	'Преобразовывать URL-адреса в гиперссылки в сообщениях.',
'Post period label'				=>	'Время редактирования',
'Post period help'				=>	'Время в секундах для редактирования сообщения, без отображения «Отредактировано...».',
'Allow capitals group'			=>	'Заглавные буквы',
'All caps message label'		=>	'Разрешить сообщения, которые содержат только заглавные буквы.',
'All caps subject label'		=>	'Разрешить темы, которые содержат только заглавные буквы.',
'Indent size label'				=>	'Отступ тега [code]',
'Indent size help'				=>	'Количество пробелов для отступа. Если в значении 8, будет обычный отступ, как у других блоков.',
'Quote depth label'				=>	'Глубина цитирования',
'Quote depth help'				=>	'Максимальное количество вложений тега [quote], любое количество вложений, свыше указанного, будет отклонено.',
'Features reputation'			=>	'Репутация',
'Features reputation legend'	=>	'Настройки подписей',
'Allow reputation'				=>	'Разрешить репутацию',
'Allow reputation label'		=>	'Разрешить участникам менять репутацию.',
'Reputation timeout'			=>	'Таймаут репутации',
'Reputation timeout help'		=>	'Время в минута через которое участник может проголосовать снова.',
'Features sigs'					=>	'Подписи участников и их содержание',
'Features sigs legend'			=>	'Настройки подписей',
'Allow signatures'				=>	'Разрешить подписи',
'Allow signatures label'		=>	'Разрешить подписи участников под сообщениями.',
'Signature content group'		=>	'Содержание подписи',
'BBCode in sigs label'			=>	'Разрешить BB-код в подписях.',
'Img in sigs label'				=>	'Разрешить тег (BB-код ) [img] в подписях (не рекомендуется).',
'All caps sigs label'			=>	'Разрешить все заглавные буквы в подписях.',
'Smilies in sigs label'			=>	'Преобразовывать текстовые смайлы в графические в подписях.',
'Max sig length label'			=>	'Максимум символов',
'Max sig lines label'			=>	'Максисмум строк',
'Features Avatars'				=>	'Аватары участников (параметры загрузки и его размеры)',
'Features Avatars legend'		=>	'Настройки аватаров участников',
'Allow avatars'					=>	'Разрешить аватары',
'Allow avatars label'			=>	'Разрешить участникам загружать аватары для отображения в сообщениях.',
'Avatar Max width label'		=>	'Максимальная ширина',
'Avatar Max width help'			=>	'Пикселей (рекомендуется 120).',
'Avatar Max height label'		=>	'Максимальная высота',
'Avatar Max height help'		=>	'Пикселей (рекомендуется 120).',
'Avatar Max size label'			=>	'Максимальный размер',
'Avatar Max size help'			=>	'Байт (рекомендуется 20480).',
'Allow gravatar'				=>	'Использовать Gravatar',
'Gravatar disabled label'		=>	'Отключить Gravatar.',
'Gravatar G label'				=>	'C ретингом G, пригодны для показа на сайтах с любой аудиторией.',
'Gravatar PG label'				=>	'C рейтингом PG, могут содержать неприличные жесты, провокационно одетых людей, грубые выражения или умеренную жестокость, для аудитории старше 13 лет.',
'Gravatar R label'				=>	'C рейтингом R, могут быть оскорбительными, содержать сцены жестокости, обнаженные тела или связанные с наркотиками, для аудиторию старше 17 лет.',
'Gravatar X label'				=>	'C рейтингом X, могут содержать откровенные сексуальные изображения или сцены экстремальной жестокости.',
'Settings for polls'			=>	'Настройка голосования',
'Disable revoting'				=>	'Переголосование',
'Disable revoting info'			=>	'Разрешить участникам изменять свой голос.',
'Disable see results'			=>	'Просмотр результатов',
'Disable see results info'		=>	'Участники могут видеть результаты опроса без голосования.',
'Maximum answers info'			=>	'Максимум вариантов ответов в голосовании (2-100).',
'Maximum answers'				=>	'Максимум ответов',
'Poll min posts'				=>	'Минимум сообщений',
'Poll min posts info'			=>	'Минимум сообщений для голосования',
'Features title'				=>	'Личные сообщения',
'Inbox limit'					=>	'Ограничение входящих',
'Inbox limit info'				=>	'Максимальное колличество входящих сообщениц. 0 — без ограничений.',
'Outbox limit'					=>	'Ограничение исходящих',
'Outbox limit info'				=>	'Максимальное колличество исходящих сообщениц. 0 — без ограничений.',
'Navigation links'				=>	'Ссылка в меню',
'Snow new count'				=>	'Показать «Новые сообщения (Ч)» в верхней части каждой страницы.',
'Show global link'				=>	'Показывать ссылку на страницу личные сообщения в главное меню',
'Disable pm get mail'			=>	'Уведомления о сообщениях',
'Disable pm get mail info'		=>	'Включить отправку уведомлений о новом личном сообщении на электронную почту.',
'Google Analytics'				=>	'Add Google Analytics',
'Tracker'						=>	'ID in Google Analytics',
'Tracker help'					=>	'Ваш ID в Google Analytics (пример UA-6488859-1). Оставте пыстым если не хотите использовать.',
'Features update'				=>	'Проверять автоматически наличие обновлений',
'Features update info'			=>	'Если включена автоматическая проверка обновлений, Flazy будет периодически проверять, есть ли новые важные обновления. Включает в себя как релизы новых версий, так и хотфиксы расширений.',
'Features update disabled info'	=>	'Возможность автоматической проверки обновлений отключена. Для поддержки этой функции, PHP вашего сервера должен поддерживать <a href="http://www.php.net/manual/ru/ref.curl.php">cURL extension</a> и <a href="http://www.php.net/manual/ru/function.fsockopen.php">fsockopen() function</a> или быть сконфигурирован с поддержкой <a href="http://www.php.net/manual/ru/ref.filesystem.php#ini.allow-url-fopen">allow_url_fopen</a>.',
'Features update legend'		=>	'Автаматическое обновление',
'Update check'					=>	'Проверять обновления',
'Update check label'			=>	'Включить автоматическую проверку обновлений.',
'Features gzip'					=>	'Compress output using gzip',
'Features gzip legend'			=>	'Output compression',
'Features gzip info'			=>	'If enabled, PunBB will gzip the output sent to browsers. This will reduce bandwidth usage, but use a little more CPU. This feature requires that PHP is configured with zlib (--with-zlib). Note: If you already have one of the Apache modules mod_gzip or mod_deflate set up to compress PHP scripts, you should disable this feature.',
'Enable gzip'					=>	'Enable gzip',
'Enable gzip label'				=>	'Enable output compression using gzip.',

// Announcements section
'Announcements head'			=>	'Display an announcement on each page of your board',
'Announcements legend'			=>	'Announcement',
'Enable announcement'			=>	'Enable announcement',
'Enable announcement label'		=>	'Display an announcement message.',
'Announcement heading label'	=>	'Announcement heading',
'Announcement message label'	=>	'Announcement message',
'Announcement message help'		=>	'You may use HTML in your message. Announcements are not parsed like posts.',
'Announcement message default'	=>	'<p>Enter your announcement here.</p>',
'HTML legend'					=>	'HTML коды',
'HTML head'						=>	'HTML коды — будут отображаться на всех страницах вашего форума',
'Enable HTML top'				=>	'Отображать HTML верх',
'HTML label'					=>	'Показывать HTML коды',
'HTML top part'					=>	'HTML верх',
'HTML top help'					=>	'Сообщение будет отображаться в шапке, на самом верху форума. <p>Вы можете использовать HTML в вашем сообщении. Предайте шапке форума индивидуальность.</p>',
'Enable HTML bottom'			=>	'Отображать HTML низ',
'HTML bottom part'				=>	'HTML низ',
'HTML bottom help'				=>	'Сообщение будет отображаться в самом низу форума. <p>Вы можете использовать. HTML в вашем сообщении. Подойдет для вставки кодов кнопок и баннеров.</p>',
'HTML message default'			=>	'Введите HMTL код',
'Adbox legend'					=>	'HTML коды',
'Ad head'						=>	'Рекламные сообщения — будут отображаться на всех страницах вашего форума',
'Enable Adbox'					=>	'Рекламное сообщение',
'Adbox label'					=>	'Показывать HTML коды',
'Adbox part'					=>	'Текст сообщения',
'Adbox help'					=>	'Сообщение будет отображаться на всех страницах вашего форума.',
'Adbox message default'			=>	'<p>Введите здесь текст. Это сообщение увидят все посетители</p>',
'Enable Guestbox'				=>	'Гостевое сообщение',
'Guestbox help'					=>	'Сообщение будет отображаться только для гостей на всех страницах вашего форума.',
'Guestbox message default'		=>	'<p>Если вы хотите получить доступ к всем разделам форума, необходимо <a href="login.php">войти</a> или <a href="register.php">зарегистрироваться</a></p>',
'Enable Topicbox'				=>	'Блок под сообщением',
'Topic legend'					=>	'Число обозначает после какого сообщения в теме будет отображаться блок. Поставте 0 если хотите его выключить.',
'Topicbox help'					=>	'Сообщение будет отображаться под сообщением в теме.',
'Enable Externbox'				=>	'Блок в синдикациях',
'Externbox help'				=>	'Сообщение будет отобржаться в RSS и Atom лентах после основного текста.',
'HTML help'						=>	' Вы можете использовать HTML в вашем сообщении. Идеально подходит для вставки рекламных объявлений, но может использоваться и в любой другой сфере.',

// Registration section
'Registration new'				=>	'New registrations',
'New reg info'					=>	'Регистрация новых участников',
'New reg info'					=>	'Вы можете выбрать, требовать ли от пользователей подтверждения регистрации по e-mail. Когда подтверждение регистрации включено пользователи получают письмо с кодом активации на свой e-mail после процедуры регистрации. По e-mail они также смогут восстанавливать пароли. Если пользователь захочет изменить e-mail после регистрации, то ему также придется отдельно подтвердить это, перейдя по ссылке активации в письме. Это эффективный метод для пресечения регистраций роботов, а также эффективный метод стимуляции пользователей указывать настоящий e-mail при регистрации.',
'Registration new legend'		=>	'Настройки новых регистраций',
'Allow new reg'					=>	'Новые регистрации',
'Allow new reg label'			=>	'Разрешить регистрацию новых участников. Отключайте только по особым обстоятельствам.',
'Verify reg'					=>	'Подтверждать регистрации',
'Verify reg label'				=>	'Требовать подтверждения от всех вновь зарегистрированных участников по e-mail.',
'Reg e-mail group'				=>	'E-mail адрес регистрации',
'Allow banned label'			=>	'Разрешить регистрацию с адресом e-mail, который находится в черном списке (забанен).',
'Allow dupe label'				=>	'Разрешить регистрацию с адресом e-mail, который уже принадлежит другому участнику.',
'Report new reg'				=>	'Уведомлять по e-mail',
'Report new reg label'			=>	'Уведомлять лиц в списке рассылки о регистрации новых участников на форуме.',
'E-mail setting group'			=>	'Базовые настройки e-mail',
'Display e-mail label'			=>	'Показывать e-mail адрес другим участникам.',
'Allow form e-mail label'		=>	'Скрывать e-mail адрес, но разрешить отправлять e-mail сообщения через форум.',
'Disallow form e-mail label'	=>	'Скрывать e-mail адрес и запретить отправлять e-mail сообщения через форум.',
'Registration timeout'			=>	'Время регистрации',
'Registration timeout help'		=>	'Позволяет устанавливать перерыв между регистрацией с одного IP адреса (в секундах).',
'Spam check info'				=>	'Вы можете проверять IP-адрес, e-mail, имя в крупнейшей базе спамеров — <a href="http://www.stopforumspam.com">Stop Forum Spam</a>. Участнику чей IP-адрес, e-mail или имя находится в базе будет отказано в регистрации. Проверка прозрачна и не требует от регистранта никаких лишних дейстий.',
'Spam check legend'				=>	'Включить блокировку',
'Spam ip info'					=>	'Проверять IP-адрес на нахождение в базе спамеров.',
'Spam email info'				=>	'Проверять адрес e-mail на нахождение в базе спамеров.',
'Spam name info'				=>	'Проверять имя на нахождение в базе спамеров.',
'Registration rules'			=>	'Правила форума (использование и оформление правил форума)',
'Registration rules info'		=>	'Вы можете обязать пользователей принимать правила форума при регистрации (напишите их в текстовом поле ниже). Правила всегда будут доступны для просмотра по ссылке из главного меню на каждой странице форума.',
'Registration rules legend'		=>	'Правила форума',
'Require rules'					=>	'Соглашение с правилами',
'Require rules label'			=>	'Обязать участников принимать правила форума перед процедурой регистрации.',
'Compose rules label'			=>	'Правила',
'Compose rules help'			=>	'Вы можете использовать HTML в этом блоке. Оставьте пустым, чтобы не использовать правила.',
'Rules default'					=>	'Введите сюда ваши правила.',
'Username'						=>	'Username',
'User added'   					=>	'Участник добавлен успешно.',
'Username help'  				=>	'От 2 до 25 символов.',
'E-mail help'					=>	'Введите текущий и действующий адрес электронной почты.',
'Edit user'						=>	'Редактировать участника',
'Edit help'						=>	'Редактировать информацию об участнике после добавления.',
'Add user'						=>	'Добавить участника',
'There are some errors'			=>	'<strong>Внимание!</strong> Следующие ошибки необходимо исправить, прежде чем вы сможете добавить участника:',

// Email section
'E-mail head'					=>	'Forum e-mail addresses and mailing list',
'E-mail addresses legend'		=>	'E-mail addresses',
'Admin e-mail'					=>	'Administrator\'s e-mail',
'Webmaster e-mail label'		=>	'Webmaster e-mail',
'Webmaster e-mail help'			=>	'The "from" address of e-mails sent by the forum',
'Mailing list label'			=>	'Create mailing list',
'Mailing list help'				=>	'A comma separated list of recipients of reports and/or new registration notifications.',
'E-mail server'					=>	'Mail server configuration for sending e-mails from the forum',
'E-mail server legend'			=>	'E-mail server',
'E-mail server info'			=>	'In most cases Flazy will be able to send e-mail using your local e-mail program in which case you can ignore the following settings. Flazy can be configured to use an external mail server. Enter the address of the external server and, if required, specify a custom port number if the SMTP server doesn\'t run on the default port 25 (example: mail.example.com:3580).',
'SMTP address label'			=>	'SMTP server address',
'SMTP address help'				=>	'For external servers. Leave blank to use local mail program',
'SMTP username label'			=>	'SMTP server username',
'SMTP help'						=>	'Not required by most SMTP servers',
'SMTP password label'			=>	'SMTP server password',
'SMTP SSL'						=>	'Encrypt SMTP using SSL',
'SMTP SSL label'				=>	'If your version of PHP supports SSL and your SMTP server requires it.',
'Error invalid admin e-mail'	=>	'The admin e-mail address you entered is invalid.',
'Error invalid web e-mail'		=>	'The webmaster e-mail address you entered is invalid.',
'Error no subject'				=>	'Вы не ввели тему письма.',
'Error no massage'				=>	'Вы не ввели тело письма.',
'Error no group'				=>	'Вы не выбрали группу.',
'Error no partition'			=>	'Вы не указали разбивку на части.',
'Mass e-mail'					=>	'Массовая рассылка e-mail',
'Mass subject label'			=>	'Тема',
'Mass massage label'			=>	'Сообщение',
'Mass recipient label'			=>	'Получатели',
'Mass partition label'			=>	'Разбить',
'Mass partition help'			=>	'Вы можете разбить отправку писем на несколько частей. Введите число, участников в одном отправлении. В дальшейшем при завершении отправки одной части нужно повторно нажать «Отправить» для отправки следующий части. Введите 0 если не хотите разбивать рассылку.',
'All group'						=>	'Все группы',
'Preview'						=>	'Преварительный просмотр',
'Preview mail'					=>	'Массовая рассылка — подтверждение',
'Successfully sent'				=>	'Успешно отправлено',
'Сlick only once'				=>	'Пожалуйста нажмите кнопку только один раз. Дождитесь сообщения о результате.',

// Maintenance section
'Maintenance head'				=>	'Setup maintenance message and activate maintenance mode',
'Maintenance mode info'			=>	'<strong>IMPORTANT!</strong> Putting the board into maintenance mode means it will only be available to administrators. This should be used if the board needs to taken down temporarily for maintenance.',
'Maintenance mode warn'			=>	'<strong>WARNING!</strong> DO NOT LOGOUT when the board is in maintenance mode. You will not be able to login again.',
'Maintenance legend'			=>	'Maintenance',
'Maintenance mode'				=>	'Maintenance mode',
'Maintenance mode label'		=>	'Put board into maintenance mode.',
'Maintenance message label'		=>	'Maintenance message',
'Maintenance message help'		=>	'The message to be shown when the board is in maintenance mode. You may use the default message provided or compose your own. You may use HTML in your message.',
'Maintenance message default'	=>	'The forums are temporarily down for maintenance. Please try again in a few minutes.<br /><br />Administrator',

);