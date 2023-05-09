<?php
    use \RATWEB\Model;
    use \RATWEB\DB\Query;
    use \RATWEB\DB\Record;

    class AuthorModel extends Model  {

        function __construct() {
            parent::__construct();
            $this->setTable('authors');
            $this->errorMsg = ''; 
        }

        /**
         * üres group rekord
         */
        public function emptyRecord(): Record {
            $result = new Record();
                    $result->id = 0;
        $result->author_name = "";

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
						if ($filter[$i] == 'author_name') {
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
            $db = new Query($this->table,'a');
            $db->join('LEFT','book_author','ba','ba.author_id','=','a.id')
                ->join('LEFT','books','b','b.id','=','ba.book_id')
                ->select(['a.id','a.author_name','count(b.id) as book_count'])
                ->groupBy(['a.id','a.author_name'])
                ->offset((($page - 1) * $limit))
                ->limit($limit)
                ->orderBy('author_name');
            $this->filterToQuery($db,$filter);        
            $result = $db->all();
            return $result;        
        }

        /**
         * Összes rekord száma
         * @return int
         */
        public function getTotal($filter = ''): int {
            $db = new Query($this->table,'a');
            $db->join('LEFT','book_author','ba','ba.author_id','=','a.id')
                ->join('LEFT','books','b','b.id','=','ba.book_id')
                ->select(['a.id','a.author_name','count(b.id) as book_count'])
                ->groupBy(['a.id','a.author_name']);
            $this->filterToQuery($db,$filter);        
            return $db->count();
        }

        /**
         * új szerző felvétele ha még nincs, ha már van akkor id visszadása
         * @param string $name
         * @return int
         */
        public function addAuthor(string $name):int {
            $db = new Query($this->table);
            $res = $db->select(['id'])->where('author_name','=',$name)->first();
            if (isset($res->id)) {
                return $res->id;
            } else {
                $record = new Record;
                $record->id = 0;
                $record->author_name = $name;
                return $db->insert($record);
            }
        }

        /**
         * könyv szerzőinek lekérdezése
         * @param int $book_id
         * @return array [id]
         */
        public function getBookAuthors(int $book_id): array {
            $db = new Query('book_author','ba');
            $db->join('LEFT','authors','a','ba.author_id','=','a.id')
                ->select(['a.id'])
                ->where('ba.book_id','=',$book_id);
            $res = $db->all();
            $result = [];
            foreach ($res as $r) {
                $result[] = (int)$r->id;
            }
            return $result;
        }

        /**
         * könyv szerző kapcsolása
         * @param int $book_id
         * @param int $author_id
         */
        public function addBookAuthor(int $book_id, int $author_id) {
            $db = new Query('book_author');
            $record = new Record;
            $record->id = 0;
            $record->book_id = $book_id;
            $record->author_id = $author_id;
            $db->insert($record);
        }

        /**
         * könyv szerző kapcsolat törlése
         * @param int $book_id
         * @param int $author_id
         */
        public function deleteBookAuthor(int $book_id, int $author_id) {
            $db = new Query('book_author');
            $db->where('book_id','=', $book_id)
                ->where('author_id','=', $author_id)->delete();
        }

}    
?>
