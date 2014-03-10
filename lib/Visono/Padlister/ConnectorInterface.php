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
interface ConnectorInterface
{
	public function getPadKeys();


	public function getAuthorById($id);
}
