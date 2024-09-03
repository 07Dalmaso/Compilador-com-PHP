<?php
require_once 'Lexeme.php';
require_once 'Symbol.php';
require_once 'Token.php';

/**
 * Classe responsável pela análise léxica da linguagem de programação SIMPLE 15.01
 */
class LexicalAnalysis
{
    private $column;
    private $error;
    private $lexeme;
    private $line;
    private $source;
    private $symbolTable;
    private $tokens;

    public function __construct()
    {
        $this->line = 1;  // Começa na linha 1 como no Java
        $this->column = 0;
        $this->error = false;
        $this->symbolTable = [];
        $this->tokens = [];
    }

    private function addSymbolTable($lexeme)
    {
        if (!array_key_exists($lexeme, $this->symbolTable)) {
            $this->symbolTable[$lexeme] = count($this->symbolTable);
        }
        return $this->symbolTable[$lexeme];
    }

    private function addToken()
    {
        if ($this->lexeme->getType() != Symbol::fromUid(Symbol::ERROR)) {
            if ($this->lexeme->getType() == Symbol::fromUid(Symbol::INTEGER) || $this->lexeme->getType() == Symbol::fromUid(Symbol::VARIABLE)) {
                $address = $this->addSymbolTable($this->lexeme->getTerm());
                $this->tokens[] = $this->lexeme->toTokenWithAddress($address);
            } else {
                $this->tokens[] = $this->lexeme->toToken();
            }
        } else {
            var_dump("Erro na análise léxica");
            var_dump("Token não reconhecido: " . $this->lexeme . "\n"); 
            $this->error = true;
        }
    }

    public function getSymbolTable()
    {
        return $this->symbolTable;
    }

    public function getTokens()
    {
        return $this->tokens;
    }

    private function next()
    {
        $character = 0;

        if ($this->source) {
            $character = fgetc($this->source);

            if ($character === "\r") {
                $character = fgetc($this->source);
            }

            if ($character !== false) {
                $this->column++;
                return $character;
            } else {
                fclose($this->source);
                $this->source = null;
            }
        }

        return "\0";
    }

    public function parser($sourceFile)
    {
        $this->source = fopen($sourceFile, 'r');
        $this->tokens = [];
        $this->symbolTable = [];

        while ($this->source) {
            $this->q0();
        }

        return !$this->error;
    }

