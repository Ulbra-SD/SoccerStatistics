<?php
	$file = file_get_contents('./config/config.json');
	$config = json_decode($file);
	$memcachedServer = $config->memcachedServer;
  	$memcachedPort 	 = $config->memcachedPort;

	$mc = new Memcache();
	try{
		$mc->connect($memcachedServer, $memcachedPort);
	} catch(Exception $e) {
		echo "Falha na conexão ao MemCached!";
		echo "StackTrace: ", $e->getMessage();
	}
?>