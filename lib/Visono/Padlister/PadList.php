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
class PadList extends \ArrayObject
{
	protected $pads = array();
	protected $db = null;
	protected $config = array();


	public function __construct($config)
	{
		$this->config = $config;
		$this->db = $this->getConnector($config['database']);
		$this->exchangeArray($this->loadPads());
		$this->uasort($this->getCompare($config['default']['orderBy']));
	}


	public function getPads()
	{

		return $this;
	}


	protected function loadPads()
	{
		$padKeys = $this->db->getPadKeys();
		$pads    = array();
		foreach ($padKeys as $key) {
			$pads[] = new Pad(
				$key,
				$this->db,
				9001,
				$this->config['default']['apiKeyPath'],
				$this->config['default']['host']
			);
		}

		return $pads;
	}


	public function getCompare($orderBy)
	{
		return function ($a, $b) use ($orderBy) {
			$sortValueA = null;
			$sortValueB = null;
			switch ($orderBy) {
				case 'num':
					$sortValueA = strtotime($a->lastEdit);
					$sortValueB = strtotime($b->lastEdit);
					break;
				case 'user':
					$sortValueA = $a->authorsCount;
					$sortValueB = $b->authorsCount;
					break;
				default:
					$sortValueA = strtolower($b->id);
					$sortValueB = strtolower($a->id);
					break;
			}
			if ($sortValueA === $sortValueB) {
				return 0;
			}

			return $sortValueA < $sortValueB ? 1 : -1;
		};
	}


	protected function getConnector($config)
	{
		$db = null;
		switch ($config['type']) {
			case 'sqlite':
				$db = new SqliteConnector($config['path']);
				break;
			default:
				throw new \Exception('Missing/Wrong Type in config["database"]!');
		}

		return $db;
	}
}
