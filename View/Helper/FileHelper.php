<?php
App::uses('AppHelper', 'View/Helper');
class FileHelper extends AppHelper {

	public $helpers = array(
		'Html',
		'Form',
		'Url',
		'FileManager.PhpThumb'
	);

	public $options = array(
		'width' => null, // was 300 but sometimes we just don't want any width or height attribute, so should only have value if specified
		'height' => null, // was 300 but sometimes we just don't want any width or height attribute, so should only have value if specified
		'url' => array(),
		'class' => 'file-item',
		'conversion' => 'resizeCrop',
	);

	public $types = array(
		'images' => array(
			'jpg',
			'jpeg',
			'gif',
			'png',
			'bmp'
		),
		'video' => array(
			'mpg',
			'mov',
			'wmv',
			'rm',
			'3g2',
			'3gp',
			'3gp2',
			'3gpp',
			'3gpp2',
			'avi',
			'divx',
			'dv',
			'dv-avi',
			'dvx',
			'f4v',
			'flv',
			'h264',
			'hdmov',
			'm4v',
			'mkv',
			'mp4',
			'mp4v',
			'mpe',
			'mpeg',
			'mpeg4',
			'mpg',
			'nsv',
			'qt',
			'swf',
			'xvid',
			'youtube'
		),
		'audio' => array(
			'aif',
			'mid',
			'midi',
			'mka',
			'mp1',
			'mp2',
			'mp3',
			'mpa',
			'wav',
			'aac',
			'flac',
			'ogg',
			'ra',
			'raw',
			'wma'
		),
		'docs' => array(
			'pdf',
			'doc',
			'docx',
			'ods',
			'odt'
		),
	);

/**
 * Constructor
 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		$this->filePath = DS . 'theme' . DS . 'default' . DS . 'files' . DS;
		$this->fileUrl = '/theme/default/files/';
		$this->streamUrl = Router::url(array('plugin' => 'file_manager', 'controller' => 'files', 'action' => 'stream'));
	}

/**
 * After render file
 */
	public function afterRenderFile($viewFile, $content) {
		// reset because there can be conflicts with more than one display() on a page
		$defaults = get_class_vars('FileHelper');
		$this->options = $defaults['options'];
	}

/**
 * Display method
 * 
 * @param array (File array)
 * @param array 
 */
	public function display($item, $options = array()) {
		$this->options = array_merge($this->options, $options);
		$item = isset($item['File']) ? $item['File'] : $item;
		if ($this->getType($item)) {
			$method = $this->type . 'File';
			return $this->$method($item);
		} else {
			return $this->noImage();
		}
	}

/**
 * Thumb method
 * 
 * @param array (File array)
 * @param array 
 */
	public function thumb($item, $options = array()) {
		$this->options = array_merge($this->options, $options);
		$item = isset($item['File']) ? $item['File'] : $item;
		if ($this->getType($item)) {
			 // call thumb method if it exists, else get the standard pre-dating File function
			$method = method_exists($this, $this->type . 'Thumb') ? $this->type . 'Thumb' : $this->type . 'File';
			return $this->$method($item);
		} else {
			return $this->noImage();
		}
	}
	
	public function videoThumb($item) {
		if ($item['extension'] == 'youtube') {
			parse_str(parse_url($item['filename'], PHP_URL_QUERY), $vars);
			return $this->_View->element('File.youtube_thumb_display', array(
				'youtubeId' => $vars['v'],
				'id' => $item['id'],
				'options' => $this->options
			));
		} else {
			debug('have not written any non youtube video thumb functions');
			exit;
		}
	}

/**
 * Find method
 */
 	public function find($type = 'first', $params = array()) {
		App::uses('FileAttachment', 'FileManager.Model');
		$FileAttachment = new FileAttachment;
		// $params['contain'][] = 'File'; contain isn't working here, and I don't know why???? RK
		$attachments = $FileAttachment->find($type, $params);
		if (!empty($attachments[0])) {
			// $type = 'all'
			$ids = Set::extract('/FileAttachment/file_id');
		} else {
			// $type = 'first'
			$ids = $attachments['FileAttachment']['file_id'];
		}
		App::uses('File', 'File.Model');
		$File = new File;
 		return $File->find('all', array('conditions' => array('File.id' => $ids)));
 	}
	
/**
 * Show method
 * Like display except it will look up the image for you if you give a model and foreignKey
 * 
 * @param array find params
 */
 	public function show(array $params, $options = array()) {
 		$file = $this->find('first', $params);
		return $this->display($file[0]['File'], $options);
 	}

/**
 * Images file
 * 
 * @param array
 */
	public function imagesFile($item) {
		$imagePath = $this->fileUrl . $this->type . '/' . $item['filename'] . '.' . $item['extension'];
		$thumbImageOptions = array_merge(array(
			'width' => $this->options['width'],
			'height' => $this->options['height'],
			'alt' => $item['title'],
			'class' => $this->options['class'] . ' file-image-thumb',
		), $this->options);
		
		$extOptions = array('conversion' => $this->options['conversion'], 'quality' => 70, 'alt' => 'thumbnail', 'caller' => 'File');
		$image = $this->Html->image($imagePath, $thumbImageOptions, $extOptions);
		
		return $this->_View->element('File.image_display', array(
			'image' => $image,
			'class' => $this->options['class'],
			'url' => $this->options['url'],
			'id' => $item['id'],
		));
	}

/**
 * Audio display helper uses jplayer see
 * http://jplayer.org/
 */
	public function audioFile($item) {
		$track = array($item['extension'] => $this->fileUrl . $this->type . '/' . $item['filename'] . '.' . $item['extension']);
		return $this->_View->element('File.audio_display', array(
			'tracks' => json_encode($track),
			'class' => $this->options['class'],
			'url' => $this->options['url'],
			'id' => $item['id'],
			'title' => $item['title']
		));
	}

/**
 * Audio display helper uses jplayer see
 * http://jplayer.org/
 */
	public function docsFile($item) {
		$file = array($item['extension'] => $this->fileUrl . $this->type . '/' . $item['filename'] . '.' . $item['extension']);
		return $this->_View->element('File.document_display', array_merge(array(
			'class' => $this->options['class'],
			'url' => $file[$item['extension']],
			'id' => $item['id'],
			'title' => $item['title']
		), $file));
	}

/**
 * jplayer display helper uses jplayer see
 * http://jplayer.org/
 */
	public function jplayer($items, $options = array()) {
		$tracks = array();
		if (is_array($items)) {
			foreach ($items as $item) {
				$item = isset($item['File']) ? $item['File'] : $item;
				$this->getType($item);
				$track = array(
					'title' => $item['title'],
					 $item['extension'] => $this->streamUrl.'/'.$item['filename'].'.'.$item['extension'],
					'poster' => ''
				);
				$tracks[] = $track;
			}
		} else {
			$this->audioFile($items);
		}
		return $this->_View->Element('File.jplayer_list', array(
			'tracks' => json_encode($tracks),
			'class' => $this->options['class'],
			'url' => $this->options['url'],
			'id' => $item['id'],
			'title' => $item['title']
		));
	}

