# Assets

Die Assets werden alle mit gulp (siehe gulpfile.js) gemanaged. Damit das Setup nicht für jedes Projekt, welches das CMS benutzt neu geschrieben werden muss, kann der CMS.Builder genutzt werden.
Wie man den CMS.Builder benutzt kann man hier in diesem Repository sehen, wenn man sich die `guplfile.js` anschaut.  

Dieser Mechanismus wird im `GulpBuilderTest.php` getestet. Denn eigentlich fungiert dieses Repository auch als eine Installation des CMS und ist nicht das CMS selbst.


## Struktur

Die requirejs-config im dev mode hat 3 verschiedene Pfade: `app`, `cms` und `admin`. Damit js files direkt geladen werden können, ohne, dass der build neu ausgeführt werden muss, gibt es 2 apache aliase: `cms-root` zeigt auf das Root-Verzeichnis der webforge-cms sourcen. `root` zeigt auf das Root-Verzeichnis des Projektes. `admin` und `app` liegen im Projekt-Root während `cms` im Cms-Root liegt.

