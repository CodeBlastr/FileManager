<?php
App::uses('FileManagerAppModel', 'FileManager.Model');
/**
 * FileAttach
 */
class FileAttach extends FileManagerAppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'FileAttach';

/**
 * Table name
 *
 * @var string
 */
	public $useTable = 'file_attachments';

/**
 * Displayfield
 *
 * @var string
 */
	public $displayField = 'title';

/**
 * Belongs to
 * 
 * @var array
 */
 	public $belongsTo = array(
		'FileStorage' => array(
			'className' => 'FileManager.FileStorage',
			'foreign_key' => 'file_storage_id'
			),
		'AudioStorage' => array(
			'className' => 'FileManager.AudioStorage',
			'foreign_key' => 'file_storage_id'
			),
		'ImageStorage' => array(
			'className' => 'FileManager.ImageStorage',
			'foreign_key' => 'file_storage_id'
			),
		'VideoStorage' => array(
			'className' => 'FileManager.VideoStorage',
			'foreign_key' => 'file_storage_id'
			)
		);

}
