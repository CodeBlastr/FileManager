<?php
die('XXX');
Configure::write('StorageType', 'aws'); //local or aws

if(strtoupper(Configure::read('StorageType'))=='AWS')	{
	require_once ROOT.DS.APP_DIR.DS.'Plugin'.DS.'FileManager'.DS.'Vendor'.DS.'AWSsdk'.DS.'aws-autoloader.php';
	App::uses('S3StorageListener', 'FileManager.Event');
	App::uses('FileStorageUtils', 'FileManager.Lib/Utility');
	App::uses('StorageManager', 'FileManager.Lib');
	App::uses('ImageProcessingListener', 'FileManager.Event');
	App::uses('CakeEventManager', 'Event');
	App::uses('ClassRegistry', 'Utility');
	CakePlugin::load(array('Imagine' => array('bootstrap' => true)));
	Configure::write('Imagine.salt', 'T!6stub6f=as5e4U#u8u7!ut9wRuphUS');
	// Only required if you're *NOT* using composer or another autoloader!
	spl_autoload_register(__NAMESPACE__ .'\FileStorageUtils::gaufretteLoader');

	// Attach the Image Processing Listener to the global CakeEventManager
	$listener = new ImageProcessingListener();
	CakeEventManager::instance()->attach($listener);

	Configure::write('Media', array(
		// Configure the `basePath` for the Local adapter, not needed when not using it
		//'basePath' => APP . 'FileManager' . DS,
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

	if(isset($dataSource['aws'])) {
		// Attach the S3 Listener to the global CakeEventManager
		$listener = new S3StorageListener();
		CakeEventManager::instance()->attach($listener);
		
		$S3Client = \Aws\S3\S3Client::factory ( $dataSource['aws'] );
		StorageManager::config ( 'S3Storage', array (
				'adapterOptions' => array (
					$S3Client,
					'isby',
					array (),
					true 
					),
				'adapterClass' => '\Gaufrette\Adapter\AwsS3',
				'class' => '\Gaufrette\Filesystem' 
		) );
	}
}


