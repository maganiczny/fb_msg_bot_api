<?php

	class bot {

		static public function hej($fb)
		{
			$fb->message($fb->message.'! Witam Cię
				Napisz "pomoc" aby dowiedzieć się więcej!');

			return true;
		}

		static function hejka($fb)
		{
			$fb->message('Dzięki. Nic ciekawego. Nudne życie bota, ciągle tylko odpowiadam na te same pytania... Potrzebna jest moja POMOC ?');
			return true;
		}

		static function pomoc($fb)
		{

			$fb->message('Działające polecenia:

				* pomoc

				Więcej: pomoc [polecenie] lub [polecenie]');

			return true;
		}

		static public function nothelp($fb)
		{
			$fb->addQuickReply('pomoc');
		}

	}
