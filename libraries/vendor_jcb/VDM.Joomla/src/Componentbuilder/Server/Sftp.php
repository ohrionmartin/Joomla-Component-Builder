<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September, 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Server;


use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\CMSApplication;
use phpseclib3\Net\SFTP as SftpClient;
use VDM\Joomla\Componentbuilder\Crypt\KeyLoader;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Utilities\FileHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Componentbuilder\Interfaces\Serverinterface;


/**
 * Sftp Class
 * 
 * @since 3.2.0
 */
class Sftp implements Serverinterface
{
	/**
	 * The KeyLoader
	 *
	 * @var    KeyLoader
	 * @since 3.2.0
	 */
	protected KeyLoader $key;

	/**
	* The client object
	 *
	 * @var     SftpClient|null
	 * @since 3.2.0
	 **/
	protected ?SftpClient $client = null;

	/**
	* The server details
	 *
	 * @var     object
	 * @since 3.2.0
	 **/
	protected ?object $details = null;

	/**
	 * Application object.
	 *
	 * @var    CMSApplication
	 * @since 3.2.0
	 **/
	protected CMSApplication $app;

	/**
	 * Constructor
	 *
	 * @param KeyLoader    $key   The key loader object.
	 * @param CMSApplication|null     $app          The app object.
	 *
	 * @since 3.2.0
	 */
	public function __construct(KeyLoader $key, ?CMSApplication $app = null)
	{
		$this->key = $key;
		$this->app = $app ?: Factory::getApplication();
	}

	/**
	 * set the server details
	 *
	 * @param   object    $details    The server details
	 *
	 * @return  Sftp
	 * @since 3.2.0
	 **/
	public function set(object $details): Sftp
	{
		// we need to make sure the if the details changed to get a new server client
		if (!ObjectHelper::equal($details, $this->details))
		{
			// set the details
			$this->details = $details;

			// reset the client if it was set before
			$this->client = null;
		}

		return $this;
	}

	/**
	 * move a file to server with the FTP client
	 *
	 * @param   string      $localPath      The full local path to the file
	 * @param   string      $fileName      The file name
	 *
	 * @return  bool
	 * @since 3.2.0
	 **/
	public function move(string $localPath, string $fileName): bool
	{
		if ($this->connected() &&
			($data = FileHelper::getContent($localPath, null)) !== null)
		{
			// get the remote path
			$path = '';
			if (isset($this->details->path) &&
				StringHelper::check($this->details->path) &&
				$this->details->path !== '/')
			{
				$path = trim((string) $this->details->path);
				$path = '/' . trim($path, '/') . '/';
			}

			try
			{
				return $this->client->put($path . trim($fileName), $data);
			}
			catch(\Exception $e)
			{
				$this->app->enqueueMessage(
					Text::sprintf('COM_COMPONENTBUILDER_MOVING_OF_THE_S_FAILED', $fileName) . ': ' . $e->getMessage(),
					'Error'
				);
			}
		}

		return false;
	}

	/**
	 * Make sure we are connected
	 *
	 * @return  bool
	 * @since 3.2.0
	 **/
	private function connected(): bool
	{
		// check if we have a connection
		if ($this->client instanceof SftpClient && ($this->client->isConnected() || $this->client->ping()))
		{
			return true;
		}

		$this->client = $this->getClient();

		return $this->client instanceof SftpClient;
	}

	/**
	 * get the SftpClient object
	 *
	 * @return  SftpClient|null
	 * @since 3.2.0
	 **/
	private function getClient(): ?SftpClient
	{
		// make sure we have a host value set
		if (isset($this->details->host) && StringHelper::check($this->details->host) &&
			isset($this->details->username) && StringHelper::check($this->details->username))
		{
			// insure the port is set
			$port = (int)($this->details->port ?? 22);

			// open the connection
			$sftp = new SftpClient($this->details->host, $port);

			// set the passphrase if it exist
			$passphrase = (isset($this->details->secret) && StringHelper::check(trim($this->details->secret))) ? trim($this->details->secret) : false;

			// set the password if it exist
			$password = (isset($this->details->password) && StringHelper::check(trim($this->details->password))) ? trim($this->details->password) : false;

			// now login based on authentication type
			$key = null;
			switch($this->details->authentication)
			{
				case 1: // password
					$key = $password ?? null;
					$password = null;
				break;
				case 2: // private key file
				case 3: // both password and private key file
					if (isset($this->details->private) && StringHelper::check($this->details->private) &&
						($private_key = FileHelper::getContent($this->details->private, null)) !== null)
					{
						try
						{
							$key = $this->key::load(trim($private_key), $passphrase);
						}
						catch(\Exception $e)
						{
							$this->app->enqueueMessage(
								Text::_('COM_COMPONENTBUILDER_LOADING_THE_PRIVATE_KEY_FILE_FAILED') . ': ' . $e->getMessage(),
								'Error'
							);
							$key = null;
						}
					}
				break;
				case 4: // private key field
				case 5: // both password and private key field
					if (isset($this->details->private_key) && StringHelper::check($this->details->private_key))
					{
						try
						{
							$key = $this->key::load(trim($this->details->private_key), $passphrase);
						}
						catch(\Exception $e)
						{
							$this->app->enqueueMessage(
								Text::_('COM_COMPONENTBUILDER_LOADING_THE_PRIVATE_KEY_TEXT_FAILED') . ': ' . $e->getMessage(),
								'Error'
							);
							$key = null;
						}
					}
				break;
			}

			// remove any null bites from the username
			$this->details->username = trim($this->details->username);

			// login
			if (!empty($key) && !empty($password))
			{
				try
				{
					$sftp->login($this->details->username, $key, $password);
					return $sftp;
				}
				catch(\Exception $e)
				{
					$this->app->enqueueMessage(
						Text::_('COM_COMPONENTBUILDER_LOGIN_FAILED') . ': ' . $e->getMessage(),
						'Error'
					);
				}
			}
			elseif (!empty($key))
			{
				try
				{
					$sftp->login($this->details->username, $key);
					return $sftp;
				}
				catch(\Exception $e)
				{
					$this->app->enqueueMessage(
						Text::_('COM_COMPONENTBUILDER_LOGIN_FAILED') . ': ' . $e->getMessage(),
						'Error'
					);
				}
			}
		}

		return null;
	}
}

