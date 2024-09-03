<?php
require_once 'LexicalAnalysis.php';

class Compiler {
    public static function main() {
        echo'<pre>';
        echo "Início da análise léxica\n";

        $lexical = new LexicalAnalysis();
        $arquivo = 'codigo.txt';  


        if ($lexical->parser($arquivo)) {
            foreach ($lexical->getSymbolTable() as $key => $value) {
                echo $value . " : " . $key . "\n";
            }

            foreach ($lexical->getTokens() as $token) {
                echo $token . "\n";
            }
        }

        echo "Fim da análise léxica\n";
    }
}


Compiler::main();

?>
