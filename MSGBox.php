<?php
/**
 *
 * @author Alexander Drebert
 * @version 1.0
 *
 */
class MSGBox
{
	/**
	 * Sequence name for MSG.ID
	 * If your database use autoincrement(like mysql) let it null
	 * @var string
	 */
	private static $SEQ_MSG_ID = "leg.seq_msg_id";
	/**
	 * Sequence name for MSG.Thread_ID
	 * If your database use autoincrement(like mysql) let it null
	 * @var string
	 */
	private static $SEQ_THREAD_ID = "leg.seq_thread_id";
	
	private static $IDENTIFIER = '"';
	private static $SCHEMA = 'leg';
	//*** DO NOT EDIT BEHIND THIS LINE ***/
	
	private static $PDO_DSN = NULL;
	private static $PDO_USERNAME = NULL;
	private static $PDO_PASSWORD = NULL;
	private static $PDO_OPTIONS = NULL;
	
	public static $NOT_READED = 0;
	public static $READED = 1;
	public static $DELETED_BY_TO_USER = 2;
	public static $DELETED_BY_FROM_USER = 3;
	public static $DELETED_BY_BOTH = 4;
	
	/**
	 * @var pdo
	 */
	private $pdo;
	/**
	 * @var number
	 */
	private $userId;
	/**
	 * @methode MSGBox constructor.
	 * @param number $userId
	 * @param Optional PDO instance $pdo
	 */
	public function MSGBox($userId,$pdo=null)
	{
		require_once 'Thread.php';
		$this->userId 	= $userId;
		if($pdo != null) {
			$this->pdo = $pdo;
		}
		else {
			$this->pdo = new PDO(self::$PDO_DSN, self::$PDO_USERNAME, self::$PDO_PASSWORD, self::$PDO_OPTIONS);
		}
	}
	
	/**
	 * @param string $seqName name of the sequenz
	 * @return number
	 */
	public function getNextVal($seqName)
	{
		return $this->pdo->query("select nextval('$seqName')")->fetch()[0];
	}
	
	/**
	 * @param string $seqName name of the sequenz
	 * @return number
	 */
	public function getCurrVal($seqName)
	{
		return $this->pdo->query("select currval('$seqName')")->fetch()[0];
	}
	
	private function executeQueryRollbackOnException($stmt,$dataArray=null) 
	{
		try {
			$stmt->execute($dataArray);
		} catch (Exception $ex) {
			$this->pdo->rollBack();
		}
	}
		
	/**
	 * @method Adds die columnidentifierquote and schemaname
	 * @param string $sql query
	 * @return string the sql query
	 */
	private function getQuery($sql)
	{
		$sql = str_replace('"', self::$IDENTIFIER, $sql);
		return $sql;
	}
	
		
	/**
	 * @method Sends a MSG
	 * @param number $toUserId receiver
	 * @param string subject of the message
	 * @param string text/body of the message
	 * @param date date of the message
	 * @example sendMessage("Meet today","Hey Bob, you are today at home?",time(),12))
	 */
	public function sendNewMessage($subject,$text,$date,$toUserId)
	{	
		$this->pdo->beginTransaction();
		
		$msgId = $this->getNextVal(self::$SEQ_MSG_ID);
		$threadId = $this->getNextVal(self::$SEQ_THREAD_ID);
		
		$sql = $this->getQuery('INSERT INTO '.self::$SCHEMA.'.MSG ("ID", "Subject", "Text", "Date", "Thread_ID") VALUES ('.$msgId.', ?, ?, ?, '.$threadId.')');
		
		$stmt = $this->pdo->prepare($sql);
		$this->executeQueryRollbackOnException($stmt,array($subject,$text,$date));
		
		$sql = $this->getQuery('INSERT INTO '.self::$SCHEMA.'.MSG_BOX ("MSG_ID", "To_User_ID", "From_User_ID", "Status") VALUES ('.$msgId.', ?, ?, ?)');
		
		$stmt = $this->pdo->prepare($sql);
		$this->executeQueryRollbackOnException($stmt,array($toUserId,$this->userId,self::$NOT_READED));
		
		$this->pdo->commit();
	}
	
