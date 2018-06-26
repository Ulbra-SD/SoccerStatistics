<html>
<head>
	<meta charset="utf-8">
	<title>Soccer Statistics</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
</head>
<body></body>
</html>
<script language="javascript">
	window.onload = () => {

    const update = () => {
        fetch("consultaServers.php",{
            method: "GET"
        }).catch((error) => console.log(error));
    };
    
    setInterval(() => {
        update();
    },5000);
};
	
</script>


<?php
	require ('./consultaServers.php');
	require ('./config/conexao_memcached.php');
	
	$config = json_decode(file_get_contents('./config/config.json'));	// Carrega configurações
	$serverName	= $config->serverName;
  	$serverIP 	= $config->serverIP;
  	$portListen 	= $config->portListen;
	$database	= $config->database;
  	$yearData 	= $config->yearData;

	// ----- Coloca este processo na lista de servidores ativos no Memcached -----
	$myServer = array('name'=>"$serverName", 'location'=>"$serverIP:$portListen", 'year'=>$yearData, 'active'=>true);
	$row = json_decode($mc->get("SD_ListServers"), true);	// Lê a chave no Memcached
	$jaTem = false;
	foreach ($row['servers'] as &$srv) { 
		if ($srv['name'] == $serverName) {
			$srv['active'] = true;
			unset($srv);
			$jaTem = true;
			break;
		}
	}
	if (!$jaTem) {	// Não estou na lista ainda
		$row['servers'][] = $myServer;
		$serverList = json_encode($row);
		$mc->set("SD_ListServers",$serverList);
	} else {
		$serverList = json_encode($row);
		$mc->set("SD_ListServers",$serverList);
	}
	// -----------------------------------------------------------------------------------------------------

	$clubName 	= ($_GET["clubName"]);
	$playerName 	= ($_GET["playerName"]);
	$year 		= ($_GET["periodo"]);
	$year 		= explode("/", $year);
	$year 		= $year[1];

	$db = mysqli_connect("$database","raubach","","soccer") or die("Erro ao conectar na base de dados MySQL!");

	if (in_array($year, $yearData)) {	// Se a requisição é para os anos que este processo trata...
		if ($playerName == "") {	// CONSULTA DO TIME

			$cacheKey = "SD_Data_" . $year . "_" . $clubName;
			$cacheResult = consultaCache($cacheKey,$memcachedServer, $memcachedPort);
			if ($cacheResult == "") {
				$query = "SELECT ts.wins, ts.loses FROM team_scores ts WHERE ts.season=$year AND ts.team_long_name='$clubName'";
				$result = mysqli_query($db, $query);
				if(mysqli_num_rows($result) == 0) { 
    				defineErro(2);
    				exit;
				}
				while ($row = mysqli_fetch_assoc($result)) {
					$wins = $row['wins'];
					$losses = $row['loses'];
					$resposta = new \stdClass();
					$resposta->wins = $wins;
					$resposta->losses = $losses;
					$resposta = json_encode($resposta, JSON_PRETTY_PRINT);
				}

				atualizaCache($cacheKey,$resposta,$memcachedServer, $memcachedPort);
				print ($resposta);
			} else {
				print ($cacheResult);
			}
		} else if ($clubName == "") {	// CONSULTA DO JOGADOR

			$cacheKey = "SD_Data_" . $year . "_" . $playerName;
			$cacheResult = consultaCache($cacheKey,$memcachedServer, $memcachedPort);
			if ($cacheResult == "") {
				$query = "SELECT ps.wins, ps.loses FROM player_scores ps WHERE ps.season=$year AND ps.player_name='$playerName'";
				$result = mysqli_query($db, $query);
				if(mysqli_num_rows($result) == 0) { 
    				defineErro(2);
    				exit;
				}
				while ($row = mysqli_fetch_assoc($result)) {
					$wins = $row['wins'];
					$losses = $row['loses'];
					$resposta = new \stdClass();
					$resposta->wins = $wins;
					$resposta->losses = $losses;
					$resposta = json_encode($resposta, JSON_PRETTY_PRINT);
				}
			
				atualizaCache($cacheKey,$resposta,$memcachedServer, $memcachedPort);
				print ($resposta);
			} else {
				print ($cacheResult);
			}
		} else {		// CONSULTA COMPLETA

			$cacheKey = "SD_Data_" . $year . "_" . $clubName . "_" . $playerName;
			$cacheResult = consultaCache($cacheKey,$memcachedServer, $memcachedPort);
			if ($cacheResult == "") {
				$query = "SELECT (`match`.`home_team_goal` - `match`.`away_team_goal`) as placar FROM `player`
						INNER JOIN `match`
    						ON `match`.`home_player_1` = `player`.`player_api_id`
    						or `match`.`home_player_2` = `player`.`player_api_id`
    						or `match`.`home_player_3` = `player`.`player_api_id`
    						or `match`.`home_player_4` = `player`.`player_api_id`
    						or `match`.`home_player_5` = `player`.`player_api_id`
    						or `match`.`home_player_6` = `player`.`player_api_id`
    						or `match`.`home_player_7` = `player`.`player_api_id`
    						or `match`.`home_player_8` = `player`.`player_api_id`
    						or `match`.`home_player_9` = `player`.`player_api_id`
    						or `match`.`home_player_10` = `player`.`player_api_id`
    						or `match`.`home_player_11` = `player`.`player_api_id`
						INNER JOIN `team`
    						ON `match`.`home_team_api_id` = `team`.`team_api_id`
						where `player`.`player_name` = '$playerName'
    						and `team`.`team_long_name` = '$clubName'
    						and year(`match`.`date`) = '$year'
    						and (`match`.`home_team_goal` - `match`.`away_team_goal`) <> 0
						union all
						SELECT (`match`.`away_team_goal` - `match`.`home_team_goal`) as placar FROM `player`
						INNER JOIN `match`
    						ON `match`.`away_player_1` = `player`.`player_api_id`
    						or `match`.`away_player_2` = `player`.`player_api_id`
    						or `match`.`away_player_3` = `player`.`player_api_id`
    						or `match`.`away_player_4` = `player`.`player_api_id`
    						or `match`.`away_player_5` = `player`.`player_api_id`
    						or `match`.`away_player_6` = `player`.`player_api_id`
    						or `match`.`away_player_7` = `player`.`player_api_id`
    						or `match`.`away_player_8` = `player`.`player_api_id`
    						or `match`.`away_player_9` = `player`.`player_api_id`
    						or `match`.`away_player_10` = `player`.`player_api_id`
    						or `match`.`away_player_11` = `player`.`player_api_id`
						INNER JOIN `team`
    						ON `match`.`away_team_api_id` = `team`.`team_api_id`
						where `player`.`player_name` = '$playerName'
    						and `team`.`team_long_name` = '$clubName'
    						and year(`match`.`date`) = '$year'
							and (`match`.`away_team_goal` - `match`.`home_team_goal`) <> 0";

				$result = mysqli_query($db, $query);

				if(mysqli_num_rows($result) == 0) { 
    				defineErro(2);
    				exit;
				} 

				$wins = 0; $losses = 0;
				while ($row = mysqli_fetch_assoc($result)) {
					if ($row["placar"] > 0) {
						$wins++;
					} else {
						$losses++;
					}
				}
				$resposta = new \stdClass();
				$resposta->wins = $wins;
				$resposta->losses = $losses;
				$resposta = json_encode($resposta, JSON_PRETTY_PRINT);

				atualizaCache($cacheKey,$resposta,$memcachedServer, $memcachedPort);
				print ($resposta);
			} else {
				print ($cacheResult);
			}
		}
	} else {		// Se a requisição for para outro server, reencaminha...
		//echo "<br />\n Encaminhando requisição ao server responsável... <br />\n";
		$a = json_decode(file_get_contents('./config/listaServers.json'),TRUE);
		foreach ($a['servers'] as $srv) {
			if (in_array($year, $srv['year']) and $srv['active'] == 1) {
				$location = explode(":", $srv['location']);
				$srvIP = $location[0];
				$srvPort = $location[1];
				exec('ping -c 1 ' . $srvIP, $output, $result);
				if ($result == 0) {		// Server ON
					$requisicao = "192.168.15.5/" . $_SERVER['REQUEST_URI'];
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $requisicao);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$resposta = curl_exec($ch);

					print_r($resposta);
					exit;
				} else {		// Server OFF
					defineErro(1);
					exit;
				}
			} else {
				//defineErro(1);
				//exit;
			}
		}
		//echo "<br />\n <br />\n";
	}

	mysqli_close($db);


	// ***************************************************************************************
	// =============================== DECLARAÇÃO DAS FUNÇÕES ================================
	// ***************************************************************************************

	// ----------------------------- FUNÇÃO DE CONSULTA AO CACHE -----------------------------
	function consultaCache($key,$memcachedServer,$memcachedPort){
		$mc = new Memcache();
		try{
			$mc->connect($memcachedServer, $memcachedPort);
		} catch(Exception $e) {
			echo "Falha na conexão ao MemCached!";
			echo "StackTrace: ", $e->getMessage();
		}

		if (!($row = $mc->get($key))) {
			//echo "Nao tem no cache!<br/>\n";
			$mc->close();
    		return "";
		} else {
			//echo "Já tem no cache!<br/>\n";
			$mc->close();
			return $row;
		}
	}

	// --------------------------- FUNÇÃO DE ATUALIZAÇÃO DO CACHE ----------------------------
	function atualizaCache($key, $value,$memcachedServer, $memcachedPort){
		$mc = new Memcache();
		try{
			$mc->connect($memcachedServer, $memcachedPort);
		} catch(Exception $e) {
			echo "Falha na conexão ao MemCached!";
			echo "StackTrace: ", $e->getMessage();
		}

		//echo "<br />\n Atualizando cache... <br />";
		$mc->set($key,$value);
		$mc->close();
	}

	// ---------------------------- FUNÇÃO DE DEFINIÇÂO DE ERRO ------------------------------
	function defineErro($codErro){
		if ($codErro == 1)		$desc = "Servidor Indisponivel";
		elseif ($codErro == 2)	$desc = "Dados Inexistentes";
		else					$desc = "Erro Desconhecido";

		header('HTTP/1.0 417 Expectation Failed');
		$erro = array('errorCode'=>$codErro, 'errorDescription'=>"$desc");
		echo "<br />\n"; print json_encode($erro, JSON_PRETTY_PRINT);
	}

?>
