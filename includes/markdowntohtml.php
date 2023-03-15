<?php

/**
 * Egyszerüsített markdown --> html konvertel
 * 
 * Értelmezett markdown elemek:
 * # text			<h1>text</h1>
 * ## text			<h2>text</h2>
 * ### text			<h3>text</h3>
 * #### text        <h4>text</h4
 * ***...* 			<hr />
 * **text**			<strong>text</strong>
 * __text__			<strong>text</strong>
 * *text*			<em>text</em>
 * _text_			<em>text</em>
 * >text			<blocquote>text...</blockquote>
 * - text
 * - text ...    	<ol><l>text</li>,...</ol>
 * ```
 * multilie_tex
 * ```				<pre><code>multilie_text</code></pre>
 * ![txt](url) 		<img alt="text" src="url" />
 * [txt](url) 		<a title="text" href="url">url</a>	
 * empty_line 		<br />
 */ 

/**
 * markdownToHtml
 * @param string $text
 * @return string
 */
function markdownToHtml(string $text): string {

	$text = preg_replace('/\*\*\*\*+/i', '<hr />', $text);


	$text = preg_replace('/(\*\*.+?)+(\*\*)/i', '<strong>$1</strong>', $text);
	$text = str_replace('**','',$text);

	$text = preg_replace('/(__.+?)+(__)/i', '<strong>$1</strong>', $text);
	$text = str_replace('__','',$text);

	$text = preg_replace('/(\*.+?)+(\*)/i', '<em>$1</em>', $text);
	$text = str_replace('*','',$text);

	$text = preg_replace('/(_.+?)+(_)/i', '<em>$1</em>', $text);
	$text = str_replace('_','',$text);

	$text = preg_replace('/(####\s.+?)+(\n)/i', '<h4>$1</h4>'."\n", $text);
	$text = str_replace('#### ','',$text);

	$text = preg_replace('/(###\s.+?)+(\n)/i', '<h3>$1</h3>'."\n", $text);
	$text = str_replace('### ','',$text);

	$text = preg_replace('/(##\s.+?)+(\n)/i', '<h2>$1</h2>'."\n", $text);
	$text = str_replace('## ','',$text);

	$text = preg_replace('/(#\s.+?)+(\n)/i', '<h1>$1</h1>'."\n", $text);
	$text = str_replace('# ','',$text);

	$text = preg_replace('/(-.+?)+(\n)/i', '<ul><li>$1</li></ul>', $text);
	$text = str_replace('- ','',$text);
	$text = str_replace('</ul><ul>',"\n",$text);

	$text = str_replace("\n>","\n<blockquote>",$text);
	$text = preg_replace('/(<blockquote>.+?)+(\n)/i', '$1</blockquote>'."\n", $text);
	$text = str_replace("</blockquote>\n<blockquote>","\n",$text);

	// image és link kezelés előkészítése
	$text = preg_replace('/(\!\[.+?)+(\))/i', '<img alt="$1</img>', $text);
	$text=str_replace('alt="![','alt="',$text);
	$text = preg_replace('/(\[.+?)+(\))/i', '<a title="$1</a>', $text);
	$text=str_replace('title="[','title="',$text);
	// most ezek lehetnek:
	//  <img alt="text](url"</img>
	//  <a title="text](url"</a>

	// image
	$text = preg_replace('/(\]\(.+?)+(\<\/img\>)/i', '" src="$1 />', $text); // <img alt="text" src="(url" />
	$text = str_replace('src="](','src="',$text); // <img alt="text" src="url" />

	// link
	$text = preg_replace('/(\]\(.+?)+(\<\/a\>)/i', '" href="$1>$1</a>', $text); // <a tile="text" href="(url">url"</a>
	$text = str_replace('href="](','href="',$text);
	$text = str_replace('](','',$text);
    $text = str_replace('"</a>','</a>',$text);

	// code
	while (strpos($text,'```') > 0) {
		$text = preg_replace('/```/','<pre><code>',$text,1);
		$text = preg_replace('/```/','</code></pre>',$text,1);
	}

	$text = str_replace("\n\n",'<br />'."\n",$text);
	return $text;
}

/** htm from .md file
 * @param string $fileName
 * @return string
 */
function markdownFileToHtml(string $fileName): string {
	$text = file_get_contents($fileName);
	$html = markdownToHtml($text);
	return $html;
}

// Test

$text = '
# főcím
## cim2
### cim3
#### cím4
ez egy szövegsor **bold** résszel _italic résszel_ megint normál szöveg.

ez a második szövegsor **bold** résszel *itali* résszel_ megint normál szöveg.

ez a harmadik szövegsor __bold__

> behuzott szöveg
> behuzott szöveg 2.sor
> behuzott szöveg 3.sor

> behuzott szöveg 4.sor

## *****************


- lista1
- lista2
- lista3

```
$i = $a + 1;
if ($a > $b) {
	echo $c;
}
```
![image1](http://image1.png")
[link1](http://link1.html")
![image2](http://image2.png")
[link2](http://link2.html")

';
echo markdownToHtml($text);


?>
