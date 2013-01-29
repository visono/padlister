<?php
/**
 * VisonoPadLister
 *
 * PHP Version 5.3.x
 *
 * @category   Padlister
 * @package    Visono
 * @author     René Woltman <r.woltmann@visono.biz>
 * @author     Nils Bujny <n.bujny@visono.biz>
 * @copyright  2012-2013 Visono GmbH
 * @license    http://www.gnu.org/licenses/gpl-3.0
 * @version    1.5
 * @link       https://github.com/Visono/padlister
 */

namespace Visono\Padlister;

/**
 * wrapper for a single pad
 *
 * @author     René Woltmann
 * @copyright  2012-2013 Visono GmbH
 * @version    1.5
 */
class Pad
{
	/**
	 * indicates how many current authors are using this pad
	 *
	 * @var Integer
	 */
	public $authorsCount;
	/**
	 * list of author names
	 *
	 * @var Array
	 */
	public $authors;
	/**
	 * id of this pad
	 *
	 * @var String
	 */
	public $id;
	/**
	 * date of the last edit
	 *
	 * @var \DateTime
	 */
	public $lastEdit;
	/**
	 * port of the pad, usually 9001
	 *
	 * @var int
	 */
	public $port;
	/**
	 * instance of a sqlite connection
	 *
	 * @var ConnectorInterface
	 */
	public $db;
	/**
	 * key for the pad api
	 *
	 * @var String
	 */
	public $apiKey;
	/**
	 * url for deleting a pad
	 *
	 * @var String
	 */
	public $deleteUrl;
	/**
	 * where the api key file lies
	 *
	 * @var String
	 */
	public $pathToApiKey;
	public $host;
	public $revisions;


	/**
	 * returns the id of this pad
	 *
	 * @return String
	 */
	public function __toString()
	{
		return $this->id;
	}


	/**
	 *
	 * @param String             $id
	 * @param ConnectorInterface $db
	 * @param Integer            $port
	 * @param String             $pathToApiKey
	 * @param string             $host
	 */
	public function __construct(
		$id,
		$db,
		$port = 9001,
		$pathToApiKey = '/var/etherpad-lite/APIKEY.txt',
		$host = 'localhost'
	)
	{
		$this->pathToApiKey = $pathToApiKey;
		$this->port         = $port;
		$this->id           = $id;
		$this->db           = $db;
		$this->apiKey       = file_get_contents($this->pathToApiKey);
		$this->port         = 9001;
		$this->host         = $host;
		$this->getTime();
		$this->getAuthors();
		$this->getAuthorsCount();
		$this->getRevisions();
		$this->viewUrl   = 'http://' . $this->host . ':' . $this->port . '/p/' . $this->id;
		$this->deleteUrl = 'http://' . $this->host . ':' . $this->port . '/api/1/deletePad?apikey=' . $this->apiKey . '&padID=' . $this->id;
	}


	/**
	 * calls the api for a field
	 *
	 * @param String $field
	 *
	 * @return \stdClass
	 */
	private function callApi($field)
	{
		$result = file_get_contents(
			'http://' . $this->host . ':' . $this->port . '/api/1/' . $field . '?apikey=' . $this->apiKey . '&padID=' . $this->id
		);
		$json   = json_decode($result);

		return $json;
	}


	/**
	 * mapper for a field
	 *
	 * @param String $field
	 *
	 * @throws \Exception
	 * @return String
	 *
	 */
	private function getFunctionNameForField($field)
	{
		switch ($field) {
			case 'getLastEdited':
				$value = 'lastEdited';
				break;
			case 'getRevisionsCount':
				$value = 'revisions';
				break;
			case 'listAuthorsOfPad':
				$value = 'authorIDs';
				break;
			case 'padUsersCount':
				$value = 'padUsersCount';
				break;
			default:
				throw new \Exception('Unknown Pad-Api-Call ("' . $field . ')');
		}
		return $value;
	}


	/**
	 * calls the api for a field
	 *
	 * @param String $field
	 *
	 * @throws \Exception
	 * @return \stdClass
	 */
	private function getField($field)
	{
		$value = $this->getFunctionNameForField($field);
		$json  = $this->callApi($field);

		return $json->data->{$value};
	}


	/**
	 * sets the last edit date
	 */
	private function getTime()
	{
		$time = $this->getField('getLastEdited');
		$date = new \DateTime();
		$date->setTimestamp(substr($time, 0, strlen($time) - 3));
		$this->lastEdit = $date->format('d.m.Y H:i:s');
	}


	/**
	 * sets the authors count
	 */
	private function getAuthorsCount()
	{
		$this->authorsCount = $this->getField('padUsersCount');
	}


	/**
	 * sets the revisions
	 */
	private function getRevisions()
	{
		$this->revisions = $this->getField('getRevisionsCount');
	}


	/**
	 * retrieves the name of an author with the provided id
	 *
	 * @param String $authorId
	 *
	 * @return String
	 */
	private function getAuthor($authorId)
	{
		$author = $this->db->getAuthorById($authorId);

		return $author->name;
	}


	/**
	 * sets the authors for this pad
	 */
	private function getAuthors()
	{
		$authorIds = $this->getField('listAuthorsOfPad');
		$authors   = array();
		foreach ($authorIds as $current) {
			$author    = $this->getAuthor($current);
			$author    = null === $author ? 'Arno Nym' : $author;
			$authors[] = ucfirst($author);
		}
		$authors = array_unique($authors);
		sort($authors);
		$this->authors = $authors;
	}
}
