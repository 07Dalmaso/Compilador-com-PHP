<?php
/**
 * Classe para análise semântica de um código baseado em tokens.
 * 
 * Autor: Lucas Santos Dalmaso
 * Email: lucassdalmaso25@gmail.com
 */
require_once 'Lexeme.php';
require_once 'Symbol.php';
require_once 'Token.php';

class SemanticAnalysis
{
    private LexicalAnalysis $lexicalAnalysis;
    private array $declaredVariables;
    private array $lineNumbers;
    private array $gotoTargets;
    private int $lastLineNumber = 0;
    private array $errorMessages = [];
    private bool $error = false;
    private array $tokens;
    private int $tokenIndex = 0;
    public bool $end = false;

    /**
     * Construtor da classe SemanticAnalysis.
     *
     * @param LexicalAnalysis $lexicalAnalysis Análise lexical fornecida.
     */
    public function __construct(LexicalAnalysis $lexicalAnalysis)
    {
        $this->lexicalAnalysis = $lexicalAnalysis;
        $this->declaredVariables = [];
        $this->lineNumbers = [];
        $this->gotoTargets = [];
        $this->tokens = $lexicalAnalysis->getTokens();
    }

    /**
     * Avança o índice do token atual.
     */
    public function advanceToken()
    {
        echo "Avançando token do índice " . $this->tokenIndex . "\n";
        if ($this->tokenIndex < count($this->tokens) - 1) {
            $this->tokenIndex++;
        } else {
            echo "Já no último token.\n";
        }
    }

    /**
     * Inicia a análise semântica dos tokens.
     *
     * @return bool Retorna true se a análise for bem-sucedida, caso contrário, false.
     */
    public function analyze()
    {
        while ($this->tokenIndex < count($this->tokens)) {
            $currentToken = $this->tokens[$this->tokenIndex];
            echo sprintf("Analisando token: %s", $currentToken) . PHP_EOL;

            if ($currentToken->getType()->getUid() == Symbol::END) {
                $this->end = true;
                $this->advanceToken(); 
                break;
            }
            if ($currentToken->getType()->getUid() == Symbol::ETX) {
                if (!$this->end) {
                    $this->addError("Falta o comando 'END' para o fechamento do código." . $this->getLineCollumn());
                }
                break;
            }

            // Processar número da linha
            if (!$this->lineNumber($currentToken)) {
                // break;  // Se erro ocorrer, interromper análise
            }

            // Processar comando
            if (!$this->command()) {
                // break;
            }
            if ($this->tokens[$this->tokenIndex]->getType()->getUid() == Symbol::LF) {
                $this->advanceToken();
            }
        }

        $this->verifyGotoTargets();

        // Se houver erros, exiba todos eles
        if ($this->error) {
            echo "Erros encontrados durante a análise semântica:" . PHP_EOL;
            foreach ($this->errorMessages as $message) {
                echo $message . PHP_EOL;
            }
            return false;
        } else {
            echo "Análise semântica concluída sem erros!" . PHP_EOL;
            return true;
        }
    }

    /**
     * Processa o número da linha atual.
     *
     * @param Token $currentToken Token atual a ser analisado.
     * @return bool Retorna true se o número da linha for válido, caso contrário, false.
     */
    private function lineNumber($currentToken)
    {
        if ($currentToken->getType()->getUid() != Symbol::INTEGER) {
            $this->addError("Esperado número de linha no início da expressão" . $this->getLineCollumn());
            return false;
        }

        $address = $currentToken->getAddress();  // Pega o valor do endereço (ex: 0)
        $symbolTable = $this->lexicalAnalysis->getSymbolTable();  // Tabela de símbolos

        $currentLineNumber = array_search($address, $symbolTable);
        if ($currentLineNumber === false || $currentLineNumber <= $this->lastLineNumber) {
            $this->addError("Número de linha $currentLineNumber não está em ordem crescente ou foi repetido." . $this->getLineCollumn());
            $this->advanceToken();
            return false;
        }
        if (in_array($currentLineNumber, $this->lineNumbers)) {
            $this->addError("Número de linha $currentLineNumber repetido." . $this->getLineCollumn());
            $this->advanceToken();
            return false;
        }
        $this->lineNumbers[] = $currentLineNumber;
        $this->lastLineNumber = $currentLineNumber;
        $this->advanceToken();
        return true;
    }

