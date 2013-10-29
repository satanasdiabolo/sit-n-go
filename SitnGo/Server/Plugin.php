<?php

namespace ManiaLivePlugins\SitnGo\Server;

use ManiaLive\DedicatedApi\Callback\Event as DedicatedEvent;
use ManiaLivePlugins\SitnGo\Services;
use DedicatedApi\Structures\GameInfos;

class Plugin extends \ManiaLive\PluginHandler\Plugin
{

	protected $cost;
	protected $state;
	protected $maxPlayerWhenOpen;
	protected $serverLogin;
	protected $gameMode;

	/**
	 * @var Services\MatchService
	 */
	protected $matchService;
	protected $currentMatchId;
	protected $bills;

	function onInit()
	{
		$this->setVersion("0.0.1");
	}

	function onLoad()
	{
		$this->enableDedicatedEvents(
				DedicatedEvent::ON_PLAYER_CONNECT |
				DedicatedEvent::ON_PLAYER_DISCONNECT |
				DedicatedEvent::ON_BILL_UPDATED |
				DedicatedEvent::ON_BEGIN_MATCH |
				DedicatedEvent::ON_END_MATCH |
				DedicatedEvent::ON_STATUS_CHANGED
		);
		$this->cost = \ManiaLivePlugins\SitnGo\Config::getInstance()->cost;

		$registrationAction = \ManiaLive\Gui\ActionHandler::getInstance()->createAction(array($this, 'onClickRegisterButton'));
		\ManiaLivePlugins\SitnGo\Windows\RegistrationWindow::setRegisterAction($registrationAction);
		\ManiaLivePlugins\SitnGo\Windows\RegistrationWindow::setMatchPrice($this->cost);


		$this->state = Services\Match::STATE_REGISTRATION_OPEN;
		$this->serverLogin = $this->storage->serverLogin;
		switch ($this->storage->gameInfos->gameMode)
		{
			case GameInfos::GAMEMODE_SCRIPT:
				$this->gameMode = $this->storage->gameInfos->scriptName;
				break;
			case GameInfos::GAMEMODE_ROUNDS:
				$this->gameMode = 'Rounds';
				break;
			case GameInfos::GAMEMODE_TIMEATTACK:
				$this->gameMode = 'TimeAttack';
				break;
			case GameInfos::GAMEMODE_TEAM:
				$this->gameMode = 'Team';
				break;
			case GameInfos::GAMEMODE_LAPS:
				$this->gameMode = 'Laps';
				break;
			case GameInfos::GAMEMODE_CUP:
				$this->gameMode = 'Cup';
				break;
			case GameInfos::GAMEMODE_STUNTS:
				$this->gameMode = 'Stunts';
				break;
		}
		$this->matchService = new Services\MatchService();
		$this->createMatch();
	}

	function onPlayerConnect($login, $isSpectator)
	{
		$this->welcomePlayer($login);
	}

	function onPlayerDisconnect($login, $disconnectionReason)
	{
		
	}

	function onBillUpdated($billId, $state, $stateName, $transactionId)
	{
		if ($transactionId && array_key_exists($billId, $this->bills) && $this->bills[$billId]['payer'] != $this->serverLogin)
		{
			$this->matchService->registerTransaction($transactionId, $this->bills[$billId]['payer'], $this->bills[$billId]['payee'], $this->bills[$billId]['cost'], $state);
			$this->matchService->registerPlayer($this->bills[$billId]['payer'], $this->currentMatchId, $transactionId);
			unset($this->bills[$billId]);
		}
		elseif ($transactionId)
		{
			$this->matchService->updateTransaction($transactionId, $state);
			$playerLogin = $this->matchService->getPlayerLogin($transactionId);
			if ($playerLogin && $this->matchService->isPlayerConfirmed($playerLogin, $this->currentMatchId))
			{
				$this->confirmPlayer($playerLogin);
			}

			$maxPlayerPerMatch = \ManiaLivePlugins\SitnGo\Config::getInstance()->maxPlayerPerMatch;
			if (count($this->matchService->getPlayersConfirmed($this->currentMatchId)) == $maxPlayerPerMatch)
			{
				$this->prepareMatch();
			}
		}
		//Check the new Bill status
		//If it's payed and the payer is a player,
		//then register it for the next match, and switch him to player state
		//If the number of player is equal to the limit, the match begun on the next map
	}
	
	function onBeginMatch()
	{
		if($this->state == Services\Match::STATE_FINISHED)
		{
			\ManiaLivePlugins\SitnGo\Windows\PricesWindow::EraseAll();
			$this->createMatch();
			$this->openServer();
		}
	}

	function onEndMatch($rankings, $winnerTeamOrMap)
	{
		if ($this->state == Services\Match::STATE_REGISTRATION_CLOSED)
		{
			$this->updateState(Services\Match::STATE_GAME_IN_PROGRESS);
		}
		elseif ($this->state == Services\Match::STATE_GAME_IN_PROGRESS)
		{
			$this->endMatch($rankings);
		}
		//If the match is not started and the number of registration is good,
		//Then start the match.
		//If a match is in progress, stop it, and give a price to players
	}
	
