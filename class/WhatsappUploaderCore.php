<?php
	require_once 'WhatsappParser.php';
	require_once 'utils/array_column.php';

	abstract class WhatsappUploaderCore extends WhatsappParser
	{
		protected $Domain = null;
		protected $MIMETypes = null;
		protected $UploadPath = null;
		protected $MaxFileSize = null;

		public function __construct($DB_H, $DB_U, $DB_P, $DB_D, $WP_U, $WP_I, $WP_P, $WP_N, $D, $MIME, $Limits, Catcher &$C = null, Logger &$L = null, $UP = 'uploads', $MFS = 8388608)
		{
			try
			{
				parent::__construct($DB_H, $DB_U, $DB_P, $DB_D, $WP_U, $WP_I, $WP_P, $WP_N, $Limits, $C, $L);

				$this->Domain = $D;
				$this->MIMETypes = $MIME;
				$this->UploadPath = trim($UP, '/\\');
				$this->MaxFileSize = intval($MFS);
				
				if(!is_dir($this->UploadPath))
					if(!mkdir($this->UploadPath))
						trigger_error('Can\'t create uploads directory', E_USER_ERROR);
				
				if(!is_writable($this->UploadPath))
					trigger_error('Uploads directory is not writable', E_USER_ERROR);
			}
			catch (Exception $E)
			{
				trigger_error('Error: WhatsappUploaderCore, __construct: ' . $E->getMessage(), E_USER_ERROR);
			}
		}

		protected function uploadImage($Data)
		{
			try
			{
				if(strlen($Data) < $this->MaxFileSize)
				{
					$MIME = self::getMIMEType($Data);
					
					if(in_array($MIME, array_column($this->MIMETypes['image'], 'mime')))
					{
						$Extension = self::getMIMEExtension('image', $MIME);
						if(!$Extension)
							trigger_error('MIME types are not defined correctly', E_USER_ERROR);

						$Filename = self::generateFilename($Extension);

						if(!file_put_contents($Filename, $Data))
							trigger_error('Can\'t save image to file', E_USER_ERROR);

						return $Filename;
					}
					return -2;
				}
				return -1;
			}
			catch (Exception $E)
			{
				trigger_error('Error: WhatsappUploaderCore, uploadImage: ' . $E->getMessage(), E_USER_ERROR);
			}
		}

		protected function uploadAudio($Data) // Y si evitamos todo el bardo de finfo mimetype, y diréctamente obtenemos eso de whatsapi? (O lo testeamos fuera del uploader)
		{
			if(strlen($Data) < $this->MaxFileSize)
			{
				$MIME = self::getMIMEType($Data);

				if(in_array($MIME, array_column($this->MIMETypes['audio'], 'mime')))
				{
					$Extension = self::getMIMEExtension('audio', $MIME);
					if(!$Extension)
						trigger_error('MIME types are not defined correctly', E_USER_ERROR);

					$Filename = self::generateFilename($Extension);

					if(!file_put_contents($Filename, $Data))
						trigger_error('Can\'t save audio to file', E_USER_ERROR);

					return $Filename;
				}
				return -2;
			}
			return -1;
		}

		protected function getMIMEType($Data)
		{
			try
			{
				$Tempname = tempnam($this->UploadPath . '/', 'tmp_');
				file_put_contents($Tempname, $Data);

				$FInfo = finfo_open(FILEINFO_MIME_TYPE);
				$MIME = finfo_file($FInfo, $Tempname);
				finfo_close($FInfo);

				if(is_file($Tempname))
					unlink($Tempname);

				return $MIME;
			}
			catch (Exception $E)
			{
				trigger_error('Error: WhatsappUploaderCore, getMIMEType: ' . $E->getMessage(), E_USER_ERROR);
			}
		}

		protected function getMIMEExtension($Type, $MIME)
		{
			try
			{
				if(isset($this->MIMETypes[$Type]))
				{
					$D = array_column($this->MIMETypes[$Type], 'ext', 'mime');

					if(isset($D[$MIME]))
						return $D[$MIME];
				}

				return false;
			}
			catch (Exception $E)
			{
				trigger_error('Error: WhatsappUploaderCore, getMIMEExtension: ' . $Type . ': ' . $E->getMessage(), E_USER_ERROR);
			}
		}

		protected function generateFilename($Extension)
		{
			try
			{
				$Name = str_replace('.', '', str_replace(' ', '', microtime()));
				
				if(is_file($this->UploadPath . DIRECTORY_SEPARATOR . $Name . '.' . $Extension))
				{
					return self::generateFilename($Extension);
				}
				
				return $this->UploadPath . DIRECTORY_SEPARATOR . $Name . '.' . $Extension;
			}
			catch (Exception $E)
			{
				trigger_error('Error: WhatsappUploaderCore, generateFilename: ' . $E->getMessage(), E_USER_ERROR);
			}
		}
	}