<?php
/**
 *
 * @author Alexander Drebert
 * @version 1.0
 *
 */
class Thread
{
	/**
	 * 
	 * @var number
	 */
	private $id;
	/**
	 * 
	 * @var array of MSG
	 */
	private $MSG;
	/**
	 *
	 * @var boolean $deleted
	 */
	private $isDeleted;
	/**
	 *
	 * @var boolean $hasNewMessages
	 */
	private $hasNewMessages;
	
	
	/**
	 * 
	 * @param number $id
	 * @param MSG $msg array
	 */
	public function Thread($id,$msg)
	{
		$this->id = $id;
		$this->MSG = $msg;
	}
	
	/**
	 * @method Returns the messages of the thread
	 */
	public function getMessages()
	{
		return $this->MSG;
	}
	
	/**
	 * @method set the messages of the thread
	 * @var msg array
	 */
	public function setMessages($msg)
	{
		$this->MSG = $msg;
	}
	
	/**
	 * @method returns true if thread is deleted
	 */
	public function isDeleted()
	{
		return $this->isDeleted;
	}
	
	/**
	 * @method sets the deleted flag
	 * @var boolean $bool
	 */
	public function setIsDeleted($bool)
	{
		$this->isDeleted = $bool;
	}
	
	/**
	 * @method returns true if thread has unreaded messages
	 */
	public function hasNewMessages()
	{
		return $this->hasNewMessages;
	}
	
	/**
	 * @method true if the thread has unreaded messages
	 * @var boolean $bool
	 */
	public function setHasNewMessages($bool)
	{
		$this->hasNewMessages = $bool;
	}
	
	/**
	 * @method returns the threadId
	 */
	public function getId()
	{
		return $this->id;
	}
}
?>