    /**
     * Estado inicial do automato finito deterministico
     */
    private function q0()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->q04();
                break;
            case "\n":
                $this->q03();
                break;
            case ' ':
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::INTEGER), $this->line, $this->column);
                $this->q01();
                break;
            case 'a':
            case 'b':
            case 'c':
            case 'd':
            case 'f':
            case 'h':
            case 'j':
            case 'k':
            case 'm':
            case 'n':
            case 'o':
            case 'q':
            case 's':
            case 't':
            case 'u':
            case 'v':
            case 'w':
            case 'x':
            case 'y':
            case 'z':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q02();
                break;
            case 'r':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q14();
                break;
            case 'i':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q16();
                break;
            case 'l':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q20();
                break;
            case 'p':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q22();
                break;
            case 'g':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q26();
                break;
            case 'e':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q29();
                break;
            case '+':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ADD), $this->line, $this->column);
                $this->q05();
                break;
            case '-':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::SUBTRACT), $this->line, $this->column);
                $this->q05();
                break;
            case '*':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MULTIPLY), $this->line, $this->column);
                $this->q05();
                break;
            case '/':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::DIVIDE), $this->line, $this->column);
                $this->q05();
                break;
            case '%':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MODULO), $this->line, $this->column);
                $this->q05();
                break;
            case '=':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ASSIGNMENT), $this->line, $this->column);
                $this->q06();
                break;
            case '<':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::LT), $this->line, $this->column);
                $this->q07();
                break;
            case '>':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::GT), $this->line, $this->column);
                $this->q08();
                break;
            case '!':
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ERROR), $this->line, $this->column);
                $this->q13();
                break;
            default:
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ERROR), $this->line, $this->column);
                $this->q99();
        }
    }

    /**
     * Estado responsavel pelo reconhecimento da constante numerica inteira
     */
    private function q01()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                $this->lexeme->append($character, Symbol::fromUid(Symbol::INTEGER));
                $this->q01();
                break;
            case '+':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ADD), $this->line, $this->column);
                $this->q05();
                break;
            case '-':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::SUBTRACT), $this->line, $this->column);
                $this->q05();
                break;
            case '*':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MULTIPLY), $this->line, $this->column);
                $this->q05();
                break;
            case '/':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::DIVIDE), $this->line, $this->column);
                $this->q05();
                break;
            case '%':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MODULO), $this->line, $this->column);
                $this->q05();
                break;
            case '=':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ASSIGNMENT), $this->line, $this->column);
                $this->q06();
                break;
            case '<':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::LT), $this->line, $this->column);
                $this->q07();
                break;
            case '>':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::GT), $this->line, $this->column);
                $this->q08();
                break;
            case '!':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ERROR), $this->line, $this->column);
                $this->q13();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q01();
        }
    }

    /**
     * Estado responsavel pelo reconhecimento do identificador
     */
    private function q02()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case '+':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ADD), $this->line, $this->column);
                $this->q05();
                break;
            case '-':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::SUBTRACT), $this->line, $this->column);
                $this->q05();
                break;
            case '*':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MULTIPLY), $this->line, $this->column);
                $this->q05();
                break;
            case '/':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::DIVIDE), $this->line, $this->column);
                $this->q05();
                break;
            case '%':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MODULO), $this->line, $this->column);
                $this->q05();
                break;
            case '=':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ASSIGNMENT), $this->line, $this->column);
                $this->q06();
                break;
            case '<':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::LT), $this->line, $this->column);
                $this->q07();
                break;
            case '>':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::GT), $this->line, $this->column);
                $this->q08();
                break;
            case '!':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ERROR), $this->line, $this->column);
                $this->q13();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q02();
        }
    }

    /**
     * Estado responsavel pelo reconhecimento do delimitador de nova linha
     */
    private function q03()
    {
        $this->lexeme = new Lexeme('\n', Symbol::fromUid(Symbol::LF), $this->line, $this->column);

        $this->addToken();

        $this->line = $this->line + 1;

        $this->column = 0;
    }

    /**
     * Estado responsavel pelo reconhecimento do delimitador de fim de texto
     */
    private function q04()
    {
        $this->lexeme = new Lexeme('\0', Symbol::fromUid(Symbol::ETX), $this->line, $this->column);

        $this->addToken();
    }

    /**
     * Estado responsavel pelo reconhecimento dos operadores aritmeticos
     *   adicao (+)
     *   subtracao (-)
     *   multiplicacao (*)
     *   divisao inteira (/)
     *   resto da divisao inteira (%)
     */
    private function q05()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::INTEGER), $this->line, $this->column);
                $this->q01();
                break;
            case 'a':
            case 'b':
            case 'c':
            case 'd':
            case 'f':
            case 'h':
            case 'j':
            case 'k':
            case 'm':
            case 'n':
            case 'o':
            case 'q':
            case 's':
            case 't':
            case 'u':
            case 'v':
            case 'w':
            case 'x':
            case 'y':
            case 'z':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q02();
                break;
            case 'r':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q14();
                break;
            case 'i':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q16();
                break;
            case 'l':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q20();
                break;
            case 'p':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q22();
                break;
            case 'g':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q26();
                break;
            case 'e':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q29();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q05();
        }
    }

    /**
     * Estado responsavel pelo reconhecimento do operador de atribuicao (=)
     */
    private function q06()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::INTEGER), $this->line, $this->column);
                $this->q01();
                break;
            case 'a':
            case 'b':
            case 'c':
            case 'd':
            case 'f':
            case 'h':
            case 'j':
            case 'k':
            case 'm':
            case 'n':
            case 'o':
            case 'q':
            case 's':
            case 't':
            case 'u':
            case 'v':
            case 'w':
            case 'x':
            case 'y':
            case 'z':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q02();
                break;
            case 'r':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q14();
                break;
            case 'i':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q16();
                break;
            case 'l':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q20();
                break;
            case 'p':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q22();
                break;
            case 'g':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q26();
                break;
            case 'e':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q29();
                break;
            case '=':
                $this->lexeme->append($character, Symbol::fromUid(Symbol::EQ));
                $this->q09();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q06();
        }
    }

    /**
     * Estado responsavel pelo reconhecimento do operador relacional
     * menor que (<)
     */
    private function q07()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::INTEGER), $this->line, $this->column);
                $this->q01();
                break;
            case 'a':
            case 'b':
            case 'c':
            case 'd':
            case 'f':
            case 'h':
            case 'j':
            case 'k':
            case 'm':
            case 'n':
            case 'o':
            case 'q':
            case 's':
            case 't':
            case 'u':
            case 'v':
            case 'w':
            case 'x':
            case 'y':
            case 'z':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q02();
                break;
            case 'r':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q14();
                break;
            case 'i':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q16();
                break;
            case 'l':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q20();
                break;
            case 'p':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q22();
                break;
            case 'g':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q26();
                break;
            case 'e':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q29();
                break;
            case '=':
                $this->lexeme->append($character, Symbol::fromUid(Symbol::LE));
                $this->q10();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q07();
        }
    }

    /**
     * Estado responsavel pelo reconhecimento do operador relacional
     * maior que (>)
     */
    private function q08()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::INTEGER), $this->line, $this->column);
                $this->q01();
                break;
            case 'a':
            case 'b':
            case 'c':
            case 'd':
            case 'f':
            case 'h':
            case 'j':
            case 'k':
            case 'm':
            case 'n':
            case 'o':
            case 'q':
            case 's':
            case 't':
            case 'u':
            case 'v':
            case 'w':
            case 'x':
            case 'y':
            case 'z':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q02();
                break;
            case 'r':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q14();
                break;
            case 'i':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q16();
                break;
            case 'l':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q20();
                break;
            case 'p':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q22();
                break;
            case 'g':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q26();
                break;
            case 'e':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q29();
                break;
            case '=':
                $this->lexeme->append($character, Symbol::fromUid(Symbol::GE));
                $this->q11();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q08();
        }
    }

    /**
     * Estado responsavel pelo reconhecimento do operador relacional
     * igual a (==)
     */
    private function q09()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::INTEGER), $this->line, $this->column);
                $this->q01();
                break;
            case 'a':
            case 'b':
            case 'c':
            case 'd':
            case 'f':
            case 'h':
            case 'j':
            case 'k':
            case 'm':
            case 'n':
            case 'o':
            case 'q':
            case 's':
            case 't':
            case 'u':
            case 'v':
            case 'w':
            case 'x':
            case 'y':
            case 'z':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q02();
                break;
            case 'r':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q14();
                break;
            case 'i':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q16();
                break;
            case 'l':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q20();
                break;
            case 'p':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q22();
                break;
            case 'g':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q26();
                break;
            case 'e':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q29();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q09();
        }
    }

    /**
     * Estado responsavel pelo reconhecimento do operador relacional
     * maior ou igual a (>=)
     */
    private function q10()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::INTEGER), $this->line, $this->column);
                $this->q01();
                break;
            case 'a':
            case 'b':
            case 'c':
            case 'd':
            case 'f':
            case 'h':
            case 'j':
            case 'k':
            case 'm':
            case 'n':
            case 'o':
            case 'q':
            case 's':
            case 't':
            case 'u':
            case 'v':
            case 'w':
            case 'x':
            case 'y':
            case 'z':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q02();
                break;
            case 'r':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q14();
                break;
            case 'i':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q16();
                break;
            case 'l':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q20();
                break;
            case 'p':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q22();
                break;
            case 'g':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q26();
                break;
            case 'e':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q29();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q10();
        }
    }

    /**
     * Estado responsavel pelo reconhecimento do operador relacional
     * menor ou igual a (<=)
     */
    private function q11()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::INTEGER), $this->line, $this->column);
                $this->q01();
                break;
            case 'a':
            case 'b':
            case 'c':
            case 'd':
            case 'f':
            case 'h':
            case 'j':
            case 'k':
            case 'm':
            case 'n':
            case 'o':
            case 'q':
            case 's':
            case 't':
            case 'u':
            case 'v':
            case 'w':
            case 'x':
            case 'y':
            case 'z':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q02();
                break;
            case 'r':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q14();
                break;
            case 'i':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q16();
                break;
            case 'l':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q20();
                break;
            case 'p':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q22();
                break;
            case 'g':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q26();
                break;
            case 'e':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q29();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q11();
        }
    }

    /**
     * Estado responsavel pelo reconhecimento do operador relacional
     * diferente de (!=)
     */
    private function q12()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::INTEGER), $this->line, $this->column);
                $this->q01();
                break;
            case 'a':
            case 'b':
            case 'c':
            case 'd':
            case 'f':
            case 'h':
            case 'j':
            case 'k':
            case 'm':
            case 'n':
            case 'o':
            case 'q':
            case 's':
            case 't':
            case 'u':
            case 'v':
            case 'w':
            case 'x':
            case 'y':
            case 'z':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q02();
                break;
            case 'r':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q14();
                break;
            case 'i':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q16();
                break;
            case 'l':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q20();
                break;
            case 'p':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q22();
                break;
            case 'g':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q26();
                break;
            case 'e':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
                $this->q29();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q12();
        }
    }

    /**
     * Estado responsável pelo reconhecimento do operador relacional
     * diferente de (!=)
     */
    private function q13()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case '=':
                $this->lexeme->append($character, Symbol::fromUid(Symbol::NE));
                $this->q12();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q13();
        }
    }

    /**
     * Estado responsável pelo reconhecimento da palavra reservada rem
     */
    private function q14()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case '+':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ADD), $this->line, $this->column);
                $this->q05();
                break;
            case '-':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::SUBTRACT), $this->line, $this->column);
                $this->q05();
                break;
            case '*':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MULTIPLY), $this->line, $this->column);
                $this->q05();
                break;
            case '/':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::DIVIDE), $this->line, $this->column);
                $this->q05();
                break;
            case '%':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MODULO), $this->line, $this->column);
                $this->q05();
                break;
            case '=':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ASSIGNMENT), $this->line, $this->column);
                $this->q06();
                break;
            case '<':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::LT), $this->line, $this->column);
                $this->q07();
                break;
            case '>':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::GT), $this->line, $this->column);
                $this->q08();
                break;
            case '!':
                $this->addToken();
                $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ERROR), $this->line, $this->column);
                $this->q13();
                break;
            case 'e':
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q15();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q14();
        }
    }

    /**
     * Estado responsável pelo reconhecimento da palavra reservada rem
     */
    private function q15()
    {
        $character = $this->next();

        switch ($character) {
            case "\0":
                $this->addToken();
                $this->q04();
                break;
            case "\n":
                $this->addToken();
                $this->q03();
                break;
            case ' ':
                $this->addToken();
                break;
            case 'm':
                $this->lexeme->append($character, Symbol::fromUid(Symbol::REM));
                $this->q31();
                break;
            default:
                $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
                $this->q15();
        }
    }

    /**
 * Estado responsável pelo reconhecimento da palavra reservada if
 */
