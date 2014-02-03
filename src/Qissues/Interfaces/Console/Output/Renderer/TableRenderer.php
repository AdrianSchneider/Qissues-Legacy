<?php

namespace Qissues\Interfaces\Console\Output\Renderer;

class TableRenderer
{
    protected $chars = array(
        'top'          => '─',
        'top-mid'      => '┬',
        'top-left'     => '┌',
        'top-right'    => '┐',
        'bottom'       => '─',
        'bottom-mid'   => '┴',
        'bottom-left'  => '└',
        'bottom-right' => '┘',
        'left'         => '│',
        'left-mid'     => '├',
        'mid'          => '─',
        'mid-mid'      => '┼',
        'right'        => '│',
        'right-mid'    => '┤',
        'middle'       => '│',
        'truncate'     => '…'
    );

    private $data;
    private $lengths;

    public function render(array $data, $width)
    {
        $this->data = $data;
        $this->lengths = array();

        $rows = array();
        $rows[0] = array_keys($data[0]);

        foreach ($data as $info) {
            $rows[] = array_values(array_map('trim', $info));
        }

        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                if (!isset($this->lengths[$key]) or mb_strlen($value, 'utf-8') > $this->lengths[$key]) {
                    $this->lengths[$key] = mb_strlen($value, 'utf-8');
                }
            }
        }

        foreach ($rows as $i => $row) {
            foreach ($row as $key => $value) {
                $rows[$i][$key] = $this->pad($value, $this->lengths[$key], ' ');
                if (!$i) {
                    $rows[$i][$key] = str_replace((string)$value, "<info>$value</info>", $rows[$i][$key]);
                } else if (!$key) {
                    $rows[$i][$key] = str_replace((string)$value, "<comment>$value</comment>", $rows[$i][$key]);
                }
            }
        }

        $lines = array();
        $lines[] = $this->drawLine($this->chars['top-left'], $this->chars['top-mid'], $this->chars['mid'], $this->chars['top-right']);

        foreach ($rows as $i => $row) {
            $lines[] = ' ' . $this->chars['middle'] . ' ' . implode(' ' . $this->chars['middle'] . ' ', $row) . ' ' . $this->chars['middle'] .  ' ';
            if (!$i) {
                $lines[] = $this->drawLine($this->chars['left-mid'], $this->chars['mid-mid'], $this->chars['mid'], $this->chars['right-mid']);
            }
        }

        $lines[] = $this->drawLine($this->chars['bottom-left'], $this->chars['bottom-mid'], $this->chars['mid'], $this->chars['bottom-right']);
        return $lines;
    }

    /**
     * Draw separator line
     *
     * @param string $left border
     * @param string $separator middle border
     * @param string $main line
     * @param string $right border
     * @return string rendered divider
     */
    protected function drawLine($left, $separator, $main, $right)
    {
        $center = '';
        $last = count($this->lengths) - 1;
        foreach ($this->lengths as $i => $length) {
            $center .= str_repeat($main, $length + 2) ;

            if ($i != $last) {
                $center .= $separator;
            }
        }

        return " $left$center$right ";
    }

    /**
     * Basic utf-8 compatible padder
     *
     * @param string $str
     * @param integer $len length to pad until
     * @param string $with pad with string
     * @return $str padding with $with to length $len
     */
    protected function pad($str, $len, $with)
    {
        while (mb_strlen($str, 'utf-8') < $len) {
            $str .= $with;
        }

        return $str;
    }
}
