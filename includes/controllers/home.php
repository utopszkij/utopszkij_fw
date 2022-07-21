<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/usermodel.php';

class Home extends Controller {

	function __construct() {
		parent::__construct();
		// $this->model = new HomeModel();
        $this->name = "home";
        //$this->browserURL = 'index.php?task=userek';
        //$this->addURL = 'index.php?task=regist';
        //$this->editURL = 'index.php?task=useredit';
        //$this->browserTask = 'userek';
	}

	public function show() {
		view('description',[]);
	}

	public function description() {
		view('description',[]);
	}
	
	public function licence() {
		view('licence',[]);
	}
	
	public function policy() {
		view('policy',[]);
	}
	
	public function protest() {
		view('protest',[]);
	}
	
	public function impressum() {
		view('impressum',[]);
	}

	public function swdoc() {
		view('swdoc',[]);
	}
	
	
}


?>
