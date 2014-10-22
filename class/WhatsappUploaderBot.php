<?php
	require_once 'WhatsappUploaderCore.php';

	class WhatsappUploader_Bot extends WhatsappUploaderCore
	{
		public function Listen()
		{
			try
			{
				$i = 0;

				while($i < 60)
				{
					$this->Whatsapp->pollMessage();

					$Messages = $this->Whatsapp->getMessages();

					foreach($Messages as $Message)
					{
						$M = self::ParseMessage($Message);

						$R = self::doWithMessage($M);
					}

					sleep(1);

					$i++;
				}
			}
			catch (Exception $E)
			{
				trigger_error('Error: WhatsappUploader, Listen: ' . $E->getMessage(), E_USER_ERROR);
			}
		}

		protected function doWithMessage($M)
		{
			try
			{
				if(isset($M['success']) && $M['success'] === true)
				{
					switch ($M['type'])
					{
						case 'media':
							switch ($M['subtype'])
							{
								case 'image':
									$Data = file_get_contents($M['data']['url']);

									$Filename = self::uploadImage($Data);

									if($Filename === -1)
										$this->Whatsapp->sendMessage($M['from'], 'That image is too big...');
									else if ($Filename === -2)
										$this->Whatsapp->sendMessage($M['from'], 'That image type is not supported...');
									else
									{
										$M['from'] = str_replace('@s.whatsapp.net', '', $M['from']);

										$UID = self::GetUIDFromPhone($M['from']);
										if(!$UID)
										{
											self::RegisterNull($M['from']);

											$UID = self::GetUIDFromPhone($M['from']);
										}

										$Can = self::CheckIfCanUpload('image', $UID);

										if($Can == true)
										{
											self::AddImage($UID, $Filename, hash('sha512', $Data));

											$FinalURL = $this->Domain . '/' . str_replace('\\', '/', $Filename);

											$this->Whatsapp->sendMessage($M['from'], 'Image uploaded to ' . $FinalURL);
										}
										else if($Can == -1)
											$this->Whatsapp->sendMessage($M['from'], 'Has alcanzado el límite de imágenes en el modo gratuito. Envía un mensaje con "register" <contraseña> para registrarte...');
										else if($Can == -2)
											$this->Whatsapp->sendMessage($M['from'], 'Mejora tu plan para poder cargar éste tipo de archivo...');
									}
									break;
							}
							break;
					}
				}
				else
					if(isset($M['msg']))
						$this->Whatsapp->sendMessage($M['from'], $M['msg']);
					else
						$this->Whatsapp->sendMessage($M['from'], 'An internar server error has ocurred. Please try again in a few seconds. ');
			}
			catch (Exception $E)
			{
				trigger_error('Error: WhatsappUploader, doWithMessage: ' . $E->getMessage(), E_USER_ERROR);
			}
		}
	}