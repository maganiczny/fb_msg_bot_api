<?php

	require_once('./lib/fb.php');
	require_once('./lib/bot.php');

	$fb = new \fb\fb(
    'APP ID',
		'VERIFY ID',
		'USER (ADMIN) ID'
	);

	/****************************************/
	/*****			TRIGGER AREA		*****/
	/*****			==~~BEGIN~~==		*****/
	/****************************************/

	/*****	quickReply przy innym niż pomoc	*****/
	$fb->trigger('/^(?!pomóż( mi)?|pomocy?|help( me)?)/i','bot::nothelp');

	/*****			POWITANIE I POMOC	*****/
	$fb->trigger('/^(hej|wita(j|m)|siema|elo|cześć|czesc)$/i','bot::hej');
	$fb->trigger('/^(pomóż( mi)?|pomocy?|help( me)?)$/i','bot::pomoc');
	$fb->trigger('/^(co tam|co (tam )?słychać|jak tam)\??$/i','bot::hejka');

	/*****			DOMYŚLNE			*****/
	$fb->trigger('/.*/','bot::default');


	/****************************************/
	/*****			TRIGGER AREA		*****/
	/*****			 ==~~END~~==		*****/
	/****************************************/

	$fb->run();
