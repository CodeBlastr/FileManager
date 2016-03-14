<?php
App::uses('FileManagerAppController', 'Controller');
class FileManagerAppController extends AppController {

	/**
	 * Simple Method for detecting what model to save to
	 * by file mime_type
	 */

	protected function _detectModelByFileType ($mime_type) {
		if (empty($mime_type)) {
			return false;
		}
		return FileStorageUtils::detectModelByFileType($mime_type);
	}
}
