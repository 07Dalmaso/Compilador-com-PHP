<?php

class CodeGenerator {
    private $tokens;
    private $symbolTable;
    private $symbolTable1;
    private $memory = array();
    private $flags = array();
    private $instructionCounter = 0;
    private $dataCounter = 99;
    private $variableAddresses = array();
    private $lineAddresses = array();
    private $constantsAddresses = array();
    private $maxMemorySize = 100;
    public $errors = array();
    private $tempAddress = -1;

    public function __construct($tokens, $symbolTable, $symbolTable1) {
        $this->tokens = $tokens;
        $this->symbolTable = $symbolTable;
        $this->symbolTable1 = $symbolTable1;
        $this->memory = array_fill(0, $this->maxMemorySize, 0);
    }

    public function generateCode() {
        $this->firstPass();
        $this->secondPass();
var_dump($this->variableAddresses);
var_dump($this->constantsAddresses);
        return $this->memory;
    }

    public function outputCode($fileName) {
        $file = fopen($fileName, "w");
        foreach ($this->memory as $instruction) {
            $sign = ($instruction >= 0) ? "+" : "-";
            $absInstruction = abs($instruction);
            fwrite($file, sprintf("%s%04d\n", $sign, $absInstruction));
        }
        fclose($file);
    }

    private function firstPass() {
        $index = 0;
        while ($index < count($this->tokens)) {
            $token = $this->tokens[$index];


            if ($token->getType()->getUid() === Symbol::INTEGER && $token->getColumn() == 1) { 
                $lineNumber = intval($this->symbolTable1[$token->getAddress()]);
                $this->lineAddresses[$lineNumber] = $this->instructionCounter;
                $index++;

                if ($index >= count($this->tokens)) break;

                $nextToken = $this->tokens[$index];
                switch ($nextToken->getType()->getUid()) {
                    case Symbol::REM:
                        while ($index < count($this->tokens) && $this->tokens[$index]->getType()->getUid() !== Symbol::LF) {
                            $index++;
                        }
                        $index++;
                        break;
                    case Symbol::INPUT:
                        $index++;
                        $variableToken = $this->tokens[$index] ?? null;
                        if ($variableToken && $variableToken->getType()->getUid() === Symbol::VARIABLE) {
                            $variable = $this->symbolTable1[$variableToken->getAddress()];
                            $address = $this->getVariableAddress($variable);
                            $instruction = 10 * 100 + $address;
                            $this->addInstruction($instruction);
                            $index++;
                        } else {
                            $this->errors[] = "Expected variable after INPUT at line $lineNumber";
                        }
                        break;
                    case Symbol::PRINT:
                        $index++;
                        $variableToken = $this->tokens[$index] ?? null;
                        if ($variableToken && $variableToken->getType()->getUid() === Symbol::VARIABLE) {
                            $variable = $this->symbolTable1[$variableToken->getAddress()];

                            $address = $this->getVariableAddress($variable);
                            $instruction = 11 * 100 + $address;
                            $this->addInstruction($instruction);
                            $index++;
                        } else {
                            $this->errors[] = "Expected variable after PRINT at line $lineNumber";
                        }
                        break;
                    case Symbol::LET:
                        $index++;
                        $variableToken = $this->tokens[$index] ?? null;
                        if ($variableToken && $variableToken->getType()->getUid() === Symbol::VARIABLE) {
                            $variable = $this->symbolTable1[$variableToken->getAddress()];
                            $variableAddress = $this->getVariableAddress($variable);
                            $index++;
                            if (isset($this->tokens[$index]) && $this->tokens[$index]->getType()->getUid() === Symbol::ASSIGNMENT) {
                                $index++;
                                $expressionTokens = array();
                                while ($index < count($this->tokens) && $this->tokens[$index]->getType()->getUid() !== Symbol::LF) {
                                    $expressionTokens[] = $this->tokens[$index];
                                    $index++;
                                }
                                $postfix = $this->infixToPostfix($expressionTokens);
                                $this->generateExpressionCode($postfix);
                                if (count($postfix) === 1) {
                                    $operandAddress = $this->getOperandAddress($postfix[0]);
                                    $this->addInstruction(2000 + $operandAddress);
                                }
                                $this->addInstruction(2100 + $variableAddress);

                                if (isset($this->tokens[$index]) && $this->tokens[$index]->getType()->getUid() !== Symbol::LF) {
                                    $index++;
                                }
                            } else {
                                $this->errors[] = "Expected '=' after variable at line $lineNumber";
                            }
                        } else {
                            $this->errors[] = "Expected variable after LET at line $lineNumber";
                        }
                        break;
                    case Symbol::GOTO:
                        $index++;
                        $lineNumberToken = $this->tokens[$index] ?? null;
                        if ($lineNumberToken && $lineNumberToken->getType()->getUid() === Symbol::INTEGER) {
                            $targetLine = intval($this->symbolTable1[$lineNumberToken->getAddress()]);
                            $instructionAddress = $this->instructionCounter;
                            $this->addInstruction(4000);
                            $this->flags[$instructionAddress] = $targetLine;
                            $index++;
                        } else {
                            $this->errors[] = "Expected line number after GOTO at line $lineNumber";
                        }                        
                        break;
                    case Symbol::IF:
                        $index++;

                        $conditionTokens = array();
                        while ($index < count($this->tokens) && $this->tokens[$index]->getType()->getUid() !== Symbol::GOTO) {
                            $conditionTokens[] = $this->tokens[$index];
                            $index++;
                        }
                        if (isset($this->tokens[$index]) && $this->tokens[$index]->getType()->getUid() === Symbol::GOTO) {
                            $index++;
                            $lineNumberToken = $this->tokens[$index] ?? null;
                            if ($lineNumberToken && $lineNumberToken->getType()->getUid() === Symbol::INTEGER) {
                                $targetLine = intval($this->symbolTable1[$lineNumberToken->getAddress()]);
                        
                                $this->generateConditionCode($conditionTokens, $targetLine);
                                $index++;
                            } else {
                                $this->errors[] = "Expected line number after GOTO at line $lineNumber";
                            }
                        } else {
                            $this->errors[] = "Expected GOTO after condition at line $lineNumber";
                        }                        
                        break;
                    case Symbol::END:
                        $this->addInstruction(4300);
                        $index++;
                        break;
                    default:
                        $this->errors[] = "Unknown instruction at line $lineNumber: " . $nextToken->getType()->getUid();
                        $index++;
                        break;
                }
            } else {
                $index++;
            }
        }
    }