	function onStatusChanged($statusCode, $statusName)
	{
		
	}

	function onClickRegisterButton($login)
	{
		$registeredPlayers = count($this->matchService->getPlayersConfirmed($this->currentMatchId));
		$playerInRegistration = count($this->bills);
		if (($this->state == Services\Match::STATE_REGISTRATION_OPEN) && ($registeredPlayers + $playerInRegistration < 8))
		{
			$cost = \ManiaLivePlugins\SitnGo\Config::getInstance()->cost;
			$billId = $this->connection->sendBill($login, $cost, "Registration to Sit'n'Go Match");
			$this->bills[$billId] = array('payer' => $login, 'payee' => $this->serverLogin, 'cost' => $cost);
		}
		//Save billId to be used by onBilUpdated to know if player payed
	}

	function welcomePlayer($login)
	{
		//Force the player as spectator
		$this->connection->forceSpectator($login, 1);
		//Display him the welcome screen
		\ManiaLivePlugins\SitnGo\Windows\RegistrationWindow::Create($login)->show();
	}

	function confirmPlayer($login)
	{
		$this->connection->forceSpectator($login, 2);
		\ManiaLivePlugins\SitnGo\Windows\RegistrationWindow::Erase($login);
	}

	function prepareMatch()
	{
		$this->updateState(Services\Match::STATE_REGISTRATION_CLOSED);
		$this->closeServer();
		//Display Message to warn that match will begun w/ the next map
	}

	function endMatch($rankings)
	{
		usort($rankings, function ($a, $b) {
					if ($a['Ranking'] == $b['Ranking'])
					{
						return 0;
					}
					return $a['Ranking'] < $b['Ranking'] ? -1 : 1;
				}
		);
		$podium = array_slice($rankings, 0, 3);
		$podiumLogins = array_map(function ($p) {
					return $p['Login'];
				}, $podium);
		$this->payPrices($podiumLogins);
		\ManiaLivePlugins\SitnGo\Windows\PricesWindow::Create()->show();
		$this->updateState(Services\Match::STATE_FINISHED);
	}

	function createMatch()
	{
		$this->currentMatchId = $this->matchService->createMatch($this->serverLogin, $this->gameMode, Services\Match::STATE_REGISTRATION_OPEN);
		$this->updateState(Services\Match::STATE_REGISTRATION_OPEN);
		foreach (array_merge($this->storage->spectators, $this->storage->players) as $player)
		{
			$this->welcomePlayer($player->login);
		}
	}

	//Set the server as full
	function closeServer()
	{
		$this->maxPlayerWhenOpen = $this->connection->getMaxPlayers();
		$this->maxPlayerWhenOpen = $this->maxPlayerWhenOpen['CurrentValue'];

		$players = $this->connection->getPlayerList(-1, 0);
		foreach ($players as $player)
		{
			$this->connection->addGuest($player, true);
		}
		$this->connection->setMaxPlayers(0, true);
		$this->connection->executeMulticall();
	}

	function payPrices($podium)
	{
		//See how to manage ex-aequo
		$config = \ManiaLivePlugins\SitnGo\Config::getInstance();
		$pricePool = $config->cost * $config->maxPlayerPerMatch;
		$partNumber = (pow(2, count($podium)) - 1);
		$pricePart = $pricePool / $partNumber;
		
		$window = \ManiaLivePlugins\SitnGo\Windows\PricesWindow::Create();

		$i = 1;
		while ($player = array_shift($podium))
		{
			$playerPrice = $pricePart * pow(2, count($podium));
			$playerNickname = $this->storage->getPlayerObject($player)->nickName;
			switch ($i++)
			{
				case 1:
					$window->setFirstNickname($playerNickname);
					$window->setFirstPrice($playerPrice);
					break;
				case 2:
					$window->setSecondNickname($playerNickname);
					break;
				case 3:
					$window->setThirdNickname($playerNickname);
					break;
			}
			$this->connection->pay($player, $playerPrice, sprintf('You finished %d to the Sit\'n\'Go match', ($size - count($podium))));
		}
	}

	function openServer()
	{
		$this->connection->cleanGuestList(true);
		$this->connection->setMaxPlayers($this->maxPlayerWhenOpen, true);
		$this->connection->executeMulticall();
	}

	function isServerClosed()
	{
		$maxPlayer = $this->connection->getMaxPlayers();
		return (count($this->connection->getGuestList(-1, 0)) > 0) && ($maxPlayer['CurrentValue'] > 0);
	}

	function updateState($state)
	{
		if ($state != $this->state)
		{
			$this->matchService->updateMatchState($this->currentMatchId, $state);
		}
		$this->state = $state;
	}

}

?>
