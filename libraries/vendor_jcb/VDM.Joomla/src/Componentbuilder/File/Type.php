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

namespace VDM\Joomla\Componentbuilder\File;


use Joomla\Filesystem\Path;
use VDM\Joomla\Interfaces\Data\ItemInterface as Item;


/**
 * File Type Class
 * 
 * @since  5.0.2
 */
final class Type
{
	/**
	 * The Item Class.
	 *
	 * @var    Item
	 * @since 5.0.2
	 */
	protected Item $item;

	/**
	 * The File Types
	 *
	 * @var    array
	 * @since  5.0.2
	 */
	protected array $fileTypes = [1 => 'image' , 2 => 'document' , 3 => 'media', 4 => 'file'];

	/**
	 * Constructor.
	 *
	 * @param Item   $item   The Item Class.
	 *
	 * @since 5.0.2
	 */
	public function __construct(Item $item)
	{
		$this->item = $item;
	}

	/**
	 * Retrieves the file type details (ajax)
	 *
	 * @param string $guid    The GUID (Globally Unique Identifier) used as the key to retrieve the file type
	 * @param string $target  The entity target name.
	 *
	 * @return array|null   The item object if found, or null if the item does not exist.
	 * @since  5.0.2
	 */
	public function get(string $guid, string $target): ?array
	{
		if (($fileType = $this->details($guid)) !== null &&
			$this->validTarget($fileType, $target))
		{
			return [
				'name' => $this->getFieldName($fileType),
				'allow' => $this->getAllow($fileType),
				'allow_span' => $this->getAllowSpan($fileType),
				'file_type_span' => $fileType->name ?? 'file',
				'display_fields' => $fileType->display_fields ?? null,
				'param_fields' => $fileType->param_fields ?? null,
			];
		}

		return null;
	}

	/**
	 * Retrieves the file type details (upload)
	 *
	 * @param string $guid    The GUID (Globally Unique Identifier) used as the key to retrieve the file type
	 * @param string $target  The entity target name.
	 *
	 * @return array|null   The item object if found, or null if the item does not exist.
	 * @since  5.0.2
	 */
	public function load(string $guid, string $target): ?array
	{
		if (($fileType = $this->details($guid)) !== null &&
			$this->validTarget($fileType, $target))
		{
			return [
				'name' => $fileType->name ?? 'files',
				'access' => $fileType->access ?? 1,
				'download_access' => $fileType->download_access ?? 1,
				'field' => $this->getFieldName($fileType),
				'type' => $this->getFieldName($fileType),
				'formats' => $this->getAllowFormats($fileType) ?? [],
				'filter' => $fileType->filter ?? null,
				'path' => $this->getFileTypePath($fileType)
			];
		}

		return null;
	}

	/**
	 * Retrieves the file type details
	 *
	 * @param string $guid   The GUID (Globally Unique Identifier) used as the key to retrieve the file type.
	 *
	 * @return object|null   The item object if found, or null if the item does not exist.
	 * @since  5.0.2
	 */
	public function details(string $guid): ?object
	{
		return $this->item->table('file_type')->get($guid);
	}

	/**
	 * Valid if this is a correct target trying to call this file type
	 *
	 * @param object  $data   The type data array
	 * @param string  $target The entity target name.
	 *
	 * @return bool   True if valid target
	 * @since  5.0.2
	 */
	protected function validTarget(object $data, string $target): bool
	{
		$targets = $data->target ?? null;
		if (!empty($targets))
		{
			$targets = (array) $targets;
			return in_array($target, $targets);
		}

		return false;
	}

	/**
	 * Retrieves the field name
	 *
	 * @param object  $data   The type data array
	 *
	 * @return string   The field name
	 * @since  5.0.2
	 */
	protected function getFieldName(object $data): string
	{
		$type = $data->type ?? 4;
		if (isset($this->fileTypes[$type]))
		{
			return $this->fileTypes[$type];
		}
		return 'file';
	}

	/**
	 * Retrieves the allow formats (for script)
	 *
	 * @param object  $data   The type data array
	 *
	 * @return string   The allow values
	 * @since  5.0.2
	 */
	protected function getAllow(object $data): string
	{
		$formats = $this->getAllowFormats($data);
		if (!empty($formats))
		{
			return '*.(' . implode('|', $formats) . ')';
		}
		return '';
	}

	/**
	 * Retrieves the allow formats (for span)
	 *
	 * @param object  $data   The type data array
	 *
	 * @return string   The allow values
	 * @since  5.0.2
	 */
	protected function getAllowSpan(object $data): string
	{
		$formats = $this->getAllowFormats($data);
		if (!empty($formats))
		{
			return '(formats allowed: <b>' . implode(', ', $formats) . '</b>)';
		}
		return '';
	}

	/**
	 * Retrieves the allow formats
	 *
	 * @param object|null  $data   The type data array
	 *
	 * @return array|null   The allow values
	 * @since  5.0.2
	 */
	protected function getAllowFormats(object $data): ?array
	{
		$type = $data->type ?? 4;
		switch ($type)
		{
			case 1:
				$formats = $data->image_formats ?? null;
			break;
			case 2:
				$formats = $data->document_formats ?? null;
			break;
			case 3:
				$formats = $data->media_formats ?? null;
			break;
			default:
				$formats = $data->file_formats ?? null;
			break;
		}

		if ($formats)
		{
			return (array) $formats;
		}

		return null;
	}

	/**
	 * Retrieves the file type path based on provided data.
	 *
	 * Performs safety checks and returns either a cleaned path if it exists
	 * and is a writable directory, or constructs a relative path to the 'images' folder
	 * based on the last folder name from the given path.
	 *
	 * @param object  $data  The type data object containing path information.
	 *
	 * @return string|null Returns the cleaned file path or null if no valid path is found.
	 * @since  5.0.2
	 */
	protected function getFileTypePath(object $data): ?string
	{
		// Validate the provided path data
		$path = isset($data->path) && is_string($data->path) && trim($data->path) !== '' ?
			Path::clean(trim($data->path)) : null;

		// Return the path if it's a valid directory and writable
		if ($path !== null && is_dir($path) && is_writable($path))
		{
			return $path;
		}

		// If no valid path is found, try to derive a relative path from the 'images' folder
		if ($path !== null && ($folder = $this->getLastFolderName($path)) !== null)
		{
			return JPATH_SITE . '/images/' . $folder;
		}

		return null;
	}

	/**
	 * Recursively retrieves the last folder name from a given path, ignoring any file names.
	 * If the last part of the path contains a dot (indicating a file), it moves up the directory tree
	 * until it finds a valid folder name. Returns null if no valid folder is found.
	 *
	 * @param string $path The file system path from which to extract the last folder name.
	 * 
	 * @return string|null Returns the last folder name if found, or null if no valid folder exists.
	 * @since  5.0.2
	 */
	protected function getLastFolderName(string $path): ?string
	{
		// Remove any trailing slashes to avoid an empty result
		$path = rtrim($path, '/\\');

		// If the path becomes empty, return null (base case)
		if (empty($path))
		{
			return null;
		}

		// Get the last part of the path
		$lastPart = basename($path);

		// If the last part contains a dot (and it's not a hidden folder), move up the directory tree
		if (strpos($lastPart, '.') > 0)
		{
			// If it contains a dot, treat it as a file and move up one level
			return $this->getLastFolderName(dirname($path));
		}

		// Return the last folder name (if it's valid and not a file)
		return $lastPart;
	}
}

