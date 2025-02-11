<?php

namespace App\Http\Controllers;

use App\Exports\ClassContentEngagementReportExport;
use App\Exports\ClassContentUsageReportExport;
use App\Exports\ClassHeatmapReportExport;
use App\Exports\ClassTrialsReportExport;
use App\Exports\SchoolContentGapReportExport;
use App\Exports\StudentCompletionReportExport;
use App\Exports\StudentContentEngagementReportExport;
use App\Exports\StudentHeatmapReportExport;
use App\Exports\StudentTrialsReportExport;
use App\Exports\TeacherContentCoverageReportExport;
use App\Exports\TeacherContentEngagementReportExport;
use App\Exports\StudentMasteryReportExport;
use App\Exports\TeacherCompletionReportExport;
use App\Exports\TeacherHeatmapReportExport;
use App\Exports\TeacherMasteryReportExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\SchoolCompletionReportExport;
use App\Exports\ClassCompletionReportExport;
use App\Exports\ClassMasteryReportExport;
use App\Exports\LoginReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportExcelController extends Controller
{
    public function exportSchoolCompletionReport($sessionKey)
    {
        $tests = session($sessionKey, []);

        return Excel::download(new SchoolCompletionReportExport($tests), 'School_Completion_Report.xlsx');
    }
    public function exportClassCompletionReport($sessionKey)
    {
        $tests = session($sessionKey, []);

        return Excel::download(new ClassCompletionReportExport($tests), 'Class_Completion_Report.xlsx');
    }
    public function exportStudentCompletionReport($sessionKey)
    {
        $tests = session($sessionKey, []);

        return Excel::download(new StudentCompletionReportExport($tests), 'Student_Completion_Report.xlsx');
    }
    public function exportTeacherCompletionReport($sessionKey)
    {
        $tests = session($sessionKey, []);

        return Excel::download(new TeacherCompletionReportExport($tests), 'Teacher_Completion_Report.xlsx');
    }
    public function exportClassMasteryReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new ClassMasteryReportExport($exportData, $exportHeaders), 'Class_Mastery_Report.xlsx');
    }
    public function exportStudentMasteryReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new StudentMasteryReportExport($exportData, $exportHeaders), 'Student_Mastery_Report.xlsx');
    }
    public function exportTeacherMasteryReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new TeacherMasteryReportExport($exportData, $exportHeaders), 'Teacher_Students_Mastery_Report.xlsx');
    }
    public function exportSchoolContentEngagementReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new TeacherMasteryReportExport($exportData, $exportHeaders), 'School_Content_Engagement_Report.xlsx');
    }
    public function exportStudentContentEngagementReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new StudentContentEngagementReportExport($exportData, $exportHeaders), 'Student_Content_Engagement_Report.xlsx');
    }
    public function exportTeacherContentEngagementReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new TeacherContentEngagementReportExport($exportData, $exportHeaders), 'Teacher_Content_Engagement_Report.xlsx');
    }
    public function exportClassContentEngagementReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new ClassContentEngagementReportExport($exportData, $exportHeaders), 'Class_Content_Engagement_Report.xlsx');
    }
    public function exportClassTrialsReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new ClassTrialsReportExport($exportData, $exportHeaders), 'Class_Trials_Report.xlsx');
    }
    public function exportStudentTrialsReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new StudentTrialsReportExport($exportData, $exportHeaders), 'Student_Trials_Report.xlsx');
    }
    public function exportSchoolContentGapReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new SchoolContentGapReportExport($exportData, $exportHeaders), 'School_Content_Gap_Report.xlsx');
    }
    public function exportClassContentGapReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new SchoolContentGapReportExport($exportData, $exportHeaders), 'Class_Content_Gap_Report.xlsx');
    }
    public function exportClassContentUsageReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new ClassContentUsageReportExport($exportData, $exportHeaders), 'Class_Content_Usage_Report.xlsx');
    }
    public function exportTeacherContentCoverageReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new TeacherContentCoverageReportExport($exportData, $exportHeaders), 'Teacher_Content_Coverage_Report.xlsx');
    }
    public function exportClassHeatmapReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new ClassHeatmapReportExport($exportData, $exportHeaders), 'Class_Heatmap_Report.xlsx');
    }
    public function exportTeacherHeatmapReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new TeacherHeatmapReportExport($exportData, $exportHeaders), 'Teacher_Heatmap_Report.xlsx');
    }
    public function exportStudentHeatmapReport($sessionKey)
    {
        $exportData = session($sessionKey, []);
        $exportHeaders = session($sessionKey . '_headers', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new StudentHeatmapReportExport($exportData, $exportHeaders), 'Student_Heatmap_Report.xlsx');
    }
    public function exportTeacherLoginReport($sessionKey)
    {
        $exportData = session('teacher_' . $sessionKey, []);
        $exportHeaders = session($sessionKey . '_teacherheaders', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new LoginReportExport($exportData, $exportHeaders), 'Teachers_Login_Report.xlsx');
    }
    public function exportStudentLoginReport($sessionKey)
    {
        $exportData = session('student_' . $sessionKey, []);
        $exportHeaders = session($sessionKey . '_studentheaders', []);

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data available for export.');
        }

        return Excel::download(new LoginReportExport($exportData, $exportHeaders), 'Students_Login_Report.xlsx');
    }
}
