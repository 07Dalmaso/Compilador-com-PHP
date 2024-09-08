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
 
     public function __construct($tokens)
     {
         $this->tokens = $tokens;
         $this->currentIndex = 0;
         echo "Parser inicializado com " . count($this->tokens) . " tokens.\n";
     }
 
     // Obtém o token atual e imprime uma mensagem com o token corrente
     public function getCurrentToken()
     {
         if ($this->currentIndex < count($this->tokens)) {
             $token = $this->tokens[$this->currentIndex];
             echo "Token atual: " . $token . "\n";
             return $token;
         } else {
             echo "Fim da entrada atingido.\n";
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
             echo "Avançando token do índice " . $this->currentIndex . "\n";
             $this->currentIndex++;
         }
     }
 
     // Ignora tokens até alcançar uma nova linha (LF) ou o fim da entrada
     private function skipToNextLine()
     {
         echo "Pulando para a próxima linha...\n";
         while ($this->currentIndex < count($this->tokens) && $this->tokens[$this->currentIndex]->getType()->getUid() !== Symbol::LF) {
             $this->advanceToken();
         }
 
         if ($this->currentIndex < count($this->tokens) && $this->tokens[$this->currentIndex]->getType()->getUid() === Symbol::LF) {
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
 
         $this->replaceWithErrorToken();
         $this->skipToNextLine();
     }
 
     // Verifica se a entrada terminou
     public function isEndOfInput()
     {
         return $this->currentIndex >= count($this->tokens);
     }
 
     // Pula tokens de nova linha
     private function skipLineFeeds()
     {
         echo "Pulando tokens de nova linha...\n";
         while (!$this->isEndOfInput() && $this->getCurrentToken()->getType()->getUid() === Symbol::LF) {
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
                 $currentToken = $this->getCurrentToken();
                 if ($currentToken->getType()->getUid() === Symbol::END) {
                     echo "Fim do programa encontrado.\n";
                     break; 
                 }
                 $this->parseTag();
             }
         }
 
         $this->printErrors();
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
         echo "Analisando Tag (número da linha seguido de declaração)...\n";
         $this->skipLineFeeds();
         $currentToken = $this->getCurrentToken();
 
         if ($currentToken->getType()->getUid() === Symbol::INTEGER) {
             echo "Número de linha encontrado.\n";
             $this->advanceToken();
             $this->parseStmt();
         } else {
             $this->throwError("Número de linha esperado.");
         }
     }
 
     // Analisando Declaração (Stmt -> IO | Assign | GotoStmt | rem | end)
     private function parseStmt()
     {
         $currentToken = $this->getCurrentToken();
         echo "Analisando declaração...\n";
 
         switch ($currentToken->getType()->getUid()) {
             case Symbol::INPUT:
             case Symbol::PRINT:
                 echo "Declaração de entrada/saída encontrada.\n";
                 $this->parseIO();
                 break;
 
             case Symbol::LET:
                 echo "Declaração de atribuição encontrada.\n";
                 $this->parseAssign();
                 break;
 
             case Symbol::IF:
                 echo "Declaração condicional 'if' encontrada.\n";
                 $this->parseGotoStmt();
                 break;
 
             case Symbol::REM:
                 echo "Declaração 'rem' encontrada.\n";
                 $this->parseRem();
                 break;
 
             case Symbol::END:
                 echo "Declaração 'end' encontrada. Finalizando...\n";
                 return;
 
             default:
                 $this->throwError("Declaração inesperada.");
         }
     }
 
     // Analisando IO (IO -> "input" id | "print" A)
     private function parseIO()
     {
         $currentToken = $this->getCurrentToken();
 
         if ($currentToken->getType()->getUid() === Symbol::INPUT) {
             echo "Analisando declaração 'input'.\n";
             $this->advanceToken();
             $currentToken = $this->getCurrentToken();
             if ($currentToken->getType()->getUid() === Symbol::VARIABLE) {
                 echo "Identificador encontrado após 'input'.\n";
                 $this->advanceToken();
             } else {
                 $this->throwError("Esperado um identificador após 'input'.");
             }
         } elseif ($currentToken->getType()->getUid() === Symbol::PRINT) {
             echo "Analisando declaração 'print'.\n";
             $this->advanceToken();
             $this->parseA();
         } else {
             $this->throwError("Erro de sintaxe na declaração de IO.");
         }
     }
 
     // Analisando Atribuição (Assign -> "let" id "=" A)
     private function parseAssign()
     {
         echo "Analisando declaração 'let'.\n";
         $this->advanceToken();
 
         $currentToken = $this->getCurrentToken();
         if ($currentToken->getType()->getUid() === Symbol::VARIABLE) {
             echo "Identificador encontrado na atribuição.\n";
             $this->advanceToken();
 
             $currentToken = $this->getCurrentToken();
             if ($currentToken->getType()->getUid() === Symbol::ASSIGNMENT) {
                 echo "Operador '=' encontrado.\n";
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
         $currentToken = $this->getCurrentToken();
     
         if ($currentToken->getType()->getUid() === Symbol::IF) {
             echo "Analisando declaração 'if'.\n";
             $this->advanceToken();
             $this->parseC();
 
             $currentToken = $this->getCurrentToken();
             if ($currentToken->getType()->getUid() === Symbol::GOTO) {
                 echo "'goto' encontrado após condição 'if'.\n";
                 $this->advanceToken();
     
                 $currentToken = $this->getCurrentToken();
                 if ($currentToken->getType()->getUid() === Symbol::INTEGER) {
                     echo "Número de linha encontrado para 'goto'.\n";
                     $this->advanceToken();
                 } else {
                     $this->throwError("Esperado número de linha após 'goto'.");
                 }
             } else {
                 $this->throwError("Esperado 'goto' após a condição.");
             }
         } elseif ($currentToken->getType()->getUid() === Symbol::GOTO) {
             echo "Analisando declaração 'goto'.\n";
             $this->advanceToken();
     
             $currentToken = $this->getCurrentToken();
             if ($currentToken->getType()->getUid() === Symbol::INTEGER) {
                 echo "Número de linha encontrado para 'goto'.\n";
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
         echo "Analisando declaração 'rem'. Pulando até o final da linha...\n";
         while(!$this->isEndOfInput() && $this->getCurrentToken()->getType()->getUid() !== Symbol::LF) {
             $this->advanceToken();
         }
         $this->skipLineFeeds();
     }
 
     // A -> id | num
     private function parseA()
     {
         $currentToken = $this->getCurrentToken();
 
         if ($currentToken->getType()->getUid() === Symbol::VARIABLE || $currentToken->getType()->getUid() === Symbol::INTEGER) {
             echo "Identificador ou número encontrado.\n";
             $this->advanceToken();
         } else {
             $this->throwError("Esperado identificador ou número.");
         }
     }
 
     // Analisando Expressões Aritméticas (B)
     private function parseB()
     {
         echo "Analisando expressão aritmética...\n";
         $this->parseA();
 
         $currentToken = $this->getCurrentToken();
         if (in_array($currentToken->getType()->getUid(), [Symbol::ADD, Symbol::SUBTRACT])) {
             echo "Operador aritmético encontrado.\n";
             $this->advanceToken();
             $this->parseA();
         }
     }
 
     // Analisando Expressões Booleanas (C)
     private function parseC()
     {
         echo "Analisando expressão booleana...\n";
         $this->parseA();
 
         $currentToken = $this->getCurrentToken();
         if (in_array($currentToken->getType()->getUid(), [Symbol::EQ, Symbol::NE, Symbol::LT, Symbol::GT, Symbol::LE, Symbol::GE])) {
             echo "Operador de comparação encontrado.\n";
             $this->advanceToken();
             $this->parseA();
         } else {
             $this->throwError("Esperado operador de comparação.");
         }
     }
 }
?>