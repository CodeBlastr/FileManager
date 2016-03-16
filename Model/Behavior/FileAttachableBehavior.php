<?php
App::uses('FileAttachment', 'FileManager.Model');
App::uses('Myfile', 'FileManager.Model');

class FileAttachableBehavior extends ModelBehavior {

	public $settings = array();

/**
 * Setup method
 */
	public function setup(Model $Model, $settings = array()) {
		//Add the HasMany Relationship to the $Model
		$Model->bindModel($this->_bindModel($Model),false);
	}

/**
 * beforeSave is called before a model is saved.  Returning false from a beforeSave callback
 * will abort the save operation.
 *
 * This strips the File from the request and places it in a variable
 * Uses the AfterSave Method to save the attchement
 *
 * * @todo Might be a better way to do this with model associations
 *
 * @param Model $Model Model using this behavior
 * @return mixed False if the operation should abort. Any other result will continue.
 */
	public function beforeSave(Model $Model, $options = array()) {
		if (!empty($Model->data['Myfile'])) { // do not make this ['Myfile'][0]
			$File = new Myfile();
			$files = $File->upload($Model->data);
			$ids = Set::extract('/id', $files['Myfile']);
			foreach ($ids as $id) {
				$Model->data['FileAttachment'][]['file_id'] = $id;
			}
		}
		//doing it this way to protect against saveAll
		if (isset($Model->data['FileAttachment'])) {
			$this->data['FileAttachment'] = $Model->data['FileAttachment'];
			unset($Model->data['FileAttachment']);
		}
		return true;
	}


/**
 * afterSave is called after a model is saved.
 * We use this to save the attachement after the $Model is saved
 *
 * @param Model $Model Model using this behavior
 * @param boolean $created True if this save created a new record
 * @return boolean
 */
	public function afterSave(Model $Model, $created, $options = array()) {
		if(isset($this->data['FileAttachment'])) {
			$FileAttachment = new FileAttachment();
			//Removes all Attachment Records so they can be resaved
			if(!$created) {
				$FileAttachment->deleteAll(array(
					'model' => $Model->alias,
					'foreign_key' => $Model->data[$Model->alias]['id']
					), false);
			}
			if(is_array($this->data['FileAttachment'])) {
				foreach($this->data['FileAttachment'] as $k => $file) {
					$file['model'] = $Model->alias;
					$file['foreign_key'] = $Model->data[$Model->alias]['id'];
					$this->data['FileAttachment'][$k] = $file;
				}
			} else {
				$this->data['FileAttachment']['model'] = $Model->alias;
				$this->data['FileAttachment']['foreign_key'] = $Model->data[$Model->alias]['id'];
			}
			$FileAttachment->create();
			$FileAttachment->saveMany($this->data['FileAttachment']);
		}
		return true;
	}

/**
 * Before delete is called before any delete occurs on the attached model, but after the model's
 * beforeDelete is called.  Returning false from a beforeDelete will abort the delete.
 *
 * We are unbinding the association model, so we can handle the delete ourselves
 *
 * @todo Might be a better way to do this with model associations
 *
 * @param Model $Model Model using this behavior
 * @param boolean $cascade If true records that depend on this record will also be deleted
 * @return mixed False if the operation should abort. Any other result will continue.
 */
	public function beforeDelete(Model $Model, $cascade = true) {
		//unbinds the model, so we can handle the delete
		$Model->unbindModel(
        	array('hasMany' => array('FileAttachment'))
    	);
		return true;
	}

/**
 * After delete is called after any delete occurs on the attached model.
 *
 * Deletes all attachment records, but keeps the attached data
 *
 * @todo The deleteAll() here seems to not even be necessary, due to the attachments in _bindModel()
 *
 * @param Model $Model Model using this behavior
 * @return void
 */
	public function afterDelete(Model $Model) {
		$FileAttachment = new FileAttachment; // This was added to support UserGroup delete
		// delete all File links
		$FileAttachment->deleteAll(array(
			'model' => $Model->alias,
			'foreign_key' => $Model->data[$Model->alias]['id']
		), false);
	}

/**
 * After find callback. Can be used to modify any results returned by find.
 *
 * This is used to attach the actual File to the $Model Data and removes the attachment data
 *
 * @todo There is probable a better way to do this with model binding and associations
 *
 *
 * @param Model $Model Model using this behavior
 * @param mixed $results The results of the find operation
 * @param boolean $primary Whether this model is being queried directly (vs. being queried as an association)
 * @return mixed An array value will replace the value of $results - any other value will be ignored.
 */
	public function beforeFind(Model $Model, $query) {
		//Allows us to pass $query['file'] = false to not contain file
		if(isset($query['file']) && !$query['file']) {
			return $query;
		}
		if(empty($Model->hasAndBelongsToMany['Myfile'])){
			$Model->bindModel($this->_bindModel($Model),false);
		}

		$query['contain'][] = 'Myfile';
		$query['contain'][] = 'FileThumbnail';
		return $query;
	}

/**
 * After find callback
 * 
 * Unserialize the response from Google Maps
 * 
 * @param Model $Model
 * @param array $results
 * @param boolean $primary
 * @return array
 */
	public function afterFind(Model $Model, $results, $primary = false) {
		// handles many
		for ($i=0; $i < count($results); $i++) {
			if (!empty($results[$i]['Myfile'])) {
				$results[$i]['_File'] = Set::combine($results[$i], 'FileManager.{n}.code', 'FileManager.{n}'); 
			}
		}
		// handles one
		if (!empty($results['Myfile'])) {
			$results['_File'] = Set::combine($results, 'FileManager.{n}.code', 'FileManager.{n}'); 
		}
		return $results;
	}

/**
 * Bind Model method
 *
 * @param object $Model
 */
	protected function _bindModel($Model){
    	return array('hasAndBelongsToMany' => array(
        	'Myfile' =>
            	array(
                	'className' => 'FileManager.Myfile',
                	'joinTable' => 'file_attachments',
                	'foreignKey' => 'foreign_key',
                	'associationForeignKey' => 'file_id',
                	'conditions' => array(
                		'FileAttachment.model' => $Model->alias,
                		'OR' => array(
                				array('FileAttachment.primary' => 0),
                				array('FileAttachment.primary' => null)
                			)
                		),
            		'order' => array('FileAttachment.order')
            	),
        	'FileThumbnail' =>
        		array(
    				'className' => 'FileManager.Myfile',
    				'joinTable' => 'file_attachments',
    				'foreignKey' => 'foreign_key',
    				'associationForeignKey' => 'file_id',
    				'conditions' => array('FileAttachment.model' => $Model->alias, 'FileAttachment.primary' => true),
        		)
        	)
		);
	}

}
