<?php
/**
 * Classe responsável pela representação de um token
 * Autores: Lucas Santos Dalmaso e André Santoro
 * Email's: lucassdalmaso25@gmail.com e asbnbm00@gmail.com
 */
class Token
{
    /**
     * Tipo/classe do token
     */
    private $type;

    /**
     * Endereço na tabela de símbolos
     */
    private $address;

    /**
     * Número da linha no código-fonte
     */
    private $line;

    /**
     * Número da coluna no código-fonte
     */
    private $column;


    /**
     * Número da coluna no código-fonte
     */
    private $value;

    /**
     * Construtor para inicializar o token - sem tabela de símbolo
     *
     * @param Symbol $type tipo/classe do token
     * @param int $line número da linha no código-fonte
     * @param int $column número da coluna no código-fonte
     */
    public function __construct(Symbol $type, int $line, int $column, $value,  int $address = -1)
    {
        $this->type = $type;
        $this->address = $address;
        $this->line = $line;
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * Retornar o tipo/classe do token
     *
     * @return Symbol tipo/classe do token
     */
    public function getType(): Symbol
    {
        return $this->type;
    }

    /**
     * Retornar o endereço na tabela de símbolos
     *
     * @return int endereço na tabela de símbolos
     */
    public function getAddress(): int
    {
        return $this->address;
    }

    /**
     * Retornar o endereço na tabela de símbolos
     *
     * @return int endereço na tabela de símbolos
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Retornar o número da linha no código-fonte
     *
     * @return int número da linha no código-fonte
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Retornar o número da coluna no código-fonte
     *
     * @return int número da coluna no código-fonte
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
     * Retornar a representação em string do token
     *
     * @return string representação em string do token
     */
    public function __toString(): string
    {
        if ($this->getAddress() != -1) {
            return "[" . $this->getType()->getUid() . ", " . $this->getAddress() . ", (" . $this->getLine() . ", " . $this->getColumn() . ")]";
        } else {
            return "[" . $this->getType()->getUid() . ", , (" . $this->getLine() . ", " . $this->getColumn() . ")]";
        }
    }
}
?>