    /**
     * Processa o comando atual.
     *
     * @return bool Retorna true se o comando for válido, caso contrário, false.
     */
    private function command(): bool
    {
        $currentToken = $this->tokens[$this->tokenIndex];
        echo sprintf("Analisando expressão semântica: %s", $currentToken->getType()->getUid()) . PHP_EOL;

        switch ($currentToken->getType()->getUid()) {
            case Symbol::REM:
                $this->advanceToken();
                break;
            case Symbol::INPUT:
                $this->advanceToken(); 
                $this->declareVariable(array_search($this->tokens[$this->tokenIndex]->getAddress(), $this->lexicalAnalysis->getSymbolTable()));
                $this->advanceToken();
                break;
            case Symbol::LET:
                $this->advanceToken(); 
                if ($this->tokenIndex < count($this->tokens)) {
                    $this->declareVariable(array_search($this->tokens[$this->tokenIndex]->getAddress(), $this->lexicalAnalysis->getSymbolTable()));

                    $this->advanceToken();
                    if ($this->tokenIndex < count($this->tokens)) {
                        $this->advanceToken();
                        $this->expression();
                    }
                }
                break;
            case Symbol::IF:
                $this->advanceToken();
                $this->condition();
                if (
                    $this->tokenIndex < count($this->tokens) &&
                    $this->tokens[$this->tokenIndex]->getType()->getUid() == Symbol::GOTO
                ) {
                    $this->advanceToken(); 
                    if (
                        $this->tokenIndex < count($this->tokens) &&
                        $this->tokens[$this->tokenIndex]->getType()->getUid() == Symbol::INTEGER
                    ) {
                        $this->gotoTargets[] = (int)array_search($this->tokens[$this->tokenIndex]->getAddress(), $this->lexicalAnalysis->getSymbolTable());
                        $this->advanceToken(); 
                    } else {
                        $this->addError("Número de linha esperado após GOTO." . $this->getLineCollumn());
                        return false;
                    }
                } else {
                    $this->addError("Esperado GOTO após condição." . $this->getLineCollumn());
                    return false;
                }
                break;
            case Symbol::GOTO:
                $this->advanceToken();
                if (
                    $this->tokenIndex < count($this->tokens) &&
                    $this->tokens[$this->tokenIndex]->getType()->getUid() == Symbol::INTEGER
                ) {
                    $this->gotoTargets[] = (int)array_search($this->tokens[$this->tokenIndex]->getAddress(), $this->lexicalAnalysis->getSymbolTable());
                    $this->advanceToken();
                } else {
                    $this->addError("Número de linha esperado após GOTO." . $this->getLineCollumn());
                    return false;
                }
                break;
            case Symbol::PRINT:
                $this->advanceToken();
                if ($this->tokenIndex < count($this->tokens)) {
                    $this->useVariablePrint(array_search($this->tokens[$this->tokenIndex]->getAddress(), $this->lexicalAnalysis->getSymbolTable()));
                    $this->advanceToken();
                }
                break;
            case Symbol::END:
                $this->end = true;
                // Não faz nada.
                break;
            default:
                $this->addError("Comando não reconhecido." . $this->getLineCollumn());
                return false;
        }

        echo sprintf("Expressão semântica %s analisada e válida!", $currentToken->getType()->getUid()) . PHP_EOL;
        return true;
    }

    /**
     * Processa uma expressão matemática.
     */
    private function expression(): void
    {
        if ($this->tokens[$this->tokenIndex]->getType()->getUid() == Symbol::SUBTRACT) {
            $this->advanceToken();
        }
        $this->term();
        while ($this->isArithmeticOperator($this->tokens[$this->tokenIndex]->getType()->getUid())) {
            $this->advanceToken();
            $this->term();
        }
    }

    /**
     * Verifica se o símbolo atual é um operador aritmético.
     *
     * @param int $symbol Tipo do símbolo.
     * @return bool Retorna true se for um operador aritmético, caso contrário, false.
     */
    private function isArithmeticOperator($symbol): bool
    {
        return $symbol == Symbol::ADD ||
            $symbol == Symbol::SUBTRACT ||
            $symbol == Symbol::MULTIPLY ||
            $symbol == Symbol::DIVIDE ||
            $symbol == Symbol::MODULO;
    }