    private function secondPass() {
        var_dump($this->lineAddresses);
        foreach ($this->flags as $instructionAddress => $targetLine) {
            $targetAddress = $this->lineAddresses[$targetLine] ?? null;

            if($targetLine < 0){
                $targetAddress =  - $targetLine;
            }
            var_dump($targetAddress);
            if ($targetAddress !== null) {
                $originalInstruction = $this->memory[$instructionAddress];
                $opcode = intdiv($originalInstruction, 100);
                $newInstruction = $opcode * 100 + $targetAddress;
                $this->memory[$instructionAddress] = $newInstruction;
            } else {
                $this->errors[] = "Undefined target line: $targetLine";
            }
        }
    }
    

    private function addInstruction($instruction) {
        if ($this->instructionCounter >= $this->maxMemorySize) {
            $this->errors[] = "Memory overflow: instructions exceed memory size";
            return;
        }
        $this->memory[$this->instructionCounter] = $instruction;
        $this->instructionCounter++;
    }

    private function getVariableAddress($variable) {
        echo 'variavel' . $variable .'<br>';
        if (!isset($this->variableAddresses[$variable])) {
            if ($this->dataCounter < $this->instructionCounter) {
                $this->errors[] = "Memory overflow: data storage exceeded";
                return -1;
            }
            $address = $this->dataCounter;
            $this->dataCounter--;
            $this->symbolTable->addSymbol($variable, 'V', $address);
            $this->variableAddresses[$variable] = $address;
        }
        return $this->variableAddresses[$variable];
    }

    private function getConstantAddress($constant) {
        echo 'Constante' . $constant .'<br>';
        if (!isset($this->constantsAddresses[$constant])) {
            if ($this->dataCounter <= $this->instructionCounter) {
                $this->errors[] = "Memory overflow: data storage exceeded";
                return -1;
            }
            $address = $this->dataCounter;
            $this->dataCounter--;
            $this->symbolTable->addSymbol($constant, 'C', $address);
            $this->addInstructionAt($address, intval($constant));
            $this->constantsAddresses[$constant] = $address;
        }
        return $this->constantsAddresses[$constant];
    }

    private function addInstructionAt($address, $value) {
        $this->memory[$address] = $value;
    }

    private function infixToPostfix($tokens) {
        $outputQueue = array();
        $operatorStack = array();

        $precedence = array(
            Symbol::ADD => 1,
            Symbol::SUBTRACT => 1,
            Symbol::MULTIPLY => 2,
            Symbol::DIVIDE => 2,
            Symbol::MODULO => 2
        );

        foreach ($tokens as $token) {
            switch ($token->getType()->getUid()) {
                case Symbol::VARIABLE:
                case Symbol::INTEGER:
                    $outputQueue[] = $token;
                    break;
                    case Symbol::ADD:
                    case Symbol::SUBTRACT:
                    case Symbol::MULTIPLY:
                    case Symbol::DIVIDE:
                    case Symbol::MODULO:
                    while (!empty($operatorStack) && ($precedence[end($operatorStack)->getType()->getUid()] ?? 0) >= ($precedence[$token->getType()->getUid()] ?? 0)) {
                        $outputQueue[] = array_pop($operatorStack);
                    }
                    $operatorStack[] = $token;
                    break;
                default:
                    $this->errors[] = "Unknown token in expression: " . $token->getType()->getUid();
            }
        }

        while (!empty($operatorStack)) {
            $outputQueue[] = array_pop($operatorStack);
        }

        return $outputQueue;
    }

