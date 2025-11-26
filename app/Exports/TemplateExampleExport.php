<?php

namespace App\Exports;

use App\Models\Template;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class TemplateExampleExport implements FromArray, WithHeadings, WithColumnWidths
{
    protected Template $template;

    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    protected function getLabelFromItem($item): string
    {
        if (is_array($item)) {
            if (isset($item['value']) && $item['value'] !== '') {
                return (string)$item['value'];
            }
            if (isset($item['key']) && $item['key'] !== '') {
                return (string)$item['key'];
            }
            
            foreach ($item as $v) {
                if ($v !== null && $v !== '') {
                    return (string)$v;
                }
            }
            return 'Column';
        }

        if (is_object($item)) {
            if (isset($item->value) && $item->value !== '') {
                return (string)$item->value;
            }
            if (isset($item->key) && $item->key !== '') {
                return (string)$item->key;
            }
            if (method_exists($item, '__toString')) {
                return (string)$item;
            }
            return 'Column';
        }

        if (is_scalar($item) && $item !== '') {
            return (string)$item;
        }

        return 'Column';
    }

    public function headings(): array
    {
        $variables = $this->template->variables ?? [];
        $labels = [];

        foreach ($variables as $item) {
            $labels[] = $this->getLabelFromItem($item);
        }

        $labels[] = 'Telefon raqami';

        return $labels;
    }

    public function array(): array
    {
        $variables = $this->template->variables ?? [];
        $row = [];

        foreach ($variables as $item) {
            $row[] = '';
        }

        $row[] = '';

        return [$row];
    }

    protected function columnLetter(int $index): string
    {
        $index--; 
        $letters = '';
        while ($index >= 0) {
            $mod = $index % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = intdiv($index, 26) - 1;
        }
        return $letters;
    }

    public function columnWidths(): array
    {
        $variables = $this->template->variables ?? [];
        $count = count($variables) + 1;

        $widths = [];
        for ($i = 1; $i <= $count; $i++) {
            $col = $this->columnLetter($i);
            $widths[$col] = 30;
        }

        return $widths;
    }
}
