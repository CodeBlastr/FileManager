<?php

/**
 * To Extend use code
 * $refuseInit = true; require_once(ROOT.DS.'app'.DS.'Plugin'.DS.'FileManager'.DS.'Controller'.DS.'FileManagerController.php');
 */


class AppFilesController extends FileManagerAppController {

	public $name = 'Files';
	public $uses = 'FileManager.Myfile';
	public $allowedActions = array('index', 'view', 'notification', 'stream', 'my', 'add', 'edit', 'sorted', 'record');
	public $helpers = array('FileManager.Myfile');


/**
 * kinda expects URL to be: /file_manager/files/index/(audio|video)
 * shows file of the type passed in the request
 */
	public function index() {
		if (isset($this->request->pass[0])) {
			$fileType = $this->request->pass[0];
		}
		$allFiles = $this->Myfile->find('all', array(
			'conditions' => array(
				'Myfile.filename !=' => '',
				//'Myfile.is_visible' => '1', // 0 = not on our server; 1 = good to go
				'Myfile.type' => $fileType
				)
			));
		$this->set('files', $allFiles);
	}

/**
 * Add method
 */
	public function add() {
		if ($this->request->is('post')) {
			$file = $this->Myfile->upload($this->request->data);
			if (!empty($file)) {
				$this->Session->setFlash('File saved.', 'flash_success');
				if ($this->request->isAjax()) {
					$this->set('file', $file);
					$this->layout = false;
					$this->view = 'ajax-upload';
				} else {
					$this->redirect(array('action' => 'my'));
				}
			} else {
				$this->Session->setFlash('Upload failed, check directory permissions', 'flash_danger');
			}
		}
	}

/**
 *
 * @param char $uid The UUID of the file in question.
 */
	public function edit($uid = null) {
    	$this->Myfile->id = $uid;
		if (empty($this->request->data)) {
			$this->request->data = $this->Myfile->findById($uid);
		} else {
            // save the new file metadata
            if ($this->Myfile->save($this->request->data)) {
                $this->Session->setFlash('Your file has been updated.', 'flash_success');
                $this->redirect($this->referer());
            }
		}
	}


	public function delete($id) {
		try {
			if (isset($id) && $this->Myfile->exists($id)) {
				//Get the file info so we can delete the file
				$file = $this->Myfile->findById($id);
				$type = $this->Myfile->fileType($file['Myfile']['extension']);
				$this->loadModel('FileManager.FileAttachment');
				//Delete the file don't cascade because we need better control over what get delete
				if (!$this->Myfile->delete($id, false)) {
					throw new Exception('Could not delete File Record');
				}
				if (!$this->FileAttachment->deleteAll(array('file_id' => $id))) {
					throw new Exception('Could not delete attachment records');
				}
				if (!unlink($this->Myfile->themeDirectory.DS.$type.DS.$file['Myfile']['filename'].'.'.$file['Myfile']['extension']) && $file['Myfile']['extension'] !== 'youtube') {
					throw new Exception('Could not delete file, please check permissions');
				}
				$this->Session->setFlash(__('Deleted %n', !empty($file['Myfile']['title']) ? $file['Myfile']['title'] : $id ), 'flash_success');
			} else {
				throw new MethodNotAllowedException('Action not allowed');
			}
		} catch(Exception $e) {
			$this->Session->setFlash($e->getMessage(), 'flash_warning');
		}

		$this->redirect($this->referer());
	}


/**
 *
 * @param char $fileID The UUID of the file in question.
 */
	public function view($fileID = null) {
		if ($fileID) {
            // Increase the Views by 1
            $this->Myfile->updateAll(array('Myfile.views'=>'Myfile.views+1'), array('Myfile.id'=>$fileID));

			// Use this to save the Overall Rating to Myfile.rating
			$this->Myfile->calculateRating($fileID, 'rating');

			$theFiles = $this->Myfile->find('first', array(
				'conditions' => array(
                    'Myfile.id' => $fileID
                    ),
				'contain' => 'User'
				));

			$this->pageTitle = $theFile['Myfile']['title'];
			$this->set('theFile', $theFile);
		}
	}


