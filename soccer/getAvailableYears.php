<html>
<head>
	<meta charset="utf-8">
	<title>Soccer Statistics</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
</head>
<body></body>
</html>

<?php
	require_once('./config/conexao_memcached.php');

	$listaServer = json_decode($mc->get("SD_ListServers"), true);	// Lê a chave no Memcached
	if ($listaServer != "") {
		foreach ($listaServer['servers'] as &$srv) { 
			if ($srv['active'] == true) {
				foreach ($srv['year'] as $ano) {
					$years[] = $ano;
				}
			}
		}
		$yearsObj = new \stdClass();
		$yearsObj->years = $years;
		print json_encode($yearsObj, JSON_PRETTY_PRINT);
	} else {
		$yearsObj = new \stdClass();
		$yearsObj->years = NULL;
		print json_encode($yearsObj, JSON_PRETTY_PRINT);
	}
?>