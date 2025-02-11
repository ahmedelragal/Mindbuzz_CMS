<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TeacherMasteryReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $headers;

    public function __construct($data, $headers)
    {
        $this->data = $data;
        $this->headers = $headers;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function map($row): array
    {
        return array_map(function ($value) {
            return ($value === 0 || $value === '0') ? '0' : $value;
        }, array_values($row));
    }
}
