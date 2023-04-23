<?php
	include_once 'vendor/model.php';
	
	class ProductModel extends \RATWEB\Model {

		function __construct() {
			$this->table = PRODUCTS;
			$q = new \RATWEB\DB\Query($this->table);
		}
		
		public function getNews(int $limit):array {
			$q = new \RATWEB\DB\Query($this->table);
			return $q->orderBy('id')->orderDir('DESC')
				->limit($limit)->all();
		}
		
		public function getFavorites(int $limit):array {
			$q = new \RATWEB\DB\Query($this->table);
			return $q->where('favorit','=','I')
				->orderBy('id')->orderDir('DESC')
				->limit($limit)->all();
		}
		
		/**
		 * rekorzi rutin az sql "in" keresés paraméter összeállításához
		 */ 
		protected function getChildrens($tag, string  &$s) {
			if ($s == '') {
				$s = '"'.$tag->name.'"';
			} else {
				$s .= ',"'.$tag->name.'"';
			}
			$q = new \RATWEB\DB\Query('tags');
			$recs = $q->where('owner','=',$tag->id)->all();
			foreach ($recs as $rec) {
				$this->getChildrens($rec,$s);
			}	
		}
		
		public function getItems($page,$limit,$filter,$order) {
			if ($page < 1) $page = 1;
			$q = new \RATWEB\DB\Query($this->table);
			if ($filter == 'all') {
				$result = $q->orderBy($order)->orderDir('DESC')
				->offset(($page - 1)*$limit)
				->limit($limit)
				->all();
			} else {
				// tag fa szerkezet kezelés $s -be a tag fa childrens
				$s = '';
				$q = new \RATWEB\DB\Query('tags');
				$rec = $q->where('name','=',$filter)->first();
				$this->getChildrens($rec,$s);

				$q->setSql('select * from '.$this->table.'
				where tag in ('.$s.')
				order by id DESC
				limit '.(($page-1)*$limit).','.$limit);	
				
				// echo $q->getSql().'<br />';
				
				$result = $q->all();
			}
			return $result;
		}
		
		public function getTotal($filter) {
			$q = new \RATWEB\DB\Query($this->table);
			if ($filter != 'all') {
				$q->where('tag','=',$filter);
			}
			return $q->count();
		}

		public function delByImg(string $img) {
			$q = new \RATWEB\DB\Query($this->table);
			$q->where('img','=',$img)->delete();
		}

	}
?>
