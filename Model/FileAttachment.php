<?php
App::uses('FileManagerAppModel', 'FileManager.Model');

/**
 * File Attachments Model.
 * 
 * Controls what File is attached to what model.
 * We are using a seperate Model for this so Multiple File Items can
 * be attached to a Model
 * 
 */

class FileAttachment extends FileManagerAppModel {
		
	public $name = 'FileAttachment';

	public $hasMany = array(
		'Myfile' => array(
			'className' => 'FileManager.Myfile',
			'foreignKey' => 'file_id'
		)
	);
}