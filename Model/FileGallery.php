<?php
App::uses('FileManagerAppModel', 'FileManager.Model');

/**
 * File Gallery Model.
 * 
 * Metadata for a collection of File
 * 
 */

class AppFileGallery extends FileManagerAppModel {
		
	public $name = 'FileGallery';
	public $actsAs = array('FileManager.FileAttachable');
	
	 public $hasOne = array(
        'Thumbnail' => array(
            'className' => 'FileManager.File',
        	'foreignKey' => false,
            'conditions' => array('Thumbnail.id' => 'FileGallery.thumbail'),
            'dependent' => true
        )
    );
	 
	 public $belongsTo = array(
 		'Creator' => array(
 				'className' => 'Users.User',
 				'foreignKey' => 'creator_id',
 		),
 		'Modifier' => array(
 				'className' => 'Users.User',
 				'foreignKey' => 'modifier_id',
 		)
	 );
	
	
	/**
	 * Duplicates an entire gallery, that will be owned by the current logged in user.
	 * 
	 * @param char $galleryId
	 */
	public function duplicate($galleryId) {
		$fileGallery = $this->find('first', array(
			'conditions' => array('id' => $galleryId)
		));
		
		// create gallery
		$newGallery = $this->create(array(
			'title' => 'Copy of ' . $fileGallery['FileGallery']['title'],
			'description' => $fileGallery['FileGallery']['description']
		));
		$this->save($newGallery);
		
		// clone the File & Attach it
		$i = 0;
		foreach ($fileGallery['File'] as $file) {
			$originalId = $file['id'];
			$this->File->create();
			$file['id'] = null;
			$file['creator_id'] = $file['modifier_id'] = $file['user_id'] = $this->userId;
			$this->File->save($file, array('callbacks' => false));
			// re-save the `content` with the correct ID.
			$updatedContent = str_replace($originalId, $this->File->id, $file['content']);
			$this->File->set('content', $updatedContent);
			$this->File->save($file, array('callbacks' => false));
			
			$this->File->FileAttachment->create();
			$this->File->FileAttachment->save(array(
				'FileAttachment' => array(
					'model' => 'FileGallery',
					'foreign_key' => $this->id,
					'file_id' => $this->File->id,
					'creator_id' => $this->userId,
					'modifier_id' => $this->userId,
					'order' => $i
				)
			), array('callbacks' => false));
			++$i;
		}
		
		return $this->id;
	}

/**
 * Generates a FileGallery with $options['File'] number of attached file
 */
	public function generate($options) {
		// create gallery
		$newGallery = $this->create(array(
			'title' => 'Untitled'
		));
		$this->save($newGallery);
		
		// create a File row foreach page
		$fileToGenerate = $options['File'];
		for ($i=0; $i < $fileToGenerate; $i++) {
			$this->File->create();
			$this->File->save(array(
				'File' => array(
					'filename' => '',
					'model' => 'File'
				)
			), array('callbacks' => false));
			if ($i === 0) {
				// store the first page's id (File.order), so we can redirect them later
				$firstFileId = $this->File->id;
			}
			$this->File->FileAttachment->create();
			$this->File->FileAttachment->save(array(
				'FileAttachment' => array(
					'model' => 'FileGallery',
					'foreign_key' => $this->id,
					'file_id' => $this->File->id,
					'creator_id' => $this->userId,
					'modifier_id' => $this->userId,
					'order' => $i
				)
			), array('callbacks' => false));
		}
		
		return $firstFileId;
	}
	
}

if (!isset($refuseInit)) {
	class FileGallery extends AppFileGallery {}
}
