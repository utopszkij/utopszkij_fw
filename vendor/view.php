<?php
/**
 * vue alapú viewer
 * 
 * használata:
 * 
 * view('viewName',[ "p1" => $value, ....], appName);
 * vagy
 * view('viewName',[ "p1" => $value, ....]);
 * 
 * /includes/views/viewname.html:
 *  html kód vue attributumokkal pl:
 *  <li v-for="(item, index) in items">{{ index }} {{ item}}</li>
 *  <input type="text" name="..." v-if="p2 == 1" v-model="adat" :disabled="disabled" />
 *  <var v-html="htmlstr" v-on:click="method1()" v-bind:class="classname"></var>
 *  ....
 *  include htmlname
 *  ....
 *  <script>  
 *    const methods = {
 *       method1(param) {
 *           ...  this.  használható
 *       },
 *       ....
 *       afterMount() {
 *           ...  this.  használható
 *       }
 *    };
 *  </script>
 * 
 * @param string $name
 * @param array $params
 * @return void
 */

global $tokens;
$tokens = false;

function view(string $name,array $params, string $appName = 'app') {
    $scriptExist = false;
    
    if (file_exists(__DIR__.'/../styles/'.STYLE.'/'.$name.'_'.LNG.'.html')) {
        $lines = file(__DIR__.'/../styles/'.STYLE.'/'.$name.'_'.LNG.'.html');
    } else if (file_exists(__DIR__.'/../styles/'.STYLE.'/'.$name.'.html')) {
        $lines = file(__DIR__.'/../styles/'.STYLE.'/'.$name.'.html');
    } else  if (file_exists(__DIR__.'/../includes/views/'.$name.'_'.LNG.'.html')) {
        $lines = file(__DIR__.'/../includes/views/'.$name.'_'.LNG.'.html');
    } else if (file_exists(__DIR__.'/../includes/views/'.$name.'.html')) {
        $lines = file(__DIR__.'/../includes/views/'.$name.'.html');
    } else if (file_exists('includes/views/'.$name.'.html')) {
        $lines = file('includes/views/'.$name.'.html');
    } else {
        echo 'Fatal error '.$name.' view not found. '.__DIR__; exit();
    }    
    echo '<div id="'.$appName.'" style="display:none">'."\n";  
    foreach ($lines as $line) {
        if (trim($line) == '<script>') {
            $scriptExist = true;
            echoScript($appName);
        } else if (trim($line) == '</script>') {
            echoEndScript($params,$appName);
        } else if (substr(trim($line),0,7) == 'include') {
            $lines2 = file(__DIR__.'/../includes/views/'.trim(substr(trim($line),7,100)).'.html');
            echo implode("\n",$lines2);
        } else {
            echo $line."\n";
        } // if
    } // foreach
    if (!$scriptExist) {
        echoScript($appName);
        echoEndScript($params,$appName);
    }
} // function

function echoScript(string $appName) {
    echo '
    </div><!-- '.$appName.' -->		
    <script>'."\n";
}

function echoEndScript(array $params, string $appName) {
    echo '
    if (methods == undefined) { var methods = {}; }
    const '.$appName.' = createApp({
            data() {
            return {'."\n";
            foreach ($params as $fn => $param) {
                echo $fn.': '.JSON_encode($param).",\n";
            }			
            echo '				
            innerWidth : window.innerWidth,
            HREF: window.HREF,
            location: encodeURI(window.location),
            lng: window.lng,
            siteurl: window.siteurl,
            rewrite : '.REWRITE.',
            sid:"'.session_id().'"
            };
        },
        mounted() {
            if (this.afterMount != undefined) {
                this.afterMount();
            }    
            document.getElementById("'.$appName.'").style.display="block";
        },
        methods: methods
    }).mount("#'.$appName.'");
    </script>'."\n";
}


function lng(string $token) {
    global $tokens;
    $result = $token;
    if (!$tokens) {
        $s = file_get_contents(__DIR__.'/../languages/'.LNG.'.js');
        $s = str_replace('tokens','',$s);
        $s = str_replace('=','',$s);
        $s = str_replace('};','}',$s);
        $s = preg_replace('|/\*.+\*/|', '', $s);
        $tokens = JSON_decode(trim($s));
    }
    if (isset($tokens->$token)) {
        $result = $tokens->$token;
    }
    return $result;
}
?>
