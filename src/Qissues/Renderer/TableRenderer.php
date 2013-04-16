<?php

namespace Qissues\Renderer;

class TableRenderer
{
    public function render(array $data, $width)
    {
        $rows = array();
        $rows[0] = array_keys($data[0]);

        foreach ($data as $info) {
            $rows[] = array_values($info);
        }

        $lengths = array();
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                if (!isset($lengths[$key]) or strlen($value) > $lengths[$key]) {
                    $lengths[$key] = strlen($value);
                }
            }
        }

        foreach ($rows as $i => $row) {
            foreach ($row as $key => $value) {
                $rows[$i][$key] = str_pad($value, $lengths[$key], ' ');
            }
        }

        $lines = array();
        $lines[] =' +' . str_repeat('-', $width - 4) . '+ ';
        foreach ($rows as $i => $row) {
            $lines[] = ' | ' . implode(' | ', $row) . ' | ';
            if (!$i) {
                $lines[] = ' +' . str_repeat('-', $width - 4) . '+ ';
            }
        }
        $lines[] = ' +' . str_repeat('-', $width - 4) . '+ ';

        return $lines;
    }
}
