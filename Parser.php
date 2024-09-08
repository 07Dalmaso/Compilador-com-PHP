<?php

/**
 * Autores: Lucas Santos Dalmaso e André Santoro
 * Email's: lucassdalmaso25@gmail.com e asbnbm00@gmail.com
 * 
 * Classe responsável pela análise da Gramática Livre do Contexto:
 * 
 * A -> id | num
 * B -> A + A | A - A | A * A | A / A | A % A | A
 * C -> A = A | A != A | A < A | A > A | A <= A | A >= A | A
 * IO -> "input" id | "print" B
 * Assign -> "let" id "=" B
 * GotoStmt -> "if" C "goto" num | "goto" num
 * Rem -> "rem" ... LF
 * Stmt -> IO | Assign | GotoStmt | "end"
 * Tag -> num Stmt
 * Program -> Tag

 */

class Parser
{
    private $tokens = [];
    private $currentIndex = 0;
    private $errorMessages = [];  // Array para armazenar mensagens de erro
    public $isPrint;
    public bool $end = false;

    public function __construct($tokens, $isPrint)
    {
        $this->tokens = $tokens;
        $this->currentIndex = 0;
        $this->isPrint = $isPrint;
        echo $isPrint ? "Parser inicializado com " . count($this->tokens) . " tokens.\n" : '';
    }

    // Obtém o token atual e imprime uma mensagem com o token corrente
    public function getCurrentToken()
    {
        if ($this->currentIndex < count($this->tokens)) {
            $token = $this->tokens[$this->currentIndex];
            echo $this->isPrint ?  "Token atual: " . $token . "\n" : '';
            return $token;
        } else {
            echo $this->isPrint ? "Fim da entrada atingido.\n" : '';
            return null;
        }
    }

    // Substitui o token atual por um token de erro
    private function replaceWithErrorToken()
    {
        $currentToken = $this->getCurrentToken();
        echo "Substituindo token por token de erro na linha: " . $currentToken->getLine() . "\n";
        $this->tokens[$this->currentIndex] = new Token(
            new Symbol(Symbol::ERROR),
            $currentToken->getLine(),
            $currentToken->getColumn(),
            -1
        );
    }

    // Avança para o próximo token e imprime uma mensagem de avanço
    public function advanceToken()
    {
        if ($this->currentIndex < count($this->tokens)) {
            echo $this->isPrint ? "Avançando token do índice " . $this->currentIndex . "\n" : '';
            $this->currentIndex++;
        }
    }

    // Ignora tokens até alcançar uma nova linha (LF) ou o fim da entrada
    private function skipToNextLine()
    {
        echo $this->isPrint ? "Pulando para a próxima linha...\n" : '';
        while ($this->currentIndex < count($this->tokens) && $this->tokens[$this->currentIndex]->getType()->getUid() != Symbol::LF) {
            $this->advanceToken();
        }

        if ($this->currentIndex < count($this->tokens) && $this->tokens[$this->currentIndex]->getType()->getUid() == Symbol::LF) {
            $this->advanceToken();
        }
    }

    // Registra um erro e substitui o token inválido
    private function throwError($message)
    {
        $currentToken = $this->getCurrentToken();
        $errorLocation = " na linha " . $currentToken->getLine() . ", coluna " . $currentToken->getColumn();
        $fullMessage = "Erro: $message" . $errorLocation;

        echo $fullMessage . "\n";
        $this->errorMessages[] = $fullMessage;

        // $this->replaceWithErrorToken();
        // $this->skipToNextLine();
    }

    // Verifica se a entrada terminou
    public function isEndOfInput()
    {
        return $this->currentIndex >= count($this->tokens);
    }

    // Pula tokens de nova linha
    private function skipLineFeeds()
    {
        echo $this->isPrint ? "Pulando tokens de nova linha...\n" : '';
        while (!$this->isEndOfInput() && $this->getCurrentToken()->getType()->getUid() == Symbol::LF) {
            $this->advanceToken();
        }
    }

    // Analisando o Programa (Program)
    public function parseProgram()
    {
        echo "Iniciando a análise do programa...\n";
        while (!$this->isEndOfInput()) {
            $this->skipLineFeeds();

            if (!$this->isEndOfInput()) {
                
                if ($this->getCurrentToken()->getType()->getUid() == Symbol::ETX) {
                    break;
                }

                $this->parseTag();
            }
        }

        // $this->printErrors();
        echo "Análise do programa concluída.\n";
    }

