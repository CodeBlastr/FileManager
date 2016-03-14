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
		foreach ($fileGallery['Myfile'] as $file) {
			$originalId = $file['id'];
			$this->Myfile->create();
			$file['id'] = null;
			$file['creator_id'] = $file['modifier_id'] = $file['user_id'] = $this->userId;
			$this->Myfile->save($file, array('callbacks' => false));
			// re-save the `content` with the correct ID.
			$updatedContent = str_replace($originalId, $this->Myfile->id, $file['content']);
			$this->Myfile->set('content', $updatedContent);
			$this->Myfile->save($file, array('callbacks' => false));
			
			$this->Myfile->FileAttachment->create();
			$this->Myfile->FileAttachment->save(array(
				'FileAttachment' => array(
					'model' => 'FileGallery',
					'foreign_key' => $this->id,
					'file_id' => $this->Myfile->id,
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
 * Generates a FileGallery with $options['Myfile'] number of attached file
 */
	public function generate($options) {
		// create gallery
		$newGallery = $this->create(array(
			'title' => 'Untitled'
		));
		$this->save($newGallery);
		
		// create a File row foreach page
		$fileToGenerate = $options['Myfile'];
		for ($i=0; $i < $fileToGenerate; $i++) {
			$this->Myfile->create();
			$this->Myfile->save(array(
				'Myfile' => array(
					'filename' => '',
					'model' => 'Myfile'
				)
			), array('callbacks' => false));
			if ($i === 0) {
				// store the first page's id (File.order), so we can redirect them later
				$firstFileId = $this->Myfile->id;
			}
			$this->Myfile->FileAttachment->create();
			$this->Myfile->FileAttachment->save(array(
				'FileAttachment' => array(
					'model' => 'FileGallery',
					'foreign_key' => $this->id,
					'file_id' => $this->Myfile->id,
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
