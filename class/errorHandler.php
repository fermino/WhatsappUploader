
<?php
	class errorHandler
	{
		public static function onError($N, $Error, $File, $Line)
		{
			echo $N . "\n";
			echo $Error . "\n";
			echo $File . "\n";
			echo $Line . "\n";
			die();
		}
	}

	set_error_handler('errorHandler::onError');