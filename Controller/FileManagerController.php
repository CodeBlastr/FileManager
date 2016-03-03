<?php

/**
 * To Extend use code
 * $refuseInit = true; require_once(ROOT.DS.'app'.DS.'Plugin'.DS.'FileManager'.DS.'Controller'.DS.'FilesController.php');
 */

/**
 * This is the REST API for the File Manager
 * 
 *
 */


class AppFileManagerController extends FileManagerAppController {

	public $name = 'FileManager';

	public $uses = array('FileManager.File', 'FileManager.FileGallery');

	public $helpers = array('FileManager.File');
	
	public $allowedActions = array('index');
	
	public $viewPath = '/FileManager';
	
/**
 * Filebrowser Action
 * Supports Ajax
 * All this does is return the filebrower view
 * 
 */
	public function filebrowser($galleryid = false) {
		
		if(isset($this->request->query['selected'])) {
			$selected = $this->request->query['selected'];
		} else {
			$selected = 0;
		}

		$limit = $this->request->query('limit');
		$page = $this->request->query('page');
		if(!$limit) $limit = 20;

		$this->Session->write('FileManager.limit', $limit);
	
		$galleryid = isset($this->request->query['galleryid']) ? $this->request->query['galleryid'] : array();
		
		$this->set('galleryid', $galleryid);
		$this->set('galleries', $this->FileGallery->find('list'));
	
		if($this->request->isAjax()) {
			$this->layout = null;
		}
	}
	
/**
 * File method
 * Index of the file. Only gives back the file owned by the person viewing the
 * file Should only be called with AJAX
 * 
 * @param uuid $id
 */
	public function file($id = null) {
		if($this->request->is('get')) {
			$conditions = array();
			if(isset($this->request->query['limit'])) {
				$conditions['limit'] = $this->request->query['limit'];
			}

			if(!$conditions['limit'])	{
				$conditions['limit'] = $this->Session->read('FileManager.limit') ? $this->Session->read('FileManager.limit') : 20;
			}

			if(isset($this->request->query['type']) && $this->request->query['type'] != 'all') {
				$conditions['conditions']['type'] = $this->request->query['type'];
			}
			if(isset($this->request->query['order'])) {
				$conditions['order'] = $this->request->query['order'];
			}else {
				$conditions['order'] = array('title ASC');
			}
			if($this->Session->read('Auth.User.user_role_id') != 1) {
				$conditions['conditions']['creator_id'] = $this->userId;
			}
			$files = $this->File->find('all', $conditions);
			
			$this->request->data = array();
			foreach($files as $f) {
				$f['File']['type'] = $this->File->fileType($f['File']['extension']);
				$f = $this->File->save($f, array('callbacks' => false));
				$this->request->data[] = $f['File'];
			}
			//debug($this->request->data);
			if($this->request->is('ajax')) {
				$this->autoRender = false;
				return json_encode($this->request->data);
			}
		}
		
		if($this->request->is('put')) {
			$file['File'] = $this->request->data;
			if($this->File->save($file, array('callbacks' => false))) {
				$this->response->statusCode(200);
			} else {
				$this->response->statusCode(500);
			}
		}
		
		if($this->request->is('delete')) {
			$file = $this->File->findById($id);
			if (in_array($file['File']['extension'], $this->File->uploadExclusionExtensions)) {
				// some file are just links 
				if(!$this->File->delete($id)) {
				   $this->response->statusCode(200);
				}
			} else {
				$filename = $this->File->themeDirectory.DS.$file['File']['type'].DS.$file['File']['filename'].'.'.$file['File']['extension'];
				$this->response->statusCode(200);
				if(unlink($filename)) {
					if(!$this->File->delete($id)) {
					   $this->response->statusCode(500);
					}
				} else {
					$this->response->statusCode(500);
				}
			}
		}
	}
	
	public function upload() {
		$this->layout = false;
		$this->autoRender = false;
		if (!empty($this->request->data)) {
		 try{
			if(isset($this->request->data['FileAttachment'])) {
				$this->loadModel('FileManager.FileAttachment');
			}
				
			$this->request->data['User']['id'] = $this->Auth->user('id');
			$filearray = array();
			foreach($this->request->data['File']['files'] as $file) {
				$file['File'] = array(
						'user_id' => $this->Auth->user('id'),
						'filename' => $file,
						'title' => is_array($file) ? $file['name'] : $file
				);
				$this->File->create();
				$file = $this->File->upload($file);
				if(isset($this->request->data['FileAttachment'])) {
					$attachedfile = array(
							'FileAttachment' => array(
									'file_id' => $file['File']['id'],
									'model' => $this->request->data['FileAttachment']['model'],
									'foreign_key' => $this->request->data['FileAttachment']['foreign_key'],
							));
					$this->FileAttachment->create();
					$file = $this->FileAttachment->save($attachedfile);
				}
		
				if($file) {
					$filearray[] = $file['File'];
				}
			}
			$this->response->statusCode(200);
			if(!empty($filearray)) {
				$this->layout = false;
				$this->autoRender = false;
				return json_encode($filearray);
			} else {
				return 'No Files Uploaded';
			}
		 }catch(Exception $e) {
		 	$this->response->statusCode(500);
		 	return 'Error: '.$e->getMessage();
		 }
				
		}
	}
	
}

if (!isset($refuseInit)) {
	class FileManagerController extends AppFileManagerController{}
}