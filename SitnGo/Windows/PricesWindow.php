<?php

namespace ManiaLivePlugins\SitnGo\Windows;

use ManiaLib\Gui\Elements;

class PricesWindow extends \ManiaLive\Gui\Window
{
	protected $firstNickname = 'satanasdiabolo';
	protected $secondNickname = 'satanasdiabolo';
	protected $thirdNickname = 'satanasdiabolo';
	protected $firstPrice = 50;
	
	/** @var Elements\Label */
	protected $firstLabel;
	/** @var Elements\Label */
	protected $secondLabel;
	/** @var Elements\Label */
	protected $thirdLabel;
	/** @var Elements\Label */
	protected $titleLabel;
	
	function setFirstNickname($nickname)
	{
		$this->firstNickname = $nickname;
	}
	
	function setFirstPrice($price)
	{
		$this->firstPrice = $price;
	}
	
	function setSecondNickname($nickname)
	{
		$this->secondNickname = $nickname;
	}
	
	function setThirdNickname($nickname)
	{
		$this->thirdNickname = $nickname;
	}
			
	
	function onConstruct()
	{
		$ui = new Elements\Bgs1InRace(165, 30);
		$ui->setAlign('center', 'bottom');
		$ui->setSubStyle(Elements\Bgs1InRace::BgList);
		$this->addComponent($ui);
		
		$this->titleLabel = new Elements\Label(160);
		$this->titleLabel->setStyle(Elements\Label::TextTitle1);
		$this->titleLabel->setAlign('center');
		$this->titleLabel->setPosY(28);
		$this->titleLabel->setText('Machin won 100 planets');
		$this->addComponent($this->titleLabel);
		
		$ui = new Elements\Quad(40, 13);
		$ui->setAlign('center','bottom');
		$ui->setBgcolor('FD0');
		$ui->setPosY(0.5);
		$this->addComponent($ui);
		
		$ui = new Elements\Icons64x64_1(8);
		$ui->setAlign('center','bottom');
		$ui->setSubStyle(Elements\Icons64x64_1::First);
		$ui->setPosY(0.5);
		$this->addComponent($ui);
		
		$this->firstLabel = new Elements\Label(40);
		$this->firstLabel->setAlign('center','bottom');
		$this->firstLabel->setPosition(0, 14);
		$this->firstLabel->setTextColor('fff');
		$this->addComponent($this->firstLabel);
		
		$ui = new Elements\Quad(40, 8);
		$ui->setAlign('center','bottom');
		$ui->setBgcolor('CCC');
		$ui->setPosition(-40, 0.5);
		$this->addComponent($ui);
		
		$ui = new Elements\Icons64x64_1(6);
		$ui->setAlign('center','bottom');
		$ui->setSubStyle(Elements\Icons64x64_1::Second);
		$ui->setPosition(-40, 0.5);
		$this->addComponent($ui);
		
		$this->secondLabel = new Elements\Label(40);
		$this->secondLabel->setAlign('center','bottom');
		$this->secondLabel->setPosition(-40, 9);
		$this->secondLabel->setTextColor('fff');
		$this->addComponent($this->secondLabel);
		
		$ui = new Elements\Quad(40, 5);
		$ui->setAlign('center','bottom');
		$ui->setBgcolor('652');
		$ui->setPosition(40, 0.5);
		$this->addComponent($ui);
		
		$ui = new Elements\Icons64x64_1(4);
		$ui->setAlign('center','bottom');
		$ui->setSubStyle(Elements\Icons64x64_1::Third);
		$ui->setPosition(40, 0.5);
		$this->addComponent($ui);
		
		$this->thirdLabel = new Elements\Label(40);
		$this->thirdLabel->setAlign('center','bottom');
		$this->thirdLabel->setPosition(40, 6);
		$this->thirdLabel->setTextColor('fff');
		$this->addComponent($this->thirdLabel);
		
	}
	
	function onDraw()
	{
		$this->firstLabel->setText($this->firstNickname);
		$this->secondLabel->setText($this->secondNickname);
		$this->thirdLabel->setText($this->thirdNickname);
		$this->titleLabel->setText(sprintf('$<%s$> won %d planets', $this->firstNickname, $this->firstPrice));
	}
}

?>
