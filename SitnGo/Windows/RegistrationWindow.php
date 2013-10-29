<?php

namespace ManiaLivePlugins\SitnGo\Windows;

use ManiaLib\Gui\Elements;

class RegistrationWindow extends \ManiaLive\Gui\Window
{
	static protected $registerAction;
	static protected $matchPrice;


	/**
	 * @var Elements\Button
	 */
	protected $registrationButton;
			
	static function setRegisterAction($action)
	{
		static::$registerAction = $action;
	}
	
	static function setMatchPrice($price)
	{
		static::$matchPrice = $price;
	}
			
	
	function onConstruct()
	{
		$ui = new Elements\Bgs1InRace(160, 90);
		$ui->setAlign('center','center');
		$this->addComponent($ui);
		
		$ui = new Elements\Label(80);
		$ui->setStyle(Elements\Label::TextTitle1);
		$ui->setPosY(43);
		$ui->setHalign('center');
		$ui->setText('Welcome on Sit\'n\'Go System');
		$this->addComponent($ui);
		
		$ui = new Elements\Label(100);
		$ui->setStyle(Elements\Label::TextTips);
		$ui->setHalign('center');
		$ui->setPosY(25);
		$ui->setText(sprintf('Pay %d planets to play one match', static::$matchPrice));
		$this->addComponent($ui);
		
		$ui = new Elements\Label(100);
		$ui->setStyle(Elements\Label::TextTips);
		$ui->setHalign('center');
		$ui->setPosY(18);
		$ui->setText('All registration are distributed between the 3 first players');
		$this->addComponent($ui);
		
		$this->registrationButton = new Elements\Button;
		$this->registrationButton->setText('Register');
		$this->registrationButton->setAlign('center', 'bottom');
		$this->registrationButton->setPosY(-43);
		$this->addComponent($this->registrationButton);
	}
	
	function onDraw()
	{
		$this->registrationButton->setAction(static::$registerAction);
	}
}

?>
