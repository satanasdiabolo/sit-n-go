<?php
namespace ManiaLivePlugins\SitnGo\Windows;

class MessageWindow extends \ManiaLive\Gui\Window
{
	protected $message;
	
	protected $messageLabel;
		
	function onConstruct()
	{
		$this->messageLabel = new \ManiaLib\Gui\Elements\Label(250);
		$this->messageLabel->setStyle(\ManiaLib\Gui\Elements\Label::TextRaceMessageBig);
		$this->messageLabel->setHalign('center');
		$this->messageLabel->setTextSize(7);
		$this->addComponent($this->messageLabel);
		
		$this->message = 'Lorem Ipsum';
	}
	
	function setMessage($message)
	{
		$this->message = $message;
	}
	
	function onDraw()
	{
		$this->messageLabel->setText($this->message);
	}
}

?>