private function q16()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case '+':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ADD), $this->line, $this->column);
            $this->q05();
            break;
        case '-':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::SUBTRACT), $this->line, $this->column);
            $this->q05();
            break;
        case '*':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MULTIPLY), $this->line, $this->column);
            $this->q05();
            break;
        case '/':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::DIVIDE), $this->line, $this->column);
            $this->q05();
            break;
        case '%':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MODULO), $this->line, $this->column);
            $this->q05();
            break;
        case '=':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ASSIGNMENT), $this->line, $this->column);
            $this->q06();
            break;
        case '<':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::LT), $this->line, $this->column);
            $this->q07();
            break;
        case '>':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::GT), $this->line, $this->column);
            $this->q08();
            break;
        case '!':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ERROR), $this->line, $this->column);
            $this->q13();
            break;
        case 'f':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::IF));
            $this->q32();
            break;
        case 'n':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q17();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q16();
    }
}

/**
 * Estado responsável pelo reconhecimento da palavra reservada input
 */
private function q17()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case 'p':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q18();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q17();
    }
}

/**
 * Estado responsável pelo reconhecimento da palavra reservada input
 */
private function q18()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case 'u':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q19();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q18();
    }
}

/**
 * Estado responsável pelo reconhecimento da palavra reservada input
 */
