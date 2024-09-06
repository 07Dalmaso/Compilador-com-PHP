<?php

/**
 * Autores: Lucas Santos Dalmaso e André Santoro
 * Email's: lucassdalmaso25@gmail.com e andre
 * 
 * Classe responsável pela análise da Gramática Livre do Contexto:
 * 
 * A -> A + A | A - A | A * A | A / A | A % A | id | num
 * B -> A = A | A != A | A < A | A > A | A <= A | A >= A
 * IO -> "input" id | "print" A
 * Assign -> "let" id "=" A
 * GotoStmt -> "if" B "goto" num
 * Rem -> "rem" ... LF
 * Stmt -> IO | Assign | GotoStmt | "end"
 * Tag -> num Stmt
 * Program -> Tag
 */

class Parser
{
    private $tokens = [];
    private $currentIndex = 0;

    public function __construct($tokens)
    {
        $this->tokens = $tokens;
        $this->currentIndex = 0;
        echo "Parser initialized with " . count($this->tokens) . " tokens.\n";
    }

    // Obtém o token atual
    public function getCurrentToken()
    {
        if ($this->currentIndex < count($this->tokens)) {
            $token = $this->tokens[$this->currentIndex];
            echo "Current token: " . $token . "\n";
            return $token;
        } else {
            echo "End of input reached.\n";
            return null;
        }
    }

    // Escreve a mensagem de erro
    private function throwError($message)
    {
        $currentToken = $this->getCurrentToken();
        if ($currentToken !== null) {
            $line = $currentToken->getLine();
            $column = $currentToken->getColumn();
            throw new Exception("Erro de sintaxe: $message (linha: $line, índice: $column)");
        } else {
            throw new Exception("Erro de sintaxe: $message (token não encontrado)");
        }
    }

    // Avança para o próximo token
    public function advanceToken()
    {
        echo "Advancing token from index " . $this->currentIndex . "\n";
        if ($this->currentIndex < count($this->tokens)) {
            $this->currentIndex++;
        }
    }

    public function peekToken()
    {
        if ($this->currentIndex + 1 < count($this->tokens)) {
            $token = $this->tokens[$this->currentIndex + 1];
            echo "Peeking at token: " . $token . "\n";
            return $token;
        } else {
            echo "No token to peek at.\n";
            return null;
        }
    }

    private function skipLineFeeds()
    {
        while (!$this->isEndOfInput() && $this->getCurrentToken()->getType()->getUid() === Symbol::LF) {
            echo "Skipping line feed token.\n";
            $this->advanceToken();
        }
    }

    // Verifica se a entrada terminou
    public function isEndOfInput()
    {
        $end = $this->currentIndex >= count($this->tokens);
        if ($end) {
            echo "End of input reached.\n";
        }
        return $end;
    }

    // Analisando o Programa (Program)
    public function parseProgram()
    {
        echo "Starting to parse program...\n";
        while (!$this->isEndOfInput()) {
            $this->skipLineFeeds();
    
            if (!$this->isEndOfInput()) {
                $currentToken = $this->getCurrentToken();
                if ($currentToken->getType()->getUid() === Symbol::END) {
                    echo "End encontrado, finalizando o parser.\n";
                    break; 
                }
                $this->parseTag();
            }
        }
        echo "Finished parsing program.\n";
    }

    // Analisando Tag (num Stmt)
    private function parseTag()
    {
        $this->skipLineFeeds();
        $currentToken = $this->getCurrentToken();

        if ($currentToken->getType()->getUid() === Symbol::INTEGER) {
            echo "Parsing Tag: Line number found.\n";
            $this->advanceToken();
            $this->parseStmt();
        } else {
            $this->throwError("Esperado número de linha. Token encontrado: " . $currentToken);
        }
    }

    // Analisando Declaração (Stmt -> IO | Assign | GotoStmt | rem | end)
    private function parseStmt()
    {
        $currentToken = $this->getCurrentToken();
        echo "Parsing statement...\n";

        switch ($currentToken->getType()->getUid()) {
            case Symbol::INPUT:
            case Symbol::PRINT:
                echo "Found IO statement.\n";
                $this->parseIO();
                break;

            case Symbol::LET:
                echo "Found assignment statement.\n";
                $this->parseAssign();
                break;

            case Symbol::IF:
                echo "Found conditional goto statement.\n";
                $this->parseGotoStmt();
                break;

            case Symbol::REM:
                echo "Found rem statement.\n";
                $this->parseRem();
                break;

            case Symbol::END:
                echo "Found 'end' statement.\n";
                return;
                break;

            default:
                $this->throwError("Declaração inesperada. Token encontrado: " . $currentToken);
        }
    }

