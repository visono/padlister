<?php
/**
 * VisonoPadLister
 *
 * Dieses kleine Script listet alle bestehenden Pads auf.
 * Es verbindet sich mit der SQLite Datenbank, die auch das Etherpad benutzt.
 * In der Tabelle gibt es einen Link, der die Api anspricht und ein Pad mit der betreffenden ID löscht.
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
 *
 * @todo i18n Im moment ist alles auf deutsch. Sprachpaket einbinden. Default sollte englisch sein.
 * @todo mysql Unterstützung für MySQL Anbindung einbauen. Refactoring wurde schon erledigt.
 */

//let me know, what's wrong!
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);
ini_set('error_log', './log/phperror.log');

$config = parse_ini_file('./config/config.ini', true);
$config['default']['orderBy'] = isset($_REQUEST['order']) ? $_REQUEST['order'] : $config['default']['orderBy'];

$baseName = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];


require_once './lib/Visono/Padlister/Pad.php';
require_once './lib/Visono/Padlister/ConnectorInterface.php';
require_once './lib/Visono/Padlister/SqliteConnector.php';
require_once './lib/Visono/Padlister/PadList.php';

$pads = new Visono\Padlister\PadList($config);

// Display HTML - Oldschool Way
?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<title>VisonoPadLister</title>
	<script src="static/jquery.js"></script>
	<script src="static/pad.js"></script>
	<link rel="stylesheet" type="text/css" href="static/style.css">
</head>
<body>
<h1>VisonoPadOverview</h1>

<p>
	<a id="addpad">Neues Pad erstellen</a> <a target="_blank" id="silentLink"></a>
</p>

<p id="order">
	Sortieren:
	<a href="http://<?= $baseName ?>?order=num">letzte Änderung</a> |
	<a href="http://<?= $baseName ?>?order=alpha">alphabetisch</a> |
	<a href="http://<?= $baseName ?>?order=user">aktive User</a>
</p>
<table>
	<tr>
		<th>Name</th>
		<th>Letzte Änderung</th>
		<th>Aktive User</th>
		<th>Autoren</th>
		<th>Revisionen</th>
		<th>Tools</th>
	</tr>
	<?php foreach ($pads as $pad) : ?>
		<tr id="tr<?= $pad->id ?>">
			<td>
				<a href="<?= $pad->viewUrl ?>" target="_blank"><?= $pad->id ?></a>
			</td>
			<td>
				<?= $pad->lastEdit ?>
			</td>
			<td>
				<?= $pad->authorsCount ?>
			</td>
			<td>
				<ul>
					<?php foreach ($pad->authors as $author): ?>
						<li><?= $author ?></li>
					<?php endforeach ?>

				</ul>
			</td>
			<td>
				<?= $pad->revisions ?>
			</td>
			<td>
				<a name="<?= $pad ?>" title='Pad "<?= $pad ?>" löschen' class="deletor" href="<?= $pad->deleteUrl ?>">
					<img src="static/trash.png" alt="[X]"/>
				</a>
			</td>
		</tr>
	<?php endforeach ?>
</table>
</body>
</html>