    private function generateExpressionCode($postfixTokens) {
        $stack = array();

        foreach ($postfixTokens as $token) {
            switch ($token->getType()->getUid()) {
                case Symbol::VARIABLE:
                case Symbol::INTEGER:
                    $address = $this->getOperandAddress($token);
                    $stack[] = $address;
                    break;
                case Symbol::ADD:
                case Symbol::SUBTRACT:
                case Symbol::MULTIPLY:
                case Symbol::DIVIDE:
                case Symbol::MODULO:
                    if (count($stack) < 2) {
                        $this->errors[] = "Insufficient operands for operator " . $token->getType()->getUid();
                        return;
                    }

                    $right = array_pop($stack);
                    $left = array_pop($stack);

                    $this->addInstruction(2000 + $left);
                    switch ($token->getType()->getUid()) {
                        case Symbol::ADD:
                            $this->addInstruction(3000 + $right);
                            break;
                        case Symbol::SUBTRACT:
                            $this->addInstruction(3100 + $right);
                            break;
                        case Symbol::MULTIPLY:
                            $this->addInstruction(3300 + $right);
                            break;
                        case Symbol::DIVIDE:
                            $this->addInstruction(3200 + $right);
                            break;
                        case Symbol::MODULO:
                            $this->addInstruction(3400 + $right);
                            break;
                        default:
                            $this->errors[] = "Unknown operator: " . $token->getType()->getUid();
                    }
                    $stack[] = $left;
                    break;
                default:
                    $this->errors[] = "Unknown token in expression: " . $token->getType()->getUid();
            }
        }

        if (count($stack) != 1) {
            $this->errors[] = "Expression did not result in a single getType()->getUid()";
        }
    }

    private function generateConditionCode($conditionTokens, $targetLine) {
        if (count($conditionTokens) != 3) {
            $this->errors[] = "Invalid condition in IF statement";
            return;
        }
    
        $leftToken = $conditionTokens[0];
        $operatorToken = $conditionTokens[1];
        $rightToken = $conditionTokens[2];
    
        $leftAddress = $this->getOperandAddress($leftToken);
        $rightAddress = $this->getOperandAddress($rightToken);
    
        // Carrega o operando esquerdo e subtrai o direito
        $this->addInstruction(2000 + $leftAddress); // LOAD left
        $this->addInstruction(3100 + $rightAddress); // SUBTRACT right
    
        switch ($operatorToken->getType()->getUid()) {
            case Symbol::EQ: // '=='
                // BRANCHZERO targetAddress
                $this->addInstruction(4200); // Placeholder
                $this->flags[$this->instructionCounter - 1] = $targetLine;
                break;
    
            case Symbol::NE: // '!='
                // BRANCHZERO para pular o BRANCH incondicional
                $this->addInstruction(4200); // BRANCHZERO
                $this->flags[$this->instructionCounter - 1] = -($this->instructionCounter + 1);
                // BRANCH targetAddress
                $this->addInstruction(4000); // BRANCH
                $this->flags[$this->instructionCounter - 1] = $targetLine;
                break;
    
            case Symbol::LT: // '<'
                // BRANCHNEG targetAddress
                $this->addInstruction(4100); // Placeholder
                $this->flags[$this->instructionCounter - 1] = $targetLine;
                break;
    
            case Symbol::GT: // '>'
                // BRANCHNEG para pular se negativo
                $this->addInstruction(4100);
                $this->flags[$this->instructionCounter - 1] = $this->instructionCounter + 4;
                // BRANCHZERO para pular se zero
                $this->addInstruction(4200);
                $this->flags[$this->instructionCounter - 1] = $this->instructionCounter + 3;
                // BRANCH targetAddress
                $this->addInstruction(4000);
                $this->flags[$this->instructionCounter - 1] = $targetLine;
                break;
    
            case Symbol::LE: // '<='
                // BRANCHNEG targetAddress
                $this->addInstruction(4100);
                $this->flags[$this->instructionCounter - 1] = $targetLine;
                // BRANCHZERO targetAddress
                $this->addInstruction(4200);
                $this->flags[$this->instructionCounter - 1] = $targetLine;
                break;
    
            case Symbol::GE: // '>='
                // BRANCHNEG para pular se negativo
                $this->addInstruction(4100);
                $this->flags[$this->instructionCounter - 1] = $this->instructionCounter + 3;
                // BRANCH targetAddress
                $this->addInstruction(4000);
                $this->flags[$this->instructionCounter - 1] = $targetLine;
                break;
    
            default:
                $this->errors[] = "Unknown operator in condition: " . $operatorToken->getType()->getUid();
                return;
        }
    }
    

    private function getOperandAddress($token) {
        switch ($token->getType()->getUid()) {
            case Symbol::VARIABLE:
                return $this->getVariableAddress($this->symbolTable1[$token->getAddress()]);
            case Symbol::INTEGER:
                return $this->getConstantAddress($this->symbolTable1[$token->getAddress()]);
            default:
                $this->errors[] = "Invalid operand";
                return -1;
        }
    }
}