    // Analisando IO (IO -> "input" id | "print" A)
    private function parseIO()
    {
        $currentToken = $this->getCurrentToken();

        if ($currentToken->getType()->getUid() === Symbol::INPUT) {
            echo "Parsing 'input' statement.\n";
            $this->advanceToken();
            $currentToken = $this->getCurrentToken();
            if ($currentToken->getType()->getUid() === Symbol::VARIABLE) {
                echo "Identifier found for input statement.\n";
                $this->advanceToken();
            } else {
                $this->throwError("Esperado um identificador após 'input'.");
            }
        } elseif ($currentToken->getType()->getUid() === Symbol::PRINT) {
            echo "Parsing 'print' statement.\n";
            $this->advanceToken();
            $this->parseA();
        } else {
            $this->throwError("Erro de sintaxe na declaração de IO.");
        }
    }

    // Analisando Atribuição (Assign -> "let" id "=" A)
    private function parseAssign()
    {
        echo "Parsing 'let' statement.\n";
        $this->advanceToken();

        $currentToken = $this->getCurrentToken();
        if ($currentToken->getType()->getUid() === Symbol::VARIABLE) {
            echo "Identifier found for assignment.\n";
            $this->advanceToken();

            $currentToken = $this->getCurrentToken();
            if ($currentToken->getType()->getUid() === Symbol::ASSIGNMENT) {
                echo "Assignment operator '=' found.\n";
                $this->advanceToken();
                $this->parseA();
            } else {
                $this->throwError("Esperado '=' na atribuição.");
            }
        } else {
            $this->throwError("Esperado identificador após 'let'.");
        }
    }

    // Analisando Goto Condicional (GotoStmt -> "if" B "goto" num)
    private function parseGotoStmt()
    {
        echo "Parsing 'if' statement.\n";
        $this->advanceToken();
        $this->parseB();

        $currentToken = $this->getCurrentToken();
        if ($currentToken->getType()->getUid() === Symbol::GOTO) {
            echo "'goto' statement found.\n";
            $this->advanceToken();

            $currentToken = $this->getCurrentToken();
            if ($currentToken->getType()->getUid() === Symbol::INTEGER) {
                echo "Line number found for goto.\n";
                $this->advanceToken();
            } else {
                $this->throwError("Esperado número de linha após 'goto'.");
            }
        } else {
            $this->throwError("Esperado 'goto'.");
        }
    }

    // Analisando Rem (Rem -> "rem" ... LF)
    private function parseRem()
    {
        echo "Parsing 'rem', skipping until LF.\n";
        while(!$this->isEndOfInput() && $this->getCurrentToken()->getType()->getUid() !== Symbol::LF) {
            $this->advanceToken();
        }
        $this->skipLineFeeds();
    }

    // Analisando Expressões Aritméticas (A)
    private function parseA()
    {
        echo "Parsing arithmetic expression (A)...\n";
        $this->parseAddSub(); // menor precedência
    }

    // A -> A + A | A - A
    private function parseAddSub()
    {
        $this->parseMulDivMod(); //segunda maior precedência

        while (!$this->isEndOfInput()) {
            $currentToken = $this->getCurrentToken();
            if (in_array($currentToken->getType()->getUid(), [Symbol::ADD, Symbol::SUBTRACT])) {
                echo "Found addition or subtraction operator: " . $currentToken->getType()->getUid() . "\n";
                $this->advanceToken();  
                $this->parseMulDivMod();
            } else {
                break;
            }
        }
    }

    // A -> A * A | A / A | A % A
    private function parseMulDivMod()
    {
        $this->parseIdNum(); //maior precedência

        while (!$this->isEndOfInput()) {
            $currentToken = $this->getCurrentToken();
            if (in_array($currentToken->getType()->getUid(), [Symbol::MULTIPLY, Symbol::DIVIDE, Symbol::MODULO])) {
                echo "Found multiplication, division, or modulus operator: " . $currentToken->getType()->getUid() . "\n";
                $this->advanceToken();
                $this->parseIdNum(); 
            } else {
                break; 
            }
        }
    }

    // A -> id | num
    private function parseIdNum()
    {
        $currentToken = $this->getCurrentToken();

        if ($currentToken->getType()->getUid() === Symbol::VARIABLE || $currentToken->getType()->getUid() === Symbol::INTEGER) {
            echo "Found identifier or number: " . $currentToken . "\n";
            $this->advanceToken();
        } else {
            $this->throwError("Esperado identificador ou número.");
        }
    }
  // Analisando Expressões Booleanas (B)
    private function parseB()
    {
        echo "Parsing boolean expression (B)...\n";
        $this->parseA();
        $currentToken = $this->getCurrentToken();
        if (in_array($currentToken->getType()->getUid(), [Symbol::EQ, Symbol::NE, Symbol::LT, Symbol::GT, Symbol::LE, Symbol::GE])) {
            echo "Comparison operator found.\n";
            $this->advanceToken();
            $this->parseA();
        } else {
            $this->throwError("Esperado operador de comparação.");
        }
    }
}
?>
