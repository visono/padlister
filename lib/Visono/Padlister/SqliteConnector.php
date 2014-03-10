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
 *
 */
class SqliteConnector implements ConnectorInterface
{
	/**
	 * @var null|\SQLite3 $connection
	 */
	protected $connection = null;


	public function __construct($dsn)
	{
		if (!file_exists($dsn)) {
			throw new \Exception('Given FilePath: "' . $dsn . '" is not found.');
		}
		$this->connection = $this->openConnection($dsn);
	}


	public function getPadKeys()
	{
		$query = 'SELECT DISTINCT substr(store.key,5,1000) AS PAD FROM store WHERE store.key LIKE "pad:%"';
		/** @var $results \SQLite3Result */
		$results = $this->connection->query($query);
		$keys    = array();
		while (false !== ($row = $results->fetchArray())) {
			//have unique pad ids in array
			$piece           = explode(':', $row[0]);
			$keys[$piece[0]] = $piece[0];
		}

		return $keys;
	}


	public function getAuthorById($id)
	{
		$query = 'select * from store where store.key = "globalAuthor:' . $id . '"';

		/** @var $result \SQLite3Result */
		$result = $this->connection->query($query);
		$result = $result->fetchArray();
		$author = json_decode($result['value']);
		if (isset($author->name) === false || null === $author->name) {
			$author->name = 'Arno Nym';
		}

		return $author;
	}


	protected function openConnection($databaseFilePath)
	{
		return new \SQLite3($databaseFilePath);
	}
}
