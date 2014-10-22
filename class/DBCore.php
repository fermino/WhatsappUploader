<?php
	require_once 'errorHandler.php';
	
	abstract class DBCore
	{
		protected $DB = null;

		public function __construct($DB_H, $DB_U, $DB_P, $DB_D)
		{
			try
			{
				$this->DB = new PDO("mysql:host={$DB_H};dbname={$DB_D}", $DB_U, $DB_P);
			}
			catch (PDOException $E)
			{
				trigger_error('Error: DBCore, __construct, PDO Connection: ' . $E->getMessage(), E_USER_ERROR);
			}
			catch (Exception $E)
			{
				trigger_error('Error: DBCore, __construct: ' . $E->getMessage(), E_USER_ERROR);
			}
		}

		public function Register($Phone, $Password)
		{
			if(strlen($Phone) < 10)
				return -2;

			$R = $this->DB->prepare('SELECT `pack`FROM `users` WHERE `phone` = :phone LIMIT 1');
			$R->bindParam(':phone', $Phone);
			$R->execute();
			$R = $R->fetch();

			if($R == false)
			{
				$Verifcode = mt_rand(1000000, 99999999999);
				$Password = password_hash($Password, PASSWORD_DEFAULT);

				$R = $this->DB->prepare('INSERT INTO `users`(`id`, `phone`, `password`, `enabled`, `verifcode`, `pack`) VALUES (null,:phone,:pass,\'0\',:verifcode,\'1\')');
				$R->bindParam(':phone', $Phone);
				$R->bindParam(':pass', $Password);
				$R->bindParam(':verifcode', $Verifcode);
				$R->execute();

				return 1;
			}
			else if($R['pack'] == 0)
			{
				$Verifcode = mt_rand(1000000, 99999999999);
				$Password = password_hash($Password, PASSWORD_DEFAULT);

				$R = $this->DB->prepare('UPDATE `users` SET `password`=:pass,`verifcode`=:verifcode,`pack`=\'1\' WHERE `phone` = :phone LIMIT 1');

				$R->bindParam(':phone', $Phone);
				$R->bindParam(':pass', $Password);
				$R->bindParam(':verifcode', $Verifcode);
				$R->execute();

				return 2;
			}
			else
				return -1;

			return false;
		}

		public function RegisterNull($Phone)
		{
			$R = $this->DB->prepare('INSERT INTO `users`(`id`, `phone`, `password`, `enabled`, `verifcode`, `pack`) VALUES (null,:phone,null,\'0\',null,\'0\')');
			$R->bindParam(':phone', $Phone);
			$R->execute();

			return true;
		}

		public function AddImage($UID, $Filename, $Hash)
		{
			$Time = time();
			$R = $this->DB->prepare('INSERT INTO `images`(`id`, `userid`, `path`, `hash`, `time`) VALUES (null,:uid,:path,:hash,:time)');
			$R->bindParam(':uid', $UID);
			$R->bindParam(':path', $Filename);
			$R->bindParam(':hash', $Hash);
			$R->bindParam(':time', $Time);
			$R->execute();

			return true;
		}

		public function GetUIDFromPhone($Phone)
		{
			$R = $this->DB->prepare('SELECT `id` FROM `users` WHERE `phone` = :phone LIMIT 1');
			$R->bindParam(':phone', $Phone);
			$R->execute();
			$R = $R->fetch();

			if($R != false)
				return $R['id'];

			return false;
		}
	}