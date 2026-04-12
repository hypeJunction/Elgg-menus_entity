<?php

return [
	'plugin' => [
		'name' => 'Dropdown Entity Menus',
		'version' => '4.0.0',
	],

	'bootstrap' => \hypeJunction\MenusEntity\Bootstrap::class,

	'settings' => [
		'primary_actions' => 'access,likes,unlike,likes_count,published_status,membership,members',
		'remove_actions' => '',
		'icon' => 'ellipsis-v',
	],

	'hooks' => [
		'register' => [
			'menu:entity' => [
				\hypeJunction\MenusEntity\SetupEntityMenu::class => [
					'priority' => 999,
				],
			],
		],
	],
];
