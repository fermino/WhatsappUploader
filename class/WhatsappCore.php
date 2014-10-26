<?php
	require_once 'DBController.php';
	require_once 'whatsapi/whatsprot.class.php';

	abstract class WhatsappCore extends DBController
	{
		protected $Whatsapp = null;

		public function __construct($DB_H, $DB_U, $DB_P, $DB_D, $WP_U, $WP_I, $WP_P, $WP_N, $Limits, Catcher &$C = null, Logger &$L = null)
		{
			try
			{
				parent::__construct($DB_H, $DB_U, $DB_P, $DB_D, $Limits, $C, $L);

				$this->Whatsapp = new WhatsProt($WP_U, $WP_I, $WP_N);
				$this->Whatsapp->connect();
				$this->Whatsapp->loginWithPassword($WP_P);

				if(is_file('nextChallenge.dat'))
					unlink('nextChallenge.dat');
			}
			catch (Exception $E)
			{
				trigger_error('Error: WhatsappCore, __construct: ' . $E->getMessage(), E_USER_ERROR);
			}
		}
	}