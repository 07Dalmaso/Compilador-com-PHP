<?php
/**
 * Autores: Lucas Santos Dalmaso e André Santoro
 * Email's: lucassdalmaso25@gmail.com e asbnbm00@gmail.com
 */
require_once 'LexicalAnalysis.php';
require_once 'Parser.php';
require_once 'SemanticAnalysis.php';

class Compiler {
    public static function main() {

        $counter = 1;
        # Caso deseja ver o passo a passo é só colocar true
        $isPrint = false;

        echo '<pre>';
        echo "=== Início da análise léxica ===\n";

        $lexical = new LexicalAnalysis();

        # Arquivo com o código-fonte a ser analisado
        $arquivo = 'codigo.txt';

        # Análise Léxica
        if ($lexical->parser($arquivo)) {
            if($isPrint){

                echo "\nTabela de Símbolos:\n";
                foreach ($lexical->getSymbolTable() as $key => $value) {
                    echo $value . " : " . $key . "\n";
                }
                
                echo "\nTokens Gerados:\n";
                foreach ($lexical->getTokens() as $token) {
                    echo $counter . ": " . $token . "\n";
                    $counter++;
                }
            } 
            echo "Análise léxica concluída com sucesso.\n";
        } else {
                echo "Erro durante a análise léxica.\n";
        }
            
        echo "=== Fim da análise semântica ===\n";

            # Análise Sintática
            echo "\n=== Início da análise sintática (Parsing) ===\n";

            $tokens = $lexical->getTokens(); 
            $parser = new Parser($tokens, $isPrint);

            try {
                $parser->parseProgram(); 
                echo "Parsing concluído com sucesso.\n";
            } catch (Exception $e) {
                echo $e->getMessage() . "\n";
            }
            echo "=== Fim da análise sintática (Parsing) ===\n";

            # Análise Semântica
            echo "\n=== Início da análise semântica ===\n";
            $semanticAnalysis = new SemanticAnalysis($lexical, $isPrint);
            if($semanticAnalysis->analyze()){
                echo "Análise semântica concluída com sucesso.\n";
            } else {
                echo "Erro durante a análise semântica.\n";
            }
            echo "=== Fim da análise semântica ===\n";


        echo '</pre>';
    }
}

Compiler::main();
?>