    /**
     * Processa um termo na expressão.
     */
    private function term(): void
    {
        $this->factor();
        while (
            $this->tokens[$this->tokenIndex]->getType()->getUid() == Symbol::MULTIPLY ||
            $this->tokens[$this->tokenIndex]->getType()->getUid() == Symbol::DIVIDE
        ) {
            $this->advanceToken();
            $this->factor();
        }
    }

    /**
     * Processa um fator na expressão.
     */
    private function factor(): void
    {
        $currentToken = $this->tokens[$this->tokenIndex];
        if ($currentToken->getType()->getUid() == Symbol::VARIABLE) {
            $this->useVariable(array_search($this->tokens[$this->tokenIndex]->getAddress(), $this->lexicalAnalysis->getSymbolTable()));
            $this->advanceToken();
        } elseif ($currentToken->getType()->getUid() == Symbol::INTEGER) {
            $this->advanceToken();
        } else {
            $this->addError("Token inesperado no fator da expressão: " . $currentToken->getType()->getUid());
        }
    }

    /**
     * Processa uma condição para uma estrutura condicional.
     */
    private function condition(): void
    {
        $this->expression();
        $this->relationalOperator(); // Operador de comparação
        $this->expression(); // Segunda expressão
    }

    /**
     * Processa um operador relacional.
     */
    private function relationalOperator(): void
    {
        $currentSymbol = $this->tokens[$this->tokenIndex]->getType()->getUid();
        if (in_array($currentSymbol, [
            Symbol::GT,
            Symbol::LT,
            Symbol::EQ,
            Symbol::NE,
            Symbol::GE,
            Symbol::LE
        ])) {
            $this->advanceToken();
        } else {
            $this->addError("Operador de comparação esperado.");
        }
    }

    /**
     * Declara uma variável.
     *
     * @param string $varName Nome da variável.
     */
    private function declareVariable($varName): void
    {
        $this->declaredVariables[] = $varName;
    }

    /**
     * Verifica se uma variável foi declarada.
     *
     * @param string $varName Nome da variável.
     */
    private function useVariable($varName): void
    {
        if (!in_array($varName, $this->declaredVariables)) {
            $this->addError("Variável $varName não foi declarada.");
        }
    }

    /**
     * Verifica se os alvos dos comandos GOTO são válidos.
     */
    private function verifyGotoTargets(): void
    {
        foreach ($this->gotoTargets as $target) {
            if (!in_array($target, $this->lineNumbers)) {
                $this->addError("GOTO para linha $target que não existe.");
            }
        }
    }

    /**
     * Verifica o uso de uma variável no comando PRINT.
     *
     * @param string $varName Nome da variável.
     */
    private function useVariablePrint($varName): void
    {
        if (empty($varName)) {
            $this->addError("Comando 'print' necessita de uma variável." . $this->getLineCollumn());
            return;
        }
        
        if (!in_array($varName, $this->declaredVariables) || 
            !ctype_alpha($varName) || 
            strlen($varName) != 1 || 
            ctype_upper($varName) || 
            is_numeric($varName)) {

            $errorMessage = "Erro na variável $varName: ";

            if (!in_array($varName, $this->declaredVariables)) {
                $errorMessage .= "não foi declarada, ";
            }

            if (!ctype_alpha($varName)) {
                $errorMessage .= "deve conter apenas letras, ";
            }

            if (strlen($varName) != 1) {
                $errorMessage .= "deve ser apenas uma letra, ";
            }

            if (ctype_upper($varName)) {
                $errorMessage .= "não deve ser uma letra maiúscula, ";
            }

            if (is_numeric($varName)) {
                $errorMessage .= "não pode ser um número.";
            }

            // Remove vírgula final e adiciona ponto final
            $errorMessage = rtrim($errorMessage, ', ') . ".";

            $this->addError($errorMessage . $this->getLineCollumn());
        }
    }

    /**
     * Obtém a linha e coluna atuais do token.
     *
     * @return string Informações sobre linha e coluna.
     */
    private function getLineCollumn(): string
    {
        $currentToken = $this->tokens[$this->tokenIndex];
        return " (Linha: " . $currentToken->getLine() . ", Coluna: " . $currentToken->getColumn() . ")";
    }

    /**
     * Adiciona uma mensagem de erro à lista de erros.
     *
     * @param string $message Mensagem de erro.
     */
    private function addError($message): void
    {
        $this->errorMessages[] = $message;
        $this->error = true;
    }
}
?>
