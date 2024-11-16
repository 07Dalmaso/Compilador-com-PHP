<?php

class CodeGenerator {
    private $tokens;
    private $symbolTable;
    private $smlCode = [];

    // Mapeamento de tipos de tokens de entrada para opcodes da SML
    private $symbolToOpcode = [
        Symbol::INPUT => 10,       // INPUT (62) -> READ (10)
        Symbol::PRINT => 11,       // PRINT (64) -> WRITE (11)
        Symbol::LET => 21,         // LET (63) -> STORE (21)
        Symbol::ADD => 30,         // ADD (21) -> ADD (30)
        Symbol::SUBTRACT => 31,    // SUBTRACT (22) -> SUBTRACT (31)
        Symbol::DIVIDE => 32,      // DIVIDE (24) -> DIVIDE (32)
        Symbol::MULTIPLY => 33,    // MULTIPLY (23) -> MULTIPLY (33)
        Symbol::MODULO => 34,      // MODULO (25) -> MODULE (34)
        Symbol::GOTO => 40,        // GOTO (65) -> BRANCH (40)
        Symbol::IF => [41, 42],    // IF (66) -> BRANCHNEG (41) ou BRANCHZERO (42)
        Symbol::END => 43          // END (67) -> HALT (43)
    ];

    public function __construct($tokens, $symbolTable) {
        $this->tokens = $tokens;
        $this->symbolTable = $symbolTable;
    }

    public function generateCode() {
        echo "=== Iniciando a Geração de Código ===\n";
    
        $instructionIndex = 0; // Index starts from 0
        $variableMapping = []; // To store new variable addresses
    
        foreach ($this->tokens as $index => $token) {
            $tokenType = $token->getType()->getUid();
    
            // Ignorar tokens de quebra de linha (LF) e de fim de texto (ETX)
            if ($tokenType === Symbol::LF || $tokenType === Symbol::ETX) {
                continue;
            }
    
            if (array_key_exists($tokenType, $this->symbolToOpcode)) {
                $this->processToken($tokenType, $token, $index, $instructionIndex, $variableMapping);
                $instructionIndex++; // Increment after processing each valid instruction
            }
        }
    
        // Filter the symbol table to only include variables
        $variables = array_filter($this->symbolTable, function ($key) {
            return preg_match('/^[a-zA-Z]+$/', $key); // Keep only variables (alphabetic keys)
        }, ARRAY_FILTER_USE_KEY);
    
        // Assign variables at the end and map their new addresses
        echo "Adicionando variáveis ao final do código...\n";
        foreach ($variables as $variable => $address) {
            echo "Atribuindo variável '{$variable}' no índice {$instructionIndex}\n";
            $this->smlCode[] = "{$instructionIndex}: +0000";
            $variableMapping[$variable] = $instructionIndex; // Map the new address
            $instructionIndex++;
        }
    
        // Replace old addresses with new ones
        $this->updateAddresses($variableMapping);
    
        echo "=== Geração de Código Completa ===\n";
    
        // Output the indexed SML code
        foreach ($this->smlCode as $instruction) {
            echo $instruction . "\n";
        }
    
        return $this->smlCode;
    }
    
    private function updateAddresses($variableMapping) {
        foreach ($this->smlCode as $index => $instruction) {
            if (preg_match('/\+(\d{2}) (\d+)/', $instruction, $matches)) {
                $opcode = $matches[1];
                $oldAddress = $matches[2];
    
                // Check if the old address maps to a new variable address
                $newAddress = array_search($oldAddress, $this->symbolTable);
                if ($newAddress !== false && isset($variableMapping[$newAddress])) {
                    $updatedAddress = $variableMapping[$newAddress];
                    $this->smlCode[$index] = preg_replace('/\d+$/', $updatedAddress, $instruction);
                }
            }
        }
    }
    
    private function processToken($instructionType, $token, $index, $instructionIndex, &$variableMapping) {
        $opcode = $this->symbolToOpcode[$instructionType];
        $symbolIndex = $token->getAddress();
        $address = isset($this->symbolTable[$symbolIndex]) ? $this->symbolTable[$symbolIndex] : null;
    
        switch ($instructionType) {
            case Symbol::LET:
                echo "Gerando instrução LET\n";
                $variableToken = $this->tokens[$index + 1];
                $variableAddress = $variableToken->getAddress();
                echo "Endereço da variável: {$variableAddress}\n";
                $this->smlCode[] = "{$instructionIndex}: +{$opcode} {$variableAddress}";
                break;
            case Symbol::INPUT:
                echo "Gerando instrução INPUT\n";
                $variableToken = $this->tokens[$index + 1];
                $variableAddress = $variableToken->getAddress();
                echo "Endereço da variável: {$variableAddress}\n";
                $this->smlCode[] = "{$instructionIndex}: +{$opcode} {$variableAddress}";
                break;
            case Symbol::PRINT:
                echo "Gerando instrução PRINT\n";
                $variableToken = $this->tokens[$index + 1];
                $variableAddress = $variableToken->getAddress();
                echo "Endereço da variável: {$variableAddress}\n";
                $this->smlCode[] = "{$instructionIndex}: +{$opcode} {$variableAddress}";
                break;
            case Symbol::IF:
                echo "Gerando instrução IF\n";
                $this->generateIfInstruction($this->tokens, $index, $instructionIndex, $variableMapping);
                break;
            case Symbol::GOTO:
                echo "Gerando instrução GOTO\n";
                $gotoTargetToken = $this->tokens[$index + 1];
                $gotoTargetAddress = $gotoTargetToken->getAddress();
                echo "Endereço de GOTO: {$gotoTargetAddress}\n";
                $this->smlCode[] = "{$instructionIndex}: +{$opcode} {$gotoTargetAddress}";
                break;
            case Symbol::END:
                echo "Gerando instrução END\n";
                $this->smlCode[] = "{$instructionIndex}: +{$opcode}00";
                break;
            default:
                echo "Tipo de token não tratado: {$instructionType}\n";
                break;
        }
    }
    
    private function generateIfInstruction($tokens, $ifTokenIndex, $instructionIndex, $variableMapping) {
        $variableToken = $tokens[$ifTokenIndex + 1];
        $operatorToken = $tokens[$ifTokenIndex + 2];
        $comparisonValueToken = $tokens[$ifTokenIndex + 3];
        $gotoTargetToken = $tokens[$ifTokenIndex + 5];
    
        $variableAddress = $variableToken->getAddress();
        $operatorType = $operatorToken->getType()->getUid();
        $gotoTargetAddress = $gotoTargetToken->getAddress();
    
        $branchOpcode = null;
        if ($operatorType === Symbol::EQ) {
            $branchOpcode = 42; // BRANCHZERO
        } elseif ($operatorType === Symbol::LT) {
            $branchOpcode = 41; // BRANCHNEG
        }
    
        if ($branchOpcode !== null && $variableAddress !== null && $gotoTargetAddress !== null) {
            echo "Condição IF com operador '{$operatorType}', variável endereço {$variableAddress}, destino {$gotoTargetAddress}\n";
            $this->smlCode[] = "{$instructionIndex}: +{$branchOpcode} {$gotoTargetAddress}";
        } else {
            echo "Aviso: Condição IF não suportada ou endereços inválidos.\n";
        }
    }
    
}
