<?php

namespace ManiaLivePlugins\SitnGo\Services;

class Transaction
{
	public $id;
	public $payerLogin;
	public $payeeLogin;
	public $cost;
	public $creationDate;
	public $state;
	
	function __construct()
	{
		if($this->creationDate)
			$this->creationDate = new \DateTime($this->creationDate);
	}
}

?>