private function q19()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case 't':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::INPUT));
            $this->q32();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q19();
    }
}

/**
 * Estado responsável pelo reconhecimento da palavra reservada let
 */
private function q20()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case '+':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ADD), $this->line, $this->column);
            $this->q05();
            break;
        case '-':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::SUBTRACT), $this->line, $this->column);
            $this->q05();
            break;
        case '*':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MULTIPLY), $this->line, $this->column);
            $this->q05();
            break;
        case '/':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::DIVIDE), $this->line, $this->column);
            $this->q05();
            break;
        case '%':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MODULO), $this->line, $this->column);
            $this->q05();
            break;
        case '=':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ASSIGNMENT), $this->line, $this->column);
            $this->q06();
            break;
        case '<':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::LT), $this->line, $this->column);
            $this->q07();
            break;
        case '>':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::GT), $this->line, $this->column);
            $this->q08();
            break;
        case '!':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ERROR), $this->line, $this->column);
            $this->q13();
            break;
        case 'e':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q21();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q20();
    }
}

/**
 * Estado responsável pelo reconhecimento da palavra reservada let
 */
private function q21()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case 't':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::LET));
            $this->q32();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q21();
    }
}

/**
 * Estado responsável pelo reconhecimento da palavra reservada print
 */
private function q22()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case '+':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ADD), $this->line, $this->column);
            $this->q05();
            break;
        case '-':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::SUBTRACT), $this->line, $this->column);
            $this->q05();
            break;
        case '*':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MULTIPLY), $this->line, $this->column);
            $this->q05();
            break;
        case '/':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::DIVIDE), $this->line, $this->column);
            $this->q05();
            break;
        case '%':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MODULO), $this->line, $this->column);
            $this->q05();
            break;
        case '=':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ASSIGNMENT), $this->line, $this->column);
            $this->q06();
            break;
        case '<':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::LT), $this->line, $this->column);
            $this->q07();
            break;
        case '>':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::GT), $this->line, $this->column);
            $this->q08();
            break;
        case '!':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ERROR), $this->line, $this->column);
            $this->q13();
            break;
        case 'r':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q23();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q22();
    }

}