    // Imprime as mensagens de erro acumuladas
    private function printErrors()
    {
        if (!empty($this->errorMessages)) {
            echo "Análise concluída com os seguintes erros:\n";
            foreach ($this->errorMessages as $errorMessage) {
                echo $errorMessage . "\n";
            }
        } else {
            echo "Análise concluída sem erros.\n";
        }
    }

    // Analisando Tag (num Stmt)
    private function parseTag()
    {
        echo $this->isPrint ? "Analisando Tag (número da linha seguido de declaração)...\n" : '';
        $this->skipLineFeeds();

        if ($this->getCurrentToken()->getType()->getUid() == Symbol::INTEGER) {
            echo $this->isPrint ?  "Número de linha encontrado.\n" : '';
            $this->advanceToken();
            $this->parseStmt();
        } else {
            // $this->throwError("Número de linha esperado.");
            $this->advanceToken();
        }
    }

    // Analisando Declaração (Stmt -> IO | Assign | GotoStmt | rem | end)
    private function parseStmt()
    {
        echo $this->isPrint ? "Analisando declaração...\n" : '';

        switch ($this->getCurrentToken()->getType()->getUid()) {
            case Symbol::INPUT:
            case Symbol::PRINT:
                echo $this->isPrint ? "Declaração de entrada/saída encontrada.\n" : '';
                $this->parseIO();
                break;

            case Symbol::LET:
                echo $this->isPrint ? "Declaração de atribuição encontrada.\n" : '';
                $this->parseAssign();
                break;

            case Symbol::IF:
                echo $this->isPrint ? "Declaração condicional 'if' encontrada.\n" : '';
                $this->parseGotoStmt();
                break;

            case Symbol::REM:
                echo $this->isPrint ? "Declaração 'rem' encontrada.\n" : '';
                $this->parseRem();
                break;

            case Symbol::GOTO:
                echo $this->isPrint ? "Declaração 'goto' encontrada.\n" : '';
                $this->parseGoto();
                break;

            case Symbol::END:
                echo $this->isPrint ? "Declaração 'end' encontrada. Finalizando...\n" : '';
                $this->end = true;
                $this->advanceToken();
                return;

            default:
                $this->throwError("Declaração inesperada.");
        }
    }

    // Analisando IO (IO -> "input" id | "print" A)
    private function parseIO()
    {

        if ($this->getCurrentToken()->getType()->getUid() == Symbol::INPUT) {
            echo $this->isPrint ? "Analisando declaração 'input'.\n" : '';
            $this->advanceToken();
            if ($this->getCurrentToken()->getType()->getUid() == Symbol::VARIABLE) {
                echo $this->isPrint ? "Identificador encontrado após 'input'.\n" : '';
                $this->advanceToken();
            } else {
                $this->throwError("Esperado um identificador após 'input'.");
            }
        } elseif ($this->getCurrentToken()->getType()->getUid() == Symbol::PRINT) {
            echo $this->isPrint ? "Analisando declaração 'print'.\n" : '';
            $this->advanceToken();
            $this->parseA();
        } else {
            $this->throwError("Erro de sintaxe na declaração de IO.");
        }
    }

    // Analisando Atribuição (Assign -> "let" id "=" A)
    private function parseAssign()
    {
        echo $this->isPrint ? "Analisando declaração 'let'.\n" : '';
        $this->advanceToken();

        if ($this->getCurrentToken()->getType()->getUid() == Symbol::VARIABLE) {
            echo $this->isPrint ? "Identificador encontrado na atribuição.\n" : '';
            $this->advanceToken();

            if ($this->getCurrentToken()->getType()->getUid() == Symbol::ASSIGNMENT) {
                echo $this->isPrint ? "Operador '=' encontrado.\n" : '';
                $this->advanceToken();
                $this->parseB(); // Analisar expressão
            } else {
                $this->throwError("Esperado '=' após a variável.");
            }
        } else {
            $this->throwError("Esperado identificador após 'let'.");
        }
    }