	/**
	 * @method add a message to a thread.
	 * @param $MSG the message
	 * @param string subject of the message
	 * @param string text/body of the message
	 * @param date date of the message
	 * @param number $threadId the thread id to add msg
	 */
	public function addMessage($subject,$text,$date,$threadId)
	{
		$this->pdo->beginTransaction();
		
		$msgId = $this->getNextVal(self::$SEQ_MSG_ID);
		
		$sql = $this->getQuery('INSERT INTO '.self::$SCHEMA.'.MSG ("ID", "Subject", "Text", "Date", "Thread_ID") VALUES ('.$msgId.', ?, ?, ?, '.$threadId.')');
		
		$stmt = $this->pdo->prepare($sql);
		$this->executeQueryRollbackOnException($stmt,array($subject,$text,$date));
		
		$sql = $this->getQuery('INSERT INTO '.self::$SCHEMA.'.MSG_BOX ("MSG_ID", "To_User_ID", "From_User_ID", "Status") VALUES ('.$msgId.', ?, ?, ?)');
		
		$stmt = $this->pdo->prepare($sql);
		$this->executeQueryRollbackOnException($stmt,array($this->getConversationPartner($threadId),$this->userId,self::$NOT_READED));
		
		$this->pdo->commit();
	}
	
	/**
	 * @method by adding a MSG to a thread you need to find the right to_user_id
	 * @param number $threadId
	 */
	private function getConversationPartner($threadId) {
		
		$sql = $this->getQuery('SELECT DISTINCT "To_User_ID", "From_User_ID" FROM '.self::$SCHEMA.'.MSG_BOX INNER JOIN '.self::$SCHEMA.'.MSG ON MSG."ID" = MSG_BOX."MSG_ID" WHERE "Thread_ID"=?');
		
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array($threadId));
		
		$row = $stmt->fetch();
		
		if($row['To_User_ID'] != $this->userId)
		{
			return $row['To_User_ID'];
		}
		else
		{
			return $row['From_User_ID'];
		}
		
