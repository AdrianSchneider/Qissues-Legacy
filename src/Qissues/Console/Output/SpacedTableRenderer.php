<?php

namespace Qissues\Console\Output;

class SpacedTableRenderer
{
    public function __construct()
    {
        $this->rows = array();
        $this->columnSizes = array();
    }

    public function addRow(array $columns)
    {
        foreach ($columns as $i => $column) {
            $len = mb_strlen(strip_tags($column), 'utf-8');
            if (empty($this->columnSizes[$i]) or $len > $this->columnSizes[$i]) {
                $this->columnSizes[$i] = $len;
            }
        }

        $this->rows[] = $columns;
    }

    public function render()
    {
        $rows = array();
        foreach ($this->rows as $rowNum => $row) {
            foreach ($row as $columnNum => $column) {
                $row[$columnNum] = str_pad((string)$column, $this->columnSizes[$columnNum]);
            }
            $rows[] = implode(' ', $row);
        }

        return implode("\n", $rows);
    }
}