/**
 * Estado responsável pelo reconhecimento da palavra reservada print
 */
private function q23()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case 'i':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q24();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q23();
    }
}

/**
 * Estado responsável pelo reconhecimento da palavra reservada print
 */
private function q24()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case 'n':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q25();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q24();
    }
}
/**
 * Estado responsável pelo reconhecimento da palavra reservada print
 */
private function q25()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case 't':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::PRINT));
            $this->q32();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q25();
    }
}

/**
 * Estado responsável pelo reconhecimento da palavra reservada goto
 */
private function q26()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case '+':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ADD), $this->line, $this->column);
            $this->q05();
            break;
        case '-':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::SUBTRACT), $this->line, $this->column);
            $this->q05();
            break;
        case '*':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MULTIPLY), $this->line, $this->column);
            $this->q05();
            break;
        case '/':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::DIVIDE), $this->line, $this->column);
            $this->q05();
            break;
        case '%':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MODULO), $this->line, $this->column);
            $this->q05();
            break;
        case '=':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ASSIGNMENT), $this->line, $this->column);
            $this->q06();
            break;
        case '<':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::LT), $this->line, $this->column);
            $this->q07();
            break;
        case '>':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::GT), $this->line, $this->column);
            $this->q08();
            break;
        case '!':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ERROR), $this->line, $this->column);
            $this->q13();
            break;
        case 'o':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q27();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q26();
    }
}

/**
 * Estado responsável pelo reconhecimento da palavra reservada goto
 */
private function q27()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case 't':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q28();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q27();
    }
}

/**
 * Estado responsável pelo reconhecimento da palavra reservada goto
 */
private function q28()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case 'o':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::GOTO));
            $this->q32();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q28();
    }
}