		return null;
	}
	
	public function getThreads($limit=null,$offset=null) 
	{
		$threads = array();
		
		$sql = $this->getQuery('SELECT "Subject", "Thread_ID" ,"ID", "To_User_ID", "From_User_ID", "Status", "Text", "Date" 
					FROM '.self::$SCHEMA.'.MSG INNER JOIN '.self::$SCHEMA.'.MSG_BOX ON "ID"="MSG_ID" WHERE ("To_User_ID"='.$this->userId.' OR "From_User_ID"='.$this->userId.') ORDER BY "Date" DESC '.($limit == null ? '' : 'LIMIT ? OFFSET ? '));
		
		$stmt = $this->pdo->prepare($sql);
		if($limit != null)
		{
			echo $sql;
			$stmt->execute(array($limit,$offset));
		}
		else 
		{
			$stmt->execute();
		}

		foreach ($stmt->fetchAll() as $row) {
				$msg = new MSG($row['ID'], $row['Text'], $row['Subject'], $row['Date'], $row['Thread_ID'],$row['Status'],$row['To_User_ID'],$row['From_User_ID']);
				$msg = $this->setIsMsgDeleted($msg);
				$threads = $this->fillMSGinThreads($threads, $msg, $row['Thread_ID']);
		}
		return $threads;
	}
	
	/**
	 * @method fills the MSG in the correct place in an array of threads.
	 * 			Search in the array after the given $threadId. If found -> place msg in this thread.
	 * 			Else create a new thread and place msg.
	 */
	private function fillMSGinThreads($threads,$MSG,$threadId)
	{
		foreach ($threads as $thread)
		{
			if ($thread->getId() == $threadId) {
				$messages = $thread->getMessages();
				array_push($messages, $MSG);
				$thread->setMessages($messages);
				return $threads;
			}
		}
		//not found
		$t = new Thread($threadId, array($MSG));
		array_push($threads, $this->setIsThreadDeletedOrHasNewMessages($t));
		return $threads;
	}
	
	public function getThread($id)
	{
		$messages = array();
		
		$sql = $this->getQuery('SELECT "Thread_ID", "Text", "Status", "Subject", "To_User_ID", "From_User_ID", "ID", "Date" FROM '.self::$SCHEMA.'.MSG
								INNER JOIN '.self::$SCHEMA.'.MSG_BOX ON "ID"="MSG_ID" WHERE "Thread_ID"=? AND ("To_User_ID"='.$this->userId.' OR "From_User_ID"='.$this->userId.') ORDER BY "Date" DESC');
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array($id));
		
		foreach ($stmt->fetchAll() as $row) {
			$msg = new MSG($row['ID'], $row['Text'], $row['Subject'], $row['Date'], $row['Thread_ID'],$row['Status'],$row['To_User_ID'],$row['From_User_ID']);
			$msg = $this->setIsMsgDeleted($msg);
			array_push($messages, $msg);
		}
		$thread = new Thread($id, $messages);
		return $this->setIsThreadDeletedOrHasNewMessages($thread);
	}
	
	/**
	 * 
	 * @param Thread $thread
	 * @return Thread
	 */
	private function setIsThreadDeletedOrHasNewMessages($thread)
	{
		$isDeleted = true;
		$hasNewMessages = false;
		foreach ($thread->getMessages() as $message)
		{
			//check if msg id deleted, if one message is not deletet then the thread is not marked as deleted
			if( !( ($message->getToUserId() == $this->userId && ($message->getStatus() == self::$DELETED_BY_TO_USER || 
							$message->getStatus() == self::$DELETED_BY_BOTH) ) || 
				($message->getFromUserId() == $this->userId && ($message->getStatus() == self::$DELETED_BY_FROM_USER || 
						$message->getStatus() == self::$DELETED_BY_BOTH) ) ) )			
			{
				$isDeleted = false;	
			}
			if($message->getToUserId() == $this->userId && $message->getStatus() == self::$NOT_READED)
			{
				$hasNewMessages = true;
			}
		}
		$thread->setHasNewMessages($hasNewMessages);
		$thread->setIsDeleted($isDeleted);
		return $thread;
	}
	
	/**
	 * 
	 * @param MSG $msg
	 * @return MSG
	 */
	private function setIsMsgDeleted($msg)
	{
		$isDeleted = false;
		if(($msg->getToUserId() == $this->userId && ($msg->getStatus() == self::$DELETED_BY_TO_USER ||
				$msg->getStatus() == self::$DELETED_BY_BOTH) ) ||
				($msg->getFromUserId() == $this->userId && ($msg->getStatus() == self::$DELETED_BY_FROM_USER ||
						$msg->getStatus() == self::$DELETED_BY_BOTH) ) )
		{
			$isDeleted = true;
		}
		$msg->setIsDeleted($isDeleted);
		return $msg;
	}
	
	public function getMsg($msgId)
	{
		$sql = $this->getQuery('SELECT "Thread_ID", "Text", "Status", "Subject", "To_User_ID", "From_User_ID", "ID", "Date" FROM '.self::$SCHEMA.'.MSG
								INNER JOIN '.self::$SCHEMA.'.MSG_BOX ON "ID"="MSG_ID" WHERE "ID"=? AND ("To_User_ID"='.$this->userId.' OR "From_User_ID"='.$this->userId.') ORDER BY "Date" DESC');
		
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array($msgId));
		
		foreach ($stmt->fetchAll() as $row) {
			$msg = new MSG($row['ID'], $row['Text'], $row['Subject'], $row['Date'], $row['Thread_ID'],$row['Status'],$row['To_User_ID'],$row['From_User_ID']);
			$msg = $this->setIsMsgDeleted($msg);
			return $msg;
		}
	}
	
	public function getCountNotReadedMessages()
	{
		$sql = $this->getQuery('select count(*) from '.self::$SCHEMA.'.MSG INNER JOIN '.self::$SCHEMA.'.MSG_BOX ON"ID"="MSG_ID" WHERE 
								"To_User_ID"='.$this->userId.' AND "Status"='.self::$NOT_READED);
		$stmt = $this->pdo->query($sql);
		$row = $stmt->fetch();
		return $row['count'];
	}
	
	public function getCountReadedMessages()
	{
		$sql = $this->getQuery('select count(*) from '.self::$SCHEMA.'.MSG INNER JOIN '.self::$SCHEMA.'.MSG_BOX ON"ID"="MSG_ID" WHERE
								"To_User_ID"='.$this->userId.' AND "Status"='.self::$READED);
		$stmt = $this->pdo->query($sql);
		$row = $stmt->fetch();
		return $row['count'];
	}
	
	/**
	 * @method set the status=READED of a message, if userId == toUserId
	 * @param number $msgId
	 */
	public function setMsgReaded($msgId)
	{
		if ($this->userId == $MSG->getToUserId())
		{
			$sql = $this->getQuery('UPDATE '.self::$SCHEMA.'.MSG_BOX SET "Status"=? WHERE "MSG_ID"=?');
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute(array(self::$READED,$msgId)); 
		}
	}
	
	/**
	 * @method set the status=READED of all messages in thread where userId == toUserId
	 * @param number $threadId
	 */
	public function setAllMsgReadedInThread($threadId)
	{
		$sql = $this->getQuery('UPDATE '.self::$SCHEMA.'.MSG_BOX set "Status"=? WHERE "MSG_ID" in (Select "ID" from '.self::$SCHEMA.'.MSG where "Thread_ID"=?) and "To_User_ID"='.$this->userId);
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array(self::$READED,$threadId));
	}
	
	/**
	 * @method set the status=delete_by/from/to user if userId== toUserId or fromUserId
	 * @param number $msgId
	 * @param MSG $MSG if a valid $MSG object ist given, no select is needed to find msg data 
	 */
	public function setMsgDelete($msgId,$MSG=null)
	{
		if($MSG == null)
			$MSG = $this->getMsg($msgId);
		
		if ($this->userId == $MSG->getToUserId())
		{
			$status = self::$DELETED_BY_TO_USER;
			$sql = $this->getQuery('UPDATE '.self::$SCHEMA.'.MSG_BOX SET "Status"= (
															CASE WHEN "Status" = '.self::$DELETED_BY_FROM_USER.' THEN '.self::$DELETED_BY_BOTH.'
																ELSE '.$status.'
															END )
							WHERE "MSG_ID"=? and ("To_User_ID"='.$this->userId.' OR "From_User_ID"='.$this->userId.')');
		}
		else
		{
			$status = self::$DELETED_BY_FROM_USER;
			$sql = $this->getQuery('UPDATE '.self::$SCHEMA.'.MSG_BOX SET "Status"= (
															CASE WHEN "Status" = '.self::$DELETED_BY_TO_USER.' THEN '.self::$DELETED_BY_BOTH.'
																ELSE '.$status.'
															END )
							WHERE "MSG_ID"=? and ("To_User_ID"='.$this->userId.' OR "From_User_ID"='.$this->userId.')');
			
		}
		
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array($msgId));
	}
	
	public function setAllMsgDeleteInThread($threadId)
	{
		$sql = $this->getQuery('UPDATE '.self::$SCHEMA.'.MSG_BOX set "Status"=(
																CASE WHEN ( "To_User_ID" = '.$this->userId.' AND "Status" = '.self::$DELETED_BY_FROM_USER.') THEN '.self::$DELETED_BY_BOTH.'
																	 WHEN ( "From_User_ID" = '.$this->userId.' AND "Status" = '.self::$DELETED_BY_TO_USER.') THEN '.self::$DELETED_BY_BOTH.'
																	 WHEN "To_User_ID" = '.$this->userId.' THEN '.self::$DELETED_BY_TO_USER.'
																	 WHEN "From_User_ID" = '.$this->userId.' THEN '.self::$DELETED_BY_FROM_USER.'
																END )
				 WHERE "MSG_ID" in (Select "ID" from '.self::$SCHEMA.'.MSG where "Thread_ID"=?) AND ("To_User_ID"='.$this->userId.' OR "From_User_ID"='.$this->userId.')');
		
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array($threadId));
	}
}
?>
