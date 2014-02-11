phpPM
=====

phpPM is a lightweight private messaging library

phpPM based on threads. Each new message starts a thread in which the conversation(between two user) runs ;)

Features:
- Uses PDO
- Uses prepare statements -> SQL-Injection safe
- Each user has a MessageBox
- Delete messages(not physically)
  - Delete the thread too if the thread has no messages
- Delete threads(not physically)
- Provide all needed functions for a postbox...

How to use
==========

After you have configure phpPM(see https://github.com/simb0/phpPM/wiki) you can use it as follow.

Get the MessageBox for your user: 

$userId is just the unique id for the user in your application

$pdo here you can provide your database pdo object. You can also provide null to force phpPM to use your configuration

$msgBox = new MSGBox($userId,$pdo); 

Let's send a new message to another user:
$msgBox->sendNewMessage($subject,$text,$date,$toUserId);
Now you have startet a conversation to answer just add(not send) a message to this thread:
$msgBox->addMessage($subject,$text,$date,$threadId);

Let's have a look at the thread:
$id we assume the thread is 100
$thread = $msgBox->getThread($id);
This function returns a 'Thread' object:
$messages = $thread->getMessages();
Now we have an array of 'MSG' that belongs to the thread
$messages[0]->getText(); // this provide the content of the message

They are more functions available that helps:
We can ask the thread if there new messages
$thread->hasNewMessages(); //true or false
$msgBox->getCountNotReadedMessages(); // count how much messages the user doesn't have readed now
$msgBox->getCountReadedMessages(); // count how much messages the user have readed now
....
