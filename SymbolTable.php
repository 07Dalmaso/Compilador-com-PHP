<?php
class SymbolTable {
    private $symbols = [];

    public function addSymbol($symbol, $type, $location) {
        foreach ($this->symbols as $entry) {
            if ($entry['symbol'] === $symbol && $entry['type'] === $type) {
                return $entry;
            }
        }

        $entry = [
            'symbol' => $symbol,
            'type' => $type,
            'location' => $location
        ];
        $this->symbols[] = $entry;
        return $entry;
    }

    public function __toString() {
        foreach ($this->symbols as $entry) {
            echo $entry['symbol'] . ' , ' . $entry['type'] . ' , ' . $entry['location'] . PHP_EOL;
        }
        return '';
    }
}
?>