	public function my() {
		$userID = ($this->Auth->user('id')) ? $this->Auth->user('id') : false;
		if ($userID) {
			$allFiles = $this->Myfile->find('all', array(
				'conditions' => array(
					'Myfile.user_id' => $userID,
					// 'Myfile.type' => $fileType
					)
				));
			$this->set('files', $allFiles);
		} else {
			$this->redirect('/');
		}
	}


/**
 * This action can stream or download a files file.
 * Expected Use: /file_manager/files/stream/{UUID}/{FORMAT}
 * @param char $fileID The UUID of the file in question.
 * @param string $requestedFormat The filetype of the file expected.
 */
	public function stream($filename = false) {
		$this->layout = false;
		$this->view = false;
		if ($filename) {
			$filename = explode('.', $filename);
			$ext = array_pop($filename);
			$filename = implode('.', $filename);
			// find the filetype
			$file = $this->Myfile->find('first', array(
				'conditions' => array(
					'filename' => $filename,
					'extension' => $ext,
				)
			));
			
			$mime_type = $this->Myfile->getMimeType($ext);
			if(!$mime_type) {
				throw new NotFoundException();
			}
			$file_dir = $this->Myfile->themeDirectory .DS. $file['Myfile']['type'].DS;
			if(!file_exists($file_dir.$filename.'.'.$ext)) {
				throw new NotFoundException();
			}
			
			$file = $file_dir.$filename.'.'.$ext;
			$size = filesize($file);
			$this->response->header(array(
				'Content-Type' => $mime_type,
				'Accept-Ranges' => 'bytes'
			));
			$this->response->sharable(false, 3600);
	
			// multipart-download and download resuming support
			$rangeheader = $this->request->header('RANGE');
			//debug($rangeheader);exit;
			if ($rangeheader) {
				list ( $a, $range ) = explode('=', $rangeheader, 2);
				list ( $range ) = explode(',', $range, 2);
				list ( $range, $range_end ) = explode('-', $range);
	
				$range = intval($range);
	
				$range_end = (!$range_end) ? $size - 1 : intval($range_end);
				$new_length = $range_end - $range + 1;
				$this->response->statusCode(206);
				$this->response->header(array(
						'Content-Range' => 'bytes ' . ($range - $range_end / $size)
				));
			} 
			
// 			/* output the file itself */
// 			$chunksize = 1 * (1024 * 1024); // you may want to change this
// 			//$bytes_send = 0;
// 			//debug($range);exit;
// 			debug($this->response);exit;
// 			if ($file = fopen($file, 'r')) {
// 				if (isset($_SERVER ['HTTP_RANGE'])) {
// 					fseek($file, $range);
// 				}
				
// 				$this->response->body(fread($file, $chunksize));
				
				
// 				flush();
	
// 				fclose($file);

			$this->response->file($file, array('download' => true));
				
			}

			else {
				$this->response->statusCode(404);
			}
		//debug($this->response);exit;
		return $this->response->send();
	}


	/**
	 * ACTION FOR ELEMENTS
	 */

	/**
	 *
	 * @param string $fileType
	 * @param string $sortOrder
	 * @param integer $numberOfResults
	 * @return array|boolean
	 */
	function sorted($fileType, $field, $sortOrder, $numberOfResults) {
	    $options = array(
          'conditions' => array(
		    'Myfile.type' => strtolower($fileType),
		    'Myfile.is_visible' => '1'
          ),
          'order' => array('Myfile.'.$field => $sortOrder),
          'limit' => $numberOfResults
	    );

	    return $this->Myfile->find('all', $options);

	}


/**
 * record video
 */
	function record($model = 'Myfile', $foreignKey = null) {
		$this->set('uuid', $this->Myfile->_generateUUID());
		$this->set('model', $model);
		$this->set('foreignKey', $foreignKey);

		if(!empty($this->request->data)) {
			if ($this->Myfile->save($this->request->data)) {
				$this->Session->setFlash('File saved.');
				#$this->redirect('/file_manager/files/edit/'.$this->Myfile->id);
				$this->redirect(array('action' => 'my'));
			} else {
				$this->Session->setFlash('Invalid Upload.');
			}
        }

	}

