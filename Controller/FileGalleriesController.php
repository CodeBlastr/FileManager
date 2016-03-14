<?php

/**
 * To Extend use code
 * $refuseInit = true; require_once(ROOT.DS.'app'.DS.'Plugin'.DS.'FileManager'.DS.'Controller'.DS.'FileGalleriesController.php');
 */
class AppFileGalleriesController extends FileManagerAppController {

	public $name = 'FileGalleries';
	public $uses = 'FileManager.FileGallery';

	public $helpers = array('FileManager.Myfile');

	public $displayElements =  array(
		'jplayer_element' => 'JPlayer',
	);

	public function index() {
		$galleries = $this->paginate();
		$this->set('tagOptions', $this->displayElements);
		$this->set('galleries', $galleries);
	}

	public function add() {
		$this->view = 'add_edit';
		if ( !empty($this->request->data) ) {
			$this->request->data['User']['id'] = $this->Auth->user('id');
			if ( $this->FileGallery->save($this->request->data) ) {
				$this->Session->setFlash('Files Gallery created.');
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Unable to save this file gallery.');
			}
		}
	}

	/**
	 *
	 * @param char $uid The UUID of the file gallery in question.
	 */
	public function edit($uid = null) {
		$this->view = 'add_edit';
		$this->FileGallery->id = $uid;
		if ( empty($this->request->data) ) {
			$this->FileGallery->contain(array(
				'Thumbnail',
				'Myfile'
			));
			$this->request->data = $this->FileGallery->findById($uid);
		} else {
			if ( $this->FileGallery->save($this->request->data) ) {
				$this->Session->setFlash('Your file gallery has been updated.');
				$this->redirect(array('action' => 'index'));
			}
		}
	}

	/**
	 *
	 * @param char $fileID The UUID of the file gallery in question.
	 */
	public function view($fileID = null) {
		if ( $fileID ) {
			$theFile = $this->FileGallery->find('first', array(
				'conditions' => array(
					'FileGallery.id' => $fileID
				),
				'contain' => array('User', 'Myfile')
			));

			$this->pageTitle = $theFile['Myfile']['title'];
			$this->set('theFile', $theFile);
		}
	}

	public function my() {
		$userID = ($this->Auth->user('id')) ? $this->Auth->user('id') : false;
		if ( $userID ) {
			$this->request->data = $this->FileGallery->find('all', array(
				'conditions' => array(
					'FileGallery.modifier_id' => $userID,
				),
				'order' => array('FileGallery.created' => 'DESC')
			));

		} else {
			$this->redirect('/');
		}
	}

	public function delete($id) {
		try {
			if (isset($id) && $this->FileGallery->exists($id)) {
				$this->loadModel('FileManager.FileAttachment');
				if (!$this->FileGallery->delete($id, false)) {
					throw new Exception('Could not delete File Record');
				}
				if (!$this->FileAttachment->deleteAll(array('foreign_key' => $id))) {
					throw new Exception('Could not delete attachment records');
				}
				$this->Session->setFlash(__('Gallery deleted.'), 'flash_success');
			} else {
				throw new MethodNotAllowedException('Action not allowed');
			}
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
		}

		$this->redirect($this->referer());
	}

	/**
	 * function for ajax request to retrieve file element by gallery id
	 *
	 *
	 */
	public function getGalleryFiles($galleryid) {
		$limit = isset($this->request->query['limit']) ? $this->request->query['limit'] : 48;
		$this->layout = null;
		$this->autoRender = false;
		if($galleryid) {
			$this->request->data = $this->FileGallery->find('all', array(
					'conditions' => array('id' => $galleryid),
					'fields' => array('FileGallery.id'),
					'contain' => array('Myfile' => array('fields' => array('Myfile.extension', 'Myfile.filename', 'Myfile.id'), 'limit' => $limit, 'order' => 'RAND()')),
			));
		}

		$this->request->data = array('path' => $this->FileGallery->File->fileUrl.'images/', 'Myfile' => $this->request->data[0]['Myfile']);
		return json_encode($this->request->data);
	}


/**
 * action that handles the canvasBuildrr
 *
 * @etymology Late Middle English: from Old Northern French canevas, based on Latin cannabis ‘hemp,’ from Greek kannabis.
 * @param char $galleryId
 * @param char $fileId
 */
	public function canvas($galleryId = null, $fileId = null) {
		if ($this->request->isAjax()) {
			// handle presses of the canvas' save button
			$response = $this->FileGallery->File->updateCanvasObjects($this->request->data, $galleryId);
			$this->__returnJsonResponse($response);
		} else {
			// No parameters passed. This is a brand new freeform canvas gallery.
			if (!($galleryId) && !($fileId)) {
				// generate a gallery w/ 4 attached File
				$firstFileId = $this->FileGallery->generate(array('Myfile' => 4));
				// redirect them to this gallery's first page editor
				$this->redirect(array('action' => 'canvas', $this->FileGallery->id, $firstFileId));
			}

			// A fileId was specified.  Find it and return it's data.
			if ($fileId) {
				$this->request->data = $this->FileGallery->find('first', array(
					'conditions' => array('FileGallery.id' => $galleryId)
				));
				#dirty
				if (!empty($this->request->data['Myfile'])) {
					foreach ($this->request->data['Myfile'] as &$file) {
						if ($file['id'] === $this->passedArgs[1]) {
							// add the `id` into the data field, as this is the data used by the JavaScript..
							$file['data'] = json_decode($file['data']);
							$file['data']->id = $fileId;
							$file['data'] = json_encode($file['data']);
						}
					}
				}
			} else {
				// $galleryId provided & $fileId not provided.  Redirect them to their page 1 editor.
				$this->request->data = $this->FileGallery->find('first', array(
					'conditions' => array('FileGallery.id' => $galleryId)
				));
				$this->redirect(array('action' => 'canvas', $galleryId, $this->request->data['Myfile'][0]['id']));
			}
		}
	}


	public function printCanvas($id, $page = 1) {
		$this->request->data = $this->FileGallery->find('first', array(
			'conditions' => array('FileGallery.id' => $id)
		));

		// format data
		foreach ($this->request->data['Myfile'] as $file) {
			$collectionArray[] = json_decode($file['data']);
		}

		if ($page == 1) {
			$sides = array(
				$collectionArray[3],
				$collectionArray[0]
			);
		}
		if ($page == 2) {
			$sides = array(
				$collectionArray[1],
				$collectionArray[2]
			);
		}

		$this->set('collectionArray', $sides);

		if ($_REQUEST['debugger'] !== '3') {
			$this->layout = false;

			try {
				$this->WkHtmlToPdf = $this->Components->load('WkHtmlToPdf');
				$this->WkHtmlToPdf->initialize($this);
				$pdfLocation = $this->WkHtmlToPdf->rasterizePdf(true, null, 'rasterize.ttysoon');
			} catch (Exception $e) {
				$this->Session->setFlash($e->getMessage());
				$this->redirect($this->referer());
			}
		}
	}



/**
 * Creates a duplicate Gallery for the current user.
 */
	public function duplicateGallery($id) {
		try {
			$myGalleryId = $this->FileGallery->duplicate($id);
			$this->redirect(array('action' => 'canvas', $myGalleryId));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect($this->referer());
		}
	}

}

if (!isset($refuseInit)) {
	class FileGalleriesController extends AppFileGalleriesController {}
}
