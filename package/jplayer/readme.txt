

jPlayer - Use setMedia() to set the media URL
(http://stackoverflow.com/questions/7401282/jplayer-use-setmedia-to-set-the-media-url)
	

The main point of jPlayer is so that you can use html5 with flash fallback, 
so you should leverage the html5 of FF and chrome, not depending on it falling back to flash.
Firefox doesn't support mp3 on html5, they support ogg, which is better anyway. 
I always format each audio file I need to play for each browser, 
that way you can leverage html5 when its available, and you give your app a much better chance at loading the audio file, 
with 3 for each browser to choose from (not all get loaded, just the one it needs).

Use: .ogg for Chrome and Firefox, .m4a for Safari, and .mp3 for IE. 
See here (http://jplayer.org/latest/developer-guide/#reference-html5-audio-format)

