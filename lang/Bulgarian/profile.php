<?php
/**
 * Български езиков пакет.
 * @package Flazy_Bulgarian
 */

/** Езиковият пакет се използва в  profile.php и register.php */
$lang_profile = array(

// Navigation and sections
'Profile redirect'			=>	'Профилът е обновен. Пренасочване…',
'Instructions'				=>	'Ще бъдете пренасочени обратно към тази страница',
'Update profile'			=>	'Обнови профила',

// Administration stuff
'User delete redirect'		=>	'Потребителя е изтрит. Пренасочване…',
'Section admin'				=>	'Администрация',
'Delete user'				=>	'Изтриване на потребителя',
'Delete warning'			=>	'<strong>Внимание!</strong> Премахването на даден потребител или съобщение не може да бъде възстановено',
'Delete posts info'			=>	'<strong>Внимание!</strong> Ако решите да не изтривате съобщенията, публикувани от потребителите, те могат да бъдат изтрити по-късно, на ръка.',
'Delete posts'				=>	'Изтриване на поста',
'Delete posts label'		=>	'Изтриване на всички теми и мнения, публикувани от потребителите %s.',
'Group membership redirect'	=>	'Изменено членство группы. Перенаправление…',
'Moderate forums redirect'	=>	'Права модерирования форума обновлены. Перенаправление…',
'Ban redirect'				=>	'Перенаправление…',
'Ban user'					=>	'Заблокировать участника',
'Ban user info'				=>	'Заблокировать участника через админ-панель',
'Delete user info'			=>	'Навсегда удалить этого участника и все его сообщения',
'User management'			=>	'Управление участником',
'Group membership'			=>	'Состав группы',
'User group'				=>	'Назначить в группу',
'Moderator assignment'		=>	'Назначение модератора',
'Cannot delete admin'		=>	'Администраторы не могут быть удалены. Для того чтобы удалить этого участника, вы должны сначала переместить его в другую группу.',
'Update groups'				=>	'Обновление группы участника',
'Update forums'				=>	'Обновление назначений модератора',
'Reputation adm'			=>	'Изменить настройки репутации',
'Disable reputation'		=>	'Отключить репутацию',
'Disable reputation help'	=>	'Если вы отключите систему репутации, то учасник не сможете менять репутацию другим участникам и просматривать её.',

// Блок "Аватар"
'Avatar welcome'			=>	'Задайте аватар за да изградите вашата идентичност във форума',
'Avatar welcome user'		=>	'Настройване на изображението за аватар в форума %s',
'Avatar deleted redirect'	=>	'Аватарът е изтрит. Пренасочване …',
'Avatars disabled'			=>	'Администраторът е деактивирал аватарите.',
'Avatar info'				=>	'Аватара — e малко изображение, което ще се появи под името ви в публикациите в този форум.',
'No file'					=>	'Не сте избрали файл за качване.',
'Too large ini'				=>	'Избраният файл е твърде голям.',
'Partial upload'			=>	'Избраният файл беше качен само частично. Моля опитайте отново.',
'No tmp directory'			=>	'PHP не може да запази качения файл във временна директория.',
'Bad type'					=>	'Файлът, който се опитвате да качите не е от разрешен тип. Разрешените типове са gif, jpeg и png.',
'Too large'					=>	'Файлът, който се опита да се качите по-голям от максимално разрешения %s bytes.',
'Move failed'				=>	'Сървъра не може да обработи файла. Моля свържете се с администратор - %s.',
'Too wide or high'			=>	'Изображението което се опитвате да качите е по-широко и/или високо от максимално разрешените %sx%s pixels.',
'Unknown failure'			=>	'Възникна грешка. Моля опитайте отново.',
'Avatar'					=>	'Аватар',
'Current avatar'			=>	'Сегашен аватар',
'No avatar info'			=>	'Няма качен аватар.',
'Avatar info replace'		=>	'Качването на нов аватар ще замени настоящия.',
'Avatar info none'			=>	'За да се показва аватар, първо трябва да го качите.',
'Avatar info type'			=>	'Разрешените типове изображения са gif, jpeg и png.',
'Avatar info size'			=>	'Максималната големина на картинка е %sx%s pixels и %s bytes (%s KB).',
'Delete avatar info'		=>	'Изтрийте аватара за да спре да се показава.',
'Upload avatar file'		=>	'Качете аватар',
'Avatar upload help'		=>	'Изберете файл, след това обновете профила си за да се инсталира аватара.',
'No upload warn'			=>	'<strong>Важно!</strong> Трябва да изберете файл за качване преди да обновите профила си.',


// Блок "Общие" и другие конструкции
'Users profile'				=>	'Профил на %s',
'Section about'				=>	'Представяне',
'Profile welcome'			=>	'Добре дошли в вашия профил',
'Profile welcome user'		=>	'Добре дошли в профила на %s',
'Identity welcome'			=>	'Променете личните си данни и данните за контакт',
'Identity welcome user'		=>	'Промяна на лична и контактна информация',
'View your posts'			=>	'Вижте всичките си мнения',
'View your topics'			=>	'Вижте всичките си теми',
'View user posts'			=>	'Вижте всички мнения на %s',
'View user topics'			=>	'Вижте всички теми на %s',
'View your subscriptions'	=>	'Вижте всичките си абонаменти',
'View user subscriptions'	=>	'Вижте всички абонаменти %s',
'Realname'					=>	'Истинско име',
'Sex'						=>	'Пол',
'Male'						=>	'Мъжки',
'Female'					=>	'Женски',
'Do not show'				=>	'Да не се показва',
'Unknown'					=>	'(Неизвестен)',
'Location'					=>	'Местонахождение',
'From'						=>	'От',
'Country'					=>	'Страна',
'Registered'				=>	'Регистриран',
'Website'					=>	'Уеб сайт',
'IP'						=>	'IP',
'Note'						=>	'Забележка',
'Posts'						=>	'Мнения',
'Posts in day'				=>	'(%s на ден)',
'Last post'					=>	'Последно мнение',
'Last visit'            	=>	'Последно посещение',
'Send forum e-mail'			=>	'Изпрати мейл на този потребител чрез форума',
'Contact info'				=>	'Информация за контакти',
'Jabber'					=>	'Jabber',
'ICQ'						=>	'ICQ',
'MSN'						=>	'MSN Messenger',
'AOL IM'					=>	'AOL IM',
'Yahoo'						=>	'Yahoo! Messenger',
'Skype'						=>	'Skype',
'Mail Agent'				=>	'Mail Agent',
'Vkontakte'					=>	'Vkontakte',
'Сlassmates'				=>	'Одноклассники',
'Mirtesen'					=>	'Mirtesen',
'Moikrug'					=>	'Moikrug',
'Facebook'					=>	'Facebook',
'Twitter'					=>	'Twitter',
'Last.fm'					=>	'Last.fm',
'Forbidden title'			=>	'Обръщението съдържа непозволена дума.',
'Bad ICQ'					=>	'Въведохте неваледен ICQ номер.',
'Bad url page'				=>	'Невалиден адрес лична уеб страница «%s».',
'Posts and topics'			=>	'Теми и мнения',
'Private info'				=>	'Лична информация',
'Current signature'			=>	'Текущ подпис',
'Section identity'			=>	'Идентификация',
'Section settings'			=>	'Настройки',
'Section avatar'			=>	'Аватар',
'Section signature'			=>	'Подпис',
'Personal legend'			=>	'Лични данни',
'Title'						=>	'Статус',
'Leave blank'				=>	'Оставете го празно, за да използвате настройките по подразбиране форум.',
'Edit count'				=>	'Брой на съобщенията',
'Admin note'				=>	'Забележка: Админ',
'Contact legend'			=>	'Данни за контакт',

// Блок "Настройки"
'Local settings'			=>	'Локализационни настройки',
'Settings welcome'			=>	'Локализация, изглед и мейл настройки',
'Settings welcome user'		=>	'Промяната на настройките за локализация, картографиране и електронна поща',
'Timezone info'				=>	'Трябва да е настроено за коректно показване на часа.',
'Time format'				=>	'Формат на часа',
'Default'					=>	'подразбиране',
'Date format'				=>	'Формат на датата',
'Display settings'			=>	'Настройка на изгледа',
'Styles'					=>	'Стилове',
'Image display'				=>	'Показване на изборажения',
'Show avatars'				=>	'Показване на потребителските изображения в подписите им',
'Show images sigs'			=>	'Показване на изображения в потребителските подписи.',
'Show images'				=>	'Показвай изображения в мненията.',
'Show sigs'					=>	'Показвай подписа в мненията.',
'Show smilies'				=>	'Показвай емотикони.',
'Show bb panel'				=>	'Показвай панела с BB-кодове.',
'Signature display'			=>	'Показване на подпис',
'BB panel display'			=>	'Панел с BB-кодове',
'Pagination settings'		=>	'Разделане на страници',
'Topics per page'			=>	'Брой теми на страница',
'Posts per page'			=>	'Брой мнения на страница',
'E-mail and sub settings'	=>	'Настройки на E-mail и абонаментите',
'Subscription settings'		=>	'Настройка подписки',
'Notify full'				=>	'Включване на копие от мнението в уведомителния мейл.',
'Subscribe by default'		=>	'Абониране за теми в които публикувате по подразбиране.',
'Manage reputation'			=>	'Отображение репутации',
'Manage reputation label'	=>	'Можете да изключите репутацията на системата, а след това никой не може да го промените. Ако решите да деактивирате репутацията на системата, а след това не можете да промените външния вид и доброто име на другиго.',
'Security level'			=>	'Ниво на защитаи',
'High security'				=>	'Високо (автоматично разрешение IP адрес).',
'Medium security'			=>	'Средно (автоматично разрешение да се свързва с подмрежата IP адрес).',
'Low security'				=>	'Ниско (IP адрес на автоматично разрешение не е проверен).',
'UTC-12:00'					=>	'(UTC-12:00) International Date Line West',
'UTC-11:00'					=>	'(UTC-11:00) Niue, Samoa',
'UTC-10:00'					=>	'(UTC-10:00) Hawaii-Aleutian, Cook Island',
'UTC-09:30'					=>	'(UTC-09:30) Marquesas Islands',
'UTC-09:00'					=>	'(UTC-09:00) Alaska, Gambier Island',
'UTC-08:00'					=>	'(UTC-08:00) Pacific',
'UTC-07:00'					=>	'(UTC-07:00) Mountain',
'UTC-06:00'					=>	'(UTC-06:00) Central',
'UTC-05:00'					=>	'(UTC-05:00) Eastern',
'UTC-04:00'					=>	'(UTC-04:00) Atlantic',
'UTC-03:30'					=>	'(UTC-03:30) Newfoundland',
'UTC-03:00'					=>	'(UTC-03:00) Amazon, Central Greenland',
'UTC-02:00'					=>	'(UTC-02:00) Mid-Atlantic',
'UTC-01:00'					=>	'(UTC-01:00) Azores, Cape Verde, Eastern Greenland',
'UTC'						=>	'(UTC) Western European, Greenwich',
'UTC+01:00'					=>	'(UTC+01:00) Central European, West African',
'UTC+02:00'					=>	'(UTC+02:00) Eastern European, Central African',
'UTC+03:00'					=>	'(UTC+03:00) Moscow, Eastern African',
'UTC+03:30'					=>	'(UTC+03:30) Iran',
'UTC+04:00'					=>	'(UTC+04:00) Gulf, Samara',
'UTC+04:30'					=>	'(UTC+04:30) Afghanistan',
'UTC+05:00'					=>	'(UTC+05:00) Pakistan, Yekaterinburg',
'UTC+05:30'					=>	'(UTC+05:30) India, Sri Lanka',
'UTC+05:45'					=>	'(UTC+05:45) Nepal',
'UTC+06:00'					=>	'(UTC+06:00) Bangladesh, Bhutan, Novosibirsk',
'UTC+06:30'					=>	'(UTC+06:30) Cocos Islands, Myanmar',
'UTC+07:00'					=>	'(UTC+07:00) Indochina, Krasnoyarsk',
'UTC+08:00'					=>	'(UTC+08:00) Greater China, Australian Western, Irkutsk',
'UTC+08:45'					=>	'(UTC+08:45) Southeastern Western Australia',
'UTC+09:00'					=>	'(UTC+09:00) Japan, Korea, Chita',
'UTC+09:30'					=>	'(UTC+09:30) Australian Central',
'UTC+10:00'					=>	'(UTC+10:00) Australian Eastern, Vladivostok',
'UTC+10:30'					=>	'(UTC+10:30) Lord Howe',
'UTC+11:00'					=>	'(UTC+11:00) Solomon Island, Magadan',
'UTC+11:30'					=>	'(UTC+11:30) Norfolk Island',
'UTC+12:00'					=>	'(UTC+12:00) New Zealand, Fiji, Kamchatka',
'UTC+12:45'					=>	'(UTC+12:45) Chatham Islands',
'UTC+13:00'					=>	'(UTC+13:00) Tonga, Phoenix Islands',
'UTC+14:00'					=>	'(UTC+14:00) Line Islands',

// Личные сообщения
'Private messages'			=>	'Лични съобщения',
'Send PM'					=>	'Изпращане на лично съобщение',
'Begin message quote'		=>	'Покажи начало на лично съобщение в списъка на съобщенията.',
'Get mail'					=>	'Получаване на известия за нови лични съобщения, електронна поща.',

// Блок изменения пароля
'Change pass info'			=>	'От съображения за сигурност Ви препоръчваме да използвате комбинация от букви, цифри и специални символи.',
'Change pass errors'		=>	'<strong>Внимание!</strong> следните грешки трябва да бъдат коригирани преди да можете да смените паролата си:',
'Pass logout'				=>	'Потребителят е логнат в момента. Моля излезте първо.',
'Pass key bad'				=>	'Активационният ключ е грешен или изтекъл. Моля пуснете нова заявка заявка и ако отново получите грешка се свържете с амдинистратор - %s.',
'Pass updated'				=>	'Паролата е обновена. Можете да влезете с новата парола. Пренасочване …',
'Change your password'		=>	'Сменете паролата си',
'Change user password'		=>	'Сменете паролата си %s',
'Old password'				=>	'Стара парола',
'Old password help'			=>	'Необходимо е да въведете досегашната си парола.',
'New password'				=>	'Нова парола',
'Confirm new password'		=>	'Потвърдете новата парола',
'Wrong old password'		=>	'Старата парола е грешна.',
'Pass updated redirect'		=>	'Паролата е актуализирана. Пренасочване …',

// Блок изменения электронной почты
'Change e-mail errors'		=>	'<strong>Важно!</strong> Следните грешки трябва да бъдат коригирани преди да бъде сменен e-mail адреса:',
'E-mail key bad'			=>	'Ключа за активиране на e-mail е невалиден или изтекъл. Моля пуснете нова заявка за промяна и ако опитът отново се провали, свържете се с администратор - %s.',
'E-mail updated'			=>	'Вашият e-mail адрес беше обновен.',
'E-mail updated redirect'	=>	'Вашият e-mail адрес е актуализиран. Пренасочване ...',
'Wrong password'			=>	'Въведената парола е грешна.',
'Activate e-mail sent'		=>	'На указания email адрес е изпратено съобщение с инструкции как да активирате новия e-mail.',
'Change your e-mail'		=>	'Променете вашия e-mail адрес',
'Change user e-mail'		=>	'Променете вашия %s e-mail address',
'New e-mail'				=>	'Нов e-mail адрес',

// Блок "подпись"
'Sig welcome'				=>	'Създайте или редактирайте подпис, който ще се показва в подписа ви',
'Sig welcome user'			=>	'Създаване или промяна на подпис за показване в %s мнения',
'Signature info'			=>	'Подпис - това е малък послепис, приложена към съобщенията си. Това може да бъде всичко, което пожелаете. Например, от вашия любим цитат или идол автограф. Вие решавате!',
'Signatures disabled'		=>	'Администраторът е забранил подписите.',
'Sig too long'				=>	'Подписите не може да са по-дълги от %1$s знака. Моля намалете подписа си с %2$s знака.',
'Sig too many lines'		=>	'Подписите не могат да имат повече от %s реда.',
'Signature'					=>	'Подпис',
'Compose signature'			=>	'Създайте подпис',
'Sig max size'				=>	'Максимум %s знака и %s реда.',

// Блок регистрации (некоторые из них также используется в профиле)
'No new regs'				=>	'Регистрациите са временно спрени. Моля, заповядайте по-късно.',
'No regs spam'				=>	'Вашата регистрация е отказана, защото сте много сходни с бота. Ако не, обърнете се към администрацията',
'Reg cancel redirect'		=>	'Регистрацията е отмненена. Пренасочване...',
'Agreement'					=>	'Споразумение',
'Agreement label'			=>	'Съгласен съм с правилата, посочени по-горе и желая да бъда регистриран в този форум.',
'Agree'						=>	'Съгласен',
'Reg agree fail'			=>	'Трябва да се съгласите с правилата, ако искате да се регистрирате.',
'Registration flood'		=>	'Вече има регистриран потребител с същия IP адрес. За да се регистрирате отново, моля свържете се с администратор за повече информаци.',
'Pass too short'			=>	'Вашата парола трябва да бъде поне с 4 символа. Моля изберете друга парола.',
'Pass not match'			=>	'Паролите не съвпадат.',
'E-mail not match'			=>	'E-mail адреса не съвпада.',
'Banned e-mail'				=>	'Имейл адреса, който сте въвели е баннат и не може да се използва. Моля изберете друг имейл.',
'Dupe e-mail'				=>	'Вече има регистриран потребител с този имейл, моля изберете друг.',
'Blocked spamer'		=>	'Анализът на данните показва, че вие сте спамър, ако мислите, че това е грешка, моля посетете <a href="http://www.stopforumspam.com"> Stop форум Spam.com</a>.',

'Reg e-mail'				=>	'Благодаря Ви за регистрацията. Вашата парола е изпратена на имейла, посочен по време на регистрация. Ако той не дойде, моля свържете се с администратор — %s.',
'Reg complete'				=>	'Регистрацията е извършена успешно. Вече сте член на форума.',
'Register errors'			=>	'<strong>Внимание!</strong> Следните грешки трябва да бъдат коригирани преди да продължите с регистрацията:',
'E-mail info'				=>	'<strong>Важно!</strong> Случайно генерирана парола ще бъде изпратена на вашият имейл адрес.',
'Reg e-mail info'			=>	'<strong>Важно!</strong> Моля въведете <i>валиден</i> имейл адрес, защото там ще получите връзка за активация на вашият акаунт. 
<font size="3" face="arial" color="red"><br>ЗАБЕЛЕЖКА: Писмата при abv пощите може да имат забавяне до 10-15 минути. Ако и до тогава не получите имейл свържете се с администратор на имейл - <font size="3" face="arial" color="RoyalBlue">webmaster@flazy-bg.net</br></font></font>',
'Register at'				=>	'Регистриран на %s',
'Register intro'			=>	'Регистрацията ви позволява да използвате функции, недостъпни при гледане на форума като гост. Попълнете всички полета по-долу и станете член на форума. Поздрави, екип <b>flazy-bg.net</b>',
'Username'					=>	'Потребителско име',
'Username help'				=>	'От 2 до 25 символа.',
'Password'					=>	'Парола',
'Password help'				=>	'Минимум 4 символа.',
'Confirm password'			=>	'Потвърдете паролата',
'Confirm password help'		=>	'Потвърдете паролата по същия начин, както преди',
'E-mail'					=>	'E-mail',
'E-mail help'				=>	'Въведете валиден имейл адрес',
'E-mail activation help'	=>	'Въведете настоящия си имейл адрес. На този адрес ще бъде изпратен линк за активиране, който съдържа връзка, за потвърждение на вашият акаунт.',
'Confirm e-mail'			=>	'Повторете имейла',
'Confirm e-mail help'		=>	'Въведете отново електронната си поша.',
'Optional legend'			=>	'Допълнителни опции',
'Language'					=>	'Език',
'Timezone'					=>	'Часови пояс',
'Timezone help'				=>	'Задайте вашият часови пояс',
'Adjust for DST'			=>	'Лятно време',
'DST label'					=>	'Автоматично преминаване към лятно часово време',
'E-mail settings'			=>	'Настройки на E-mail\'a',
'E-mail setting 1'			=>	'Вашият е-мейл адрес ще бъде видим за останалите членове на форума.',
'E-mail setting 2'			=>	'Скриване на вашия е-мейл адрес, но позволява на други да Ви изпращат имейл съобщения чрез този форум.',
'E-mail setting 3'			=>	'Скриване на вашия е-мейл адрес, и забрана на други да Ви изпращат имейл съобщения чрез този форум.',
'Reg rules head'			=>	'Трябва да се съгласите с правилата, за да продължите',
'Register'					=>	'Регистрирай ме', // Кнопка

// Блок форм проверок
'Profile update errors'		=>	'<strong>Важно!</strong> Трябва да коригирате следните грешки, за да може този профил да бъде актуализиранактуализиран:',
'Username BBCode'			=>	'Потребителското име не може да съдържа никой от (BBCode) таговете използвани във форума. Моля изберете друго име.',
'Username IP'				=>	'Потребителските имена не могат да бъдат под формата на IP адрес. Моля, изберете друго име.',
'Username censor'			=>	'Въведеното потребителско име съдържа една или повече цензорирани думи. Моля изберете друго име.',
'Username dupe'				=>	'Някой вече е регистриран с потребителското име %s или двете имена са твърде сходни. Двете имена трябва да се различават поне с един знак (a-z or 0-9). Моля изберете друго име.',
'Username guest'			=>	'Потребителското име guest е резервирано. Изберете друго име.',
'Username reserved chars'	=>	'Потребителските имена не могат да съдържат знаци като \', " и [ или ] едновременно. Моля изберете друго име.',
'Username too long'			=>	'Потребителските имена не може да са по-дълги от 25 знака. Моля изберете по-кратко име.',
'Username too short'		=>	'Потребителските имена трябва да са с дължина най-малко 2 знака. Моля изберете по-дълго име.',
'Signature quote/code/lis'		=>	'Кавички и code BBcode тагове не са разрешени в подписа.',
'Invalid e-mail'			=>	'Въведения e-mail адрес е невалиден.',

);