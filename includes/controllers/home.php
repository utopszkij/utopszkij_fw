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
		$errorMsg = $this->request->input('errorMsg', $this->session->input('errorMsg',''),NOFILTER);
		$successMsg = $this->request->input('successMsg', $this->session->input('successMsg',''),NOFILTER);
		$this->session->set('errorMsg','');
		$this->session->set('successMsg','');

		view('description',[
			"errorMsg" => $errorMsg,
			"successMsg" => $successMsg
		]);
	}

	public function description() {
		$this->show();
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
