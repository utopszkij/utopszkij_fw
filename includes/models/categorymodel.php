<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    class CategoryModel extends Model  {

        function __construct() {
            parent::__construct();
            $this->setTable('categories');
            $this->errorMsg = ''; 
        }

        /**
         * üres group rekord
         */
        public function emptyRecord(): Record {
            $result = new Record();
                    $result->id = 0;
        $result->category_name = "";

            return $result;
        }

		/** a filter str alapján bőviti a Query -t
		 * rendszerint át kell definiálni a mező tipusoktól függően
		 * 'like' vagy '=' -s keresés
		 * @param Query
		 * @param string $filter 'name|value...'
		 */ 
		protected function filterToQuery(Query &$db, string $filter) {
            if ($filter != '') {        
				$filter = explode('|',$filter);        
				$i=0;
				while ($i < count($filter)) {
					if ($filter[$i+1] != '') {
						if ($filter[$i] == 'name') {
							$db->where($filter[$i],'like','%'.$filter[$i+1].'%');
						} else {	
							$db->where($filter[$i],'=',$filter[$i+1]);
						}
					}	
					$i = $i + 2;
				}
			}
		}

        /**
         * rekordok lapozható listája
         * rendszerint át kell definiálni a szükséges oszlopok, mezők
         * tábla összefüggések szerint
         * @param int $page
         * @param int $limit
         * @param string $filter 'name|value...' 
         * @param string $order 
         * @return array
         */
        public function getItems(int $page, int $limit, string $filter, string $order = 'id'): array {
			if ($page <= 0) $page = 1;
            $db = new Query($this->table,'c');
            $db->join('LEFT','book_category','bc','bc.category_id','=','c.id')
            ->join('LEFT','books','b','b.id','=','bc.book_id')
            ->select(['c.id','c.category_name','count(b.id) as book_count'])
            ->groupBy(['c.id','c.category_name'])
            ->offset((($page - 1) * $limit))
            ->limit($limit)
            ->orderBy('category_name');
            $this->filterToQuery($db,$filter);        
            $result = $db->all();
            return $result;        
        }

        /**
         * Összes rekord száma
         * @return int
         */
        public function getTotal($filter = ''): int {
            $db = new Query($this->table,'c');
            $db->join('LEFT','book_category','bc','bc.category_id','=','c.id')
            ->join('LEFT','books','b','b.id','=','bc.book_id')
            ->select(['c.id','c.category_name','count(b.id) as book_count'])
            ->groupBy(['c.id','c.category_name']);
            $this->filterToQuery($db,$filter);        
            return $db->count();
        }

        /**
         * könyv ketegóriák lekérdezése
         * @param int $book_id
         * @return array [id]
         */
        public function getBookCategories(int $book_id): array {
            $db = new Query('book_category','bc');
            $db->join('LEFT','categories','c','bc.category_id','=','c.id')
                ->select(['c.id'])
                ->where('bc.book_id','=',$book_id);
            $res = $db->all();
            $result = [];
            foreach ($res as $r) {
                $result[] = (int) $r->id;
            }
            return $result;
        }

        /**
         * könyv kategória kapcsolása
         * @param int $book_id
         * @param int $author_id
         */
        public function addBookCategory(int $book_id, int $catgory_id) {
            $db = new Query('book_category');
            $record = new Record;
            $record->id = 0;
            $record->book_id = $book_id;
            $record->category_id = $catgory_id;
            $db->insert($record);
        }

        /**
         * könyv kategória kapcsolat törlése
         * @param int $book_id
         * @param int $author_id
         */
        public function deleteBookCategory(int $book_id, int $catgory_id) {
            $db = new Query('book_category');
            $db->where('book_id','=', $book_id)
                ->where('category_id','=', $catgory_id)->delete();
        }


}    
?>
