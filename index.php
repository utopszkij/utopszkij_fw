<?php
if (isset($_GET['sid'])) {
	session_id($_GET['sid']);
}
if (isset($_POST['sid'])) {
	session_id($_POST['sid']);
}
session_start();
// REMOE_ADDR tárolás/ellenörzés
if (isset($_SESSION['REMOTE_ADDR'])) {
	if ($_SESSION['REMOTE_ADDR'] != $_SERVER['REMOTE_ADDR']) {
		echo 'Fatar error REMOTE_ADDR invalis'; exit();
	}
} else {
	$_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
}
global $components;

// server infok hozzáférhetővé tétele a php számára
define('DOCROOT',__DIR__);
$w1 = (int) str_replace('M', '', ini_get('post_max_size'));
$w2 = (int) str_replace('M','',ini_get('upload_max_filesize'));
define('UPLOADLIMIT',min($w1,$w2));
include_once 'config.php';
include_once 'vendor/database/db.php';
include_once('vendor/model.php');
include_once('vendor/view.php');
include_once('vendor/controller.php');
include_once('vendor/fw.php');
include_once('includes/models/statisticmodel.php');
importComponent('upgrade');

// statisztikai adatgyüjtés
// $statisticModel = new StatisticModel();
// $statisticModel->saveStatistic();


$fw = new Fw();

//+ ----------- verzio kezelés start ------------
$fileVerzio = 'v2.1.1';
$upgrade = new \Upgrade();
$dbverzio  = $upgrade->getDBVersion();
$lastVerzio = $upgrade->getLastVersion();
$upgrade->dbUpgrade($dbverzio);
$branch = $upgrade->branch;
//- ----------- verzio kezelés end ------------

// képernyő méretek elérése
if (isset($_COOKIE['screen_width'])) {
	$_SESSION['screen_width'] = $_COOKIE['screen_width'];
} else {
	$_SESSION['screen_width'] = 1024;
}
if (isset($_COOKIE['screen_height'])) {
	$_SESSION['screen_height'] = $_COOKIE['screen_height'];
} else {
	$_SESSION['screen_height'] = 800;
}

$task = $fw->task;
$comp = $fw->comp;
$compName = $fw->compName;
$title = SITETITLE;
if (method_exists($comp, 'getTitle')) {
	$title = $comp->getTitle($task);
} 

// execute API backends
if (in_array($fw->compName.'.'.$fw->task,
    ['apaTask'])) {
	$comp->$task ();
    exit();
}
if (strpos(' '.$task,'api_') > 0) {
	$comp->$task ();
	exit();
}

include 'styles/'.STYLE.'/main.php';
