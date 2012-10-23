<?
/**
 * wrapper for a single pad
 */
class Pad{
	public $authorsCount;
	public $authors;
	public $id;
	public $lastEdit;
        public $port;
        public $db;
        public $apiKey;
        public $deleteUrl;
        public $pathToApiKey;
	
	public function __toString(){
	    return $this->id;
	}
	
	public function __construct($id, $db, $port = 9001, $pathToApiKey = '/var/etherpad-lite/APIKEY.txt'){
            $this->pathToApiKey = $pathToApiKey;
            $this->port = $port;
            $this->id = $id;
            $this->db = $db;
            $this->apiKey = file_get_contents($this->pathToApiKey);
            $this->port = 9001;            
	    $this->getTime();
            $this->getAuthors();
            $this->getAuthorsCount();
            $this->getRevisions();            
            $this->viewUrl = 'http://'.$_SERVER['HTTP_HOST'].':'.$this->port.'/p/'.$this->id;
            $this->deleteUrl = 'http://'.$_SERVER['HTTP_HOST'].':'.$this->port.'/api/1/deletePad?apikey='.$this->apiKey.'&padID='.$this->id;
	}
        
        private function callApi($field){
            $result = file_get_contents('http://'.$_SERVER['HTTP_HOST'].':'.$this->port.'/api/1/'.$field.'?apikey='.$this->apiKey.'&padID='.$this->id);
            $json = json_decode($result);
            return $json;
        }
	
	private function getField($field){
            $value = null;
            switch($field){
                    case 'getLastEdited':{
                        $value = 'lastEdited';
                    }break;
                    case 'getRevisionsCount':{
                        $value = 'revisions';
                    }break;
                    case 'listAuthorsOfPad':{
                        $value = 'authorIDs';
                    }break;
                    case 'padUsersCount':{
                        $value = 'padUsersCount';                        
                    }break;
                    default:{
                        throw new Exception('Unknown Pad-Api-Call ("'.$field.')');
                    }
            }
            $json = $this->callApi($field);
            return $json->data->{$value};
	}
	
	private function getTime(){
		$time = $this->getField('getLastEdited');
		$time = substr($time, 0, strlen($time)-3);
		$time = date('d.m.Y H:i:s',$time);
                $this->lastEdit = $time;
	}
        
        private function getAuthorsCount(){
            $this->authorsCount = $this->getField('padUsersCount');
        }
        
        private function getRevisions(){
            $this->revisions = $this->getField('getRevisionsCount');
        }
        
        private function getAuthor($authorID){
            $query = 'select * from store where store.key = "globalAuthor:'.$authorID.'"';
            $result = $this->db->query($query)->fetchArray();
            $object = json_decode($result['value']);
            return $object->name;
        }
        
        private function getAuthors(){
            $authorIDs = $this->getField('listAuthorsOfPad');            
            $authors = array();
            foreach($authorIDs as $current){
                    $author = $this->getAuthor($current);
                    $author = null === $author? 'Arno Nym' : $author;
                    $authors[] = $author;
            }
            $authors = array_unique($authors);
            sort($authors);
            $this->authors = $authors;
        }
}