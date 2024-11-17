<?php
/**
 * Classe responsável pela representação de um lexema
 * Autores: Lucas Santos Dalmaso e André Santoro
 * Email's: lucassdalmaso25@gmail.com e asbnbm00@gmail.com
 */
class Lexeme
{
    /**
     * Termo lido do código-fonte
     */
    public $term;

    /**
     * Tipo/classe do lexema
     */
    public $type;

    /**
     * Número da linha no código-fonte
     */
    public $line;

    /**
     * Número da coluna no código-fonte
     */
    public $column;

    /**
     * Número da coluna no código-fonte
     */
    public $value;

    /**
     * Construtor para inicializar o lexema
     *
     * @param string $character caractere lido do código-fonte
     * @param Symbol $type tipo/classe do lexema
     * @param int $line número da linha no código-fonte
     * @param int $column número da coluna no código-fonte
     */
    public function __construct(string $character, Symbol $type, int $line, int $column, $value)
    {
        $this->term = $character;
        $this->type = $type;
        $this->line = $line;
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * Adicionar um caractere ao termo lido do código-fonte
     *
     * @param string $character caractere lido do código-fonte
     * @param Symbol $type tipo/classe do lexema
     */
    public function append(string $character, Symbol $type): void
    {
        $this->term .= $character;
        $this->type = $type;
    }

    /**
     * Retornar o termo lido do código-fonte
     *
     * @return string termo lido do código-fonte
     */
    public function getTerm(): string
    {
        return $this->term;
    }

    /**
     * Retornar o tipo/classe do lexema
     *
     * @return Symbol tipo/classe do lexema
     */
    public function getType(): Symbol
    {
        return $this->type;
    }

    /**
     * Retornar a representação em string do lexema
     *
     * @return string representação em string do lexema
     */
    public function __toString(): string
    {
        return "'" . $this->getTerm() . "' (" . $this->line . ", " . $this->column . ")";
    }

    /**
     * Retornar o token correspondente ao lexema - sem tabela de símbolo
     *
     * @return Token token correspondente ao lexema - sem tabela de símbolo
     */
    public function toToken(): Token
    {
        return new Token($this->type, $this->line, $this->column, $this->value);
    }

    /**
     * Retornar o token correspondente ao lexema - com tabela de símbolo
     *
     * @param int $address endereço na tabela de símbolos
     * @return Token token correspondente ao lexema - com tabela de símbolo
     */
    public function toTokenWithAddress(int $address): Token
    {
        return new Token($this->type, $this->line, $this->column, $this->value, $address);
    }
}
?>