/**
 * Estado responsável pelo reconhecimento da palavra reservada end
 */
private function q29()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case '+':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ADD), $this->line, $this->column);
            $this->q05();
            break;
        case '-':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::SUBTRACT), $this->line, $this->column);
            $this->q05();
            break;
        case '*':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MULTIPLY), $this->line, $this->column);
            $this->q05();
            break;
        case '/':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::DIVIDE), $this->line, $this->column);
            $this->q05();
            break;
        case '%':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MODULO), $this->line, $this->column);
            $this->q05();
            break;
        case '=':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ASSIGNMENT), $this->line, $this->column);
            $this->q06();
            break;
        case '<':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::LT), $this->line, $this->column);
            $this->q07();
            break;
        case '>':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::GT), $this->line, $this->column);
            $this->q08();
            break;
        case '!':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ERROR), $this->line, $this->column);
            $this->q13();
            break;
        case 'n':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q30();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q29();
    }
}

/**
 * Estado responsável pelo reconhecimento da palavra reservada end
 */
private function q30()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        case 'd':
            $this->lexeme->append($character, Symbol::fromUid(Symbol::END));
            $this->q32();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q30();
    }
}

/**
 * Estado responsável pelo reconhecimento da palavra reservada rem
 */
private function q31()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        default:
            $this->q31();
    }
}

/**
 * Estado responsável pelo reconhecimento das palavras reservadas
 * end
 * goto
 * if
 * input
 * let
 * print
 */
private function q32()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->addToken();
            $this->q04();
            break;
        case "\n":
            $this->addToken();
            $this->q03();
            break;
        case ' ':
            $this->addToken();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q32();
    }
}
/**
 * Estado responsável pelo reconhecimento do erro
 */
private function q99()
{
    $character = $this->next();

    switch ($character) {
        case "\0":
            $this->q04();
            break;
        case "\n":
            $this->q03();
            break;
        case ' ':
            break;
        case '0':
        case '1':
        case '2':
        case '3':
        case '4':
        case '5':
        case '6':
        case '7':
        case '8':
        case '9':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::INTEGER), $this->line, $this->column);
            $this->q01();
            break;
        case 'a':
        case 'b':
        case 'c':
        case 'd':
        case 'f':
        case 'h':
        case 'j':
        case 'k':
        case 'm':
        case 'n':
        case 'o':
        case 'q':
        case 's':
        case 't':
        case 'u':
        case 'v':
        case 'w':
        case 'x':
        case 'y':
        case 'z':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
            $this->q02();
            break;
        case 'r':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
            $this->q14();
            break;
        case 'i':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
            $this->q16();
            break;
        case 'l':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
            $this->q20();
            break;
        case 'p':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
            $this->q22();
            break;
        case 'g':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
            $this->q26();
            break;
        case 'e':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::VARIABLE), $this->line, $this->column);
            $this->q29();
            break;
        case '+':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ADD), $this->line, $this->column);
            $this->q05();
            break;
        case '-':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::SUBTRACT), $this->line, $this->column);
            $this->q05();
            break;
        case '*':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MULTIPLY), $this->line, $this->column);
            $this->q05();
            break;
        case '/':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::DIVIDE), $this->line, $this->column);
            $this->q05();
            break;
        case '%':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::MODULO), $this->line, $this->column);
            $this->q05();
            break;
        case '=':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ASSIGNMENT), $this->line, $this->column);
            $this->q06();
            break;
        case '<':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::LT), $this->line, $this->column);
            $this->q07();
            break;
        case '>':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::GT), $this->line, $this->column);
            $this->q08();
            break;
        case '!':
            $this->addToken();
            $this->lexeme = new Lexeme($character, Symbol::fromUid(Symbol::ERROR), $this->line, $this->column);
            $this->q13();
            break;
        default:
            $this->lexeme->append($character, Symbol::fromUid(Symbol::ERROR));
            $this->q99();
    }
}

public function readFileContent($filename)
{
    if (!file_exists($filename)) {
        die("O arquivo não foi encontrado: $filename");
    }
    return file_get_contents($filename);
}
}
