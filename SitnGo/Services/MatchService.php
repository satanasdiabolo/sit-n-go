<?php

namespace ManiaLivePlugins\SitnGo\Services;

use ManiaLive\Database\MySQL\Connection;

class MatchService
{
	/**
	 *
	 * @var type 
	 */
	protected $db;
	
	function __construct()
	{
		$config = \ManiaLive\Database\Config::getInstance();
		$this->db = Connection::getConnection(
				$config->host,
				$config->username,
				$config->password,
				$config->database,
				$config->type,
				$config->port
		);
	}
	
	function registerTransaction($transactionId, $payer, $payee, $cost, $state)
	{
		$this->db->execute(
				'INSERT INTO Transactions (id, payerLogin, payeeLogin, cost, creationDate, state) '.
				'VALUES (%d, %s, %s, %d, NOW(), %d)',
				$transactionId, $this->db->quote($payer), $this->db->quote($payee), $cost, $state
				);
	}
	
	function updateTransaction($transactionId, $state)
	{
		$this->db->execute('UPDATE Transactions SET state = %d WHERE id = %d', $state, $transactionId);
	}
	
	function registerPlayer($playerLogin,$matchId, $transactionId)
	{
		$this->db->execute('INSERT INTO Players (login, transactionId, matchId) VALUES (%s, %d, %d)',
				$this->db->quote($playerLogin), $transactionId, $matchId);
	}
	
	function getPlayerLogin($transactionId)
	{
		return $this->db->execute('SELECT login FROM Players WHERE transactionId = %d', $transactionId)->fetchSingleValue();
	}
	
	function isPlayerConfirmed($playerLogin, $matchId)
	{
		return $this->db->execute(
				'SELECT count(*) FROM Players P '.
				'INNER JOIN Transactions T ON P.transactionId = T.id '.
				'WHERE P.login = %s AND P.matchId = %d AND T.state = %d', 
				$this->db->quote($playerLogin), $matchId,
				\DedicatedApi\Structures\Bill::STATE_PAYED)->fetchSingleValue();
	}
	
	function getPlayersConfirmed($matchId)
	{
		return $this->db->execute(
				'SELECT login FROM Players P '.
				'INNER JOIN Transactions T ON P.transactionId = T.id '.
				'WHERE P.matchId = %d AND T.state = %d', $matchId, \DedicatedApi\Structures\Bill::STATE_PAYED
				)->fetchArrayOfSingleValues();
	}
	
	function createMatch($serverLogin, $serverGameMode, $state)
	{
		$this->db->execute(
				'INSERT INTO `Matches` (serverLogin, serverGameMode, creationDate, state) '.
				'VALUES (%s, %s, NOW(), %d)', 
				$this->db->quote($serverLogin), 
				$this->db->quote($serverGameMode),
				$state
				);
		return $this->db->insertID();
	}
	
	function updateMatchState($matchId, $state)
	{
		$this->db->execute(
				'UPDATE `Matches` SET state = %d WHERE id = %d',
				$state, $matchId
				);
	}
	
	function createTables()
	{
		$this->db->execute(				
<<<EOMatches
CREATE TABLE IF NOT EXISTS `Matches` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`serverLogin` VARCHAR(25) NOT NULL,
	`serverGameMode` VARCHAR(75) NOT NULL,
	`creationDate` DATETIME NOT NULL,
	`state` INT NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
EOMatches
				);
		
		$this->db->execute(				
<<<EOTransactions
CREATE TABLE IF NOT EXISTS `Transactions` (
	`id` INT NOT NULL,
	`payerLogin` VARCHAR(25) NOT NULL,
	`payeeLogin` VARCHAR(25) NOT NULL,
	`cost` INT NOT NULL,
	`creationDate` DATETIME NOT NULL,
	`state` INT NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
EOTransactions
				);
		
		$this->db->execute(				
<<<EOPlayers
CREATE TABLE `Players` (
	`login` VARCHAR(25) NOT NULL,
	`transactionId` INT NOT NULL,
	`matchId` INT NOT NULL,
	`rank` INT NULL DEFAULT NULL,
	PRIMARY KEY (`login`, `transactionId`),
	INDEX `FK__transactions` (`transactionId`),
	INDEX `FK__matches` (`matchId`),
	CONSTRAINT `FK__transactions` FOREIGN KEY (`transactionId`) REFERENCES `transactions` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION,
	CONSTRAINT `FK__matches` FOREIGN KEY (`matchId`) REFERENCES `matches` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
EOPlayers
				);
	}
}

?>
