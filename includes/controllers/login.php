<?php

include_once 'vendor/controller.php';
include_once 'includes/models/productmodel.php';
class Login extends Controller {
	
	function __construct() {
		$q = new ProductModel();
        $this->request = new Request();
        $this->session = new Session();
        $this->loged = $this->session->input('loged',0,INTEGER);
        $this->logedName = $this->session->input('logedName','Látogató');
        $this->logedAdmin = $this->session->input('admin',false);
        $this->logedGroup = $this->session->input('logedGroup','');
        $this->logedAvatar = $this->session->input('logedGroup','');
        $this->model = new ProductModel();
        $this->name = 'product';
        $this->browserURL = SITEURL.'/task/product.list';
        $this->formURL = SITEURL.'/task/product.show';
        $this->browserTask = 'product.list';
	}

	public function form() {
		echo '
		<form method="post" action="'.SITEURL.'/task/login.dologin">
			<div class="row">
				<div class="col-12">&nbsp;</div>
				<div class="col-12">&nbsp;</div>
				<div class="col-12 text-center"> 
					<label>Jelszó:</label>
					<input type="password" name="password" />
				</div>
				<div class="col-12">&nbsp;</div>
				<div class="col-12 text-center">
					<button type="submit" class="btn btn-primary">
						<em class="fas fa-check"></em>Rendben
					</button>
				</div>
				<div class="col-12">&nbsp;</div>
			</div>
		</form>
		';
	}
	
	public function logout() {
		$this->session->set('admin',false);
		echo '<script>location="'.SITEURL.'";</script>';
	}
	
	public function dologin() {
		if ($this->request->input('password') == '31Melinda') {
			$this->session->set('admin',true);
			$this->session->set('loged',1);
			$this->session->set('logedName','admin');
			$this->session->set('logedGroup','admin');
			echo '<script>location="'.SITEURL.'";</script>';
		} else {
			$this->session->set('admin',false);
			$this->session->set('loged',0);
			$this->session->set('logedName','guest');
			$this->session->set('logedGroup','guest');
			echo '<div class="alert alert-danger">Nem jó jelszó.</div>';
		}	
	}
}
?>