	function images() {
		$this->set('page_title_for_layout', __('File Images'));
	}
	function files() {
		$this->set('page_title_for_layout', __('File Files'));
	}


	/**
	 * Filebrowser Action
	 * Supports Ajax
	 *
	 * @param $uid - The user to show the images for
	 * @param $multiple - Allow the user to select more that one Item
	 */
	public function filebrowser($multiple = true, $uid = null) {
		if($uid == null && $this->Session->read('Auth.User.id') != 1) {
			$uid = $this->userId;
		}

		$galleryid = isset($this->request->query['galleryid']) ? $this->request->query['galleryid'] : array();
		if(!empty($galleryid)) {
			$this->set('galleryid', $galleryid);
		}
		$this->loadModel('FileManager.FilesGallery');
		$this->set('galleries', $this->FilesGallery->find('list'));

		if(!empty($galleryid)) {
			$file = $this->FilesGallery->find('first', array('contain' => 'Myfile', 'conditions' => array('id' => $galleryid)));
			$file = $file['Myfile'];
		}else {
			$multiple = isset($this->request->data['mulitple']) ? $this->request->data['mulitple'] : true;
			if($uid == null) {
				$files = $this->Myfile->find('all');
			}else{
				$files = $this->Myfile->find('all', array('conditions' => array('creator_id' => $uid)));
			}
		}

		$this->set(compact('files', 'multiple'));

		if($this->request->isAjax()) {
			$this->layout = null;
		}

	}


	/**
	 * Lazy Loader Function derives from imgsrc link.
	 * Breaks up the link and uses the filename.
	 *
	 * @param $imglink  The url of the image
	 * @return string rendered filename
	 */

	public function lazyLoad($id) {
		if(!$this->request->is('json')) {
			throw new Exception('Method Not allowed');
		}
		//Using query params to set options array
		$params = array();
		if(isset($this->request->query)) {
			foreach($this->request->query as $k => $p) {
				switch ($k) {
					case 'imgwidth':
						$param['newWidth'] = $p;
						break;

					case 'imgheight':
						$param['newHeight'] = $p;
						break;

					case 'quality':
						$param['quality'] = $p;
						break;

					case 'method':
						if( $p == 'resize' |  $p == 'resizeCrop' | $p == 'crop') {
							$param['cType'] = $p;
						}
						break;

					default:

						break;
				}
			}
		}

		$filename = $this->_resizeImage($id, $params);
		debug($filename);

	}

	/**
	 * Image Resize Handling.
	 * @TODO This probably should be a componenet
	 * (might need to be a "lib" if you want to use it globally) ^JB
	 */

	 public $resizeDefaults = array(
	 	'cType' => 'resize',
		'imgFolder' => false,
		'newName' => false,
		'newWidth' => 100,
		'newHeight' => 100,
		'quality' => 75,
		'bgcolor' => false
	);

	/**
	 * Resize Image function - uses GD Library
	 * @param $id = File ID
	 * @param $options = array(
	 * 				'cType' => { resize | resizeCrop | crop },
	 * 				'location' => { Location of original file },
	 * 				'newName' => { Defaults to False },
	 * 				'newWidth' => { New Image Width },
	 * 				'newHeight' => { New Image Height },
	 * 				'quality' => { new image quality 0-100 },
	 * 				'bgcolor => { New Backround Color }
	 * 			)
	 *
	 * @return bool
	 */

