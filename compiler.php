<?php
require_once 'LexicalAnalysis.php';
require_once 'Parser.php';

class Compiler {
    public static function main() {

        $counter = 1;

        echo '<pre>';
        echo "Início da análise léxica\n";

        $lexical = new LexicalAnalysis();
        $arquivo = 'codigo.txt';  

        // Análise Léxica
        if ($lexical->parser($arquivo)) {
            echo "\nTabela de Símbolos:\n";
            foreach ($lexical->getSymbolTable() as $key => $value) {
                echo $value . " : " . $key . "\n";
            }

            echo "\nTokens Gerados:\n";
            foreach ($lexical->getTokens() as $token) {
                echo $counter . ": " . $token . "\n";
                $counter++;
            }

            // Análise Sintática
            echo "\nInício da análise sintática (Parsing)\n";

            
            $tokens = $lexical->getTokens(); 
            $parser = new Parser($tokens);


            try {
                $parser->parseProgram(); 
                echo "\nParsing concluído com sucesso.\n";
            } catch (Exception $e) {
                echo "\n" . $e->getMessage() . "\n";
            }

        } else {
            echo "\nErro durante a análise léxica.\n";
        }

        echo "\nFim da análise léxica e sintática\n";
    }
}

Compiler::main();

?>
