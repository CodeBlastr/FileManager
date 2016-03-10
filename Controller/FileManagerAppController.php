<?php
class FileManagerAppController extends AppController {
	protected function __returnJsonResponse($response) {
		$this->autoRender = false;
		$this->response->statusCode($response['statusCode']);
		$this->response->body(json_encode($response['body']));
	}

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
