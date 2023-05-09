<?php

global $components; // [[taskName, compName],....]
$components = [];

class Fw {
	public $task = '';
	public $comp = false;
	public $compName = '';

	function __construct() {
		global $components;
		if (!defined('REWRITE')) { define('REWRITE',true); }
		if (!defined('LNG')) { define('LNG','hu'); }

		// SEO barát URL kezelés
		// url = ....../task/xxx/parname/parValue....
		$w = explode('?',$_SERVER['REQUEST_URI']);
		$w = explode('/',$w[0]);
		$i = 0;
		while ($i < count($w)) {
			if ($w[$i] == 'task') {
				$_GET['task'] = $w[$i+1];
				$i = $i + 2;
				while ($i < count($w)) {
					if (isset($w[$i+1])) { 
						$_GET[$w[$i]] = $w[$i+1];
					} else {
						$_GET[$w[$i]] = '';
					}	
					$i = $i + 2;
				}
			}
			$i++;
		}

		// egy felhasználós módban minen "0" user_id -hez rendelve 
		// szerepel az adatbázisban
		// több felhasználós változatban a bejelentkezési folyamatban
		// beállított user.id -t használjuk
		if (MULTIUSER) {
		 if (!isset($_SESSION['loged'])) {
		   $_SESSION['loged'] = -1;
		   $_SESSION['logedName'] = 'guest';
		   $_SESSION['logedAvatar'] = '';
		   $_SESSION['logedGroup'] = '';
		 }
		} else {
			$_SESSION['loged'] = 1;
			$_SESSION['logedName'] = 'user';
			$_SESSION['logedAvatar'] = '';
			$_SESSION['logedGroup'] = 'admin';
		}

		// Facebbok/google loginból érkező hívás feldolgozása
		if (isset($_GET['usercode'])) {
			$w = explode('-',$_GET['usercode']);
			$userName = 's_'.base64_decode($w[0]);
			$userId = $w[1];
			if ($w[2] == md5($userId.FB_SECRET)) {
				// van már ilyen user?
				$db = new \RATWEB\DB\Query('users');
				$db->where('username','=','"'.$userName.'"');
				$rec = $db->first();
				if (!isset($rec->id)) {
					// nincs, létrehozzuk és az újra jelentkezünk be
					$rec = new \RATWEB\DB\Record();
					$rec->username = $userName;
					$rec->password = $w[2];
					$rec->realname = $userName;
					$rec->email = '';
					$rec->avatar = '';
					$rec->email_verifyed = 1;
					$rec->enabled = 1;
					$rec->deleted = 0;
					$userId = $db->insert($rec);

				} else {
					// van; erre jelentkezünk be
					$userId = $rec->id;
				}
				// bejelentkeztetés
				$_SESSION['logedAvatar'] = $rec->avatar;
				$_SESSION['loged'] = $userId;
				$_SESSION['logedName'] = $userName;
				// logedGroups
				$q = new \RATWEB\DB\Query('user_group','ug');
				$w = $q->select(['g.id, g.name'])
					->join('INNER','groups','g','g.id','=','ug.group_id')
					->where('ug.user_id','=',$userId)
					->orderBy('g.name')
					->all();
				$_SESSION['logedGroup'] = JSON_encode($w);
			} else {
				echo 'kodolási hiba userId='.$userId.' userName='.$userName; exit();	
			}
		}

		// task kezelés
		if (isset($_GET['task'])) {
			$this->task = $_GET['task'];
		} else if (isset($_POST['task'])) {
			$this->task = $_POST['task'];
		} else {
			$this->task = 'home.show';
		}

		if (strpos($this->task,'.')) {
			$w = explode('.',$this->task);
			$compName = $w[0];
			$this->task = $w[1];
			importComponent($compName);
			$compName = ucFirst($compName); 
			$this->comp = new $compName();
			$this->compName = $compName;
		} else {
			$compName = '';
			for ($i=0; $i<count($components); $i++) {
				if ($components[$i][0] == $this->task) {
					$compName = $components[$i][1];			
				}		
			} 
			if ($compName != '') {
				$this->comp = new $compName ();
				$this->compName = $compName;
			} else {
				echo 'Fatal error  compName not found!'; exit();
			}	
		}
	}

	/**
	 *  SEO barát url kezelőshez függvény
	 * @param string $task
	 * @param array $params [name => value,...]
	 * @return string
	 */ 
	function HREF(string $task, array $params) {
		$result = SITEURL;
		if (REWRITE) {
			$result .= '/task/'.$task;
			foreach ($params as $fn => $fv) {
				$result .= '/'.$fn.'/'.$fv;
			} 
		} else {
			$result .= '?task='.$task;
			foreach ($params as $fn => $fv) {
				$result .= '&'.$fn.'='.$fv;
			} 
		}
		return $result;
	}

	/**
	* controller betöltése
	* globalis funkcióként is hívható
	* @param string $name
	*/
	public static function importComponent(string $name) {
		global $components;
		include_once 'includes/controllers/'.strtolower($name).'.php';
		$methods = get_class_methods(ucFirst($name));
		foreach ($methods as $method) {
			$components[] = [$method,ucFirst($name)];
		}
	}

	/**
	* a bejelentkezett user admin?
	* globalis funkcióként is hívható 
	* @return bool
	*/
	public static function isAdmin() {
		return (($_SESSION['logedGroup'] == 'admin') | 
				($_SESSION['logedName'] == ADMIN) |
				(strpos(' '.$_SESSION['logedGroup'],'admin') > 0));
	}
	
	/**
	* viewer hívása  
	* global funkccióként is hívható
	* @param string $viewName
	* @param array $params ["pname" => $pvalue,...]
	* @param string $appName
	*/
	public static function view(string $viewname, array $params, string $appName = 'add') {
		view($viewname, $params, $appName); 
	}
}

function importComponent(string $name) {
	Fw::importComponent($name);
} 

function isAdmin() {
	return Fw::isAdmin();
}

?>
