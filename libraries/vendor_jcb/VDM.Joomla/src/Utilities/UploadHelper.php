<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    3rd September, 2020
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Utilities;


use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use VDM\Joomla\Utilities\Component\Helper;
use VDM\Joomla\Utilities\MimeHelper;
use VDM\Joomla\Utilities\StringHelper;


/**
 * Upload Helper
 * 
 * @since  3.0.11
 */
abstract class UploadHelper
{
	/**
	 * True to use streams
	 *
	 * @var    bool
	 *
	 * @since  3.0.11
	 */
	public static bool $useStreams = false;

	/**
	 * Allow the upload of unsafe files
	 *
	 * @var    bool
	 *
	 * @since  3.0.11
	 */
	public static bool $allowUnsafe = false;

	/**
	 * Options to InputFilter::isSafeFile
	 *
	 * @var    array
	 *
	 * @since  3.0.11
	 */
	public static array $safeFileOptions = [];

	/**
	 * Set the error behavior
	 *
	 * @var    bool
	 *
	 * @since  3.0.11
	 */
	public static bool $enqueueError = true;

	/**
	 * Legal Formats
	 *
	 * @var    array
	 *
	 * @since  5.0.3
	 */
	public static array $legalFormats = [];

	/**
	 * Errors
	 *
	 * @var    array
	 *
	 * @since  3.0.11
	 */
	protected static array $errors = [];

