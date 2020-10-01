You should check out emojione at https://github.com/Ranks/emojione
==================================================================

web-emoji-An amazing Project
=========

This project brings emoji support for websites and mobile web applications. 

The general idea is to let server-side code deal with all things unicode. The clients pass over the emojis,
while the server replaces all 🍺 with :beer:. This makes it easier to store text in databases (no need to deal 
with utf8-mb4), and UTF-16 in JS for example.  

The clients are responsible for replacing :beer: with something that looks like a beer (usually an image).


PS: If you are seeing some square boxes (test: 🍺) in Chrome on OSX, try Safari. 
PHP
====
The PHP class supports conversion from emoji to :beer:, and from :beer: HTML tag with appropriate image.

```php
$e = new Emojificator('data/');
$e->emoji2text("Are you up for a 🍺?"); // Are you up for a :beer:?
$e->text2html("Are you up for a :beer:?"); // Are you up for a HTML-TAG
```

JavaScript
==========
Converts :beer: to HTML tags with appropriate image

```javascript
Emoji.text2Html("Are you up for a :beer:?"); // Are you up for a HTML-TAG
```


Worth reading
=============
http://crocodillon.com/blog/parsing-emoji-unicode-in-javascript
http://stackoverflow.com/questions/7814293/how-to-insert-utf-8-mb4-characteremoji-in-ios5-in-mysql
