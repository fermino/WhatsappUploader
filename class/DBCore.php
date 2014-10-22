<?php
	require_once 'Catcher.php';
	require_once 'Logger.php';

	abstract class DBCore
	{
		protected $Catcher = null;
        protected $Logger = null;

		protected $DB = null;

		public function __construct($DB_H, $DB_U, $DB_P, $DB_D, Catcher &$C = null, Logger &$L = null)
		{
			try
			{
				if($L == null)
                    $L = new Logger();
                if($C == null)
                    $C = new Catcher($L);

                $this->Catcher = $C;
                $this->Logger = $L;

				$this->DB = new PDO("mysql:host={$DB_H};dbname={$DB_D}", $DB_U, $DB_P);
			}
			catch (PDOException $E)
			{
				$this->Logger->onError($E, true);
			}
			catch (Exception $E)
			{
				$this->Logger->onError($E, true);
			}
		}
	}