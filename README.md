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