	public function videoFile($item, $options = array()) {
		if ($item['extension'] === 'youtube') {
			return $this->_View->element('File.youtube_display', array(
				'url' => $item['filename'],
				'height' => $this->options['height'],
				'width' => $this->options['width'],
				'class' => $this->options['class'],
				'id' => $this->options['id'],
			));
		}
		return $this->_View->element('File.video_display', array(
			'url' => $this->streamUrl . '/' . $item['filename'] . '.' . $item['extension'],
			'height' => $this->options['height'],
			'width' => $this->options['width'],
			'class' => $this->options['class'],
			'id' => $this->options['id'],
		));
	}

/**
 * Get Type method
 * 
 * @param array
 * @return string
 */
	public function getType($item) {
		foreach ($this->types as $type => $extensions) {
			if (!empty($item['extension']) && in_array($item['extension'], $extensions)) {
				$this->type = $type;
				return $type;
			}
		}
		return false;
	}

/**
 * Load data method
 */
	public function loadData($options = array()) {
		$this->Model = ClassRegistry::init('FileManager.FileGallery');
		$defaults = array();
		$options = Set::merge($options, $defaults);
		$data = $this->Model->find('all', $options);
		return $data;
	}

/**
 * Carousel method
 */
	public function carousel($type = 'default', $options = array()) {
		return $this->_View->element('FileManager.carousels/' . $type, $options);
	}

/**
 * @todo the save path (thumbsPath) should be a CDN
 * 
 * @param array $item
 * @param array $options
 * @return string|false
 */
	public function phpthumb($item, $options = array()) {
		$this->options = array_merge($this->options, $options);
		if (!empty($item)) {
			if (is_array($item)) {
				$image = $item['filename'] . '.' . $item['extension'];
			} else {
				$image = $item;
			}
			$this->getType($item);
			Configure::write('PhpThumb.thumbsPath', ROOT . DS . SITE_DIR . DS . 'Locale' . DS . 'View' . DS . 'webroot' . DS . 'files' . DS . $this->type . DS );
			Configure::write('PhpThumb.displayPath', $this->fileUrl . $this->type . '/' . 'tmp');
			return $this->PhpThumb->thumbnail($image, $options, $options);
		} else {
			Configure::write('PhpThumb.thumbsPath', ROOT . DS . 'app' . DS . 'webroot' . DS . 'img' . DS);
			Configure::write('PhpThumb.displayPath', '/' . 'img' . '/' . 'tmp');
			return $this->PhpThumb->thumbnail('lgnoimage.gif', $options, $options);
		}
	}

/**
 * Returns an Image Tag of the default "no image found" image
 *
 * @return string
 */
 	public function noImage() {
		$locale = ROOT . DS . SITE_DIR . DS . 'Locale' . DS . 'View' . DS . 'webroot' . DS . 'img' . DS;
		$root = ROOT . DS . 'app' . DS . 'webroot' . DS . 'img' . DS;
		$path = file_exists($locale . 'lgnoimage.gif') ? $locale : $root;
		Configure::write('PhpThumb.thumbsPath', $path);
		Configure::write('PhpThumb.displayPath', '/' . 'img' . '/' . 'tmp');
 		return $this->PhpThumb->thumbnail('lgnoimage.gif', $this->options, $this->options);
 	}

}
