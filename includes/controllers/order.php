<?php
include_once 'includes/models/productmodel.php';
class Order extends Controller {
	
	function __construct() {
		parent::__construct();
		$this->model = new ProductModel();
	}
	
	/**
	 * Megrendelés form
	 * @POST items  JSON string
	 * @return void
	 */ 
	public function form() {
		$s = urldecode($_POST['items']);
		$items = JSON_decode($s);
		if (count($items) > 0) {
			view('orderform',['items' => $items]);
		} else {
			echo '<div class="alert alert-danger">A kosár üres.</div>';
	    }
	}
	
	/**
	 * megrendelés elküldése
	 * @POST $items, $customerName, $customerAddress, $customerEmail,
	 *    $customerPhone
	 * @return void
	 */ 
	public function send() {
		$items = JSON_decode(urldecode($_POST['items']));
		
		$customerName = $this->request->input('customerName');
		$customerAddress = $this->request->input('customerAddress');
		$customerEmail = $this->request->input('customerEmail');
		$customerPhone = $this->request->input('customerPhone');
		if (count($items) <= 0) {
			echo '<div class="alert alert-danger">A kosár üres.</div>';
			return;
		}	
		$emailBody = 'Megrendelés%0A'.date('Y.m.d H:i').'%0A%0A'.
		$customerName.'%0A'.
		$customerAddress.'%0A'.
		$customerEmail.'%0A'.
		$customerPhone.'%0A%0A';
		foreach ($items as $item) {
				$emailBody .= $item->img.' '.
				$item->quantity.' db '.$item->name.' '.$item->price.'Ft %0A';
		}
		echo '<div class="row text-enter">
		<a href="'.SITEURL.'" class="btn btn-secondary">Tovább</a>
		</div>
		<script>
			alert("Most saját levelező programjával levelet fog küldeni a megrendelésről. Kérjük, hogy a levél tartalmán ne változtasson!");
			location="mailto:szerencsemania31@gmail.com?subject=megrendeles&body='.$emailBody.'";
		</script>';
	}
}
?>