    // Analisando Goto Condicional (GotoStmt -> "if" B "goto" num)
    private function parseGotoStmt()
    {

        if ($this->getCurrentToken()->getType()->getUid() == Symbol::IF) {
            echo $this->isPrint ? "Analisando declaração 'if'.\n" : '';
            $this->advanceToken();
            $this->parseC();

            if ($this->getCurrentToken()->getType()->getUid() == Symbol::GOTO) {
                echo $this->isPrint ? "'goto' encontrado após condição 'if'.\n" : '';
                $this->advanceToken();

                if ($this->getCurrentToken()->getType()->getUid() == Symbol::INTEGER) {
                    echo $this->isPrint ? "Número de linha encontrado para 'goto'.\n" : '';
                    $this->advanceToken();
                } else {
                    $this->throwError("Esperado número de linha após 'goto'.");
                }
            } else {
                $this->throwError("Esperado 'goto' após a condição.");
            }
        } elseif ($this->getCurrentToken()->getType()->getUid() == Symbol::GOTO) {
            echo $this->isPrint ? "Analisando declaração 'goto'.\n" : '';
            $this->advanceToken();

            if ($this->getCurrentToken()->getType()->getUid() == Symbol::INTEGER) {
                echo $this->isPrint ? "Número de linha encontrado para 'goto'.\n" : '';
                $this->advanceToken();
            } else {
                $this->throwError("Esperado número de linha após 'goto'.");
            }
        } else {
            $this->throwError("Esperado 'if' ou 'goto'.");
        }
    }

    // Analisando Rem (Rem -> "rem" ... LF)
    private function parseRem()
    {
        $this->isPrint ? "Analisando declaração 'rem'. Pulando até o final da linha...\n" : '';
        while (!$this->isEndOfInput() && $this->getCurrentToken()->getType()->getUid() != Symbol::LF) {
            $this->advanceToken();
        }
        $this->skipLineFeeds();
    }

    // A -> id | num
    private function parseA()
    {

        if($this->getCurrentToken()->getType()->getUid() == Symbol::SUBTRACT){
            $this->advanceToken();
            if($this->getCurrentToken()->getType()->getUid() == Symbol::INTEGER){
                $this->isPrint ? "Número negativo encotrado\n" : '';
            }
        }

        if ($this->getCurrentToken()->getType()->getUid() == Symbol::VARIABLE || $this->getCurrentToken()->getType()->getUid() == Symbol::INTEGER) {
            $this->isPrint ? "Identificador ou número encontrado.\n" : '';
            $this->advanceToken();
        } else {
            $this->throwError("Esperado identificador ou número.");
        }
    }

    // Analisando Expressões Aritméticas (B)
    private function parseB()
    {
        echo $this->isPrint ? "Analisando expressão aritmética...\n" : '';
        $this->parseA();

        if (
            in_array($this->getCurrentToken()->getType()->getUid(), [Symbol::ADD, Symbol::SUBTRACT, Symbol::MODULO, Symbol::DIVIDE, Symbol::MULTIPLY])) {
            echo  $this->isPrint ? "Operador aritmético encontrado.\n" : '';
            $this->advanceToken();
            $this->parseA();
            $this->expression();
        }
    }

    // Analisando Expressões Booleanas (C)
    private function parseC()
    {
        echo $this->isPrint ? "Analisando expressão booleana...\n" : '';
        $this->parseA();

        if (in_array($this->getCurrentToken()->getType()->getUid(), [Symbol::EQ, Symbol::NE, Symbol::LT, Symbol::GT, Symbol::LE, Symbol::GE])) {
            echo $this->isPrint ? "Operador de comparação encontrado.\n" : '';
            $this->advanceToken();
            $this->parseA();
        } else {
            $this->throwError("Esperado operador de comparação, Operadores condicionais válidos são: >, >=, <, <=, ==, !=.");
        }
    }

    private function parseGoto()
    {
        $this->advanceToken();

        if ($this->getCurrentToken()->getType()->getUid() == Symbol::INTEGER) {
            $this->advanceToken();
        }else{
            $this->throwError("Esperado Numero de linha após o comando 'GOTO'.");
        }
    }

    private function expression(): void
    {
        while ($this->getCurrentToken()->getType()->getUid() != Symbol::LF) {
            
            if (in_array($this->getCurrentToken()->getType()->getUid(), [Symbol::ADD, Symbol::SUBTRACT, Symbol::MODULO, Symbol::DIVIDE, Symbol::MULTIPLY])) {
                $this->throwError("Só é possível fazer uma operação com os operadores por linha.");
                $this->advanceToken();
            } else {
                $this->advanceToken();
            }
        }
    }
}
