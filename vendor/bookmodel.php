<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    class BookModel extends Model  {

        function __construct() {
            parent::__construct();
            $this->setTable('books');
            $this->errorMsg = ''; 
        }

        /**
         * üres group rekord
         */
        public function emptyRecord(): Record {
            $result = new Record();
                    $result->id = 0;
        $result->title = "";
        $result->storage = "";
        $result->book_url = "";
        $result->image_url = "";
        $result->year = "";
        $result->volumes = 1;

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
						if ($filter[$i] == 'title') {
							$db->where($filter[$i],'like','%'.$filter[$i+1].'%');
                        } else if ($filter[$i] == 'author') {
                            $db = new Query('authors','a');
                            $db->where('a.author_name','like','%'.$filter[$i+1].'%')
                                ->join('LEFT','book_author','ba','a.id','=','ba.author_id')
                                ->join('LEFT','books','b','ba.book_id','=','b.id')
                                ->select(['b.id','b.title','b.storage','b.book_url','b.image_url','b.year','b.volumes','GROUP_CONCAT(a.author_name SEPARATOR ", ") AS authors'])
                                ->groupBy(['b.id','b.title','b.storage','b.book_url','b.image_url','b.year','b.volumes']);
                        } else if ($filter[$i] == 'category') {
                                $db = new Query('categories','c');
                                $db->where('c.id','=',$filter[$i+1])
                                    ->join('LEFT','book_category','bc','c.id','=','bc.category_id')
                                    ->join('LEFT','books','b','bc.book_id','=','b.id')
                                    ->join('LEFT','book_author','ba','b.id','=','ba.book_id')
                                    ->join('LEFT','authors','a','ba.author_id','=','a.id')                                    
                                    ->select(['b.id','b.title','b.storage','b.book_url','b.image_url','b.year','b.volumes','GROUP_CONCAT(a.author_name SEPARATOR ", ") AS authors'])
                                    ->groupBy(['b.id','b.title','b.storage','b.book_url','b.image_url','b.year','b.volumes']);
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
            $db = new Query($this->table,'b');
            $db->join('LEFT','book_author','ba','b.id','=','ba.book_id')
                ->join('LEFT','authors','a','ba.author_id','=','a.id')
                ->select(['b.id','b.title','b.storage','b.book_url','b.image_url','b.year','b.volumes','GROUP_CONCAT(a.author_name SEPARATOR ", ") AS authors'])
                ->groupBy(['b.id','b.title','b.storage','b.book_url','b.image_url','b.year','b.volumes']);
            $this->filterToQuery($db,$filter);        
            $db->offset((($page - 1) * $limit))
               ->limit($limit)
               ->orderBy($order);
            $result = $db->all();
            return $result;        
        }

        /**
         * Összes rekord száma
         * @return int
         */
        public function getTotal($filter = ''): int {
            $db = new Query($this->table);
            $db->select(['id']);
            $this->filterToQuery($db,$filter);
            return $db->count();
        }

        /**
         * Összes tároló hely lekérdezése
         * @return [{id, storage_name }]
         */
        public function getStorages(): array {
            $db = new Query('storages');
            $db->select(['id','storage_name']);
            $db->orderBy('storage_name');
            return $db->all();
        }

        /**
        * Összes kategória lekérdezése
        * @return [{id, category_name, checked }]
        */
        public function getCategories(int $book_id): array {
            $db = new Query('categories');
            $results = $db->orderBy('category_name')->all();
            foreach ($results as $res) {
                $res->checked = false;
            }    
            $db2 = new Query('book_category');
            $res2 = $db2->where('book_id','=',$book_id)->all();
            foreach ($res2 as $res) {
                foreach ($results as $result) {
                    if ($res->category_id == $result->id) $result->checked = true;
                 }
            }    
            return $results;
        }

        /**
         * Összes szerző lekérdezése
         * @return [{id, author_name, checked }]
         */
        public function getAuthors(int $book_id): array {
            $db = new Query('authors');
            $db->select(['id','author_name']);
            $db->orderBy('author_name');
            $results = $db->all();
            foreach ($results as $result) {
                $result->checked = false;
            }    
            $db2 = new Query('book_author');
            $bookAuthors = $db2->where('book_id','=',$book_id)->all();
            foreach ($bookAuthors as $bookAuthor) {
                foreach ($results as $result) {
                   if ($bookAuthor->author_id == $result->id) $result->checked = true;
                }
            }    
            return $results;
        }

        public function getById(int $id): Record {
            $db = new Query($this->table);
            $db->where('id','=',$id);
            $result = $db->first();
            $db = new Query('book_author','ba');
            $db->join('LEFT','authors','a','ba.author_id','=','a.id')
                ->select(['a.id','a.author_name'])
                ->where('ba.book_id','=',$id)
                ->orderBy('a.author_name');
            $result->authors = $db->all();    
            $db = new Query('book_category','bc');
            $db->join('LEFT','categories','c','bc.category_id','=','c.id')
                ->select(['c.id','c.category_name'])
                ->where('bc.book_id','=',$id)
                ->orderBy('c.category_name');
            $result->categories = $db->all();    
            $db = new Query('storages');
            $res = $db->where('id','=',$result->storage)->first();
            if (isset($res->storage_name)) {
                $result->storageName = $res->storage_name;
            } else {
                $result->storageName = '';
            }
            return $result;
        }
}    
?>
