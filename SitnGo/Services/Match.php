<?php
namespace ManiaLivePlugins\SitnGo\Services;

class Match
{
	//When server is open and player can register
	const STATE_REGISTRATION_OPEN = 1;
	//When server is closed and waiting the change map to start the match
	const STATE_REGISTRATION_CLOSED = 2;
	//When the match is in progress
	const STATE_GAME_IN_PROGRESS = 3;
	//When the match is over and the players payed
	const STATE_FINISHED = 4;
	
	public $id;
	public $serverLogin;
	public $serverGameMode;
	public $creationDate;
	public $state;
	
	function __construct()
	{
		if($this->creationDate)
			$this->creationDate = new \DateTime($this->creationDate);
	}
}

?>
