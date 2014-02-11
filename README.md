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

Get the MessageBox for your user: <br>
$userId is just the unique id for the user in your application <br>
$pdo here you can provide your database pdo object. You can also provide null to force phpPM to use your configuration<br>
$msgBox = new MSGBox($userId,$pdo); <br>
<br>
Let's send a new message to another user:<br>
$msgBox->sendNewMessage($subject,$text,$date,$toUserId);<br>
Now you have startet a conversation to answer just add(not send) a message to this thread:<br>
$msgBox->addMessage($subject,$text,$date,$threadId);<br>
<br>
Let's have a look at the thread:<br>
$id we assume the thread is 100<br>
$thread = $msgBox->getThread($id);<br>
This function returns a 'Thread' object:<br>
$messages = $thread->getMessages();<br>
Now we have an array of 'MSG' that belongs to the thread<br>
$messages[0]->getText(); // this provide the content of the message<br>
<br>
They are more functions available that helps:<br>
We can ask the thread if there new messages<br>
$thread->hasNewMessages(); //true or false<br>
$msgBox->getCountNotReadedMessages(); // count how much messages the user doesn't have readed now<br>
$msgBox->getCountReadedMessages(); // count how much messages the user have readed now<br>
....
