<?php
/**
 * Interface responsável pelas constantes utilizadas na fase de análise
 */
class Symbol
{
    // Constantes que representam os tipos de símbolos
    const LF = 10;
    const ETX = 3;
    const ASSIGNMENT = 11;
    const ADD = 21;
    const SUBTRACT = 22;
    const MULTIPLY = 23;
    const DIVIDE = 24;
    const MODULO = 25;
    const EQ = 31;
    const NE = 32;
    const GT = 33;
    const LT = 34;
    const GE = 35;
    const LE = 36;
    const VARIABLE = 41;
    const INTEGER = 51;
    const REM = 61;
    const INPUT = 62;
    const LET = 63;
    const PRINT = 64;
    const GOTO = 65;
    const IF = 66;
    const END = 67;
    const ERROR = 99;

    /**
     * Identificador do símbolo
     */
    private $uid;

    /**
     * Inicializar o símbolo
     *
     * @param int $uid identificador do símbolo
     */
    public function __construct(int $uid)
    {
        $this->uid = $uid;
    }

    /**
     * Retornar o identificador do símbolo
     *
     * @return int identificador do símbolo
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * Cria uma instância de Symbol com base na constante correspondente
     *
     * @param int $uid identificador do símbolo
     * @return Symbol instância correspondente ao identificador
     */
    public static function fromUid(int $uid): Symbol
    {
        switch ($uid) {
            case self::LF:
                return new self(self::LF);
            case self::ETX:
                return new self(self::ETX);
            case self::ASSIGNMENT:
                return new self(self::ASSIGNMENT);
            case self::ADD:
                return new self(self::ADD);
            case self::SUBTRACT:
                return new self(self::SUBTRACT);
            case self::MULTIPLY:
                return new self(self::MULTIPLY);
            case self::DIVIDE:
                return new self(self::DIVIDE);
            case self::MODULO:
                return new self(self::MODULO);
            case self::EQ:
                return new self(self::EQ);
            case self::NE:
                return new self(self::NE);
            case self::GT:
                return new self(self::GT);
            case self::LT:
                return new self(self::LT);
            case self::GE:
                return new self(self::GE);
            case self::LE:
                return new self(self::LE);
            case self::VARIABLE:
                return new self(self::VARIABLE);
            case self::INTEGER:
                return new self(self::INTEGER);
            case self::REM:
                return new self(self::REM);
            case self::INPUT:
                return new self(self::INPUT);
            case self::LET:
                return new self(self::LET);
            case self::PRINT:
                return new self(self::PRINT);
            case self::GOTO:
                return new self(self::GOTO);
            case self::IF:
                return new self(self::IF);
            case self::END:
                return new self(self::END);
            case self::ERROR:
                return new self(self::ERROR);
            default:
                throw new InvalidArgumentException("Símbolo desconhecido: " . $uid);
        }
    }
}
?>
