<?php
require("vendor/autoload.php");
use CK\MARCspec\MARCspec;

function testPhpMarcSpec($string)
{
    try
    {
        $marcspec = new MARCspec($string);
        return $marcspec;
    }
    catch(\Exception $e)
    {
        return $e;
    }
}

function validateMARCspec($json)
{
    // Get the schema and data as objects
    $retriever = new JsonSchema\Uri\UriRetriever;
    $schema = $retriever->retrieve('file://' . realpath('schema.json'));
    $o = json_decode($json);

    // Validate
    $validator = new JsonSchema\Validator();
    $validator->check($o, $schema);
    
    if ($validator->isValid()) {
        return "The supplied JSON validates against the <a href='https://cdn.rawgit.com/MARCspec/marcspec-object-schema/88d246a68529df32db5307b8bebb6a8260d8f98b/schema.json'>schema</a>.\n";
    } else {
        echo "JSON does not validate. Violations:\n";
        foreach ($validator->getErrors() as $error) {
            return sprintf("[%s] %s\n", $error['property'], $error['message']);
        }
    }
}
if(isset($_GET['spec']))
{
    $return = testPhpMarcSpec($_GET['spec']);

    if(isset($_GET['format']))
    {
        if("json" == $_GET['format'])
        {
            header('Content-Type: application/json');
            
            if($return instanceOf CK\MARCspec\MARCspecInterface)
            {
                echo json_encode($return);
            }
            else
            {
                echo $return->getMessage();
            }
        }
        exit();
    }
}


?>
<!DOCTYPE html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>MARCspec Test</title>
        <meta name="description" content="Test the validity of your MARCspec">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body {font-size:1.6em}
            pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }
            .string, .valid { color: green; }
            .number { color: darkorange; }
            .boolean { color: blue; }
            .null { color: magenta; }
            .key, .invalid { color: red; }
        </style>
    </head>
    <body>
        <h1><img src="marcspec-logo.png">MARCspec Validator</h1>
        <p>See <a href="http://marcspec.github.io/MARCspec/">http://marcspec.github.io/MARCspec/</a> for current specification.</p>
        <form method="GET" action="">
            <input type="text" name="spec" size="60" style="font-size:1.3em;" id="input" value="<?php if(isset($_GET['spec'])) echo $_GET['spec']; ?>"/>
            <input type="submit" value="validate!" style="font-size:1.3em;" id="submit" />
        </form>

            <script>
                function output(inp) {
                    document.body.appendChild(document.createElement('pre')).innerHTML = inp;
                }
                
                function syntaxHighlight(json) {
                    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                        var cls = 'number';
                        if (/^"/.test(match)) {
                            if (/:$/.test(match)) {
                                cls = 'key';
                            } else {
                                cls = 'string';
                            }
                        } else if (/true|false/.test(match)) {
                            cls = 'boolean';
                        } else if (/null/.test(match)) {
                            cls = 'null';
                        }
                        return '<span class="' + cls + '">' + match + '</span>';
                    });
                }
            </script>
                <?php 
                    if(isset($return))
                    {
                        
                        
                        $json = json_encode($return);
                        
                        $validationText = validateMARCspec($json);
                        
                        if($return instanceOf CK\MARCspec\MARCspecInterface)
                        {
                            echo '<h2 class="valid">Valid MARCspec</h2>'.$validationText;
                            echo '<a href="?spec='.$_GET['spec'].'&format=json">get raw json</a>';
                            echo "<script>";
                            echo "var obj = ".$json.";";
                            
                            echo "\nvar str = JSON.stringify(obj, undefined, 4);\noutput(syntaxHighlight(str));\n</script>";
                        }
                        else
                        {
                            echo  '<h2 class="invalid">Invalid MARCSpec</h2><div id="exception">'.$return->getMessage().'</div>';
                        }
                    }
                ?>
        
        <div style="font-size:0.5em;position:relative; bottom:5px;">
        <p>
        <form method="GET" action="">
        Examples:
            <select name="spec" size="1" onchange="this.form.submit()">
            <option></option>
            <option>245</option>
            <option>LDR</option>
            <option>LDR/0-3</option>
            <option>LDR/0-#</option>
            <option>LDR/#-4</option>
            <option>LDR/#-0</option>
            <option>245[1]</option>
            <option>245[1-3]</option>
            <option>245[1-#]</option>
            <option>245[#-3]</option>
            <option>245/#</option>
            <option>245/#-#</option>
            <option>245/#-0</option>
            <option>245/#-1</option>
            <option>245/0-#</option>
            <option>245_0</option>
            <option>245__0</option>
            <option>245_0_</option>
            <option>245[1]_01</option>
            <option>245$a-c</option>
            <option>...[#]/1-3</option>
            <option>245$d{$c/#=\.}{?$a}</option>
            <option>245[0]{$a!=$b|300_01$a!~\abc}{\!\=!=\!}$a{$c|!$d}</option>
            <option>245$a-c{$b|$c}{$e}</option>
            </select>
        </p>
        <hr>
        Author: Carsten Klee<br>
        <a href="https://github.com/MARCspec/php-marc-spec/archive/master.zip">Download</a> current version of MARCspec parser and validator (written in PHP)<br>
        Feedback is welcome at <a href="https://github.com/MARCspec/php-marc-spec/issues">https://github.com/MARCspec/php-marc-spec/issues</a>
        </div>
    </body>
</html>
