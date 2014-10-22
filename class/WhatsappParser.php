<?php
	require_once 'WhatsappCore.php';

	abstract class WhatsappParser extends WhatsappCore
	{
		protected function ParseMessage($Message)
		{
			try
			{
				$Type = $Message->getAttribute('type');
				$From = $Message->getAttribute('from');
				$Name = $Message->getAttribute('notify');

				switch ($Type)
				{
					case 'text':
						self::ParseTextMessage($Message);
						return;
						break;
					case 'media':
						$R = self::ParseMediaMessage($Message->getChild('media'));

						if(isset($R['success']) && $R['success'] === true)
						{
							return array
							(
								'success' => true,

								'from' => $From,
								'name' => $Name,

								'type' => 'media',
								'subtype' => $R['type'],

								'data' => $R['data']
							);
						}
						else
							return array
							(
								'success' => false,

								'from' => $From,
								'name' => $Name,
								'type' => $Type,

								'msg' => (isset($R['msg'])) ? $R['msg'] : null
							);
						break;
				}
			}
			catch (Exception $E)
			{
				trigger_error('Error: WhatsappParser, ParseMessage: ' . $E->getMessage(), E_USER_ERROR);
			}
		}

		protected function ParseTextMessage($Text)
		{
			try
			{
				$Splitted = explode(' ', $Text);

				if(isset($Splitted[0]) && isset($Splitted[1]) && $Splitted[0] == 'register' && !empty($Splitted[1]))
				{
					return array
					(
						'success' => true,

						'action' => 'register',
						'password' => $Splitted[1]
					);
				}

				return array
				(
					'success' => false, 'msg' => 'unrecognized_command',
				);
			}
			catch (Exception $E)
			{
				trigger_error('Error: WhatsappParser, ParseTextMessage: ' . $E->getMessage(), E_USER_ERROR);
			}
		}

		protected function ParseMediaMessage($Media)
		{
			try
			{
				switch ($Media->getAttribute('type'))
				{
					case 'image':
						$R = self::ParseMediaMessage_Image($Media);

						return array
						(
							'success' => true,
							'type' => 'image',
							'data' => $R
						);
						break;
					default:
						return array
						(
							'success' => false, 'msg' => 'media_type_not_supported',
						);
						break;
				}
			}
			catch (Exception $E)
			{
				trigger_error('Error: WhatsappParser, ParseMediaMessage: ' . $E->getMessage(), E_USER_ERROR);
			}
		}

		protected function ParseMediaMessage_Image($Media)
		{
			try
			{
				return array
				(
					'encoding' => ($Media->getAttribute('encoding')) ? $Media->getAttribute('encoding') : null,
					'url' => ($Media->getAttribute('url')) ? $Media->getAttribute('url') : null,
					'size' => ($Media->getAttribute('size')) ? $Media->getAttribute('size') : null,
					'ip' => ($Media->getAttribute('ip')) ? $Media->getAttribute('ip') : null,
					'mime' => ($Media->getAttribute('mimetype')) ? $Media->getAttribute('mimetype') : null,
					'hash' => ($Media->getAttribute('filehash')) ? $Media->getAttribute('filehash') : null,
					'w' => ($Media->getAttribute('width')) ? $Media->getAttribute('width') : null,
					'h' => ($Media->getAttribute('height')) ? $Media->getAttribute('height') : null
				);
			}
			catch (Exception $E)
			{
				trigger_error('Error: WhatsappParser, ParseMediaMessage_Image: ' . $E->getMessage(), E_USER_ERROR);
			}
			
		}
	}