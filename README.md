sqlite-search-php
=================

PHP search engine using database SQLite

php sqlite rank is taken from below

https://gist.github.com/bohwaz/1355232

Also the search.php is used from the internet

To create a database use this command

CREATE VIRTUAL TABLE documents USING fts4(id INTEGER PRIMARY KEY AUTOINCREMENT, title VARCHAR, category VARCHAR, pageno VARCHAR, desc TEXT)

Important thing to note is the use of 'fts4'

