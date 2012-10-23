<?
/**
 * wrapper for a single pad
 * 
 * @author René Woltmann
 * @version 1.2
 * @copyright Visono GmbH 2012
 */
class Pad{
	/**
	 * indicates how many current authors are using this pad
	 * @var Integer
	 */
	public $authorsCount;
	/**
	 * list of author names
	 * @var Array
	 */
	public $authors;
	/**
	 * id of this pad
	 * @var String
	 */
	public $id;
	/**
	 * date of the last edit
	 * @var Date
	 */
	public $lastEdit;
	/**
	 * port of the pad, usually 9001
	 * @var Integer
	 */
	public $port;
	/**
	 * instance of a sqlite connection
	 * @var SQLite3
	 */
	public $db;
	/**
	 * key for the pad api 
	 * @var String
	 */
	public $apiKey;
	/**
	 * url for deleting a pad
	 * @var String
	 */
	public $deleteUrl;
	/**
	 * where the api key file lies
	 * @var String
	 */
	public $pathToApiKey;

	/**
	 * returns the id of this pad
	 * @return String
	 */
	public function __toString(){
		return $this->id;
	}

	/**
	 * 
	 * @param String $id
	 * @param SQLite3 $db
	 * @param Integer $port
	 * @param String $pathToApiKey
	 */
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

	/**
	 * calls the api for a field
	 * @param String $field
	 * @return JSONObject
	 */
	private function callApi($field){
		$result = file_get_contents('http://'.$_SERVER['HTTP_HOST'].':'.$this->port.'/api/1/'.$field.'?apikey='.$this->apiKey.'&padID='.$this->id);
		$json = json_decode($result);
		return $json;
	}

	/**
	 * mapper for a field
	 * @param String $field
	 * @throws Exception
	 * @return String
	 * 
	 */	
	private function getFunctionNameForField($field){
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
		return $value;
	}
	
	/**
	 * calls the api for a field
	 * @param String $field
	 * @throws Exception
	 * @return JSONObject
	 */
	private function getField($field){
		$json = $this->callApi($this->getFunctionNameForField($field));
		return $json->data->{$value};
	}

	/**
	 * sets the last edit date
	 */
	private function getTime(){
		$time = $this->getField('getLastEdited');
		$time = substr($time, 0, strlen($time)-3);
		$time = date('d.m.Y H:i:s',$time);
		$this->lastEdit = $time;
	}

	/**
	 * sets the authors count
	 */
	private function getAuthorsCount(){
		$this->authorsCount = $this->getField('padUsersCount');
	}

	/**
	 * sets the revisions 
	 */
	private function getRevisions(){
		$this->revisions = $this->getField('getRevisionsCount');
	}

	/**
	 * retrieves the name of an author with the provided id
	 * @param String $authorID
	 * @return String
	 */
	private function getAuthor($authorID){
		$query = 'select * from store where store.key = "globalAuthor:'.$authorID.'"';
		$result = $this->db->query($query)->fetchArray();
		$object = json_decode($result['value']);
		return $object->name;
	}

	/**
	 * sets the authors for this pad
	 */
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