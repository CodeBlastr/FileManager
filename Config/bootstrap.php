<?php
ini_set('xdebug.auto_trace', 1);
ini_set('max_execution_time', 300); //300 seconds = 5 minutes

require_once ROOT.DS.APP_DIR.DS.'Plugin'.DS.'FileManager'.DS.'Vendor'.DS.'AWSsdk'.DS.'aws-autoloader.php';


App::uses('FileStorageUtils', 'FileManager.Lib/Utility');
App::uses('StorageManager', 'FileManager.Lib');

App::uses('S3StorageListener', 'FileManager.Event');
App::uses('ImageProcessingListener', 'FileManager.Event');
App::uses('LocalFileStorageListener', 'FileManager.Event');
App::uses('LocalImageProcessingListener', 'FileManager.Event');

App::uses('CakeEventManager', 'Event');
App::uses('ClassRegistry', 'Utility');
CakePlugin::load(array('Imagine' => array('bootstrap' => true)));
Configure::write('Imagine.salt', 'T!6stub6f=as5e4U#u8u7!ut9wRuphUS');
Configure::write('FileStorage.adapter', 'Local');
// Only required if you're *NOT* using composer or another autoloader!
spl_autoload_register(__NAMESPACE__ .'\FileStorageUtils::gaufretteLoader');

// Attach the Image Processing Listener to the global CakeEventManager
$listener = new ImageProcessingListener();
CakeEventManager::instance()->attach($listener);
$listener = new LocalImageProcessingListener();
CakeEventManager::instance()->attach($listener);
//$listener = new LocalFileStorageListener();
//CakeEventManager::instance()->attach($listener);

Configure::write('FileStorage.subDir', 'file_storage');

if (defined('SITE_DIR')) {
	$basePath = ROOT . DS . SITE_DIR . DS . 'Locale' . DS .'View' . DS . 'webroot' . DS . Configure::read('FileStorage.subDir');
}	else	{
	$basePath = APP . 'plugin' . DS . 'FileManager' . DS . 'webroot' . DS . Configure::read('FileStorage.subDir');
}

Configure::write('Media', array(
	// Configure the `basePath` for the Local adapter, not needed when not using it
	'basePath' => $basePath . DS,
	// Configure image versions on a per model base
	'imageSizes' => array(
		'ImageStorage' => array(
			'large' => array(
				'thumbnail' => array(
					'mode' => 'inbound',
					'width' => 800,
					'height' => 800)),
			'medium' => array(
				'thumbnail' => array(
					'mode' => 'inbound',
					'width' => 200,
					'height' => 200
				)
			),
			'small' => array(
				'thumbnail' => array(
					'mode' => 'inbound',
					'width' => 80,
					'height' => 80
				)
			)
		)
	)
));

// This is very important! The hashes are needed to calculate the image versions!
ClassRegistry::init('FileManager.ImageStorage')->generateHashes();

use Aws\S3;
App::uses('ConnectionManager', 'Model');
$dataSource = ConnectionManager::enumConnectionObjects ();

//debug($dataSource);exit;
Configure::write('FileStorage.bucketPrefix', true);

if(isset($dataSource['aws'])) {
	Configure::write('FileStorage.adapter', 'S3Storage');
	// Attach the S3 Listener to the global CakeEventManager

	$dataSource['aws']['@http'] = [
			'progress' => function ($expectedDl, $dl, $expectedUl, $ul) {
					printf(
							"%s of %s downloaded, %s of %s uploaded.\n",
							$expectedDl,
							$dl,
							$expectedUl,
							$ul
					);
			}
	];
	
	$listener = new S3StorageListener();
	CakeEventManager::instance()->attach($listener);
	$S3Client = \Aws\S3\S3Client::factory ( $dataSource['aws'] );
	StorageManager::config ( 'S3Storage', array (
			'adapterOptions' => array (
				$S3Client,
				'testdevlocal',
				array (),
				true 
				),
			'adapterClass' => '\Gaufrette\Adapter\AwsS3',
			'class' => '\Gaufrette\Filesystem'
	) );
}	else	{
		Configure::write('FileStorage.adapter', 'Local');
		StorageManager::config('Local', [
			'adapterOptions' => [Configure::read('Media.basePath'), true],
			'adapterClass' => '\Gaufrette\Adapter\Local',
			'class' => '\Gaufrette\Filesystem'
		]);
}
