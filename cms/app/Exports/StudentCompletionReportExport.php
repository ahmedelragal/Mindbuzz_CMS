<?php

namespace App\Exports;

use App\Models\Group;
use App\Models\GroupStudent;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentCompletionReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $tests;

    public function __construct($tests)
    {
        $this->tests = $tests;
    }

    public function collection()
    {
        return collect($this->tests);
    }

    public function headings(): array
    {
        return ['Student Name', 'Class Name', 'Test Name', 'Start Date', 'Due Date', 'Status'];
    }

    public function map($test): array
    {
        $groupId = GroupStudent::where('student_id', $test->student_id)->value('group_id');
        $groupName = $groupId ? Group::find($groupId)?->name : null;
        return [
            optional(\App\Models\User::find($test['student_id']))->name,
            $groupName,
            $test['tests']['name'],
            $test['start_date'],
            $test['due_date'],
            $test['status'] == 1 ? 'Completed' : (\Carbon\Carbon::parse($test['due_date'])->endOfDay()->isPast() ? 'Overdue' : 'Pending'),
        ];
    }
}
