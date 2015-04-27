<?php
/**
 * Русский языковой пакет.
 * @package Flazy_Russian
 */

/** Языковые конструкции используемые на странице создания\изменения групп */
$lang_admin_groups = array(

'Group settings heading'		=>	'Настройки доступа для групп, которые вступают в силу для форума, если не были чётко определены права доступа',
'Group title label'				=>	'Название группы',
'User title label'				=>	'Статус участников',
'Group title head'				=>	'Названия для групп участников',
'Group title legend'			=>	'Установить названия',
'Group perms head'				=>	'Настройки доступа для группы',
'Group flood head'				=>	'Настройки защиты от флуда для группы',
'User title help'				=>	'Статус будет использоваться вместо ранга. Оставьте пустым, чтобы использовать станартный статус или ранг.',
'Remove group legend'			=>	'Удалить группу',
'Permissions'					=>	'Настройки доступа',
'Moderation'					=>	'Модерирование',
'Allow moderate label'			=>	'Выдать участнику права модератора.',
'Allow mod edit profiles label'	=>	'Разрешить модераторам редактировать профили участников.',
'Allow mod edit username label'	=>	'Разрешить модераторам переименовывать участников.',
'Allow mod change pass label'	=>	'Разрешить модераторам менять пароли участников.',
'Allow mod bans label'			=>	'Разрешить модераторам банить участников.',
'Allow read board label'		=>	'Участники могут смотреть форум.',
'Allow read board help'			=>	'Эта опция применима ко всему форуму и если она отключена, то не отменяет индивидуальные настройки доступа разделов. Если опция отключена, участники в этой группе смогут только заходить/выходить и регистрироваться.',
'Allow view users label'		=>	'Разрешить участникам смотреть списки и профили других участников.',
'Allow post replies label'		=>	'Разрешить участникам оставлять сообщения в темах.',
'Allow post topics label'		=>	'Разрешить участникам создавать новые темы.',
'Allow edit posts label'		=>	'Разрешить участникам редактировать их собственные сообщения.',
'Allow delete posts label'		=>	'Разрешить участникам удалять их собственные сообщения.',
'Allow delete topics label'		=>	'Разрешить участникам удалять созданные ими темы (включая все ответы).',
'Allow poll add label'			=>	'Разрешить участникам создавать опросы.',
'Allow set user title label'	=>	'Разрешить участникам задавать себе статус.',
'Allow use search label'		=>	'Разрешить участникам пользоваться поиском.',
'Allow search users label'		=>	'Разрешить участникам искать других участников.',
'Allow send email label'		=>	'Разрешить участникам отправлять e-mail-сообщения друг другу.',
'Allow reputation label'		=>	'Разрешить участникам изменять репутацию.',
'Group rep head'				=>	'Настройка репутации для группы',
'Min messages plus'				=>	'Минимум для плюса',
'Min messages plus help'		=>	'Минимальное количество сообщений необходимо, чтобы участники этой группы могли изменить репутацию в плюс для других участников.',
'Min messages minus'			=>	'Минимум для минуса',
'Min messages minus help'		=>	'Минимальное количество сообщений необходимо, чтобы участники этой группы могли изменить репутацию в минус для других участников.',
'Restrictions'					=>	'Ограничения',
'Mod permissions'				=>	'Права модераторов',
'User permissions'				=>	'Права участников',
'Flood interval label'			=>	'Защита от флуда',
'Flood interval help'			=>	'Количество секунд, которое должны ждать участники, состоящие в этой группе, прежде чем написать следующее сообщение. Введите 0, чтобы отключить защиту.',
'Search interval label'			=>	'Защита поиска от флуда',
'Search interval help'			=>	'Количество секунд, которое должны ждать участники, состоящие в этой группе, прежде чем начать новый поиск. Введите 0, чтобы отключить защиту.',
'Email flood interval label'	=>	'Интервал email-флуда',
'Email flood interval help'		=>	'Количество секунд, которое должны ждать участники, состоящие в этой группе, перед отправкой каждого следующего e-mail. Укажите "0" для отключения этой функции.',
'Allow moderate help'			=>	'Для того, чтобы участник в этой группе имел возможности модератора, он должен быть назначен модератором хотя бы в одном из форумов. Это можно сделать на странице администрирования участника, выбрав вкладку Управление в Профиле участника.',
'Remove group'					=>	'Удалить группу',
'Edit group'					=>	'Редактировать группу',
'default'						=>	'(по умолчанию)',
'Cannot remove group'			=>	'Эта группа не может быть удалена.',
'Cannot remove default'			=>	'Чтобы удалить эту группу вы должны назначить новую группу «по умолчанию».',
'Remove group head'				=>	'Удалить группу «%s»" в которой состоит %s человек',
'Remove group help'				=>	'(Переместить участников в эту группу)',
'Move users to'					=>	'Переместить участников в',
'Cannot remove default group'	=>	'Группа используемая «по умолчанию» не может быть удалена. Для того, чтобы удалить ее, вам необходимо сначала назначить другую группу в качестве группы «по умолчанию».',
'Add group heading'				=>	'Добавить новую группу (выберите группу с которой вы хотите взять основные настройки)',
'Add group legend'				=>	'Добавить новую группу',
'Edit group heading'			=>	'Редактировать существующую группу',
'Base new group label'			=>	'Использовать настройки от',
'Add group'						=>	'Добавить новую группу',
'Default group heading'			=>	'Группа «по умолчанию» — группа для новых участников (администратор/модератор не доступны из соображений безопасности)',
'Default group legend'			=>	'Установить группу «по умолчанию» для новых участников',
'Default group label'			=>	'По умолчанию',
'Set default'					=>	'Назначить «по умолчанию»',
'Existing groups heading'		=>	'Существующие группы',
'Existing groups intro'			=>	'Определенные группы — Гости, Администраторы и Участники не могут быть удалены. Их можно лишь отредактировать. Имейте ввиду, что в некоторых группах недоступны некоторые опции (например редактировать сообщения для гостей). Администраторы всегда имеют полные права.',
'Group removed'					=>	'Группа удалена.',
'Default group set'				=>	'Группа определена как первоначальная.',
'Group added'					=>	'Группа добавлена.',
'Group edited'					=>	'Группа отредактирована.',
'Update group'					=>	'Обновить группу',
'Must enter group message'		=>	'Вы должны ввести название группы.',
'Already a group message'		=>	'Группа <strong>«%s»</strong> уже существует.',
'Moderator default group'		=>	'Права модераторов не могут быть присвоены этой группе, т.к. эта группа определена, как первоначальная, для новых участников.',

);