<?php
	require_once('./config/conexao_memcached.php');

	global $arq;
	$arq = fopen('./config/listaServers.json', 'w');
	$lista = $mc->get("SD_ListServers");	// Lê a chave no Memcached
	fwrite($arq, $lista);
	fclose($arq);

	$mc->close();
?>