<?php
/**
 * 
 * @author Alexander Drebert
 * @version 1.0
 * 
 */
class MSG
{
	/**
	 * @var number 
	 */
	private $id;
	/**
	 * @var string
	 */
	private $text;
	/**
	 * @var string
	 */
	private	$subject;
	/**
	 * @var time
	 */
	private $date;
	/**
	 * @var number
	 */
	private $threadId;
	/**
	 * @var number
	 */
	private $status;
	/**
	 * @var number
	 */
	private $toUserId;
	/**
	 * @var number
	 */
	private $fromUserId;
	/**
	 * @var boolean
	 */
	private $isDeleted;
	
	/**
	 * @methode MSG constructor
	 * @param number $id
	 * @param string $text
	 * @param string $subject
	 * @param time $date
	 * @param number $threadId
	 */
	public function MSG($id,$text,$subject,$date,$threadId,$status,$toUserId,$fromUserId)
	{
		$this->id 			= $id;
		$this->text 		= $text;
		$this->subject 		= $subject;
		$this->date 		= $date;
		$this->threadId		= $threadId;
		$this->status   	= $status;
		$this->toUserId 	= $toUserId;
		$this->fromUserId	= $fromUserId;
	}
	
	/**
	 * @method returns the message id
	 * @return number
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @method returns the message text
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}
	
	/**
	 * @method returns the message subject
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}
	
	/**
	 * @method returns the message date
	 * @return time
	 */
	public function getDate()
	{
		return $this->date;
	}
	
	/**
	 * @method returns the message thread
	 * @return number
	 */
	public function getThreadId()
	{
		return $this->threadId;
	}
	
	/**
	 * @method returns the message status
	 * @return number
	 * 	NOT_READED = 0;
		READED = 1;
		DELETED_BY_TO_USER = 2;
		DELETED_BY_FROM_USER = 3;
		DELETED_BY_BOTH = 4;
	 */
	public function getStatus()
	{
		return $this->status;
	}
	
	public function getToUserId()
	{
		return $this->toUserId;
	}
	
	public function getFromUserId()
	{
		return $this->fromUserId;
	}
	
	public function isDeleted()
	{
		return $this->isDeleted;
	}
	/**
	 * 
	 * @param boolean $bool
	 */
	public function setIsDeleted($bool)
	{
		$this->isDeleted = $bool;
	}
}
?>