	/**
	 * Get file/files from a HTTP upload.
	 *
	 * @param  string        $field       The input field name
	 * @param  string        $type        The file type
	 * @param  string|null   $filter      The filter to use when uploading the file
	 * @param  string|null   $path        The path to the directory where the file must be placed
	 *
	 * @return  array|null   File details or false on failure.
	 * @since  3.0.11
	 */
	public static function get(string $field, string $type, string $filter = null, string $path = null): ?array
	{
		// Get the uploaded file information.
		$input = Factory::getApplication()->input;

		// set the default filter
		if (empty($filter))
		{
			$filter = 'array';
		}
		// if raw then also unsafe
		// see: https://github.com/joomla/joomla-cms/blob/4.1-dev/administrator/components/com_installer/src/Model/InstallModel.php#L259
		elseif ($filter === 'raw')
		{
			static::$allowUnsafe = true;
		}

		// check if we have a file destination name in the field name
		$name = null;
		if (strpos($field, ':') !== false)
		{
			list($field, $name) = explode(':', $field);
		}

		// See JInputFiles::get.
		$userfile = $input->files->get($field, null, $filter);

		// Make sure that file uploads are enabled in php.
		if (!(bool) ini_get('file_uploads'))
		{
			static::setError(Text::_('COM_COMPONENTBUILDER_WARNING_UPLOAD_ERROR'));

			return null;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			static::setError(Text::_('COM_COMPONENTBUILDER_NO_UPLOAD_SELECTED'));

			return null;
		}

		// Is the PHP tmp directory missing?
		if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_NO_TMP_DIR))
		{
			static::setError(Text::_('COM_COMPONENTBUILDER_THERE_WAS_AN_ERROR_UPLOADING_TO_THE_SERVER') . '<br>' . Text::_('COM_COMPONENTBUILDER_THE_PHP_TEMPORARY_FOLDER_IS_NOT_SET'));

			return null;
		}

		// Is the max upload size too small in php.ini?
		if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_INI_SIZE))
		{
			static::setError(Text::_('COM_COMPONENTBUILDER_THERE_WAS_AN_ERROR_UPLOADING_TO_THE_SERVER') . '<br>' . Text::_('COM_COMPONENTBUILDER_YOUR_FILE_WAS_IS_LARGER_THAN_THE_ALLOWED_SIZE'));

			return null;
		}

		// Check if there was a different problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			static::setError(Text::_('COM_COMPONENTBUILDER_THERE_WAS_AN_ERROR_UPLOADING_TO_THE_SERVER'));

			return null;
		}

		// check if a path was passed and exist
		if (is_string($path) && Folder::create($path))
		{
			// set the path
			$userfile['path'] = $path;
		}
		else
		{
			// get the Joomla config class
			$config = Factory::getConfig();
			// set the path
			$userfile['path'] = $config->get('tmp_path');
		}

		// set the random part of the name
		$userfile['random'] = StringHelper::random(12);

		// set the file name
		if (empty($name))
		{
			// set the file name
			$userfile['file_name'] = $userfile['random'] . $userfile['name'];
		}
		else
		{
			// check that his name has file format
			if (is_string($name) && strpos($name, '.') === false)
			{
				$name = $name . '.' . MimeHelper::extension($userfile['name']);
			}
			$userfile['file_name'] = $name;
		}

		// set full path
		$userfile['full_path'] = Path::clean($userfile['path'] . '/' . $userfile['file_name']);

		// Upload the file.
		if (File::upload($userfile['tmp_name'], $userfile['full_path'], static::$useStreams, static::$allowUnsafe))
		{
			// Check that this is a valid file
			return static::check($userfile, $type);
		}

		return null;
	}

	/**
	 * Get the errors
	 *
	 * @param  bool         $toString      The option to return errors as a string
	 *
	 * @return  array|string
	 * @since  3.0.11
	 */
	public static function getError($toString = false)
	{
		if ($toString)
		{
			return implode(' ' . PHP_EOL, static::$errors);
		}
		return static::$errors;
	}

	/**
	 * Check a file and verifies it as a allowed file format file
	 *
	 * @param  array         $upload      The uploaded details array
	 * @param  string        $type          The file type
	 *
	 * @return  array|null  of elements
	 *
	 */
	protected static function check(array $upload, string $type): ?array
	{
		// Default extensions/formats
		$extensions = MimeHelper::getFileExtensions($type);

		// Clean the path
		$upload_path = Path::clean($upload['full_path']);

		// Get file extension/format
		$extension = MimeHelper::extension($upload_path);
		$mime = $upload['type'];

		unset($upload['type']);

		// set to check
		$checking_mime = MimeHelper::mimeType($upload_path);

		// Legal file formats
		$legal = [];

		// check if the file format is even in the list
		if (in_array($extension, $extensions))
		{
			// get allowed formats
			$legal_formats = (array) Helper::getParams()->get($type . '_formats', []);
			$legal_extensions = array_values(array_unique(array_merge($legal_formats, static::$legalFormats)));
		}

		// check the extension
		if (!in_array($extension, $legal_extensions))
		{
			// Cleanup the import file
			static::remove($upload['full_path']);

			static::setError(Text::_('COM_COMPONENTBUILDER_UPLOAD_IS_NOT_A_VALID_TYPE'));

			return null;
		}

		if ($checking_mime === $mime)
		{
			$upload['mime'] = $mime; // TODO we should keep and eye on this.
		}

		$upload['extension'] = $extension;

		return $upload;
	}

	/**
	 * Clean up temporary uploaded file
	 *
	 * @param   string  $fullPath    The full path of the uploaded file
	 *
	 * @return  boolean  True on success
	 *
	 */
	protected static function remove($fullPath)
	{
		// Is the package file a valid file?
		if (is_file($fullPath))
		{
			File::delete($fullPath);
		}
		elseif (is_file(Path::clean($fullPath)))
		{
			// It might also be just a base filename
			File::delete(Path::clean($fullPath));
		}
	}

	/**
	 * Set the errors
	 *
	 * @param  string        $message   The error message
	 *
	 * @return  void
	 * @since  3.0.11
	 */
	protected static function setError($message)
	{
		if (static::$enqueueError)
		{
			Factory::getApplication()->enqueueMessage($message, 'error');
		}
		else
		{
			static::$errors[] = $message;
		}
	}
}

