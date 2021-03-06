<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- 
/*
    
Oxygen Webhelp plugin
Copyright (c) 1998-2014 Syncro Soft SRL, Romania.  All rights reserved.
Licensed under the terms stated in the license file EULA_Webhelp.txt 
available in the base directory of this Oxygen Webhelp plugin.

*/
-->

<html lang="en-US">
<head>
<title>&lt;oXygen/&gt; XML Editor - WebHelp</title>
<meta name="Description" content="WebHelp Installer" />
<META HTTP-EQUIV="CONTENT-LANGUAGE" CONTENT="en-US" />
<link rel="stylesheet" type="text/css" href="install.css" />
</head>
<body>
	<div id="logo">
		<img src="./img/LogoOxygen100x22.png" align="middle"
			alt="OxygenXml Logo" /> WebHelp Installer
	</div>
	<h1 class="centerH">
		Installation Settings for <span class="titProduct">@PRODUCT_ID@</span>&nbsp;<span
			class="titProduct">@PRODUCT_VERSION@</span>
	</h1>
	<div class="panel">
		<div class="title">Installation progress</div>
		<div class="row">

			<?php 
			require_once './db/updateDb.php';
            $baseDir0 = dirname(dirname(__FILE__));
			include $baseDir0.'/oxygen-webhelp/resources/php/init.php';
			$productId = '@PRODUCT_ID@';
			$productVersion = '@PRODUCT_VERSION@';
			$ses= Session::getInstance();			
			foreach ($_POST as $key => $val){
				$_SESSION[$key]=$val;
			}
				
			function rollback(){
				global $cfgFile,$dbname,$dbConnectionInfo,$overConfig,$createDb,$step;
				try{
					//echo "<br>Rollback started.";
					if (($overConfig=='true') && ($step>=3)){
						if (file_exists($cfgFile)){
							unlink($cfgFile);
							echo "<br/> Config file removed.";
						}
					}
				}catch (Exception $ex){
					echo "\n<br>Config file rollback performed with errors !<br/>\n (".$e->getCode().") ".$e->getMessage();
				}
					
				if ($step>=1){
					if ($createDb=='true'){
						try{

							$db= new RecordSet($dbConnectionInfo,false,true);
							$db->Run('DROP TABLE IF EXISTS `comments`;');
							$db->Run('DROP TABLE IF EXISTS `users`;');
							$db->Run('DROP TABLE IF EXISTS `webhelp`;');
							echo "<br>Db Database structure rollback performed !";
						}catch (Exception $ex){
							echo "<br>Could not perform database rollback!";
						}
					}
				}
				//echo "<br>Rollback Ended.";
			}

			function switchOnToTrue($value){
				$toReturn='false';
				if ($value=='on'){
					$toReturn='true';
				}
				return $toReturn;
			}

			function createConfigFile($cfgFile,$dbConn){
				global $overConfig;
				$toReturn=true;
				$config = '<?php '."\n";
				$config .= '### CONFIGURATION FILE AUTOMATICALLY GENERATED BY THE OXYGEN WEBHELP INSTALLER AT ';
				$config .= date("Y-m-d H:i:s").' ###'."\n";
				$config .= "### for @PRODUCT_ID@ @PRODUCT_VERSION@ \n";
				$config .= "\n";
				$config .= '// The URL where the webhelp is installed on with trailing /'."\n";
				$config .= 'define(\'__BASE_URL__\',"'.Utils::getParam($_POST, 'baseUrl').'");'."\n";
				$config .= "\n";
				$config .= '// The relative URL where the webhelp is installed on with trailing /'."\n";
				$config .= 'define(\'__BASE_PATH__\',"'.Utils::getParam($_POST, 'basePath').'");'."\n";
				$config .= "\n";
				$config .= '// Email address to be used as from in sent emails'."\n";
				$config .= 'define(\'__EMAIL__\',"'.Utils::getParam($_POST, 'email').'");'."\n";
				$config .= "\n";
				$config .= '// Email address to be notified when error occur'."\n";
				if ("no-reply@oxygenxml.com"!=Utils::getParam($_POST, 'adminEmail')){
					$config .= 'define(\'__ADMIN_EMAIL__\',"'.Utils::getParam($_POST, 'adminEmail').'");'."\n";				
					$config .= "\n";
				}
				$config .= '// Send errors to system administartor?'."\n";
				$config .= 'define(\'__SEND_ERRORS__\','.switchOnToTrue(Utils::getParam($_POST, 'sendErrors')).');'."\n";
				$config .= "\n";
				$config .= '// If the system is moderated each post must be confirmed by moderator'."\n";
				$config .= 'define(\'__MODERATE__\', '.switchOnToTrue(Utils::getParam($_POST, 'moderated')).');'."\n";
				$config .= "\n";
				$config .= '// User session life time in seconds, by default is 7 days'."\n";
				$config .= 'define(\'__SESSION_LIFETIME__\','.Utils::getParam($_POST, 'sesLifeTime').');'."\n";
				$config .= "\n";
				$config .= '// Is unauthenticated user allowed to post comments'."\n";
				$config .= 'define(\'__GUEST_POST__\', '.switchOnToTrue(Utils::getParam($_POST, 'anonymousPost')).');'."\n";
				$config .= "\n";
				$config .= '// User friendly Product name'."\n";
				$config .= 'define(\'__PRODUCT_NAME__\',\''.Utils::getParam($_POST, 'productName').'\');'."\n";
				$config .= "\n";
				$config .= "// Show comments form other products with the same version from the same database"."\n";
				$print=false;
				if (isset($_POST['shareComments'])){
					if (Utils::getParam($_POST, 'shareComments')=='on'){
						$with = implode("','", $_POST['shareWith']);						
						if ($with!=""){
							$print=true;
							$with="'".$with."'";
							$config .= "define('__SHARE_WITH__',\"".$with."\");"."\n";
						}						
					}
				}
				if (!$print){
							$config .= "//define('__SHARE_WITH__',\"'editor','diff'\");"."\n";
				}
				$config .= "\n";
				$config .= '// Data base connection info'."\n";
				$config .= '$dbConnectionInfo[\'dbName\']="'.Utils::getParam($dbConn, 'dbName').'";'."\n";
				$config .= '$dbConnectionInfo[\'dbUser\']="'.Utils::getParam($dbConn, 'dbUser').'";'."\n";
				$config .= '$dbConnectionInfo[\'dbPassword\']="'.Utils::getParam($dbConn, 'dbPassword').'";'."\n";
				$config .= '$dbConnectionInfo[\'dbHost\']="'.Utils::getParam($dbConn, 'dbHost').'";'."\n";
				$config .= "\n";
				$config .= '?>'."\n";
				$config = trim($config);

				if ((($overConfig=='true') && (file_exists($cfgFile)))||(!file_exists($cfgFile))){
					if ((is_writable($cfgFile)  || ! is_file($cfgFile)) && ($fp = fopen($cfgFile, 'w'))) {
						fputs($fp, $config, strlen($config));
						fclose($fp);
						echo "<br/>\n Config file: ".$cfgFile." created!";
					} else {
						throw new Exception('Config file could not be written');
					}

				}else{
					$toReturn=false;
				}
				return $toReturn;
			}
			
			function installProduct($dbConnectionInfo){
				global 	$productId,$productVersion;
				try{
					$db= new RecordSet($dbConnectionInfo,false,true);
					$db->Run("DELETE FROM webhelp WHERE parameter='path' AND product='".$productId."' AND version='".$productVersion."';");
					$db->Run("DELETE FROM webhelp WHERE parameter='installDate' AND product='".$productId."' AND version='".$productVersion."';");				
					$db->Run("DELETE FROM webhelp WHERE parameter='dir' AND product='".$productId."' AND version='".$productVersion."';");
					$db->Run("DELETE FROM webhelp WHERE parameter='name' AND product='".$productId."' AND version='".$productVersion."';");
					
					$db->run("INSERT INTO `webhelp` (`parameter`, `value`, `product`, `version`) VALUES
						('installDate','".date('YmdHis')."','".$productId."','".$productVersion."'),
							('path','".addslashes(Utils::getParam($_POST, 'baseUrl'))."','".$productId."','".$productVersion."'),
							('dir','".addslashes(dirname(dirname(__FILE__)))."','".$productId."','".$productVersion."'),
							('name','".addslashes(Utils::getParam($_POST, 'productName'))."','".$productId."','".$productVersion."')
							;");
					$db->Close();
				}catch (Exception $e){
					error_log("Exception installing product ".$productId." version ".$productVersion." details: ".$e->getMessage());
					echo "Exception installing product ".$productId." version ".$productVersion." details: ".$e->getMessage();
					throw $e;
				}
			}
			
			function updateDb($dbConnectionInfo){
				
				$toReturn=false;
				
				$updater=new DbUpdater($dbConnectionInfo);
				$result=$updater->updateDb();
				if (!$result['updated']){
					echo $result['message'];
				}else{
					echo "Compatible database version found.";
					$toReturn=true;
				}
				return $toReturn;
			}


			######################################################################################################################

			set_time_limit(0);

			$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
			$baseUrl .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
			$baseUrl .= isset($_SERVER['SCRIPT_NAME']) ? dirname(dirname($_SERVER['SCRIPT_NAME'])) : dirname(dirname(getenv('SCRIPT_NAME')));



			$baseUrlFromPost=Utils::getParam($_POST, 'baseUrl');

			$cfgFile = '../oxygen-webhelp/resources/php/config/config.php';

			$dbhost = trim(Utils::getParam($_POST, 'dbhost', ''));
			$dbname = trim(Utils::getParam($_POST, 'dbname', ''));
			$dbuser = trim(Utils::getParam($_POST, 'dbuser', ''));
			$dbpass = trim(Utils::getParam($_POST, 'dbpass', ''));
			$adminUserName=trim(Utils::getParam($_POST, 'adminUserName', ''));
			$adminPass=trim(Utils::getParam($_POST, 'adminPasswd',''));
			$adminEmail=trim(Utils::getParam($_POST, 'adminEmail',''));

			$overConfig=false;
			if (isset($_POST['overWriteConfig'])){
				$overConfig=switchOnToTrue(trim(Utils::getParam($_POST, 'overWriteConfig')));
			}else if (isset($_POST['overWriteConfig'])){
				$overConfig=switchOnToTrue(trim(Utils::getParam($_POST, 'overWriteConfigHid')));
			}

			$createDb=switchOnToTrue(trim(Utils::getParam($_POST, 'createDb')));

			$dbConnectionInfo = array(
					'dbHost' => $dbhost,
					'dbName' => $dbname,
					'dbPassword' => $dbpass,
					'dbUser' => $dbuser
			);

			$step=0;
			$continue=true;
			$rollBack=false;
			$showBtBack=true;

			if ($overConfig!='true'){
				if (file_exists($cfgFile)){
					echo "<br/>Config file exists and you have choosed not to be modified!";
					echo "<br/>Instalation complete!";
					$continue=false;
				}
			}

			if ($continue){
				// 	check db connection
				$step++;

				try{
					$db= new RecordSet($dbConnectionInfo,false,true);
				}catch (Exception $ex){
					echo "<br/>Could not connect to database using specified informations:";
					echo "<table class=\"info\">";
					echo "<tr><td>Host </td><td>".$dbhost."</td></tr>";
					echo "<tr><td>Database </td><td>".$dbname."</td></tr>";
					echo "<tr><td>User </td><td>".$dbuser."</td></tr>";
					echo "</table>";
		      echo "<br/>The error was:<br/>", $ex->getMessage(), "<br/>", $ex->getTraceAsString();
					$continue=false;
				}
			}
			// create database structure
			if ($continue){
				$rollBack=true;
				$step++;
				if ($createDb=='true'){
					try{
						$db= new RecordSet($dbConnectionInfo,false,true);
						$db->runFile($baseDir0."/install/initDb.sql");
						echo "<br/>Tables created.";
					}catch (Exception $ex){
						echo "<br/>Could not create database structure!";
						echo "<!-- ".$ex->getMessage()." -->";
						$continue=false;
					}
				}else{
					$continue=updateDb($dbConnectionInfo);
					if (!$continue){						
						$showBtBack=true;
// 						echo "No database structure has been yet performed!";
					}
				}
			if ($continue){
				  installProduct($dbConnectionInfo);
        }
			}

			// create administrator user
			if ($continue && ($createDb=='true')){
				$step++;
				try{
					$pass=md5($adminPass);
					$db= new RecordSet($dbConnectionInfo,false,true);
					$date = date("Y-m-d H:i:s");
					$db->run("INSERT INTO `users` (`userId`, `userName`, `email`, `name`, `company`, `password`, `date`, `level`, `status`, `notifyAll`, `notifyReply`, `notifyPage`) VALUES
							(2, '$adminUserName', '$adminEmail', 'Administrator', 'NA', '$pass', '$date', 'admin', 'validated', 'yes', 'no', 'no');");
					echo "<br/>\nAdministrator username : '".$adminUserName."'";
				}catch (Exception $ex){
					echo "<br/>Could not create administrator user!";
					echo "<br/>Details: ".$ex->getMessage();
					$continue=false;
				}
			}



			// create config file
			if ($continue){
				$step++;

				if ($overConfig=='true'){
					try{
						if (file_exists($cfgFile)){
							unlink($cfgFile);
							echo "<br/>Old config file deleted.";
						}
						if (createConfigFile($cfgFile,$dbConnectionInfo)){
							//echo "<br/>New Config file created.";
						}
							
					}catch(Exception $e){
						echo "<br/>Could not delete config file!";
						echo "<br/>Please check file permissions !";
						$continue=false;
					}
				}else{
					echo "<br/>Config file unchanged !";
				}
			}else{
				echo "<br/> Config file not updated!";
			}

			if ($continue){
				$showBtBack=false;
				echo "<br/>\n<div class='success'>Install finished.</div>";
				echo "<br/>\n<div class=\"removeInfo\">Please remove the \"".$baseDir0.DIRECTORY_SEPARATOR."install".DIRECTORY_SEPARATOR ."\" folder after install!</div>";
				echo "<br/>\n<div><a href=\"../\">Go to product main page.</a></div>";
				$ses= Session::getInstance();
				session_unset();
			}else{
				if ($rollBack){
					rollback();
				}
			}
				
			?>
		</div>
	</div>
	<?php
  if($showBtBack){
      echo "<div class='btActions'><input class='buttonBack' type='button' value='Back' onclick=\"window.location.href ='index1.php';\" /></div>";
  }
	?>

</body>
</html>