    protected function _resizeImage($id = null, $params = array()) {
    	if(empty($id)) {
    		return false;
    	}else {
    		$file = $this->Myfile->read(array('filename', 'extension', $id));
    	}

    	$params = array_merge($this->resizeDefaults, $params);

		if (file_exists($img)) {
	        list($oldWidth, $oldHeight, $type) = getimagesize($img);
	        $ext = $this->image_type_to_extension($type);

			# check for and create cacheFolder
			$cacheFolder = 'cache';
			$cachePath = $imgFolder . $cacheFolder;
			if (is_dir($cachePath)) {
				# do nothing the cache dir exists
			} else {
				if (mkdir($cachePath)) {
					# do nothing the cache dir exists
				} else {
					debug('Could not make images ' . $cachePath . ', and it doesn\'t exist.');
					break;
				}
			}

	        //check to make sure that the file is writeable, if so, create destination image (temp image)
	        if (is_writeable($cachePath)) {
	            if($newName){
	                $dest = $cachePath .DS . $newName. '.' . $id;
	            } else {
	                $dest = $cachePath . DS . 'tmp_' . $id;
	            }
	        } else {
	            //if not let developer know
	            $imgFolder = substr($imgFolder, 0, strlen($imgFolder) -1);
	            $imgFolder = substr($imgFolder, strrpos($imgFolder, '\\') + 1, 20);
	            debug("You must allow proper permissions for image processing. And the folder has to be writable.");
	            debug("Run \"chmod 775 on '$imgFolder' folder\"");
	            exit();
	        }

	        //check to make sure that something is requested, otherwise there is nothing to resize.
	        //although, could create option for quality only
	        if ($newWidth || $newHeight) {
	            /*
	             * check to make sure temp file doesn't exist from a mistake or system hang up.
	             * If so delete.
	             */
	            if(file_exists($dest)) {
					$size = @getimagesize($dest);
					return array(
						'path' => $cacheFolder . '/' . $newName. '.' . $id,
						'width' => $size[0],
						'height' => $size[1],
						);
	                #unlink($dest);
	            } else {
	                switch ($cType){
	                    default:
	                    case 'resize':
	                        # Maintains the aspect ration of the image and makes sure that it fits
	                        # within the maxW(newWidth) and maxH(newHeight) (thus some side will be smaller)
	                        $widthScale = 2;
	                        $heightScale = 2;

	                        if($newWidth) $widthScale = $newWidth / $oldWidth;
	                        if($newHeight) $heightScale = $newHeight / $oldHeight;
	                        //debug("W: $widthScale  H: $heightScale<br>");
	                        if($widthScale < $heightScale) {
	                            $maxWidth = $newWidth;
	                            $maxHeight = false;
	                        } elseif ($widthScale > $heightScale ) {
	                            $maxHeight = $newHeight;
    	                        $maxWidth = false;
	                        } else {
	                            $maxHeight = $newHeight;
	                            $maxWidth = $newWidth;
	                        }

	                        if($maxWidth > $maxHeight){
	                            $applyWidth = $maxWidth;
	                            $applyHeight = ($oldHeight*$applyWidth)/$oldWidth;
	                        } elseif ($maxHeight > $maxWidth) {
	                            $applyHeight = $maxHeight;
	                            $applyWidth = ($applyHeight*$oldWidth)/$oldHeight;
	                        } else {
	                            $applyWidth = $maxWidth;
	                            $applyHeight = $maxHeight;
	                        }
	                        #debug("oW: $oldWidth oH: $oldHeight mW: $maxWidth mH: $maxHeight<br>");
	                       	#debug("aW: $applyWidth aH: $applyHeight<br>");
	                        $startX = 0;
	                        $startY = 0;
	                        #exit();
	                        break;
	                    case 'resizeCrop':
	                        // -- resize to max, then crop to center
	                        $ratioX = $newWidth / $oldWidth;
	                        $ratioY = $newHeight / $oldHeight;

	                        if ($ratioX < $ratioY) {
	                            $startX = round(($oldWidth - ($newWidth / $ratioY))/2);
	                            $startY = 0;
	                            $oldWidth = round($newWidth / $ratioY);
	                            $oldHeight = $oldHeight;
	                        } else {
	                            $startX = 0;
	                            $startY = round(($oldHeight - ($newHeight / $ratioX))/2);
	                            $oldWidth = $oldWidth;
	                            $oldHeight = round($newHeight / $ratioX);
	                        }
	                        $applyWidth = $newWidth;
	                        $applyHeight = $newHeight;
	                        break;
	                    case 'crop':
	                        // -- a straight centered crop
	                        $startY = ($oldHeight - $newHeight)/2;
	                        $startX = ($oldWidth - $newWidth)/2;
	                        $oldHeight = $newHeight;
	                        $applyHeight = $newHeight;
	                        $oldWidth = $newWidth;
	                        $applyWidth = $newWidth;
	                        break;
	                }

	                switch($ext) {
	                    case 'gif' :
	                        $oldImage = imagecreatefromgif($img);
	                        break;
	                    case 'png' :
	                        $oldImage = imagecreatefrompng($img);
	                        break;
	                    case 'jpg' :
	                    case 'jpeg' :
	                        $oldImage = imagecreatefromjpeg($img);
	                        break;
	                    default :
	                        //image type is not a possible option
	                        return false;
	                        break;
	                }

	                //create new image
	                $newImage = imagecreatetruecolor($applyWidth, $applyHeight);

	                if($bgcolor) {
	                //set up background color for new image
	                    sscanf($bgcolor, "%2x%2x%2x", $red, $green, $blue);
	                    $newColor = ImageColorAllocate($newImage, $red, $green, $blue);
	                    imagefill($newImage,0,0,$newColor);
					};

                    // preserve transparency
                    if($ext == 'gif' || $ext == 'png'){
                        imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
                        imagealphablending($newImage, false);
                        imagesavealpha($newImage, true);
                    }

	                //put old image on top of new image
	                imagecopyresampled($newImage, $oldImage, 0,0 , $startX, $startY, $applyWidth, $applyHeight, $oldWidth, $oldHeight);
    	                switch($ext) {
	                        case 'gif' :
	                            imagegif($newImage, $dest, $quality);
	                            break;
    	                    case 'png' :
	                            imagepng($newImage, $dest, $quality);
	                            break;
	                        case 'jpg' :
	                        case 'jpeg' :
	                            imagejpeg($newImage, $dest, $quality);
	                            break;
	                        default :
	                            return false;
	                            break;
	                    }

         	       imagedestroy($newImage);
	                imagedestroy($oldImage);

	                if(!$newName){
	                    unlink($img);
	                    rename($dest, $img);
	                }

					$size = @getimagesize($cacheFolder . '/' . $newName. '.' . $id);
					return array(
						'path' => $cacheFolder . '/' . $newName. '.' . $id,
						'width' => $applyWidth,
						'height' => $applyHeight,
						);
	            }

	        } else {
	            return false;
	        }
		} else {
			return false; // end the check for if the file to convert even exists
		}
    }

	function image_type_to_extension($imagetype) {
		if(empty($imagetype)) return false;
        switch($imagetype) {
            case IMAGETYPE_GIF    : return 'gif';
            case IMAGETYPE_JPEG    : return 'jpg';
            case IMAGETYPE_PNG    : return 'png';
            case IMAGETYPE_SWF    : return 'swf';
            case IMAGETYPE_PSD    : return 'psd';
            case IMAGETYPE_BMP    : return 'bmp';
            case IMAGETYPE_TIFF_II : return 'tiff';
            case IMAGETYPE_TIFF_MM : return 'tiff';
            case IMAGETYPE_JPC    : return 'jpc';
            case IMAGETYPE_JP2    : return 'jp2';
            case IMAGETYPE_JPX    : return 'jpf';
            case IMAGETYPE_JB2    : return 'jb2';
            case IMAGETYPE_SWC    : return 'swc';
            case IMAGETYPE_IFF    : return 'aiff';
            case IMAGETYPE_WBMP    : return 'wbmp';
            case IMAGETYPE_XBM    : return 'xbm';
            default                : return false;
        }
    }

}

if (!isset($refuseInit)) {
	class FilesController extends AppFilesController{}
}
