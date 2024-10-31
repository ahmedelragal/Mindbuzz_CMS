<?php

namespace App\Http\Controllers;

use App\Models\GameType;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Program;
use App\Models\School;
use App\Models\Skills;
use App\Models\Stage;
use Illuminate\Http\Request;
use App\Models\StudentTest;
use App\Models\StudentDegree;
use App\Models\Game;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\Test;
use App\Models\StudentProgress;
use App\Models\GroupTeachers;
use App\Models\TeacherProgram;
use App\Models\UserCourse;
use App\Models\TestTypes;
use App\Models\User;
use App\Models\TestQuestion;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // $query = User::with(['details.stage', 'userCourses.program', 'groups'])
        //     ->where('hasRole', '2');
        if (Auth::user()->hasRole('school')) {
            $query = User::with(['details.stage', 'userCourses.program', 'groups'])
                ->where('role', '2')->where("school_id", Auth::user()->school_id);
        } else {
            $query = User::with(['details.stage', 'userCourses.program', 'groups'])
                ->where('role', '2');
        }

        if ($request->filled('school')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('school_id', $request->input('school'));
            });
        }

        if ($request->filled('program')) {
            $query->whereHas('userCourses', function ($q) use ($request) {
                $q->where('program_id', $request->input('program'));
            });
        }

        if ($request->filled('grade')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('stage_id', $request->input('grade'));
            });
        }

        if ($request->filled('group')) {
            $query->whereHas('groups', function ($q) use ($request) {
                $q->where('group_id', $request->input('group'));
            });
        }

        $students = $query->get();

        $schools = School::all();
        if (Auth::user()->hasRole('school')) {
            $programs = Program::with('course', 'stage')->when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $programs = Program::with('course', 'stage')->get();
        }
        $grades = Stage::all();

        if (Auth::user()->hasRole('school')) {
            $classes = Group::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $classes = Group::all();
        }
        $units = Unit::all();
        $lessons = Lesson::all();
        $games = Game::all();
        $skills = Skills::all();
        $testTypes = TestTypes::all();

        // Initialize $response and $data with default values
        $response = [
            'skills' => [],
            'units' => [],
            'lessons' => [],
            'games' => [],
            'tprogress' => [],
            'trials' => [],
            'skillsData' => [],
        ];

        $data = [
            'student_latest' => '',
            'counts' => [
                'completed' => 0,
                'overdue' => 0,
                'pending' => 0,
            ],
            'assignments_percentages' => [
                'completed' => 0,
                'overdue' => 0,
                'pending' => 0,
            ],
            'tests' => [],
            'test_types' => [],
            'skillsData' => [],
            'tprogress' => [],

        ];
        // return response()->json($students->first()->groups);
        return view(
            'dashboard.reports.index',
            compact(
                'students',
                'schools',
                'programs',
                'games',
                'response',
                'lessons',
                'units',
                'grades',
                'classes',
                'testTypes',
                'data',
                'skills'
            )
        );
    }


    public function completionReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $programs = Program::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $programs = Program::all();
        }

        $schools = School::all();
        // Initialize $data array with defaults
        $data = [
            'schools' => $schools,
            'programs' => $programs,
            'counts' => [
                'completed' => 0,
                'overdue' => 0,
                'pending' => 0,
            ],
            'assignments_percentages' => [
                'completed' => 0,
                'overdue' => 0,
                'pending' => 0,
            ],
            'tests' => [],
            'request' => $request->all(),
        ];

        $data = [
            'schools' => $schools,
            'programs' => $programs,
            'request' => $request->all(),
        ];

        if ($request->has('student_id')) {
            // Initialize the query builder for student progress
            $progressQuery = StudentTest::with('tests')->where('student_id', $request->student_id);

            if ($progressQuery->get()->isEmpty()) {
                // return redirect()->back()->with('error', 'No Student Progress Found.')->withInput();
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.student.student_completion_report', $data);
            }

            // Filter by program ID if provided
            if ($request->filled('program_id') && $request->program_id != NULL) {
                $progressQuery->where('program_id', $request->program_id);
            }
            if ($progressQuery->get()->isEmpty()) {
                $data['error'] = 'No Results Found for this Program';
                return view('dashboard.reports.student.student_completion_report', $data);
            }

            // Add the 'to_date' filter if it exists
            $progressQuery->when($request->filled('to_date'), function ($progressQuery) use ($request) {
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                return $progressQuery->where('created_at', '<=', $toDate);
            });

            if ($progressQuery->get()->isEmpty()) {
                $data['error'] = 'No Results Found for this Date';
                return view('dashboard.reports.student.student_completion_report', $data);
            }


            if ($progressQuery->get()->isEmpty()) {
                $data['error'] = 'No Results Found for this Date';
                return view('dashboard.reports.student.student_completion_report', $data);
            }
            // Execute the query
            $allTests = $progressQuery->orderBy('due_date', 'DESC')->get();
            $totalAllTests = $allTests->count();
            $finishedCount = $allTests->where('status', 1)->count();
            $overdueCount = $allTests->where('due_date', '<', \Carbon\Carbon::now()->format('Y-m-d'))
                ->where('status', '!=', 1)
                ->count();
            $pendingCount = $totalAllTests - $finishedCount - $overdueCount;

            // Calculate percentages as integers
            $finishedPercentage = $totalAllTests > 0 ? round(($finishedCount / $totalAllTests) * 100, 2) : 0;
            $overduePercentage = $totalAllTests > 0 ? round(($overdueCount / $totalAllTests) * 100, 2) : 0;
            $pendingPercentage = $totalAllTests > 0 ? round(($pendingCount / $totalAllTests) * 100, 2) : 0;

            // Filter by status if provided
            if ($request->filled('status') && $request->status != NULL) {
                $now = \Carbon\Carbon::now();
                $status = $request->status;
                switch ($status) {
                    case 'Completed':
                        $progressQuery->where('status', '1');
                        if ($progressQuery->get()->isEmpty()) {
                            $data['error'] = 'No Results Found';
                            return view('dashboard.reports.student.student_completion_report', $data);
                        }
                        break;
                    case 'Overdue':
                        $progressQuery->where('due_date', '<', $now->format('Y-m-d'))->where('status', '!=', 1);
                        if ($progressQuery->get()->isEmpty()) {
                            $data['error'] = 'No Results Found';
                            return view('dashboard.reports.student.student_completion_report', $data);
                        }
                        break;
                    case 'Pending':
                        $progressQuery->where('status', '0')->where('due_date', '>=', $now->format('Y-m-d'));
                        if ($progressQuery->get()->isEmpty()) {
                            $data['error'] = 'No Results Found';
                            return view('dashboard.reports.student.student_completion_report', $data);
                        }
                        break;
                    default:
                        // Invalid status provided
                        break;
                }
            }
            // Execute the query
            $tests = $progressQuery->orderBy('due_date', 'DESC')->get();

            // Prepare response data
            $data['counts'] = [
                'completed' => $finishedCount,
                'overdue' => $overdueCount,
                'pending' => $pendingCount,
            ];
            $data['assignments_percentages'] = [
                'completed' => ceil($finishedPercentage),
                'overdue' => floor($overduePercentage),
                'pending' => ceil($pendingPercentage),
            ];
            $data['tests'] = $tests;

            // $tests = $query->get();
            // $totalTests = $tests->count();
            // $completedTests = $tests->where('status', 1)->count();
            // $overdueTests = $tests->where('due_date', '<', now())->where('status', '!=', 1)->count();
            // $pendingTests = $totalTests - $completedTests - $overdueTests;

            // $data['counts']['completed'] = $completedTests;
            // $data['counts']['overdue'] = $overdueTests;
            // $data['counts']['pending'] = $pendingTests;

            // $data['assignments_percentages']['completed'] = $totalTests > 0 ? round(($completedTests / $totalTests) * 100, 2) : 0;
            // $data['assignments_percentages']['overdue'] = $totalTests > 0 ? round(($overdueTests / $totalTests) * 100, 2) : 0;
            // $data['assignments_percentages']['pending'] = $totalTests > 0 ? round(($pendingTests / $totalTests) * 100, 2) : 0;
            // $data['tests'] = $tests;



            // $data = [
            //     'student_latest' => 'Some latest progress data', // Replace with actual latest progress data
            //     'counts' => [
            //         'completed' => $completedTests,
            //         'overdue' => $overdueTests,
            //         'pending' => $pendingTests,
            //     ],
            //     'assignments_percentages' => [
            //         'completed' => $totalTests > 0 ? round(($completedTests / $totalTests) * 100, 2) : 0,
            //         'overdue' => $totalTests > 0 ? round(($overdueTests / $totalTests) * 100, 2) : 0,
            //         'pending' => $totalTests > 0 ? round(($pendingTests / $totalTests) * 100, 2) : 0,
            //     ],
            //     'tests' => $tests,
            //     'schools' => $schools,
            //     'programs' => $programs,
            //     'request' => $request->all(),
            // ];
        }

        // dd($data);
        return view('dashboard.reports.student.student_completion_report', $data);
    }

    public function masteryReport(Request $request)
    {
        $schools = School::all();
        $data = [
            'schools' => $schools,
            'request' => $request->all()
        ];

        if ($request->has('student_id')) {
            // Initialize query builder for student progress
            $query = StudentProgress::where('student_id', $request->student_id)
                ->where('program_id', $request->program_id);

            if ($query->get()->isEmpty()) {
                // return redirect()->back()->with('error', 'No Student Progress Found.');
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.student.student_mastery_report', $data);
            }

            // Apply filters if provided
            if ($request->has('unit_id')) {
                $query->where('unit_id', $request->unit_id);
            }
            if ($request->has('lesson_id')) {
                $query->where('lesson_id', $request->lesson_id);
            }
            if ($request->has('game_id')) {
                $query->whereHas('test', function ($q) use ($request) {
                    $q->where('game_id', $request->game_id);
                });
            }
            if ($request->has('skill_id')) {
                $query->whereHas('test.game.gameTypes.skills', function ($q) use ($request) {
                    $q->where('skill_id', $request->skill_id);
                });
            }

            // Add the 'from_date' filter if it exists
            $query->when($request->filled('from_date'), function ($query) use ($request) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                return $query->where('created_at', '>=', $fromDate);
            });

            // Add the 'to_date' filter if it exists
            $query->when($request->filled('to_date'), function (self $query) use ($request) {
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                return $query->where('created_at', '<=', $toDate);
            });


            $student_progress = $query->get();

            // Initialize arrays to hold data for grouping
            $unitsMastery = [];
            $lessonsMastery = [];
            $gamesMastery = [];
            $skillsMastery = [];

            if ($student_progress->isEmpty()) {
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.student.student_mastery_report', $data);
            }
            foreach ($student_progress as $progress) {
                // Retrieve the test and its related game, game type, and skills
                $test = Test::with(['game.gameTypes.skills.skill'])->where('lesson_id', $progress->lesson_id)->find($progress->test_id);

                // Check if the test and its relationships are properly loaded
                if (!$test || !$test->game || !$test->game->gameTypes) {
                    continue; // Skip to the next progress record if any of these are null
                }

                // Get the game type (since each game has one game type)
                $gameType = $test->game->gameTypes;

                // Group by unit
                if (!isset($unitsMastery[$progress->unit_id])) {
                    $unitsMastery[$progress->unit_id] = [
                        'unit_id' => $progress->unit_id,
                        'name' => Unit::find($progress->unit_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                        'lessons' => [],
                    ];
                }

                // Group by lesson
                if (!isset($lessonsMastery[$progress->lesson_id])) {
                    $lessonsMastery[$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'name' => Unit::find(Lesson::find($progress->lesson_id)->unit_id)->name . " | " . Lesson::find($progress->lesson_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                        'games' => [],
                    ];
                }

                // Group by game type
                if (!isset($gameTypesMastery[$gameType->id])) {
                    $gameTypesMastery[$gameType->id] = [
                        'game_type_id' => $gameType->id,
                        'name' => GameType::find($gameType->id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'count' => 0,
                        'total_score' => 0,
                        'games' => [],
                    ];
                }

                // Group by game within the game type
                if (!isset($gameTypesMastery[$gameType->id]['games'][$test->game_id])) {
                    $gameTypesMastery[$gameType->id]['games'][$test->game_id] = [
                        'game_id' => $test->game_id,
                        'name' => Game::find($test->game_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'count' => 0,
                        'total_score' => 0,
                    ];
                }

                // Group by skill
                if ($gameType && $gameType->skills) {
                    foreach ($gameType->skills->where('lesson_id', $progress->lesson_id)->unique('skill') as $gameSkill) {
                        $skill = $gameSkill->skill;

                        if (!isset($skillsMastery[$skill->id])) {
                            $skillsMastery[$skill->id] = [
                                'skill_id' => $skill->id,
                                'name' => $skill->skill,
                                'failed' => 0,
                                'introduced' => 0,
                                'practiced' => 0,
                                'mastered' => 0,
                                'total_attempts' => 0,
                                'total_score' => 0,
                                'mastery_percentage' => 0,
                            ];
                        }

                        $skillsMastery[$skill->id]['total_attempts']++;
                        if ($progress->is_done) {
                            if ($progress->score >= 80) {
                                $skillsMastery[$skill->id]['mastered']++;
                            } elseif ($progress->score >= 60) {
                                $skillsMastery[$skill->id]['practiced']++;
                            } elseif ($progress->score >= 30) {
                                $skillsMastery[$skill->id]['introduced']++;
                            } else {
                                $skillsMastery[$skill->id]['failed']++;
                            }
                        } else {
                            $skillsMastery[$skill->id]['failed']++;
                        }
                        $skillsMastery[$skill->id]['total_score'] += $progress->score;
                    }
                }

                // Update totals for units, lessons, and game types
                $unitsMastery[$progress->unit_id]['total_attempts']++;
                $lessonsMastery[$progress->lesson_id]['total_attempts']++;
                $gameTypesMastery[$gameType->id]['total_attempts']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_attempts']++;

                if ($progress->is_done) {
                    if ($progress->score >= 80) {
                        $unitsMastery[$progress->unit_id]['mastered']++;
                        $lessonsMastery[$progress->lesson_id]['mastered']++;
                        $gameTypesMastery[$gameType->id]['mastered']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['mastered']++;
                    } elseif ($progress->score >= 60) {
                        $unitsMastery[$progress->unit_id]['practiced']++;
                        $lessonsMastery[$progress->lesson_id]['practiced']++;
                        $gameTypesMastery[$gameType->id]['practiced']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['practiced']++;
                    } elseif ($progress->score >= 30) {
                        $unitsMastery[$progress->unit_id]['introduced']++;
                        $lessonsMastery[$progress->lesson_id]['introduced']++;
                        $gameTypesMastery[$gameType->id]['introduced']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['introduced']++;
                    } else {
                        $unitsMastery[$progress->unit_id]['failed']++;
                        $lessonsMastery[$progress->lesson_id]['failed']++;
                        $gameTypesMastery[$gameType->id]['failed']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
                    }
                } else {
                    $unitsMastery[$progress->unit_id]['failed']++;
                    $lessonsMastery[$progress->lesson_id]['failed']++;
                    $gameTypesMastery[$gameType->id]['failed']++;
                    $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
                }

                $unitsMastery[$progress->unit_id]['total_score'] += $progress->score;
                $lessonsMastery[$progress->lesson_id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['count']++;

                // Group lessons under units
                if (!isset($unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id])) {
                    $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                    ];
                }

                // Aggregate lesson data under the unit
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['failed'] += $lessonsMastery[$progress->lesson_id]['failed'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['introduced'] += $lessonsMastery[$progress->lesson_id]['introduced'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['practiced'] += $lessonsMastery[$progress->lesson_id]['practiced'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['mastered'] += $lessonsMastery[$progress->lesson_id]['mastered'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_attempts'] += $lessonsMastery[$progress->lesson_id]['total_attempts'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_score'] += $lessonsMastery[$progress->lesson_id]['total_score'];
            }

            // Ensure all lessons are included in units
            foreach ($unitsMastery as &$unit) {
                foreach ($lessonsMastery as $lessonId => $lessonData) {
                    if (!isset($unit['lessons'][$lessonId])) {
                        $unit['lessons'][$lessonId] = [
                            'lesson_id' => $lessonId,
                            'failed' => 0,
                            'introduced' => 0,
                            'practiced' => 0,
                            'mastered' => 0,
                            'total_attempts' => 0,
                            'total_score' => 0,
                            'mastery_percentage' => 0,
                        ];
                    }
                }
            }
            // Calculate mastery percentages for units, lessons, games, and game types
            $unitChartLabels = [];
            $unitChartPercentage = [];
            foreach ($unitsMastery as &$unitData) {
                $unitData['mastery_percentage'] = $unitData['total_attempts'] > 0 ? round(($unitData['total_score'] / $unitData['total_attempts']), 2) : 0;

                foreach ($unitData['lessons'] as &$lessonData) {
                    $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? round(($lessonData['total_score'] / $lessonData['total_attempts']), 2) : 0;
                }

                $unitData['lessons'] = array_values($unitData['lessons']); // Convert lessons to array
                array_push($unitChartLabels, $unitData['name']);
                array_push($unitChartPercentage, $unitData['mastery_percentage']);
            }
            $lessonChartPercentage = [];
            $lessonChartLabels = [];
            foreach ($lessonsMastery as &$lessonData) {
                $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? round(($lessonData['total_score'] / $lessonData['total_attempts']), 2) : 0;
                array_push($lessonChartLabels, $lessonData['name']);
                array_push($lessonChartPercentage, $lessonData['mastery_percentage']);
            }
            $gameChartPercentage = [];
            $gameChartLabels = [];
            foreach ($gameTypesMastery as &$gameTypeData) {
                foreach ($gameTypeData['games'] as &$gameData) {
                    $gameData['mastery_percentage'] = $gameData['total_attempts'] > 0 ? round(($gameData['total_score'] / $gameData['total_attempts']), 2) : 0;
                    array_push($gameChartLabels, $gameData['name']);
                    array_push($gameChartPercentage, $gameData['mastery_percentage']);
                }
                $gameTypeData['games'] = array_values($gameTypeData['games']); // Convert games to array

                $gameTypeData['mastery_percentage'] = $gameTypeData['total_attempts'] > 0 ? round(($gameTypeData['total_score'] / $gameTypeData['total_attempts']), 2) : 0;
            }

            // Calculate skill mastery level based on mastered, practiced, introduced, and failed counts
            $skillChartPercentage = [];
            $skillChartLabels = [];
            foreach ($skillsMastery as &$skillData) {
                if ($skillData['mastered'] > $skillData['practiced'] && $skillData['mastered'] > $skillData['introduced'] && $skillData['mastered'] > $skillData['failed']) {
                    $skillData['current_level'] = 'mastered';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : round($skillData['total_score'] / $skillData['total_attempts'], 2);
                } elseif ($skillData['practiced'] > $skillData['introduced'] && $skillData['practiced'] > $skillData['failed']) {
                    $skillData['current_level'] = 'practiced';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : round($skillData['total_score'] / $skillData['total_attempts'], 2);
                } elseif ($skillData['introduced'] > $skillData['failed']) {
                    $skillData['current_level'] = 'introduced';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : round($skillData['total_score'] / $skillData['total_attempts'], 2);
                } else {
                    $skillData['current_level'] = 'failed';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : round($skillData['total_score'] / $skillData['total_attempts'], 2);
                }
                array_push($skillChartLabels, $skillData['name']);
                array_push($skillChartPercentage, $skillData['mastery_percentage']);
            }
            // Prepare the response data
            if ($request->has('filter')) {
                switch ($request->filter) {
                    case 'Skill':
                        if ($skillsMastery) {
                            $data['skills'] = array_values($skillsMastery);
                            $data['chartLabels'] = $skillChartLabels;
                            $data['chartPercentage'] = $skillChartPercentage;
                        } else {
                            $data['error'] = "No Skills Found";
                            return view('dashboard.reports.student.student_mastery_report', $data);
                        }

                        break;
                    case 'Unit':
                        $data['units'] = array_values($unitsMastery);
                        $data['chartLabels'] = $unitChartLabels;
                        $data['chartPercentage'] = $unitChartPercentage;
                        break;
                    case 'Lesson':
                        $data['lessons'] = array_values($lessonsMastery);
                        $data['chartLabels'] = $lessonChartLabels;
                        $data['chartPercentage'] = $lessonChartPercentage;
                        break;
                    case 'Game':
                        $data['games'] = array_values($gameTypesMastery);
                        $data['chartLabels'] = $gameChartLabels;
                        $data['chartPercentage'] = $gameChartPercentage;
                        break;
                    default:
                        $data['skills'] = array_values($skillsMastery);
                        $data['units'] = array_values($unitsMastery);
                        $data['lessons'] = array_values($lessonsMastery);
                        $data['games'] = array_values($gameTypesMastery);
                        break;
                }
            } else {
                $data['skills'] = array_values($skillsMastery);
                $data['units'] = array_values($unitsMastery);
                $data['lessons'] = array_values($lessonsMastery);
                $data['games'] = array_values($gameTypesMastery);
            }
        }
        return view('dashboard.reports.student.student_mastery_report', $data);
    }

    public function numOfTrialsReport(Request $request)
    {
        $schools = School::all();
        $data = [
            'schools' => $schools,
            'request' => $request->all(),
        ];

        $studentId = $request->input('student_id');
        $programId = $request->input('program_id');
        $filterType = $request->input('filter_type');
        $filterValue = $request->input($filterType . '_id');

        if ($studentId) {
            $query = StudentProgress::where('student_id', $studentId)
                ->where('program_id', $programId)
                ->where('is_done', 1);
            if ($query->get()->isEmpty()) {
                $data['error'] = 'No Student Progress Found';
                return view('dashboard.reports.student.student_completion_report', $data);
            }


            if ($filterType && $filterValue) {
                if ($filterType == 'game') {
                    $query->whereHas('test', function ($q) use ($filterValue) {
                        $q->where('game_id', $filterValue);
                    });
                } elseif ($filterType == 'skill') {
                    $query->whereHas('test.game.gameTypes.skills', function ($q) use ($filterValue) {
                        $q->where('skill_id', $filterValue);
                    });
                } else {
                    $query->where($filterType . '_id', $filterValue);
                }
            }

            // if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
            //     $from_date = Carbon::parse($request->from_date)->startOfDay();
            //     $to_date = Carbon::parse($request->to_date)->endOfDay();
            //     $query->whereBetween('created_at', [$from_date, $to_date]);
            // }

            // Add the 'from_date' filter if it exists
            $query->when($request->filled('from_date'), function ($query) use ($request) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                return $query->where('created_at', '>=', $fromDate);
            });

            // Add the 'to_date' filter if it exists
            $query->when($request->filled('to_date'), function ($query) use ($request) {
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                return $query->where('created_at', '<=', $toDate);
            });

            if ($query->get()->isEmpty()) {
                $data['error'] = 'No Results Found';
                return view('dashboard.reports.student.student_completion_report', $data);
            }

            $studentProgress = $query->get();
            $response = [];

            foreach ($studentProgress as $progress) {
                $test = Test::find($progress->test_id);
                if (!$test) {
                    continue;
                }

                $data[] = [
                    'test_name' => $test->name,
                    'completion_date' => $progress->created_at->format('Y-m-d'),
                    'num_trials' => $progress->mistake_count + 1,
                    'score' => $progress->score,
                ];
            }
        }
        // dd($data);
        return view('dashboard.reports.student.student_num_of_trials_report', $data);
    }

    public function skillReport(Request $request)
    {
        $query = StudentProgress::with(['tests', 'tests.game', 'tests.game.gameTypes.skills'])
            ->where('student_id', $request->student_id)
            ->where('program_id', $request->program_id)
            ->where('is_done', 1);

        if ($request->has('skill_id')) {
            $query->whereHas('tests.game.gameTypes.skills', function ($q) use ($request) {
                $q->where('skill_id', $request->skill_id);
            });
        }

        if ($request->filled(['from_date', 'to_date']) && $request->from_date != null && $request->to_date != null) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon::parse($request->to_date)->endOfDay();
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $studentProgress = $query->get();
        $skillsData = [];

        foreach ($studentProgress as $progress) {
            $test = $progress->test;

            if ($test && $test->game) {
                $game = $test->game;

                if ($game->gameTypes) {
                    foreach ($game->gameTypes->skills->unique('skill') as $gameSkill) {
                        if (!$gameSkill->skill)
                            continue;

                        $skill = $gameSkill->skill;
                        $skillName = $skill->skill;
                        $date = $progress->created_at->format('Y-m-d');

                        $currentLevel = 'Introduced';
                        if ($progress->score >= 80) {
                            $currentLevel = 'Mastered';
                        } elseif ($progress->score >= 60) {
                            $currentLevel = 'Practiced';
                        }

                        if (!isset($skillsData[$skillName])) {
                            $skillsData[$skillName] = [
                                'skill_name' => $skillName,
                                'total_score' => 0,
                                'count' => 0,
                                'average_score' => 0,
                                'current_level' => $currentLevel,
                                'date' => $date,
                            ];
                        }

                        $skillsData[$skillName]['count']++;
                        $skillsData[$skillName]['total_score'] += $progress->score;
                        $skillsData[$skillName]['average_score'] = $skillsData[$skillName]['total_score'] / $skillsData[$skillName]['count'];
                        if ($skillsData[$skillName]['average_score'] >= 80) {
                            $skillsData[$skillName]['current_level'] = 'Mastered';
                        } elseif ($skillsData[$skillName]['average_score'] >= 60) {
                            $skillsData[$skillName]['current_level'] = 'Practiced';
                        } else {
                            $skillsData[$skillName]['current_level'] = 'Introduced';
                        }
                    }
                }
            }
        }

        $data = [
            'student_latest' => 'Some latest progress data', // Example data
            'skillsData' => $skillsData,
        ];

        return response()->json(['data' => $data]);
    }
    public function classGenderReportWeb(Request $request)
    {
        // dd($request->all());
        if (Auth::user()->hasRole('school')) {
            $groups = Group::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $groups = Group::all();
        }
        if (Auth::user()->hasRole('school')) {
            $id = Auth::user()->school_id;
            $programs = Program::whereHas('schoolProgram', function ($query) use ($id) {
                $query->where('school_id', $id);
            })
                ->with(['schoolProgram' => function ($query) use ($id) {
                    $query->where('school_id', $id);
                }])
                ->get();
        } else {
            $programs = Program::all();
        }


        $data = [
            'groups' => $groups,
            'programs' => $programs,
            'request' => $request->all(),
        ];

        if ($request->has('group_id')) {
            // Retrieve all students in the specified group
            // $students = GroupStudent::where('group_id', $request->group_id)->pluck('student_id');

            $students = GroupStudent::where('group_id', $request->group_id)
                ->join('users', 'group_students.student_id', '=', 'users.id')
                ->where('users.gender', $request->gender)
                ->pluck('group_students.student_id');

            // dd($students);


            if ($students->isEmpty()) {
                // dd("here1");
                return redirect()->back()->with('error', 'No Students Found.')->withInput();
            }

            if ($request->program_id != null) {
                $query = StudentProgress::whereIn('student_id', $students)
                    ->where('program_id', $request->program_id);
            } else {
                $query = StudentProgress::whereIn('student_id', $students);
            }

            if ($query->get()->isEmpty()) {
                // dd("here2");
                return redirect()->back()->with('error', 'No Student Progress Found.')->withInput();
            }

            // Apply filters if provided
            if ($request->has('unit_id')) {
                $query->where('unit_id', $request->unit_id);
            }
            if ($request->has('lesson_id')) {
                $query->where('lesson_id', $request->lesson_id);
            }
            if ($request->has('game_id')) {
                $query->whereHas('test', function ($q) use ($request) {
                    $q->where('game_id', $request->game_id);
                });
            }
            if ($request->has('skill_id')) {
                $query->whereHas('test.game.gameTypes.skills', function ($q) use ($request) {
                    $q->where('skill_id', $request->skill_id);
                });
            }

            if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            }

            $student_progress = $query->get();

            // Initialize arrays to hold data for grouping
            $unitsMastery = [];
            $lessonsMastery = [];
            $gamesMastery = [];
            $skillsMastery = [];

            if ($student_progress->isEmpty()) {
                // dd("here3");
                return redirect()->back()->with('error', 'No Student Progress Found.')->withInput();
            }

            // Process each progress record
            foreach ($student_progress as $progress) {
                // Retrieve the test and its related game, game type, and skills
                $test = Test::with(['game.gameTypes.skills.skill'])->where('lesson_id', $progress->lesson_id)->find($progress->test_id);

                // Check if the test and its relationships are properly loaded
                if (!$test || !$test->game || !$test->game->gameTypes) {
                    continue; // Skip to the next progress record if any of these are null
                }

                // Get the game type (since each game has one game type)
                $gameType = $test->game->gameTypes;

                // Group by unit
                if (!isset($unitsMastery[$progress->unit_id])) {
                    $unitsMastery[$progress->unit_id] = [
                        'unit_id' => $progress->unit_id,
                        'name' => Unit::find($progress->unit_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                        'lessons' => [],
                    ];
                }

                // Group by lesson
                if (!isset($lessonsMastery[$progress->lesson_id])) {
                    $lessonsMastery[$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'name' => Unit::find(Lesson::find($progress->lesson_id)->unit_id)->name . " | " . Lesson::find($progress->lesson_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                        'games' => [],
                    ];
                }

                // Group by game type
                if (!isset($gameTypesMastery[$gameType->id])) {
                    $gameTypesMastery[$gameType->id] = [
                        'game_type_id' => $gameType->id,
                        'name' => GameType::find($gameType->id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'count' => 0,
                        'total_score' => 0,
                        'games' => [],
                    ];
                }

                // Group by game within the game type
                if (!isset($gameTypesMastery[$gameType->id]['games'][$test->game_id])) {
                    $gameTypesMastery[$gameType->id]['games'][$test->game_id] = [
                        'game_id' => $test->game_id,
                        'name' => Game::find($test->game_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'count' => 0,
                        'total_score' => 0,
                    ];
                }

                // Group by skill
                if ($gameType && $gameType->skills) {
                    foreach ($gameType->skills->where('lesson_id', $progress->lesson_id)->unique('skill') as $gameSkill) {
                        $skill = $gameSkill->skill;

                        if (!isset($skillsMastery[$skill->id])) {
                            $skillsMastery[$skill->id] = [
                                'skill_id' => $skill->id,
                                'name' => $skill->skill,
                                'failed' => 0,
                                'introduced' => 0,
                                'practiced' => 0,
                                'mastered' => 0,
                                'total_attempts' => 0,
                                'total_score' => 0,
                                'mastery_percentage' => 0,
                            ];
                        }

                        $skillsMastery[$skill->id]['total_attempts']++;
                        if ($progress->is_done) {
                            if ($progress->score >= 80) {
                                $skillsMastery[$skill->id]['mastered']++;
                            } elseif ($progress->score >= 60) {
                                $skillsMastery[$skill->id]['practiced']++;
                            } elseif ($progress->score >= 30) {
                                $skillsMastery[$skill->id]['introduced']++;
                            } else {
                                $skillsMastery[$skill->id]['failed']++;
                            }
                        } else {
                            $skillsMastery[$skill->id]['failed']++;
                        }
                        $skillsMastery[$skill->id]['total_score'] += $progress->score;
                    }
                }

                // Update totals for units, lessons, and game types
                $unitsMastery[$progress->unit_id]['total_attempts']++;
                $lessonsMastery[$progress->lesson_id]['total_attempts']++;
                $gameTypesMastery[$gameType->id]['total_attempts']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_attempts']++;

                if ($progress->is_done) {
                    if ($progress->score >= 80) {
                        $unitsMastery[$progress->unit_id]['mastered']++;
                        $lessonsMastery[$progress->lesson_id]['mastered']++;
                        $gameTypesMastery[$gameType->id]['mastered']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['mastered']++;
                    } elseif ($progress->score >= 60) {
                        $unitsMastery[$progress->unit_id]['practiced']++;
                        $lessonsMastery[$progress->lesson_id]['practiced']++;
                        $gameTypesMastery[$gameType->id]['practiced']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['practiced']++;
                    } elseif ($progress->score >= 30) {
                        $unitsMastery[$progress->unit_id]['introduced']++;
                        $lessonsMastery[$progress->lesson_id]['introduced']++;
                        $gameTypesMastery[$gameType->id]['introduced']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['introduced']++;
                    } else {
                        $unitsMastery[$progress->unit_id]['failed']++;
                        $lessonsMastery[$progress->lesson_id]['failed']++;
                        $gameTypesMastery[$gameType->id]['failed']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
                    }
                } else {
                    $unitsMastery[$progress->unit_id]['failed']++;
                    $lessonsMastery[$progress->lesson_id]['failed']++;
                    $gameTypesMastery[$gameType->id]['failed']++;
                    $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
                }

                $unitsMastery[$progress->unit_id]['total_score'] += $progress->score;
                $lessonsMastery[$progress->lesson_id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['count']++;

                // Group lessons under units
                if (!isset($unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id])) {
                    $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                    ];
                }

                // Aggregate lesson data under the unit
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['failed'] += $lessonsMastery[$progress->lesson_id]['failed'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['introduced'] += $lessonsMastery[$progress->lesson_id]['introduced'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['practiced'] += $lessonsMastery[$progress->lesson_id]['practiced'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['mastered'] += $lessonsMastery[$progress->lesson_id]['mastered'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_attempts'] += $lessonsMastery[$progress->lesson_id]['total_attempts'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_score'] += $lessonsMastery[$progress->lesson_id]['total_score'];
            }

            // Ensure all lessons are included in units
            foreach ($unitsMastery as &$unit) {
                foreach ($lessonsMastery as $lessonId => $lessonData) {
                    if (!isset($unit['lessons'][$lessonId])) {
                        $unit['lessons'][$lessonId] = [
                            'lesson_id' => $lessonId,
                            'failed' => 0,
                            'introduced' => 0,
                            'practiced' => 0,
                            'mastered' => 0,
                            'total_attempts' => 0,
                            'total_score' => 0,
                            'mastery_percentage' => 0,
                        ];
                    }
                }
            }

            // Calculate mastery percentages for units, lessons, games, and game types
            foreach ($unitsMastery as &$unitData) {
                $unitData['mastery_percentage'] = $unitData['total_attempts'] > 0 ? ($unitData['total_score'] / $unitData['total_attempts']) : 0;

                foreach ($unitData['lessons'] as &$lessonData) {
                    $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? ($lessonData['total_score'] / $lessonData['total_attempts']) : 0;
                }

                $unitData['lessons'] = array_values($unitData['lessons']); // Convert lessons to array
            }

            foreach ($lessonsMastery as &$lessonData) {
                $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? ($lessonData['total_score'] / $lessonData['total_attempts']) : 0;
            }

            foreach ($gameTypesMastery as &$gameTypeData) {
                foreach ($gameTypeData['games'] as &$gameData) {
                    $gameData['mastery_percentage'] = $gameData['total_attempts'] > 0 ? ($gameData['total_score'] / $gameData['total_attempts']) : 0;
                }
                $gameTypeData['games'] = array_values($gameTypeData['games']); // Convert games to array

                $gameTypeData['mastery_percentage'] = $gameTypeData['total_attempts'] > 0 ? ($gameTypeData['total_score'] / $gameTypeData['total_attempts']) : 0;
            }

            // Calculate skill mastery level based on mastered, practiced, introduced, and failed counts
            foreach ($skillsMastery as &$skillData) {
                if ($skillData['mastered'] > $skillData['practiced'] && $skillData['mastered'] > $skillData['introduced'] && $skillData['mastered'] > $skillData['failed']) {
                    $skillData['current_level'] = 'mastered';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : $skillData['total_score'] / $skillData['total_attempts'];
                } elseif ($skillData['practiced'] > $skillData['introduced'] && $skillData['practiced'] > $skillData['failed']) {
                    $skillData['current_level'] = 'practiced';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : $skillData['total_score'] / $skillData['total_attempts'];
                } elseif ($skillData['introduced'] > $skillData['failed']) {
                    $skillData['current_level'] = 'introduced';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : $skillData['total_score'] / $skillData['total_attempts'];
                } else {
                    $skillData['current_level'] = 'failed';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : $skillData['total_score'] / $skillData['total_attempts'];
                }
            }

            // Prepare the response data
            if ($request->has('filter')) {
                switch ($request->filter) {
                    case 'Skill':
                        $data['skills'] = array_values($skillsMastery);
                        break;
                    case 'Unit':
                        $data['units'] = array_values($unitsMastery);
                        break;
                    case 'Lesson':
                        $data['lessons'] = array_values($lessonsMastery);
                        break;
                    case 'Game':
                        $data['games'] = array_values($gameTypesMastery);
                        break;
                    default:
                        $data['skills'] = array_values($skillsMastery);
                        $data['units'] = array_values($unitsMastery);
                        $data['lessons'] = array_values($lessonsMastery);
                        $data['games'] = array_values($gameTypesMastery);
                        break;
                }
            } else {
                $data['skills'] = array_values($skillsMastery);
                $data['units'] = array_values($unitsMastery);
                $data['lessons'] = array_values($lessonsMastery);
                $data['games'] = array_values($gameTypesMastery);
            }
        }

        // Return view with data
        return view('dashboard.reports.engagement.class_gender_report', $data);
    }
    public function schoolGenderReportWeb(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $schools = User::where('is_active', 1)
                ->role('school')
                ->where('users.id', Auth::user()->id) // Specify 'users.id'
                ->join('schools', 'users.school_id', '=', 'schools.id')
                ->select('schools.id', 'schools.name', 'users.email', 'users.phone', 'schools.type', 'users.id as user_id')
                ->get();
        } else {
            $schools = User::where('is_active', 1)
                ->role('school')
                ->join('schools', 'users.school_id', '=', 'schools.id')
                ->select('schools.id', 'schools.name', 'users.email', 'users.phone', 'schools.type', 'users.id as user_id')
                ->get();
        }

        $data = [
            'schools' => $schools,
            // 'programs' => $programs,
            'request' => $request->all(),
        ];

        if ($request->has('school_id')) {


            $students = User::where('school_id', $request->school_id)->where('role', 2)->where('gender', $request->gender)->pluck('id');
            // dd($students);

            if ($students->isEmpty()) {
                return redirect()->back()->with('error', 'No Students Found.')->withInput();
            }

            // Initialize query builder for student progress
            $query = StudentProgress::whereIn('student_id', $students);

            if ($query->get()->isEmpty()) {
                return redirect()->back()->with('error', 'No Student Progress Found.')->withInput();
            }

            // Apply filters if provided
            if ($request->has('unit_id')) {
                $query->where('unit_id', $request->unit_id);
            }
            if ($request->has('lesson_id')) {
                $query->where('lesson_id', $request->lesson_id);
            }
            if ($request->has('game_id')) {
                $query->whereHas('test', function ($q) use ($request) {
                    $q->where('game_id', $request->game_id);
                });
            }
            if ($request->has('skill_id')) {
                $query->whereHas('test.game.gameTypes.skills', function ($q) use ($request) {
                    $q->where('skill_id', $request->skill_id);
                });
            }

            if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            }

            $student_progress = $query->get();

            // Initialize arrays to hold data for grouping
            $unitsMastery = [];
            $lessonsMastery = [];
            $gamesMastery = [];
            $skillsMastery = [];

            if ($student_progress->isEmpty()) {
                return redirect()->back()->with('error', 'No Student Progress Found.')->withInput();
            }

            // Process each progress record
            foreach ($student_progress as $progress) {
                // Retrieve the test and its related game, game type, and skills
                $test = Test::with(['game.gameTypes.skills.skill'])->where('lesson_id', $progress->lesson_id)->find($progress->test_id);

                // Check if the test and its relationships are properly loaded
                if (!$test || !$test->game || !$test->game->gameTypes) {
                    continue; // Skip to the next progress record if any of these are null
                }

                // Get the game type (since each game has one game type)
                $gameType = $test->game->gameTypes;

                // Group by unit
                if (!isset($unitsMastery[$progress->unit_id])) {
                    $unitsMastery[$progress->unit_id] = [
                        'unit_id' => $progress->unit_id,
                        'name' => Unit::find($progress->unit_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                        'lessons' => [],
                    ];
                }

                // Group by lesson
                if (!isset($lessonsMastery[$progress->lesson_id])) {
                    $lessonsMastery[$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'name' => Unit::find(Lesson::find($progress->lesson_id)->unit_id)->name . " | " . Lesson::find($progress->lesson_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                        'games' => [],
                    ];
                }

                // Group by game type
                if (!isset($gameTypesMastery[$gameType->id])) {
                    $gameTypesMastery[$gameType->id] = [
                        'game_type_id' => $gameType->id,
                        'name' => GameType::find($gameType->id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'count' => 0,
                        'total_score' => 0,
                        'games' => [],
                    ];
                }

                // Group by game within the game type
                if (!isset($gameTypesMastery[$gameType->id]['games'][$test->game_id])) {
                    $gameTypesMastery[$gameType->id]['games'][$test->game_id] = [
                        'game_id' => $test->game_id,
                        'name' => Game::find($test->game_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'count' => 0,
                        'total_score' => 0,
                    ];
                }

                // Group by skill
                if ($gameType && $gameType->skills) {
                    foreach ($gameType->skills->where('lesson_id', $progress->lesson_id)->unique('skill') as $gameSkill) {
                        $skill = $gameSkill->skill;

                        if (!isset($skillsMastery[$skill->id])) {
                            $skillsMastery[$skill->id] = [
                                'skill_id' => $skill->id,
                                'name' => $skill->skill,
                                'failed' => 0,
                                'introduced' => 0,
                                'practiced' => 0,
                                'mastered' => 0,
                                'total_attempts' => 0,
                                'total_score' => 0,
                                'mastery_percentage' => 0,
                            ];
                        }

                        $skillsMastery[$skill->id]['total_attempts']++;
                        if ($progress->is_done) {
                            if ($progress->score >= 80) {
                                $skillsMastery[$skill->id]['mastered']++;
                            } elseif ($progress->score >= 60) {
                                $skillsMastery[$skill->id]['practiced']++;
                            } elseif ($progress->score >= 30) {
                                $skillsMastery[$skill->id]['introduced']++;
                            } else {
                                $skillsMastery[$skill->id]['failed']++;
                            }
                        } else {
                            $skillsMastery[$skill->id]['failed']++;
                        }
                        $skillsMastery[$skill->id]['total_score'] += $progress->score;
                    }
                }

                // Update totals for units, lessons, and game types
                $unitsMastery[$progress->unit_id]['total_attempts']++;
                $lessonsMastery[$progress->lesson_id]['total_attempts']++;
                $gameTypesMastery[$gameType->id]['total_attempts']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_attempts']++;

                if ($progress->is_done) {
                    if ($progress->score >= 80) {
                        $unitsMastery[$progress->unit_id]['mastered']++;
                        $lessonsMastery[$progress->lesson_id]['mastered']++;
                        $gameTypesMastery[$gameType->id]['mastered']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['mastered']++;
                    } elseif ($progress->score >= 60) {
                        $unitsMastery[$progress->unit_id]['practiced']++;
                        $lessonsMastery[$progress->lesson_id]['practiced']++;
                        $gameTypesMastery[$gameType->id]['practiced']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['practiced']++;
                    } elseif ($progress->score >= 30) {
                        $unitsMastery[$progress->unit_id]['introduced']++;
                        $lessonsMastery[$progress->lesson_id]['introduced']++;
                        $gameTypesMastery[$gameType->id]['introduced']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['introduced']++;
                    } else {
                        $unitsMastery[$progress->unit_id]['failed']++;
                        $lessonsMastery[$progress->lesson_id]['failed']++;
                        $gameTypesMastery[$gameType->id]['failed']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
                    }
                } else {
                    $unitsMastery[$progress->unit_id]['failed']++;
                    $lessonsMastery[$progress->lesson_id]['failed']++;
                    $gameTypesMastery[$gameType->id]['failed']++;
                    $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
                }

                $unitsMastery[$progress->unit_id]['total_score'] += $progress->score;
                $lessonsMastery[$progress->lesson_id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['count']++;

                // Group lessons under units
                if (!isset($unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id])) {
                    $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                    ];
                }

                // Aggregate lesson data under the unit
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['failed'] += $lessonsMastery[$progress->lesson_id]['failed'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['introduced'] += $lessonsMastery[$progress->lesson_id]['introduced'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['practiced'] += $lessonsMastery[$progress->lesson_id]['practiced'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['mastered'] += $lessonsMastery[$progress->lesson_id]['mastered'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_attempts'] += $lessonsMastery[$progress->lesson_id]['total_attempts'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_score'] += $lessonsMastery[$progress->lesson_id]['total_score'];
            }

            // Ensure all lessons are included in units
            foreach ($unitsMastery as &$unit) {
                foreach ($lessonsMastery as $lessonId => $lessonData) {
                    if (!isset($unit['lessons'][$lessonId])) {
                        $unit['lessons'][$lessonId] = [
                            'lesson_id' => $lessonId,
                            'failed' => 0,
                            'introduced' => 0,
                            'practiced' => 0,
                            'mastered' => 0,
                            'total_attempts' => 0,
                            'total_score' => 0,
                            'mastery_percentage' => 0,
                        ];
                    }
                }
            }

            // Calculate mastery percentages for units, lessons, games, and game types
            foreach ($unitsMastery as &$unitData) {
                $unitData['mastery_percentage'] = $unitData['total_attempts'] > 0 ? ($unitData['total_score'] / $unitData['total_attempts']) : 0;

                foreach ($unitData['lessons'] as &$lessonData) {
                    $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? ($lessonData['total_score'] / $lessonData['total_attempts']) : 0;
                }

                $unitData['lessons'] = array_values($unitData['lessons']); // Convert lessons to array
            }

            foreach ($lessonsMastery as &$lessonData) {
                $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? ($lessonData['total_score'] / $lessonData['total_attempts']) : 0;
            }

            foreach ($gameTypesMastery as &$gameTypeData) {
                foreach ($gameTypeData['games'] as &$gameData) {
                    $gameData['mastery_percentage'] = $gameData['total_attempts'] > 0 ? ($gameData['total_score'] / $gameData['total_attempts']) : 0;
                }
                $gameTypeData['games'] = array_values($gameTypeData['games']); // Convert games to array

                $gameTypeData['mastery_percentage'] = $gameTypeData['total_attempts'] > 0 ? ($gameTypeData['total_score'] / $gameTypeData['total_attempts']) : 0;
            }

            // Calculate skill mastery level based on mastered, practiced, introduced, and failed counts
            foreach ($skillsMastery as &$skillData) {
                if ($skillData['mastered'] > $skillData['practiced'] && $skillData['mastered'] > $skillData['introduced'] && $skillData['mastered'] > $skillData['failed']) {
                    $skillData['current_level'] = 'mastered';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : $skillData['total_score'] / $skillData['total_attempts'];
                } elseif ($skillData['practiced'] > $skillData['introduced'] && $skillData['practiced'] > $skillData['failed']) {
                    $skillData['current_level'] = 'practiced';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : $skillData['total_score'] / $skillData['total_attempts'];
                } elseif ($skillData['introduced'] > $skillData['failed']) {
                    $skillData['current_level'] = 'introduced';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : $skillData['total_score'] / $skillData['total_attempts'];
                } else {
                    $skillData['current_level'] = 'failed';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : $skillData['total_score'] / $skillData['total_attempts'];
                }
            }

            // Prepare the response data
            if ($request->has('filter')) {
                switch ($request->filter) {
                    case 'Skill':
                        $data['skills'] = array_values($skillsMastery);
                        break;
                    case 'Unit':
                        $data['units'] = array_values($unitsMastery);
                        break;
                    case 'Lesson':
                        $data['lessons'] = array_values($lessonsMastery);
                        break;
                    case 'Game':
                        $data['games'] = array_values($gameTypesMastery);
                        break;
                    default:
                        $data['skills'] = array_values($skillsMastery);
                        $data['units'] = array_values($unitsMastery);
                        $data['lessons'] = array_values($lessonsMastery);
                        $data['games'] = array_values($gameTypesMastery);
                        break;
                }
            } else {
                $data['skills'] = array_values($skillsMastery);
                $data['units'] = array_values($unitsMastery);
                $data['lessons'] = array_values($lessonsMastery);
                $data['games'] = array_values($gameTypesMastery);
            }
        }

        // Return view with data
        return view('dashboard.reports.engagement.school_gender_report', $data);
    }



    /////////////////Start Class functions/////////////////////

    public function selectGroup()
    {
        if (Auth::user()->hasRole('school')) {
            $groups = Group::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $groups = Group::all();
        }
        return view('dashboard.reports.class.select_group', compact('groups'));
    }

    // public function classCompletionReportWeb(Request $request)
    // {

    //     $groupId = $request->group_id;

    //     // Retrieve all students in the group
    //     $students = GroupStudent::where('group_id', $groupId)->pluck('student_id');
    //     // dd($students);
    //     if ($students->isEmpty()) {
    //         return view('errors.404', ['message' => 'No student progress found.']);
    //     }
    //     // Initialize the query builder for student progress
    //     $progressQuery = StudentTest::with('tests')
    //         ->whereIn('student_id', $students);

    //     if ($progressQuery->get()->isEmpty())
    //         return view('errors.404', ['message' => 'No student progress found.']);

    //     if ($request->filled('future') && $request->future != NULL) {
    //         if ($request->future == 1) {
    //             // No additional conditions needed
    //         } elseif ($request->future == 0) {
    //             $progressQuery->where('start_date', '<=', date('Y-m-d', strtotime(now())));
    //         }
    //     } else {
    //         $progressQuery->where('start_date', '<=', date('Y-m-d', strtotime(now())));
    //     }

    //     // Filter by from and to date if provided
    //     if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
    //         $fromDate = Carbon::parse($request->from_date)->startOfDay();
    //         $toDate = Carbon::parse($request->to_date)->endOfDay();
    //         $progressQuery->whereBetween('due_date', [$fromDate, $toDate]);
    //     }

    //     // Filter by program ID if provided
    //     if ($request->filled('program_id') && $request->program_id != NULL) {
    //         $progressQuery->where('program_id', $request->program_id);
    //     }

    //     // Execute the query
    //     $allTests = $progressQuery->orderBy('due_date', 'DESC')->get();
    //     $totalAllTests = $allTests->count();
    //     $finishedCount = $allTests->where('status', 1)->count();
    //     $overdueCount = $allTests->where('due_date', '<', \Carbon\Carbon::now()->format('Y-m-d'))
    //         ->where('status', '!=', 1)
    //         ->count();
    //     $pendingCount = $totalAllTests - $finishedCount - $overdueCount;

    //     // Calculate percentages as integers
    //     $finishedPercentage = $totalAllTests > 0 ? round(($finishedCount / $totalAllTests) * 100, 2) : 0;
    //     $overduePercentage = $totalAllTests > 0 ? round(($overdueCount / $totalAllTests) * 100, 2) : 0;
    //     $pendingPercentage = $totalAllTests > 0 ? round(($pendingCount / $totalAllTests) * 100, 2) : 0;

    //     // Filter by status if provided
    //     if ($request->filled('status') && $request->status != NULL) {
    //         $now = \Carbon\Carbon::now();
    //         $status = $request->status;
    //         switch ($status) {
    //             case 'Completed':
    //                 $progressQuery->where('status', '1');
    //                 break;
    //             case 'Overdue':
    //                 $progressQuery->where('due_date', '<', $now->format('Y-m-d'))->where('status', '!=', 1);
    //                 break;
    //             case 'Pending':
    //                 $progressQuery->where('status', '0')->where('due_date', '>=', $now->format('Y-m-d'));
    //                 break;
    //             default:
    //                 // Invalid status provided
    //                 break;
    //         }
    //     }

    //     // Filter by assignment types if provided
    //     if ($request->filled('types') && $request->types != NULL) {
    //         $assignmentTypes = $request->types;
    //         $progressQuery->whereHas('tests', function ($q) use ($assignmentTypes) {
    //             $q->join('test_types', 'tests.type', '=', 'test_types.id')
    //                 ->whereIn('test_types.id', $assignmentTypes);
    //         });
    //     }

    //     // Execute the query
    //     $tests = $progressQuery->orderBy('due_date', 'DESC')->get();

    //     // Prepare response data
    //     $test_types = TestTypes::all();

    //     $data['counts'] = [
    //         'completed' => $finishedCount,
    //         'overdue' => $overdueCount,
    //         'pending' => $pendingCount,
    //     ];
    //     $data['assignments_percentages'] = [
    //         'completed' => ceil($finishedPercentage),
    //         'overdue' => floor($overduePercentage),
    //         'pending' => ceil($pendingPercentage),
    //     ];
    //     $data['tests'] = $tests;
    //     $data['test_types'] = $test_types;

    //     $user_id = auth()->user()->id;
    //     $courses = DB::table('user_courses')
    //         ->join('programs', 'user_courses.program_id', '=', 'programs.id')
    //         ->join('courses', 'programs.course_id', '=', 'courses.id')
    //         ->where('user_courses.user_id', $user_id)
    //         ->select('programs.id as program_id', 'courses.name as course_name')
    //         ->get();

    //     // Add the "all programs" entry
    //     $allProgramsEntry = (object) [
    //         'program_id' => null,
    //         'course_name' => 'All Programs'
    //     ];
    //     $courses->prepend($allProgramsEntry);

    //     $data['courses'] = $courses;

    //     // Return view with data
    //     return view('dashboard.reports.class.class_completion_report', $data);
    // }

    public function classCompletionReportWeb(Request $request)
    {
        // $groups = Group::all();
        if (Auth::user()->hasRole('school')) {
            $groups = Group::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $groups = Group::all();
        }
        if (Auth::user()->hasRole('school')) {
            $programs = Program::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $programs = Program::all();
        }

        $assignmentTypes = TestTypes::all();

        // Initialize $data array with defaults
        $data = [
            'groups' => $groups,
            'programs' => $programs,
            'assignmentTypes' => $assignmentTypes,
            'counts' => [
                'completed' => 0,
                'overdue' => 0,
                'pending' => 0,
            ],
            'assignments_percentages' => [
                'completed' => 0,
                'overdue' => 0,
                'pending' => 0,
            ],
            'tests' => [],
            'request' => $request->all(),
        ];

        if ($request->has('group_id')) {
            $groupId = $request->group_id;

            // Retrieve all students in the group
            $students = GroupStudent::where('group_id', $groupId)->pluck('student_id');

            if ($students->isEmpty()) {
                // return redirect()->back()->with('error', 'No Students Found.')->withInput();
                $data['error'] = 'No Students Found.';
                return view('dashboard.reports.class.class_completion_report', $data);
            }
            // Initialize the query builder for student progress
            $progressQuery = StudentTest::with('tests')->whereIn('student_id', $students);

            if ($progressQuery->get()->isEmpty()) {
                // return redirect()->back()->with('error', 'No Student Progress Found.')->withInput();
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.class.class_completion_report', $data);
            }


            if ($request->filled('future') && $request->future != NULL) {
                if ($request->future == 1) {
                    // No additional conditions needed
                } elseif ($request->future == 0) {
                    $progressQuery->where('start_date', '<=', date('Y-m-d', strtotime(now())));
                }
            } else {
                $progressQuery->where('start_date', '<=', date('Y-m-d', strtotime(now())));
            }

            // Filter by from and to date if provided
            // if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
            //     $fromDate = Carbon::parse($request->from_date)->startOfDay();
            //     $toDate = Carbon::parse($request->to_date)->endOfDay();
            //     $progressQuery->whereBetween('due_date', [$fromDate, $toDate]);
            // }

            // Filter by program ID if provided
            if ($request->filled('program_id') && $request->program_id != NULL) {
                $progressQuery->where('program_id', $request->program_id);
            }
            if ($progressQuery->get()->isEmpty()) {
                $data['error'] = 'No Results Found for this Program';
                return view('dashboard.reports.class.class_completion_report', $data);
            }

            // Add the 'from_date' filter if it exists
            $progressQuery->when($request->filled('from_date'), function ($progressQuery) use ($request) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                return $progressQuery->where('created_at', '>=', $fromDate);
            });

            // Add the 'to_date' filter if it exists
            $progressQuery->when($request->filled('to_date'), function ($progressQuery) use ($request) {
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                return $progressQuery->where('created_at', '<=', $toDate);
            });

            if ($progressQuery->get()->isEmpty()) {
                $data['error'] = 'No Results Found for this Date';
                return view('dashboard.reports.class.class_completion_report', $data);
            }
            // Execute the query
            $allTests = $progressQuery->orderBy('due_date', 'DESC')->get();
            $totalAllTests = $allTests->count();
            $finishedCount = $allTests->where('status', 1)->count();
            $overdueCount = $allTests->where('due_date', '<', \Carbon\Carbon::now()->format('Y-m-d'))
                ->where('status', '!=', 1)
                ->count();
            $pendingCount = $totalAllTests - $finishedCount - $overdueCount;

            // Calculate percentages as integers
            $finishedPercentage = $totalAllTests > 0 ? round(($finishedCount / $totalAllTests) * 100, 2) : 0;
            $overduePercentage = $totalAllTests > 0 ? round(($overdueCount / $totalAllTests) * 100, 2) : 0;
            $pendingPercentage = $totalAllTests > 0 ? round(($pendingCount / $totalAllTests) * 100, 2) : 0;

            // Filter by status if provided
            if ($request->filled('status') && $request->status != NULL) {
                $now = \Carbon\Carbon::now();
                $status = $request->status;
                switch ($status) {
                    case 'Completed':
                        $progressQuery->where('status', '1');
                        if ($progressQuery->get()->isEmpty()) {
                            $data['error'] = 'No Results Found';
                            return view('dashboard.reports.class.class_completion_report', $data);
                        }
                        break;
                    case 'Overdue':
                        $progressQuery->where('due_date', '<', $now->format('Y-m-d'))->where('status', '!=', 1);
                        if ($progressQuery->get()->isEmpty()) {
                            $data['error'] = 'No Results Found';
                            return view('dashboard.reports.class.class_completion_report', $data);
                        }
                        break;
                    case 'Pending':
                        $progressQuery->where('status', '0')->where('due_date', '>=', $now->format('Y-m-d'));
                        if ($progressQuery->get()->isEmpty()) {
                            $data['error'] = 'No Results Found';
                            return view('dashboard.reports.class.class_completion_report', $data);
                        }
                        break;
                    default:
                        // Invalid status provided
                        break;
                }
            }


            // Filter by assignment types if provided
            if ($request->filled('types') && $request->types != NULL) {
                $assignmentTypes = $request->types;
                $progressQuery->whereHas('tests', function ($q) use ($assignmentTypes) {
                    $q->join('test_types', 'tests.type', '=', 'test_types.id')
                        ->whereIn('test_types.id', $assignmentTypes);
                });
            }

            // Execute the query
            $tests = $progressQuery->orderBy('due_date', 'DESC')->get();

            // Prepare response data
            $data['counts'] = [
                'completed' => $finishedCount,
                'overdue' => $overdueCount,
                'pending' => $pendingCount,
            ];
            $data['assignments_percentages'] = [
                'completed' => ceil($finishedPercentage),
                'overdue' => floor($overduePercentage),
                'pending' => ceil($pendingPercentage),
            ];
            $data['tests'] = $tests;
        }

        return view('dashboard.reports.class.class_completion_report', $data);
    }
    public function classMasteryReportWeb(Request $request)
    {
        // Retrieve necessary data for filters
        // $groups = Group::all();
        if (Auth::user()->hasRole('school')) {
            $groups = Group::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $groups = Group::all();
        }
        if (Auth::user()->hasRole('school')) {
            $programs = Program::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $programs = Program::all();
        }


        $data = [
            'groups' => $groups,
            'programs' => $programs,
            'request' => $request->all()
        ];

        if ($request->has('group_id')) {
            // Retrieve all students in the specified group
            $students = GroupStudent::where('group_id', $request->group_id)->pluck('student_id');

            if ($students->isEmpty()) {
                // return redirect()->back()->with('error', 'No Students Found.');
                $data['error'] = 'No Students Found.';
                return view('dashboard.reports.class.class_mastery_report', $data);
            }

            // Initialize query builder for student progress
            $query = StudentProgress::whereIn('student_id', $students)
                ->where('program_id', $request->program_id);

            if ($query->get()->isEmpty()) {
                // return redirect()->back()->with('error', 'No Student Progress Found.');
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.class.class_mastery_report', $data);
            }

            // Apply filters if provided
            if ($request->has('unit_id')) {
                $query->where('unit_id', $request->unit_id);
            }
            if ($request->has('lesson_id')) {
                $query->where('lesson_id', $request->lesson_id);
            }
            if ($request->has('game_id')) {
                $query->whereHas('test', function ($q) use ($request) {
                    $q->where('game_id', $request->game_id);
                });
            }
            if ($request->has('skill_id')) {
                $query->whereHas('test.game.gameTypes.skills', function ($q) use ($request) {
                    $q->where('skill_id', $request->skill_id);
                });
            }

            if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            }

            $student_progress = $query->get();

            // Initialize arrays to hold data for grouping
            $unitsMastery = [];
            $lessonsMastery = [];
            $gamesMastery = [];
            $skillsMastery = [];

            if ($student_progress->isEmpty()) {
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.class.class_mastery_report', $data);
            }

            // Process each progress record
            foreach ($student_progress as $progress) {
                // Retrieve the test and its related game, game type, and skills
                $test = Test::with(['game.gameTypes.skills.skill'])->where('lesson_id', $progress->lesson_id)->find($progress->test_id);

                // Check if the test and its relationships are properly loaded
                if (!$test || !$test->game || !$test->game->gameTypes) {
                    continue; // Skip to the next progress record if any of these are null
                }

                // Get the game type (since each game has one game type)
                $gameType = $test->game->gameTypes;

                // Group by unit
                if (!isset($unitsMastery[$progress->unit_id])) {
                    $unitsMastery[$progress->unit_id] = [
                        'unit_id' => $progress->unit_id,
                        'name' => Unit::find($progress->unit_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                        'lessons' => [],
                    ];
                }

                // Group by lesson
                if (!isset($lessonsMastery[$progress->lesson_id])) {
                    $lessonsMastery[$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'name' => Unit::find(Lesson::find($progress->lesson_id)->unit_id)->name . " | " . Lesson::find($progress->lesson_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                        'games' => [],
                    ];
                }

                // Group by game type
                if (!isset($gameTypesMastery[$gameType->id])) {
                    $gameTypesMastery[$gameType->id] = [
                        'game_type_id' => $gameType->id,
                        'name' => GameType::find($gameType->id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'count' => 0,
                        'total_score' => 0,
                        'games' => [],
                    ];
                }

                // Group by game within the game type
                if (!isset($gameTypesMastery[$gameType->id]['games'][$test->game_id])) {
                    $gameTypesMastery[$gameType->id]['games'][$test->game_id] = [
                        'game_id' => $test->game_id,
                        'name' => Game::find($test->game_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'count' => 0,
                        'total_score' => 0,
                    ];
                }

                // Group by skill
                if ($gameType && $gameType->skills) {
                    foreach ($gameType->skills->where('lesson_id', $progress->lesson_id)->unique('skill') as $gameSkill) {
                        $skill = $gameSkill->skill;

                        if (!isset($skillsMastery[$skill->id])) {
                            $skillsMastery[$skill->id] = [
                                'skill_id' => $skill->id,
                                'name' => $skill->skill,
                                'failed' => 0,
                                'introduced' => 0,
                                'practiced' => 0,
                                'mastered' => 0,
                                'total_attempts' => 0,
                                'total_score' => 0,
                                'mastery_percentage' => 0,
                            ];
                        }

                        $skillsMastery[$skill->id]['total_attempts']++;
                        if ($progress->is_done) {
                            if ($progress->score >= 80) {
                                $skillsMastery[$skill->id]['mastered']++;
                            } elseif ($progress->score >= 60) {
                                $skillsMastery[$skill->id]['practiced']++;
                            } elseif ($progress->score >= 30) {
                                $skillsMastery[$skill->id]['introduced']++;
                            } else {
                                $skillsMastery[$skill->id]['failed']++;
                            }
                        } else {
                            $skillsMastery[$skill->id]['failed']++;
                        }
                        $skillsMastery[$skill->id]['total_score'] += $progress->score;
                    }
                }

                // Update totals for units, lessons, and game types
                $unitsMastery[$progress->unit_id]['total_attempts']++;
                $lessonsMastery[$progress->lesson_id]['total_attempts']++;
                $gameTypesMastery[$gameType->id]['total_attempts']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_attempts']++;

                if ($progress->is_done) {
                    if ($progress->score >= 80) {
                        $unitsMastery[$progress->unit_id]['mastered']++;
                        $lessonsMastery[$progress->lesson_id]['mastered']++;
                        $gameTypesMastery[$gameType->id]['mastered']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['mastered']++;
                    } elseif ($progress->score >= 60) {
                        $unitsMastery[$progress->unit_id]['practiced']++;
                        $lessonsMastery[$progress->lesson_id]['practiced']++;
                        $gameTypesMastery[$gameType->id]['practiced']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['practiced']++;
                    } elseif ($progress->score >= 30) {
                        $unitsMastery[$progress->unit_id]['introduced']++;
                        $lessonsMastery[$progress->lesson_id]['introduced']++;
                        $gameTypesMastery[$gameType->id]['introduced']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['introduced']++;
                    } else {
                        $unitsMastery[$progress->unit_id]['failed']++;
                        $lessonsMastery[$progress->lesson_id]['failed']++;
                        $gameTypesMastery[$gameType->id]['failed']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
                    }
                } else {
                    $unitsMastery[$progress->unit_id]['failed']++;
                    $lessonsMastery[$progress->lesson_id]['failed']++;
                    $gameTypesMastery[$gameType->id]['failed']++;
                    $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
                }

                $unitsMastery[$progress->unit_id]['total_score'] += $progress->score;
                $lessonsMastery[$progress->lesson_id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['count']++;

                // Group lessons under units
                if (!isset($unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id])) {
                    $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                    ];
                }

                // Aggregate lesson data under the unit
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['failed'] += $lessonsMastery[$progress->lesson_id]['failed'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['introduced'] += $lessonsMastery[$progress->lesson_id]['introduced'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['practiced'] += $lessonsMastery[$progress->lesson_id]['practiced'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['mastered'] += $lessonsMastery[$progress->lesson_id]['mastered'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_attempts'] += $lessonsMastery[$progress->lesson_id]['total_attempts'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_score'] += $lessonsMastery[$progress->lesson_id]['total_score'];
            }

            // Ensure all lessons are included in units
            foreach ($unitsMastery as &$unit) {
                foreach ($lessonsMastery as $lessonId => $lessonData) {
                    if (!isset($unit['lessons'][$lessonId])) {
                        $unit['lessons'][$lessonId] = [
                            'lesson_id' => $lessonId,
                            'failed' => 0,
                            'introduced' => 0,
                            'practiced' => 0,
                            'mastered' => 0,
                            'total_attempts' => 0,
                            'total_score' => 0,
                            'mastery_percentage' => 0,
                        ];
                    }
                }
            }


            // Calculate mastery percentages for units, lessons, games, and game types
            $unitChartLabels = [];
            $unitChartPercentage = [];
            foreach ($unitsMastery as &$unitData) {
                $unitData['mastery_percentage'] = $unitData['total_attempts'] > 0 ? ($unitData['total_score'] / $unitData['total_attempts']) : 0;

                foreach ($unitData['lessons'] as &$lessonData) {
                    $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? ($lessonData['total_score'] / $lessonData['total_attempts']) : 0;
                }

                $unitData['lessons'] = array_values($unitData['lessons']); // Convert lessons to array
                array_push($unitChartLabels, $unitData['name']);
                array_push($unitChartPercentage, $unitData['mastery_percentage']);
            }
            $lessonChartPercentage = [];
            $lessonChartLabels = [];
            foreach ($lessonsMastery as &$lessonData) {
                $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? ($lessonData['total_score'] / $lessonData['total_attempts']) : 0;
                array_push($lessonChartLabels, $lessonData['name']);
                array_push($lessonChartPercentage, $lessonData['mastery_percentage']);
            }


            $gameChartPercentage = [];
            $gameChartLabels = [];
            foreach ($gameTypesMastery as &$gameTypeData) {
                foreach ($gameTypeData['games'] as &$gameData) {
                    $gameData['mastery_percentage'] = $gameData['total_attempts'] > 0 ? ($gameData['total_score'] / $gameData['total_attempts']) : 0;
                    array_push($gameChartLabels, $gameData['name']);
                    array_push($gameChartPercentage, $gameData['mastery_percentage']);
                }
                $gameTypeData['games'] = array_values($gameTypeData['games']); // Convert games to array

                $gameTypeData['mastery_percentage'] = $gameTypeData['total_attempts'] > 0 ? ($gameTypeData['total_score'] / $gameTypeData['total_attempts']) : 0;
            }

            // Calculate skill mastery level based on mastered, practiced, introduced, and failed counts
            $skillChartPercentage = [];
            $skillChartLabels = [];
            foreach ($skillsMastery as &$skillData) {
                if ($skillData['mastered'] > $skillData['practiced'] && $skillData['mastered'] > $skillData['introduced'] && $skillData['mastered'] > $skillData['failed']) {
                    $skillData['current_level'] = 'mastered';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : $skillData['total_score'] / $skillData['total_attempts'];
                } elseif ($skillData['practiced'] > $skillData['introduced'] && $skillData['practiced'] > $skillData['failed']) {
                    $skillData['current_level'] = 'practiced';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : $skillData['total_score'] / $skillData['total_attempts'];
                } elseif ($skillData['introduced'] > $skillData['failed']) {
                    $skillData['current_level'] = 'introduced';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : $skillData['total_score'] / $skillData['total_attempts'];
                } else {
                    $skillData['current_level'] = 'failed';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : $skillData['total_score'] / $skillData['total_attempts'];
                }
                array_push($skillChartLabels, $skillData['name']);
                array_push($skillChartPercentage, $skillData['mastery_percentage']);
            }

            // Prepare the response data
            if ($request->has('filter')) {
                switch ($request->filter) {
                    case 'Skill':
                        if ($skillsMastery) {
                            $data['skills'] = array_values($skillsMastery);
                            $data['chartLabels'] = $skillChartLabels;
                            $data['chartPercentage'] = $skillChartPercentage;
                        } else {
                            $data['error'] = "No Skills Found";
                            return view('dashboard.reports.class.class_mastery_report', $data);
                        }

                        break;
                    case 'Unit':
                        $data['units'] = array_values($unitsMastery);
                        $data['chartLabels'] = $unitChartLabels;
                        $data['chartPercentage'] = $unitChartPercentage;
                        break;
                    case 'Lesson':
                        $data['lessons'] = array_values($lessonsMastery);
                        $data['chartLabels'] = $lessonChartLabels;
                        $data['chartPercentage'] = $lessonChartPercentage;
                        break;
                    case 'Game':
                        $data['games'] = array_values($gameTypesMastery);
                        $data['chartLabels'] = $gameChartLabels;
                        $data['chartPercentage'] = $gameChartPercentage;
                        break;
                    default:
                        $data['skills'] = array_values($skillsMastery);
                        $data['units'] = array_values($unitsMastery);
                        $data['lessons'] = array_values($lessonsMastery);
                        $data['games'] = array_values($gameTypesMastery);
                        break;
                }
            } else {
                $data['skills'] = array_values($skillsMastery);
                $data['units'] = array_values($unitsMastery);
                $data['lessons'] = array_values($lessonsMastery);
                $data['games'] = array_values($gameTypesMastery);
            }
        }
        // dd($data);
        // Return view with data
        return view('dashboard.reports.class.class_mastery_report', $data);
    }
    public function classNumOfTrialsReportWeb(Request $request)
    {
        // Retrieve groups and programs for the filters
        if (Auth::user()->hasRole('school')) {
            $groups = Group::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $groups = Group::all();
        }
        if (Auth::user()->hasRole('school')) {
            $programs = Program::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $programs = Program::all();
        }
        $data = [
            'groups' => $groups,
            'programs' => $programs,
            'request' => $request->all(),
        ];

        // Get the student IDs for the given group ID
        $students = GroupStudent::where('group_id', $request->group_id)->pluck('student_id');
        // Check if students exist for the given group
        if ($request->filled('group_id')) {
            if ($students->isEmpty()) {
                // return redirect()->back()->with('error', 'No Students Found for Selected Group.')->withInput();
                $data['error'] = 'No Students Found for Selected Group.';
                return view('dashboard.reports.class.class_num_of_trials_report', $data);
            }
        }


        // Check if student progress exists for the given program
        if ($request->filled('program_id')) {
            // Initialize query builder with student IDs and program ID
            $progressQuery = StudentProgress::whereIn('student_id', $students)
                ->where('program_id', $request->program_id);
            if ($progressQuery->get()->isEmpty()) {
                $data['error'] = 'No Student Progress Found for this Program.';
                return view('dashboard.reports.class.class_num_of_trials_report', $data);
            }


            // if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
            //     $from_date = Carbon::parse($request->from_date)->startOfDay();
            //     $to_date = Carbon::parse($request->to_date)->endOfDay();
            //     $progressQuery->whereBetween('created_at', [$from_date, $to_date]);
            // }
            // Add the 'from_date' filter if it exists
            $progressQuery->when($request->filled('from_date'), function ($progressQuery) use ($request) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                return $progressQuery->where('created_at', '>=', $fromDate);
            });

            // Add the 'to_date' filter if it exists
            $progressQuery->when($request->filled('to_date'), function ($progressQuery) use ($request) {
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                return $progressQuery->where('created_at', '<=', $toDate);
            });



            // Filter by month of created_at date if provided
            if ($request->filled('month')) {
                $month = $request->month;
                $progressQuery->whereMonth('created_at', Carbon::parse($month)->month);
            }

            // Filter by test_types if provided
            if ($request->filled('type')) {
                $type = $request->type;
                $progressQuery->join('tests', 'student_progress.test_id', '=', 'tests.id')
                    ->where('tests.type', $type);
            }

            // Filter by stars if provided
            if ($request->filled('stars')) {
                $stars = (array) $request->stars;
                if ($request->stars == 2) {
                    $progressQuery->whereIn('mistake_count', range(2, 1000));
                } else {
                    $progressQuery->whereIn('mistake_count', $stars);
                }
            }

            // Get the progress data
            $progress = $progressQuery->orderBy('created_at', 'ASC')
                ->select('student_progress.*')
                ->get();

            // Check if progress data is empty
            if ($request->filled('group_id') || $request->filled('program_id')) {
                if ($progress->isEmpty()) {
                    // return redirect()->back()->with('error', 'No student progress found after applying filters.')->withInput();
                    $data['error'] = 'No student progress found after applying filters.';
                    return view('dashboard.reports.class.class_num_of_trials_report', $data);
                }
            }

            // Initialize arrays to hold the data
            $monthlyScores = [];
            $starCounts = [];

            foreach ($progress as $course) {
                $createdDate = Carbon::parse($course->created_at);
                $monthYear = $createdDate->format('Y-m');

                // Calculate the number of trials
                $numTrials = $course->mistake_count + 1;

                // Calculate the score for each test
                $testScore = [
                    'name' => $course->test_name,
                    'test_id' => $course->test_id,
                    'score' => $course->score,
                    'star' => $course->stars,  // Include star in the testScore for filtering
                    'num_trials' => $numTrials
                ];

                // Add the test score to the respective month
                if (!isset($monthlyScores[$monthYear])) {
                    $monthlyScores[$monthYear] = [
                        'month' => $createdDate->format('F'),
                        'total_score' => 0,
                        'star' => $course->stars,
                        'tests' => [],
                    ];
                }

                $monthlyScores[$monthYear]['tests'][] = $testScore;
                $monthlyScores[$monthYear]['total_score'] += $course->score;

                // Count stars
                $star = $course->stars;
                if (isset($starCounts[$star])) {
                    $starCounts[$star]++;
                } else {
                    $starCounts[$star] = 1;
                }
            }

            $totalDisplayedStars = array_sum($starCounts);
            $oneStarDisplayedCount = isset($starCounts[1]) ? $starCounts[1] : 0;
            $twoStarDisplayedCount = isset($starCounts[2]) ? $starCounts[2] : 0;
            $threeStarDisplayedCount = isset($starCounts[3]) ? $starCounts[3] : 0;

            // Filter progress by stars if provided
            if ($request->filled('stars')) {
                $stars = (array) $request->stars;
                $data['tprogress'] = array_filter($monthlyScores, function ($monthlyScore) use ($stars) {
                    foreach ($monthlyScore['tests'] as $test) {
                        if (in_array($test['star'], $stars)) {
                            return true;
                        }
                    }
                    return false;
                });
            } else {
                $data['tprogress'] = array_values($monthlyScores);
            }

            $oneStarDisplayedPercentage = $totalDisplayedStars > 0 ? round(($oneStarDisplayedCount / $totalDisplayedStars) * 100, 2) : 0;
            $twoStarDisplayedPercentage = $totalDisplayedStars > 0 ? round(($twoStarDisplayedCount / $totalDisplayedStars) * 100, 2) : 0;
            $threeStarDisplayedPercentage = $totalDisplayedStars > 0 ? round(($threeStarDisplayedCount / $totalDisplayedStars) * 100, 2) : 0;
            // Prepare response data
            $data['progress'] = $progress;

            if ($request->filled('stars')) {
                $data['counts'] = StudentProgress::where('stars', $request->stars)->count();
            } else {
                $data['counts'] = StudentProgress::whereIn('student_id', $students)
                    ->where('program_id', $request->program_id)
                    ->count();
            }

            $division = StudentProgress::whereIn('student_id', $students)->where('program_id', $request->program_id)->count();
            if ($division == 0) {
                $division = 1;
            }
            if (!$request->filled('from_date') && !$request->filled('to_date')) {
                $data['reports_percentages'] = [
                    'first_trial' => round((StudentProgress::where('mistake_count', 0)->whereIn('student_id', $students)
                        ->where('program_id', $request->program_id)->count() / $division) * 100) ?? 0,
                    'second_trial' => round((StudentProgress::where('mistake_count', 1)->whereIn('student_id', $students)
                        ->where('program_id', $request->program_id)->count() / $division) * 100) ?? 0,
                    'third_trial' => round((StudentProgress::whereIn('mistake_count', [2, 3, 4, 5, 6, 7, 8, 9, 10, 11])->whereIn('student_id', $students)
                        ->where('program_id', $request->program_id)->count() / $division) * 100) ?? 0,
                ];
            } else {
                $threestars = StudentProgress::where('mistake_count', 0)->whereIn('student_id', $students)->where('is_done', 1)->whereBetween('student_progress.created_at', [$from_date, $to_date])
                    ->where('program_id', $request->program_id)->count();
                $twostars = StudentProgress::where('mistake_count', 1)->whereIn('student_id', $students)->where('is_done', 1)->whereBetween('student_progress.created_at', [$from_date, $to_date])
                    ->where('program_id', $request->program_id)->count();
                $onestar = StudentProgress::whereIn('mistake_count', [2, 3, 4, 5, 6, 7, 8, 9, 10, 11])->where('is_done', 1)->whereIn('student_id', $students)->whereBetween('student_progress.created_at', [$from_date, $to_date])
                    ->where('program_id', $request->program_id)->count();

                $division = StudentProgress::whereIn('student_id', $students)->where('program_id', $request->program_id)
                    ->whereBetween('student_progress.created_at', [$from_date, $to_date])->count();

                if ($division == 0) {
                    $division = 1;
                }
                $data['reports_percentages'] = [
                    'first_trial' => round(($threestars / $division) * 100, 2),
                    'second_trial' => round(($twostars / $division) * 100, 2),
                    'third_trial' => round(($onestar / $division) * 100, 2),
                ];
            }

            $data['oneStarDisplayedPercentage'] = $data['reports_percentages']['first_trial'];
            $data['twoStarDisplayedPercentage'] = $data['reports_percentages']['second_trial'];
            $data['threeStarDisplayedPercentage'] = $data['reports_percentages']['third_trial'];
        }

        return view('dashboard.reports.class.class_num_of_trials_report', $data);
    }
    public function studentLoginReport(Request $request)
    {
        // dd($request->all());
        if (Auth::user()->hasRole('school')) {
            $query = User::where('role', '2')->where("school_id", Auth::user()->school_id);
        } else {
            $query = User::where('role', '2');
        }

        if ($request->filled('school')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('school_id', $request->input('school'));
            });
        }

        $students = $query->get();

        $schools = School::all();


        if ($request->filled('student_id')) {
            $numLogin = [];
            $studentName = [];
            $student =  User::find($request->student_id);
            array_push($studentName, $student->name);
            array_push($numLogin, $student->number_logins);
            return view(
                'dashboard.reports.student_login_report',
                [
                    'students' => $students,
                    'schools' => $schools,
                    'studentName' => $studentName,
                    'numLogin' => $numLogin,
                    'request' => $request->all(),
                ]
            );
        }
        return view(
            'dashboard.reports.student_login_report',
            [
                'students' => $students,
                'schools' => $schools,
            ]
        );
    }
    public function teacherLoginReport(Request $request)
    {
        // dd($request->all());
        if (Auth::user()->hasRole('school')) {
            $query = User::where('role', '1')->where("school_id", Auth::user()->school_id);
        } else {
            $query = User::where('role', '1');
        }

        if ($request->filled('school_id')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('school_id', $request->input('school_id'));
            });
        }

        $teachers = $query->get();
        $schools = School::all();


        if ($request->filled('teacher_id')) {
            $numLogin = [];
            $teacherName = [];
            $teacher =  User::find($request->teacher_id);
            array_push($teacherName, $teacher->name);
            array_push($numLogin, $teacher->number_logins);
            return view(
                'dashboard.reports.instructor.instructor_login_report',
                [
                    'teachers' => $teachers,
                    'schools' => $schools,
                    'teacherName' => $teacherName,
                    'numLogin' => $numLogin,
                    'request' => $request->all(),
                ]
            );
        }
        return view(
            'dashboard.reports.instructor.instructor_login_report',
            [
                'teachers' => $teachers,
                'schools' => $schools,
            ]
        );
    }

    public function classLoginReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $groups = Group::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $groups = Group::all();
        }

        $groupId = $request->group_id;

        if ($request->filled('group_id')) {
            $students = User::whereIn('id', GroupStudent::where('group_id', $groupId)->pluck('student_id'))->get();
            if (empty($students)) {
                return redirect()->back()->with('error', 'No Students Found.')->withInput();
            } else {
                $numLogin = [];
                $studentName = [];
                foreach ($students as $student) {
                    array_push($studentName, $student->name);
                    array_push($numLogin, $student->number_logins);
                }
                $seenIds = [];
                $teacherName = [];
                $teacherLogin = [];

                $teachers = GroupTeachers::where('group_id', $groupId)->get();

                if ($teachers) {
                    foreach ($teachers as $teacher) {
                        if (!in_array($teacher->teacher_id, $seenIds)) {
                            $user = User::find($teacher->teacher_id);

                            array_push($teacherName, $user->name);
                            array_push($teacherLogin, $user->number_logins);
                            $seenIds[] = $teacher->teacher_id;
                        }
                        if (!in_array($teacher->co_teacher_id, $seenIds)) {
                            $user = User::find($teacher->co_teacher_id);
                            if ($user) {
                                array_push($teacherName, $user->name);
                                array_push($teacherLogin, $user->number_logins);
                                $seenIds[] = $teacher->co_teacher_id;
                            }
                        }
                    }
                }
                return view(
                    'dashboard.reports.class.class_login_report',
                    [
                        'groups' => $groups,
                        'studentName' => $studentName,
                        'numLogin' => $numLogin,
                        'request' => $request->all(),
                        'teacherName' => $teacherName,
                        'teacherLogin' => $teacherLogin,
                    ]
                );
            }
        }

        return view(
            'dashboard.reports.class.class_login_report',
            compact(
                'groups',
            )
        );
    }
    public function schoolLoginReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $students = User::where('role', '2')->where("school_id", Auth::user()->school_id)->get();
            $teachers = User::where('role', '1')->where("school_id", Auth::user()->school_id)->get();
            // dd($students, $teachers);
            $teacherName = [];
            $teacherLogin = [];
            $numLogin = [];
            $studentName = [];
            if ($teachers) {
                foreach ($teachers as $teacher) {
                    array_push($teacherName, $teacher->name);
                    array_push($teacherLogin, $teacher->number_logins);
                }
            }

            if ($students) {
                foreach ($students as $student) {
                    array_push($studentName, $student->name);
                    array_push($numLogin, $student->number_logins);
                }
            }

            return view(
                'dashboard.reports.school.school_login_report',
                [
                    'studentName' => $studentName,
                    'numLogin' => $numLogin,
                    'teacherName' => $teacherName,
                    'teacherLogin' => $teacherLogin,
                ]
            );
        }

        $schools = School::all();

        if ($request->filled('school_id')) {
            $students = User::where('role', '2')->where("school_id", $request->school_id)->get();
            $teachers = User::where('role', '1')->where("school_id", $request->school_id)->get();
            $teacherName = [];
            $teacherLogin = [];
            $numLogin = [];
            $studentName = [];

            foreach ($teachers as $teacher) {
                array_push($teacherName, $teacher->name);
                array_push($teacherLogin, $teacher->number_logins);
            }

            foreach ($students as $student) {
                array_push($studentName, $student->name);
                array_push($numLogin, $student->number_logins);
            }

            return view(
                'dashboard.reports.school.school_login_report',
                [
                    'schools' => $schools,
                    'studentName' => $studentName,
                    'numLogin' => $numLogin,
                    'teacherName' => $teacherName,
                    'teacherLogin' => $teacherLogin,
                    'request' => $request->all(),
                ]
            );
        }
        return view(
            'dashboard.reports.school.school_login_report',
            [
                'schools' => $schools,
                'request' => $request->all(),

            ]
        );
    }

    public function teacherCompletionReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            // $students = User::where('role', '2')->where("school_id", Auth::user()->school_id)->get();
            $programs = Program::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $programs = Program::all();
        }

        $schools = School::all();
        $assignmentTypes = TestTypes::all();

        // Initialize $data array with defaults
        $data = [
            'schools' => $schools,
            'programs' => $programs,
            'assignmentTypes' => $assignmentTypes,
            'counts' => [
                'completed' => 0,
                'overdue' => 0,
                'pending' => 0,
            ],
            'assignments_percentages' => [
                'completed' => 0,
                'overdue' => 0,
                'pending' => 0,
            ],
            'tests' => [],
            'request' => $request->all(),
        ];

        if ($request->has('school_id')) {
            $schoolId = $request->school_id;

            // Retrieve all students in the school
            $students = User::where('role', '2')->where('school_id', $request->school_id)->pluck('id');
            if ($students->isEmpty()) {
                $data['error'] = 'No Students Found.';
                return view('dashboard.reports.instructor.instructor_completion_report', $data);
            }

            // Initialize the query builder for student progress
            $progressQuery = StudentTest::with('tests')->whereIn('student_id', $students)->where('teacher_id', $request->teacher_id);
            if ($progressQuery->get()->isEmpty()) {
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.instructor.instructor_completion_report', $data);
            }

            if ($request->filled('future') && $request->future != NULL) {
                if ($request->future == 1) {
                    // No additional conditions needed
                } elseif ($request->future == 0) {
                    $progressQuery->where('start_date', '<=', date('Y-m-d', strtotime(now())));
                }
            } else {
                $progressQuery->where('start_date', '<=', date('Y-m-d', strtotime(now())));
            }

            // Filter by from and to date if provided
            // if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
            // }
            // $fromDate = Carbon::parse($request->from_date)->startOfDay();
            // $toDate = Carbon::parse($request->to_date)->endOfDay();

            // Add the 'from_date' filter if it exists
            $progressQuery->when($request->filled('from_date'), function ($progressQuery) use ($request) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                return $progressQuery->where('created_at', '>=', $fromDate);
            });

            // Add the 'to_date' filter if it exists
            $progressQuery->when($request->filled('to_date'), function ($progressQuery) use ($request) {
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                return $progressQuery->where('created_at', '<=', $toDate);
            });
            // $progressQuery->whereBetween('due_date', [$fromDate, $toDate]);



            // Filter by program ID if provided
            if ($request->filled('program_id') && $request->program_id != NULL) {
                $progressQuery->where('program_id', $request->program_id);
            }


            // Filter by test ID if provided
            if ($request->filled('assignment_id')) {
                $progressQuery->where('test_id', $request->assignment_id);
                if ($progressQuery->get()->isEmpty()) {
                    $data['error'] = 'No Student Progress Found for this Assignment.';
                    return view('dashboard.reports.instructor.instructor_completion_report', $data);
                }
            }
            if ($progressQuery->get()->isEmpty()) {
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.instructor.instructor_completion_report', $data);
            }
            // Execute the query
            $allTests = $progressQuery->orderBy('due_date', 'DESC')->get();
            $totalAllTests = $allTests->count();
            $finishedCount = $allTests->where('status', 1)->count();
            $overdueCount = $allTests->where('due_date', '<', \Carbon\Carbon::now()->format('Y-m-d'))
                ->where('status', '!=', 1)
                ->count();
            $pendingCount = $totalAllTests - $finishedCount - $overdueCount;
            // Calculate percentages as integers
            $finishedPercentage = $totalAllTests > 0 ? round(($finishedCount / $totalAllTests) * 100, 2) : 0;
            $overduePercentage = $totalAllTests > 0 ? round(($overdueCount / $totalAllTests) * 100, 2) : 0;
            $pendingPercentage = $totalAllTests > 0 ? round(($pendingCount / $totalAllTests) * 100, 2) : 0;

            // Filter by status if provided
            if ($request->filled('status') && $request->status != NULL) {
                $now = \Carbon\Carbon::now();
                $status = $request->status;
                switch ($status) {
                    case 'Completed':
                        $progressQuery->where('status', '1');
                        if ($progressQuery->get()->isEmpty()) {
                            $data['error'] = 'No Completed Assignments Found.';
                            return view('dashboard.reports.instructor.instructor_completion_report', $data);
                        }
                        break;
                    case 'Overdue':
                        $progressQuery->where('due_date', '<', $now->format('Y-m-d'))->where('status', '!=', 1);
                        if ($progressQuery->get()->isEmpty()) {
                            $data['error'] = 'No Overdue Assignments Found.';
                            return view('dashboard.reports.instructor.instructor_completion_report', $data);
                        }
                        break;
                    case 'Pending':
                        $progressQuery->where('status', '0')->where('due_date', '>=', $now->format('Y-m-d'));
                        if ($progressQuery->get()->isEmpty()) {
                            $data['error'] = 'No Pending Assignments Found.';
                            return view('dashboard.reports.instructor.instructor_completion_report', $data);
                        }
                        break;
                    default:
                        // Invalid status provided
                        break;
                }
            }

            // Filter by assignment types if provided
            if ($request->filled('types') && $request->types != NULL) {
                $assignmentTypes = $request->types;
                $progressQuery->whereHas('tests', function ($q) use ($assignmentTypes) {
                    $q->join('test_types', 'tests.type', '=', 'test_types.id')
                        ->whereIn('test_types.id', $assignmentTypes);
                });
            }

            // Execute the query
            $tests = $progressQuery->orderBy('due_date', 'DESC')->get();


            // Prepare response data
            $data['counts'] = [
                'completed' => $finishedCount,
                'overdue' => $overdueCount,
                'pending' => $pendingCount,
            ];
            $data['assignments_percentages'] = [
                'completed' => ceil($finishedPercentage),
                'overdue' => floor($overduePercentage),
                'pending' => ceil($pendingPercentage),
            ];
            $data['tests'] = $tests;
        }

        return view(
            'dashboard.reports.instructor.instructor_completion_report',
            $data
        );
    }

    public function schoolCompletionReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $schoolId = Auth::user()->school_id;
            $programs = Program::with(['course', 'stage'])
                ->whereHas('schoolProgram', function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                })->get();
        } else {
            $programs = Program::all();
        }

        $schools = School::all();
        $assignmentTypes = TestTypes::all();

        // Initialize $data array with defaults
        $data = [
            'schools' => $schools,
            'programs' => $programs,
            'assignmentTypes' => $assignmentTypes,
            'counts' => [
                'completed' => 0,
                'overdue' => 0,
                'pending' => 0,
            ],
            'assignments_percentages' => [
                'completed' => 0,
                'overdue' => 0,
                'pending' => 0,
            ],
            'tests' => [],
            'request' => $request->all(),
        ];

        if ($request->has('school_id')) {
            $schoolId = $request->school_id;

            // Retrieve all students in the school
            $students = User::where('role', '2')->where('school_id', $request->school_id)->pluck('id');
            if ($students->isEmpty()) {
                // return redirect()->back()->with('error', '')->withInput();
                $data['error'] = 'No Students Found.';
                return view('dashboard.reports.school.school_completion_report', $data);
            }

            // Initialize the query builder for student progress
            $progressQuery = StudentTest::with('tests')->whereIn('student_id', $students);
            if ($progressQuery->get()->isEmpty()) {
                // return redirect()->back()->with('error', 'No Student Progress Found.')->withInput();
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.school.school_completion_report', $data);
            }

            if ($request->filled('future') && $request->future != NULL) {
                if ($request->future == 1) {
                    // No additional conditions needed
                } elseif ($request->future == 0) {
                    $progressQuery->where('start_date', '<=', date('Y-m-d', strtotime(now())));
                }
            } else {
                $progressQuery->where('start_date', '<=', date('Y-m-d', strtotime(now())));
            }

            // Filter by from and to date if provided
            // if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
            //     $fromDate = Carbon::parse($request->from_date)->startOfDay();
            //     $toDate = Carbon::parse($request->to_date)->endOfDay();
            //     $progressQuery->whereBetween('due_date', [$fromDate, $toDate]);
            // }

            // Add the 'from_date' filter if it exists
            $progressQuery->when($request->filled('from_date'), function ($progressQuery) use ($request) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                return $progressQuery->where('created_at', '>=', $fromDate);
            });

            // Add the 'to_date' filter if it exists
            $progressQuery->when($request->filled('to_date'), function ($progressQuery) use ($request) {
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                return $progressQuery->where('created_at', '<=', $toDate);
            });

            // Filter by program ID if provided
            if ($request->filled('program_id') && $request->program_id != NULL) {
                $progressQuery->where('program_id', $request->program_id);
            }

            // Execute the query
            $allTests = $progressQuery->orderBy('due_date', 'DESC')->get();
            $totalAllTests = $allTests->count();
            $finishedCount = $allTests->where('status', 1)->count();
            $overdueCount = $allTests->where('due_date', '<', \Carbon\Carbon::now()->format('Y-m-d'))
                ->where('status', '!=', 1)
                ->count();
            $pendingCount = $totalAllTests - $finishedCount - $overdueCount;
            // Calculate percentages as integers
            $finishedPercentage = $totalAllTests > 0 ? round(($finishedCount / $totalAllTests) * 100, 2) : 0;
            $overduePercentage = $totalAllTests > 0 ? round(($overdueCount / $totalAllTests) * 100, 2) : 0;
            $pendingPercentage = $totalAllTests > 0 ? round(($pendingCount / $totalAllTests) * 100, 2) : 0;

            // Filter by status if provided
            if ($request->filled('status') && $request->status != NULL) {
                $now = \Carbon\Carbon::now();
                $status = $request->status;
                switch ($status) {
                    case 'Completed':
                        $progressQuery->where('status', '1');
                        if ($progressQuery->get()->isEmpty()) {
                            $data['error'] = 'No Results Found';
                            return view('dashboard.reports.school.school_completion_report', $data);
                        }
                        break;
                    case 'Overdue':
                        $progressQuery->where('due_date', '<', $now->format('Y-m-d'))->where('status', '!=', 1);
                        if ($progressQuery->get()->isEmpty()) {
                            $data['error'] = 'No Results Found';
                            return view('dashboard.reports.school.school_completion_report', $data);
                        }
                        break;
                    case 'Pending':
                        $progressQuery->where('status', '0')->where('due_date', '>=', $now->format('Y-m-d'));
                        if ($progressQuery->get()->isEmpty()) {
                            $data['error'] = 'No Results Found';
                            return view('dashboard.reports.school.school_completion_report', $data);
                        }
                        break;
                    default:
                        // Invalid status provided
                        break;
                }
            }

            // Filter by assignment types if provided
            if ($request->filled('types') && $request->types != NULL) {
                $assignmentTypes = $request->types;
                $progressQuery->whereHas('tests', function ($q) use ($assignmentTypes) {
                    $q->join('test_types', 'tests.type', '=', 'test_types.id')
                        ->whereIn('test_types.id', $assignmentTypes);
                });
            }
            if ($progressQuery->get()->isEmpty()) {
                $data['error'] = 'No Results Found';
                return view('dashboard.reports.school.school_completion_report', $data);
            }
            // Execute the query
            $tests = $progressQuery->orderBy('due_date', 'DESC')->get();


            // Prepare response data
            $data['counts'] = [
                'completed' => $finishedCount,
                'overdue' => $overdueCount,
                'pending' => $pendingCount,
            ];
            $data['assignments_percentages'] = [
                'completed' => ceil($finishedPercentage),
                'overdue' => floor($overduePercentage),
                'pending' => ceil($pendingPercentage),
            ];
            $data['tests'] = $tests;
        }

        return view(
            'dashboard.reports.school.school_completion_report',
            $data
        );
    }
    public function teacherStudentsMasteryLevel(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            // $students = User::where('role', '2')->where("school_id", Auth::user()->school_id)->get();
            // $programs = Program::when(Auth::user()->hasRole('school'), function ($query) {
            //     return $query->where('school_id', Auth::user()->school_id);
            // })->get();
            $schoolId = Auth::user()->school_id;
            $programs = Program::with(['course', 'stage'])
                ->whereHas('schoolProgram', function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                })->get();
        } else {
            $programs = Program::all();
        }

        $schools = School::all();


        $data = [
            'schools' => $schools,
            'programs' => $programs,
            'request' => $request->all(),
        ];

        if ($request->has('school_id')) {
            // Retrieve all students in the specified group
            // $students = User::where('school_id', $request->school_id)->pluck('id');
            // dd($students);

            $teacher = User::find($request->teacher_id);


            $teacherGroups = $teacher->groupTeachers;
            $students = [];
            foreach ($teacherGroups as $teacherGroup) {
                $groupStudents = GroupStudent::where('group_id', $teacherGroup->group_id)->get();
                foreach ($groupStudents as $student) {
                    if (!in_array($student->student_id, $students)) {
                        $students[] = $student->student_id;
                    }
                }
            }
            if (empty($students)) {
                $data['error'] = 'No Students Found.';
                return view('dashboard.reports.instructor.instructor_student_mastery_report', $data);
            }

            // Initialize query builder for student progress
            $query = StudentProgress::whereIn('student_id', $students)
                ->where('program_id', $request->program_id);

            if ($query->get()->isEmpty()) {
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.instructor.instructor_student_mastery_report', $data);
            }

            // Apply filters if provided
            if ($request->has('unit_id')) {
                $query->where('unit_id', $request->unit_id);
            }
            if ($request->has('lesson_id')) {
                $query->where('lesson_id', $request->lesson_id);
            }
            if ($request->has('game_id')) {
                $query->whereHas('test', function ($q) use ($request) {
                    $q->where('game_id', $request->game_id);
                });
            }
            if ($request->has('skill_id')) {
                $query->whereHas('test.game.gameTypes.skills', function ($q) use ($request) {
                    $q->where('skill_id', $request->skill_id);
                });
            }

            if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            }

            $student_progress = $query->get();

            // Initialize arrays to hold data for grouping
            $unitsMastery = [];
            $lessonsMastery = [];
            $gamesMastery = [];
            $skillsMastery = [];

            if ($student_progress->isEmpty()) {
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.instructor.instructor_student_mastery_report', $data);
            }

            // Process each progress record
            foreach ($student_progress as $progress) {
                // Retrieve the test and its related game, game type, and skills
                $test = Test::with(['game.gameTypes.skills.skill'])->where('lesson_id', $progress->lesson_id)->find($progress->test_id);

                // Check if the test and its relationships are properly loaded
                if (!$test || !$test->game || !$test->game->gameTypes) {
                    continue; // Skip to the next progress record if any of these are null
                }

                // Get the game type (since each game has one game type)
                $gameType = $test->game->gameTypes;

                // Group by unit
                if (!isset($unitsMastery[$progress->unit_id])) {
                    $unitsMastery[$progress->unit_id] = [
                        'unit_id' => $progress->unit_id,
                        'name' => Unit::find($progress->unit_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                        'lessons' => [],
                    ];
                }

                // Group by lesson
                if (!isset($lessonsMastery[$progress->lesson_id])) {
                    $lessonsMastery[$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'name' => Unit::find(Lesson::find($progress->lesson_id)->unit_id)->name . " | " . Lesson::find($progress->lesson_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                        'games' => [],
                    ];
                }

                // Group by game type
                if (!isset($gameTypesMastery[$gameType->id])) {
                    $gameTypesMastery[$gameType->id] = [
                        'game_type_id' => $gameType->id,
                        'name' => GameType::find($gameType->id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'count' => 0,
                        'total_score' => 0,
                        'games' => [],
                    ];
                }

                // Group by game within the game type
                if (!isset($gameTypesMastery[$gameType->id]['games'][$test->game_id])) {
                    $gameTypesMastery[$gameType->id]['games'][$test->game_id] = [
                        'game_id' => $test->game_id,
                        'name' => Game::find($test->game_id)->name,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'count' => 0,
                        'total_score' => 0,
                    ];
                }

                // Group by skill
                if ($gameType && $gameType->skills) {
                    foreach ($gameType->skills->where('lesson_id', $progress->lesson_id)->unique('skill') as $gameSkill) {
                        $skill = $gameSkill->skill;

                        if (!isset($skillsMastery[$skill->id])) {
                            $skillsMastery[$skill->id] = [
                                'skill_id' => $skill->id,
                                'name' => $skill->skill,
                                'failed' => 0,
                                'introduced' => 0,
                                'practiced' => 0,
                                'mastered' => 0,
                                'total_attempts' => 0,
                                'total_score' => 0,
                                'mastery_percentage' => 0,
                            ];
                        }

                        $skillsMastery[$skill->id]['total_attempts']++;
                        if ($progress->is_done) {
                            if ($progress->score >= 80) {
                                $skillsMastery[$skill->id]['mastered']++;
                            } elseif ($progress->score >= 60) {
                                $skillsMastery[$skill->id]['practiced']++;
                            } elseif ($progress->score >= 30) {
                                $skillsMastery[$skill->id]['introduced']++;
                            } else {
                                $skillsMastery[$skill->id]['failed']++;
                            }
                        } else {
                            $skillsMastery[$skill->id]['failed']++;
                        }
                        $skillsMastery[$skill->id]['total_score'] += $progress->score;
                    }
                }

                // Update totals for units, lessons, and game types
                $unitsMastery[$progress->unit_id]['total_attempts']++;
                $lessonsMastery[$progress->lesson_id]['total_attempts']++;
                $gameTypesMastery[$gameType->id]['total_attempts']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_attempts']++;

                if ($progress->is_done) {
                    if ($progress->score >= 80) {
                        $unitsMastery[$progress->unit_id]['mastered']++;
                        $lessonsMastery[$progress->lesson_id]['mastered']++;
                        $gameTypesMastery[$gameType->id]['mastered']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['mastered']++;
                    } elseif ($progress->score >= 60) {
                        $unitsMastery[$progress->unit_id]['practiced']++;
                        $lessonsMastery[$progress->lesson_id]['practiced']++;
                        $gameTypesMastery[$gameType->id]['practiced']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['practiced']++;
                    } elseif ($progress->score >= 30) {
                        $unitsMastery[$progress->unit_id]['introduced']++;
                        $lessonsMastery[$progress->lesson_id]['introduced']++;
                        $gameTypesMastery[$gameType->id]['introduced']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['introduced']++;
                    } else {
                        $unitsMastery[$progress->unit_id]['failed']++;
                        $lessonsMastery[$progress->lesson_id]['failed']++;
                        $gameTypesMastery[$gameType->id]['failed']++;
                        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
                    }
                } else {
                    $unitsMastery[$progress->unit_id]['failed']++;
                    $lessonsMastery[$progress->lesson_id]['failed']++;
                    $gameTypesMastery[$gameType->id]['failed']++;
                    $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
                }

                $unitsMastery[$progress->unit_id]['total_score'] += $progress->score;
                $lessonsMastery[$progress->lesson_id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_score'] += $progress->score;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['count']++;

                // Group lessons under units
                if (!isset($unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id])) {
                    $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                    ];
                }

                // Aggregate lesson data under the unit
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['failed'] += $lessonsMastery[$progress->lesson_id]['failed'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['introduced'] += $lessonsMastery[$progress->lesson_id]['introduced'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['practiced'] += $lessonsMastery[$progress->lesson_id]['practiced'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['mastered'] += $lessonsMastery[$progress->lesson_id]['mastered'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_attempts'] += $lessonsMastery[$progress->lesson_id]['total_attempts'];
                $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_score'] += $lessonsMastery[$progress->lesson_id]['total_score'];
            }

            // Ensure all lessons are included in units
            foreach ($unitsMastery as &$unit) {
                foreach ($lessonsMastery as $lessonId => $lessonData) {
                    if (!isset($unit['lessons'][$lessonId])) {
                        $unit['lessons'][$lessonId] = [
                            'lesson_id' => $lessonId,
                            'failed' => 0,
                            'introduced' => 0,
                            'practiced' => 0,
                            'mastered' => 0,
                            'total_attempts' => 0,
                            'total_score' => 0,
                            'mastery_percentage' => 0,
                        ];
                    }
                }
            }


            // Calculate mastery percentages for units, lessons, games, and game types
            $unitChartLabels = [];
            $unitChartPercentage = [];
            foreach ($unitsMastery as &$unitData) {
                $unitData['mastery_percentage'] = $unitData['total_attempts'] > 0 ? round(($unitData['total_score'] / $unitData['total_attempts']), 2) : 0;

                foreach ($unitData['lessons'] as &$lessonData) {
                    $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? round(($lessonData['total_score'] / $lessonData['total_attempts']), 2) : 0;
                }

                $unitData['lessons'] = array_values($unitData['lessons']); // Convert lessons to array
                array_push($unitChartLabels, $unitData['name']);
                array_push($unitChartPercentage, $unitData['mastery_percentage']);
            }
            $lessonChartPercentage = [];
            $lessonChartLabels = [];
            foreach ($lessonsMastery as &$lessonData) {
                $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? round(($lessonData['total_score'] / $lessonData['total_attempts']), 2) : 0;
                array_push($lessonChartLabels, $lessonData['name']);
                array_push($lessonChartPercentage, $lessonData['mastery_percentage']);
            }


            $gameChartPercentage = [];
            $gameChartLabels = [];
            foreach ($gameTypesMastery as &$gameTypeData) {
                foreach ($gameTypeData['games'] as &$gameData) {
                    $gameData['mastery_percentage'] = $gameData['total_attempts'] > 0 ? round(($gameData['total_score'] / $gameData['total_attempts']), 2) : 0;
                    array_push($gameChartLabels, $gameData['name']);
                    array_push($gameChartPercentage, $gameData['mastery_percentage']);
                }
                $gameTypeData['games'] = array_values($gameTypeData['games']); // Convert games to array

                $gameTypeData['mastery_percentage'] = $gameTypeData['total_attempts'] > 0 ? round(($gameTypeData['total_score'] / $gameTypeData['total_attempts']), 2) : 0;
            }

            // Calculate skill mastery level based on mastered, practiced, introduced, and failed counts
            $skillChartPercentage = [];
            $skillChartLabels = [];
            foreach ($skillsMastery as &$skillData) {
                if ($skillData['mastered'] > $skillData['practiced'] && $skillData['mastered'] > $skillData['introduced'] && $skillData['mastered'] > $skillData['failed']) {
                    $skillData['current_level'] = 'mastered';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : round($skillData['total_score'] / $skillData['total_attempts'], 2);
                } elseif ($skillData['practiced'] > $skillData['introduced'] && $skillData['practiced'] > $skillData['failed']) {
                    $skillData['current_level'] = 'practiced';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : round($skillData['total_score'] / $skillData['total_attempts'], 2);
                } elseif ($skillData['introduced'] > $skillData['failed']) {
                    $skillData['current_level'] = 'introduced';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : round($skillData['total_score'] / $skillData['total_attempts'], 2);
                } else {
                    $skillData['current_level'] = 'failed';
                    $skillData['mastery_percentage'] = $skillData['total_score'] / $skillData['total_attempts'] > 100 ? 100 : round($skillData['total_score'] / $skillData['total_attempts'], 2);
                }
                array_push($skillChartLabels, $skillData['name']);
                array_push($skillChartPercentage, $skillData['mastery_percentage']);
            }

            // Prepare the response data
            if ($request->has('filter')) {
                switch ($request->filter) {
                    case 'Skill':
                        $data['skills'] = array_values($skillsMastery);
                        $data['chartLabels'] = $skillChartLabels;
                        $data['chartPercentage'] = $skillChartPercentage;
                        break;
                    case 'Unit':
                        $data['units'] = array_values($unitsMastery);
                        $data['chartLabels'] = $unitChartLabels;
                        $data['chartPercentage'] = $unitChartPercentage;
                        break;
                    case 'Lesson':
                        $data['lessons'] = array_values($lessonsMastery);
                        $data['chartLabels'] = $lessonChartLabels;
                        $data['chartPercentage'] = $lessonChartPercentage;
                        break;
                    case 'Game':
                        $data['games'] = array_values($gameTypesMastery);
                        $data['chartLabels'] = $gameChartLabels;
                        $data['chartPercentage'] = $gameChartPercentage;
                        break;
                    default:
                        $data['skills'] = array_values($skillsMastery);
                        $data['units'] = array_values($unitsMastery);
                        $data['lessons'] = array_values($lessonsMastery);
                        $data['games'] = array_values($gameTypesMastery);
                        break;
                }
            } else {
                $data['skills'] = array_values($skillsMastery);
                $data['units'] = array_values($unitsMastery);
                $data['lessons'] = array_values($lessonsMastery);
                $data['games'] = array_values($gameTypesMastery);
            }
        }
        // dd($data);
        // Return view with data
        return view('dashboard.reports.instructor.instructor_student_mastery_report', $data);
    }

    public function classContentEngagementReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $groups = Group::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $groups = Group::all();
        }
        if (Auth::user()->hasRole('school')) {
            $programs = Program::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $programs = Program::all();
        }
        // Initialize $data array with defaults
        $data = [
            'groups' => $groups,
            'programs' => $programs,
            'request' => $request->all(),
        ];
        if ($request->has('group_id')) {

            $students = GroupStudent::where('group_id', $request->group_id)->pluck('student_id');

            if ($students->isEmpty()) {
                // return redirect()->back()->with('error', 'No Students Found.');
                $data['error'] = 'No Students Found.';
                return view('dashboard.reports.class.class_content_engagement_report', $data);
            }

            // Initialize query builder for student progress
            $query = StudentProgress::whereIn('student_id', $students)
                ->where('program_id', $request->program_id);

            if ($query->get()->isEmpty()) {
                // return redirect()->back()->with('error', 'No Student Progress Found.');
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.class.class_content_engagement_report', $data);
            }

            if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            }

            $student_progress = $query->get();

            if ($student_progress->isEmpty()) {
                $data['error'] = 'No Student Progress Found.';
                return view('dashboard.reports.class.class_content_engagement_report', $data);
            }

            $unitEngagementCount = 0;
            $lessonEngagementCount = 0;
            $gameEngagementCount = 0;
            $skillEngagementCount = 0;
            foreach ($student_progress as $progress) {
                $test = Test::with(['game.gameTypes.skills.skill'])->where('lesson_id', $progress->lesson_id)->find($progress->test_id);


                // Check if the test and its relationships are properly loaded
                if (!$test || !$test->game || !$test->game->gameTypes) {
                    continue; // Skip to the next progress record if any of these are null
                }

                // Get the game type (since each game has one game type)
                $gameType = $test->game->gameTypes;
                // dd($gameType);

                // Group by unit
                if (!isset($unitsEngagement[$progress->unit_id])) {
                    $unitsEngagement[$progress->unit_id] = [
                        'unit_id' => $progress->unit_id,
                        'name' => Unit::find($progress->unit_id)->name,
                        'lessons' => [],
                        'engagement_count' => 0,
                        'engagement_percentage' => 0,
                    ];
                }
                $unitsEngagement[$progress->unit_id]['engagement_count']++;
                $unitEngagementCount++;


                // Group by lesson
                if (!isset($lessonsEngagement[$progress->lesson_id])) {
                    $lessonsEngagement[$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'name' => Unit::find(Lesson::find($progress->lesson_id)->unit_id)->name . " | " . Lesson::find($progress->lesson_id)->name,
                        'games' => [],
                        'engagement_count' => 0,
                        'engagement_percentage' => 0,
                    ];
                }
                $lessonsEngagement[$progress->lesson_id]['engagement_count']++;
                $lessonEngagementCount++;


                // Group by game type
                if (!isset($gameEngagement[$gameType->id])) {
                    $gameEngagement[$gameType->id] = [
                        'game_type_id' => $gameType->id,
                        'name' => GameType::find($gameType->id)->name,
                        'engagement_count' => 0,
                        'engagement_percentage' => 0,
                        'games' => [],
                    ];
                }
                $gameEngagement[$gameType->id]['engagement_count']++;
                $gameEngagementCount++;

                if ($gameType && $gameType->skills) {
                    foreach ($gameType->skills->where('lesson_id', $progress->lesson_id)->unique('skill') as $gameSkill) {
                        $skill = $gameSkill->skill;

                        if (!isset($skillsEngagement[$skill->id])) {
                            $skillsEngagement[$skill->id] = [
                                'skill_id' => $skill->id,
                                'name' => $skill->skill,
                                'engagement_count' => 0,
                                'engagement_percentage' => 0,
                            ];
                        }
                        $skillsEngagement[$skill->id]['engagement_count']++;
                        $skillEngagementCount++;
                    }
                }
            }

            if ($request->has('filter')) {
                switch ($request->filter) {
                    case 'Skill':
                        foreach ($skillsEngagement as $key => $Engagement) {
                            if ($skillEngagementCount > 0) {
                                $skillsEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $skillEngagementCount * 100, 2);
                            } else {
                                $skillsEngagement[$key]['engagement_percentage'] = 0;
                            }
                            $chartLabels[] = $skillsEngagement[$key]['name'];
                            $chartValues[] = $skillsEngagement[$key]['engagement_percentage'];
                        }
                        usort($skillsEngagement, function ($a, $b) {
                            return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                        });
                        $data['skillsEngagement'] = array_values($skillsEngagement);

                        $engagement_percentages = array_column($skillsEngagement, 'engagement_percentage');
                        sort($engagement_percentages);
                        $count = count($engagement_percentages);
                        $middleIndex = floor(($count - 1) / 2);
                        if ($count <= 1) {
                            $median = 0;
                        } else if ($count % 2) {
                            $median = $engagement_percentages[$middleIndex];
                        } else {
                            $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                        }

                        $highEngagementLabels = [];
                        $highEngagementValues = [];
                        $lowEngagementLabels = [];
                        $lowEngagementValues = [];

                        foreach ($skillsEngagement as $engagement) {
                            if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                $highEngagementLabels[] = $engagement['name'];
                                $highEngagementValues[] = $engagement['engagement_percentage'];
                            } else {
                                $lowEngagementLabels[] = $engagement['name'];
                                $lowEngagementValues[] = $engagement['engagement_percentage'];
                            }
                        }
                        $data['highEngagementLabels'] = $highEngagementLabels;
                        $data['highEngagementValues'] = $highEngagementValues;
                        $data['lowEngagementLabels'] = $lowEngagementLabels;
                        $data['lowEngagementValues'] = $lowEngagementValues;
                        break;
                    case 'Unit':
                        foreach ($unitsEngagement as $key => $Engagement) {
                            if ($unitEngagementCount > 0) {
                                $unitsEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $unitEngagementCount * 100, 2);
                            } else {
                                $unitsEngagement[$key]['engagement_percentage'] = 0;
                            }
                            $chartLabels[] = $unitsEngagement[$key]['name'];
                            $chartValues[] = $unitsEngagement[$key]['engagement_percentage'];
                        }
                        usort($unitsEngagement, function ($a, $b) {
                            return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                        });
                        $data['unitsEngagement'] = array_values($unitsEngagement);
                        $engagement_percentages = array_column($unitsEngagement, 'engagement_percentage');
                        sort($engagement_percentages);
                        $count = count($engagement_percentages);
                        $middleIndex = floor(($count - 1) / 2);
                        if ($count <= 1) {
                            $median = 0;
                        } else if ($count % 2) {
                            $median = $engagement_percentages[$middleIndex];
                        } else {
                            $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                        }

                        $highEngagementLabels = [];
                        $highEngagementValues = [];
                        $lowEngagementLabels = [];
                        $lowEngagementValues = [];

                        foreach ($unitsEngagement as $engagement) {
                            if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                $highEngagementLabels[] = $engagement['name'];
                                $highEngagementValues[] = $engagement['engagement_percentage'];
                            } else {
                                $lowEngagementLabels[] = $engagement['name'];
                                $lowEngagementValues[] = $engagement['engagement_percentage'];
                            }
                        }
                        $data['highEngagementLabels'] = $highEngagementLabels;
                        $data['highEngagementValues'] = $highEngagementValues;
                        $data['lowEngagementLabels'] = $lowEngagementLabels;
                        $data['lowEngagementValues'] = $lowEngagementValues;
                        break;
                    case 'Lesson':
                        foreach ($lessonsEngagement as $key => $Engagement) {
                            if ($lessonEngagementCount > 0) {
                                $lessonsEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $lessonEngagementCount * 100, 2);
                            } else {
                                $lessonsEngagement[$key]['engagement_percentage'] = 0;
                            }

                            $chartLabels[] = $lessonsEngagement[$key]['name'];
                            $chartValues[] = $lessonsEngagement[$key]['engagement_percentage'];
                        }
                        usort($lessonsEngagement, function ($a, $b) {
                            return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                        });
                        $data['lessonsEngagement'] = array_values($lessonsEngagement);
                        $engagement_percentages = array_column($lessonsEngagement, 'engagement_percentage');
                        sort($engagement_percentages);
                        $count = count($engagement_percentages);
                        $middleIndex = floor(($count - 1) / 2);
                        if ($count <= 1) {
                            $median = 0;
                        } else if ($count % 2) {
                            $median = $engagement_percentages[$middleIndex];
                        } else {
                            $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                        }

                        $highEngagementLabels = [];
                        $highEngagementValues = [];
                        $lowEngagementLabels = [];
                        $lowEngagementValues = [];

                        foreach ($lessonsEngagement as $engagement) {
                            if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                $highEngagementLabels[] = $engagement['name'];
                                $highEngagementValues[] = $engagement['engagement_percentage'];
                            } else {
                                $lowEngagementLabels[] = $engagement['name'];
                                $lowEngagementValues[] = $engagement['engagement_percentage'];
                            }
                        }
                        $data['highEngagementLabels'] = $highEngagementLabels;
                        $data['highEngagementValues'] = $highEngagementValues;
                        $data['lowEngagementLabels'] = $lowEngagementLabels;
                        $data['lowEngagementValues'] = $lowEngagementValues;
                        break;
                    case 'Game':
                        foreach ($gameEngagement as $key => $Engagement) {
                            if ($gameEngagementCount > 0) {
                                $gameEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $gameEngagementCount * 100, 2);
                            } else {
                                $gameEngagement[$key]['engagement_percentage'] = 0;
                            }
                            $chartLabels[] = $gameEngagement[$key]['name'];
                            $chartValues[] = $gameEngagement[$key]['engagement_percentage'];
                        }
                        usort($gameEngagement, function ($a, $b) {
                            return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                        });
                        $data['gameEngagement'] = array_values($gameEngagement);
                        $engagement_percentages = array_column($gameEngagement, 'engagement_percentage');
                        sort($engagement_percentages);
                        $count = count($engagement_percentages);
                        $middleIndex = floor(($count - 1) / 2);
                        if ($count <= 1) {
                            $median = 0;
                        } else if ($count % 2) {
                            $median = $engagement_percentages[$middleIndex];
                        } else {
                            $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                        }

                        $highEngagementLabels = [];
                        $highEngagementValues = [];
                        $lowEngagementLabels = [];
                        $lowEngagementValues = [];

                        foreach ($gameEngagement as $engagement) {
                            if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                $highEngagementLabels[] = $engagement['name'];
                                $highEngagementValues[] = $engagement['engagement_percentage'];
                            } else {
                                $lowEngagementLabels[] = $engagement['name'];
                                $lowEngagementValues[] = $engagement['engagement_percentage'];
                            }
                        }
                        $data['highEngagementLabels'] = $highEngagementLabels;
                        $data['highEngagementValues'] = $highEngagementValues;
                        $data['lowEngagementLabels'] = $lowEngagementLabels;
                        $data['lowEngagementValues'] = $lowEngagementValues;
                        break;
                    default:

                        break;
                }
            }
        }
        return view('dashboard.reports.class.class_content_engagement_report', $data);
    }
    public function schoolContentEngagementReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $schoolId = Auth::user()->school_id;
            $programs = Program::with(['course', 'stage'])
                ->whereHas('schoolProgram', function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                })->get();
        } else {
            $programs = Program::all();
        }

        $schools = School::all();
        // Initialize $data array with defaults
        $data = [
            'schools' => $schools,
            'programs' => $programs,
            'request' => $request->all(),
        ];
        if ($request->has('school_id')) {

            $students = User::where('role', '2')->where('school_id', $request->school_id)->pluck('id');

            if ($students->isEmpty()) {
                $data['error'] = 'No Students Found';
                return view('dashboard.reports.school.school_content_engagement_report', $data);
            }

            // Initialize query builder for student progress
            $query = StudentProgress::whereIn('student_id', $students)
                ->where('program_id', $request->program_id);

            // Add the 'from_date' filter if it exists
            $query->when($request->filled('from_date'), function ($query) use ($request) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                return $query->where('created_at', '>=', $fromDate);
            });

            // Add the 'to_date' filter if it exists
            $query->when($request->filled('to_date'), function ($query) use ($request) {
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                return $query->where('created_at', '<=', $toDate);
            });

            // Check if query is empty
            if ($query->get()->isEmpty()) {
                $data['error'] = 'No Student Progress Found';
                return view('dashboard.reports.school.school_content_engagement_report', $data);
            }

            $student_progress = $query->get();
            $groupStudentCount = $students->count();
            $totalActiveStudents = [];
            $unitEngagementCount = 0;
            $lessonEngagementCount = 0;
            $gameEngagementCount = 0;
            $skillEngagementCount = 0;
            foreach ($student_progress as $progress) {
                $test = Test::with(['game.gameTypes.skills.skill'])->where('lesson_id', $progress->lesson_id)->find($progress->test_id);


                // Check if the test and its relationships are properly loaded
                if (!$test || !$test->game || !$test->game->gameTypes) {
                    continue; // Skip to the next progress record if any of these are null
                }

                // Get the game type (since each game has one game type)
                $gameType = $test->game->gameTypes;
                // dd($gameType);

                // Group by unit
                if (!isset($unitsEngagement[$progress->unit_id])) {
                    $unitsEngagement[$progress->unit_id] = [
                        'unit_id' => $progress->unit_id,
                        'name' => Unit::find($progress->unit_id)->name,
                        'lessons' => [],
                        'engagement_count' => 0,
                        'engagement_percentage' => 0,
                    ];
                }
                $unitsEngagement[$progress->unit_id]['engagement_count']++;
                $unitEngagementCount++;


                // Group by lesson
                if (!isset($lessonsEngagement[$progress->lesson_id])) {
                    $lessonsEngagement[$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'name' => Unit::find(Lesson::find($progress->lesson_id)->unit_id)->name . " | " . Lesson::find($progress->lesson_id)->name,
                        'games' => [],
                        'engagement_count' => 0,
                        'engagement_percentage' => 0,
                    ];
                }
                $lessonsEngagement[$progress->lesson_id]['engagement_count']++;
                $lessonEngagementCount++;


                // Group by game type
                if (!isset($gameEngagement[$gameType->id])) {
                    $gameEngagement[$gameType->id] = [
                        'game_type_id' => $gameType->id,
                        'name' => GameType::find($gameType->id)->name,
                        'engagement_count' => 0,
                        'engagement_percentage' => 0,
                        'games' => [],
                    ];
                }
                $gameEngagement[$gameType->id]['engagement_count']++;
                $gameEngagementCount++;

                if ($gameType && $gameType->skills) {
                    foreach ($gameType->skills->where('lesson_id', $progress->lesson_id)->unique('skill') as $gameSkill) {
                        $skill = $gameSkill->skill;

                        if (!isset($skillsEngagement[$skill->id])) {
                            $skillsEngagement[$skill->id] = [
                                'skill_id' => $skill->id,
                                'name' => $skill->skill,
                                'engagement_count' => 0,
                                'engagement_percentage' => 0,
                            ];
                        }
                        $skillsEngagement[$skill->id]['engagement_count']++;
                        $skillEngagementCount++;
                    }
                }
            }

            if ($request->has('filter')) {
                switch ($request->filter) {
                    case 'Skill':
                        if (isset($skillsEngagement)) {
                            foreach ($skillsEngagement as $key => $Engagement) {
                                if ($skillEngagementCount > 0) {
                                    $skillsEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $skillEngagementCount * 100, 2);
                                } else {
                                    $skillsEngagement[$key]['engagement_percentage'] = 0;
                                }
                                $chartLabels[] = $skillsEngagement[$key]['name'];
                                $chartValues[] = $skillsEngagement[$key]['engagement_percentage'];
                            }
                            usort($skillsEngagement, function ($a, $b) {
                                return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                            });
                            $data['skillsEngagement'] = array_values($skillsEngagement);

                            $engagement_percentages = array_column($skillsEngagement, 'engagement_percentage');
                            sort($engagement_percentages);
                            $count = count($engagement_percentages);
                            $middleIndex = floor(($count - 1) / 2);
                            if ($count <= 1) {
                                $median = 0;
                            } else if ($count % 2) {
                                $median = $engagement_percentages[$middleIndex];
                            } else {
                                $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                            }

                            $highEngagementLabels = [];
                            $highEngagementValues = [];
                            $lowEngagementLabels = [];
                            $lowEngagementValues = [];

                            foreach ($skillsEngagement as $engagement) {
                                if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                    $highEngagementLabels[] = $engagement['name'];
                                    $highEngagementValues[] = $engagement['engagement_percentage'];
                                } else {
                                    $lowEngagementLabels[] = $engagement['name'];
                                    $lowEngagementValues[] = $engagement['engagement_percentage'];
                                }
                            }
                            $data['highEngagementLabels'] = $highEngagementLabels;
                            $data['highEngagementValues'] = $highEngagementValues;
                            $data['lowEngagementLabels'] = $lowEngagementLabels;
                            $data['lowEngagementValues'] = $lowEngagementValues;
                        } else {
                            $data['error'] = 'No Skills Found';
                            return view('dashboard.reports.school.school_content_engagement_report', $data);
                        }
                        break;
                    case 'Unit':
                        if (isset($unitsEngagement)) {
                            foreach ($unitsEngagement as $key => $Engagement) {
                                if ($unitEngagementCount > 0) {
                                    $unitsEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $unitEngagementCount * 100, 2);
                                } else {
                                    $unitsEngagement[$key]['engagement_percentage'] = 0;
                                }
                                $chartLabels[] = $unitsEngagement[$key]['name'];
                                $chartValues[] = $unitsEngagement[$key]['engagement_percentage'];
                            }
                            usort($unitsEngagement, function ($a, $b) {
                                return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                            });
                            $data['unitsEngagement'] = array_values($unitsEngagement);
                            $engagement_percentages = array_column($unitsEngagement, 'engagement_percentage');
                            sort($engagement_percentages);
                            $count = count($engagement_percentages);
                            $middleIndex = floor(($count - 1) / 2);
                            if ($count <= 1) {
                                $median = 0;
                            } else if ($count % 2) {
                                $median = $engagement_percentages[$middleIndex];
                            } else {
                                $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                            }

                            $highEngagementLabels = [];
                            $highEngagementValues = [];
                            $lowEngagementLabels = [];
                            $lowEngagementValues = [];

                            foreach ($unitsEngagement as $engagement) {
                                if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                    $highEngagementLabels[] = $engagement['name'];
                                    $highEngagementValues[] = $engagement['engagement_percentage'];
                                } else {
                                    $lowEngagementLabels[] = $engagement['name'];
                                    $lowEngagementValues[] = $engagement['engagement_percentage'];
                                }
                            }
                            $data['highEngagementLabels'] = $highEngagementLabels;
                            $data['highEngagementValues'] = $highEngagementValues;
                            $data['lowEngagementLabels'] = $lowEngagementLabels;
                            $data['lowEngagementValues'] = $lowEngagementValues;
                        } else {
                            $data['error'] = 'No Units Found';
                            return view('dashboard.reports.school.school_content_engagement_report', $data);
                        }
                        break;
                    case 'Lesson':
                        if (isset($lessonsEngagement)) {
                            foreach ($lessonsEngagement as $key => $Engagement) {
                                if ($lessonEngagementCount > 0) {
                                    $lessonsEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $lessonEngagementCount * 100, 2);
                                } else {
                                    $lessonsEngagement[$key]['engagement_percentage'] = 0;
                                }

                                $chartLabels[] = $lessonsEngagement[$key]['name'];
                                $chartValues[] = $lessonsEngagement[$key]['engagement_percentage'];
                            }
                            usort($lessonsEngagement, function ($a, $b) {
                                return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                            });
                            $data['lessonsEngagement'] = array_values($lessonsEngagement);
                            $engagement_percentages = array_column($lessonsEngagement, 'engagement_percentage');
                            sort($engagement_percentages);
                            $count = count($engagement_percentages);
                            $middleIndex = floor(($count - 1) / 2);
                            if ($count <= 1) {
                                $median = 0;
                            } else if ($count % 2) {
                                $median = $engagement_percentages[$middleIndex];
                            } else {
                                $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                            }

                            $highEngagementLabels = [];
                            $highEngagementValues = [];
                            $lowEngagementLabels = [];
                            $lowEngagementValues = [];

                            foreach ($lessonsEngagement as $engagement) {
                                if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                    $highEngagementLabels[] = $engagement['name'];
                                    $highEngagementValues[] = $engagement['engagement_percentage'];
                                } else {
                                    $lowEngagementLabels[] = $engagement['name'];
                                    $lowEngagementValues[] = $engagement['engagement_percentage'];
                                }
                            }
                            $data['highEngagementLabels'] = $highEngagementLabels;
                            $data['highEngagementValues'] = $highEngagementValues;
                            $data['lowEngagementLabels'] = $lowEngagementLabels;
                            $data['lowEngagementValues'] = $lowEngagementValues;
                        } else {
                            $data['error'] = 'No Lessons Found';
                            return view('dashboard.reports.school.school_content_engagement_report', $data);
                        }
                        break;
                    case 'Game':
                        if (isset($gameEngagement)) {
                            foreach ($gameEngagement as $key => $Engagement) {
                                if ($gameEngagementCount > 0) {
                                    $gameEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $gameEngagementCount * 100, 2);
                                } else {
                                    $gameEngagement[$key]['engagement_percentage'] = 0;
                                }
                                $chartLabels[] = $gameEngagement[$key]['name'];
                                $chartValues[] = $gameEngagement[$key]['engagement_percentage'];
                            }
                            usort($gameEngagement, function ($a, $b) {
                                return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                            });
                            $data['gameEngagement'] = array_values($gameEngagement);
                            $engagement_percentages = array_column($gameEngagement, 'engagement_percentage');
                            sort($engagement_percentages);
                            $count = count($engagement_percentages);
                            $middleIndex = floor(($count - 1) / 2);
                            if ($count <= 1) {
                                $median = 0;
                            } else if ($count % 2) {
                                $median = $engagement_percentages[$middleIndex];
                            } else {
                                $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                            }

                            $highEngagementLabels = [];
                            $highEngagementValues = [];
                            $lowEngagementLabels = [];
                            $lowEngagementValues = [];

                            foreach ($gameEngagement as $engagement) {
                                if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                    $highEngagementLabels[] = $engagement['name'];
                                    $highEngagementValues[] = $engagement['engagement_percentage'];
                                } else {
                                    $lowEngagementLabels[] = $engagement['name'];
                                    $lowEngagementValues[] = $engagement['engagement_percentage'];
                                }
                            }
                            $data['highEngagementLabels'] = $highEngagementLabels;
                            $data['highEngagementValues'] = $highEngagementValues;
                            $data['lowEngagementLabels'] = $lowEngagementLabels;
                            $data['lowEngagementValues'] = $lowEngagementValues;
                        } else {
                            $data['error'] = 'No Games Found';
                            return view('dashboard.reports.school.school_content_engagement_report', $data);
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        return view('dashboard.reports.school.school_content_engagement_report', $data);
    }

    public function teacherContentEngagementReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $programs = Program::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $programs = Program::all();
        }

        $schools = School::all();
        // Initialize $data array with defaults
        $data = [
            'schools' => $schools,
            'programs' => $programs,
            'request' => $request->all(),
        ];
        if ($request->has('school_id')) {

            $students = User::where('role', '2')->where('school_id', $request->school_id)->pluck('id');
            $teacher = User::find($request->teacher_id);


            $teacherGroups = $teacher->groupTeachers;
            $students = [];
            foreach ($teacherGroups as $teacherGroup) {
                $groupStudents = GroupStudent::where('group_id', $teacherGroup->group_id)->get();
                foreach ($groupStudents as $student) {
                    if (!in_array($student->student_id, $students)) {
                        $students[] = $student->student_id;
                    }
                }
            }
            if (empty($students)) {
                $data['error'] = 'No Students Found';
                return view('dashboard.reports.instructor.instructor_content_engagement_report', $data);
            }

            // Initialize query builder for student progress
            $query = StudentProgress::whereIn('student_id', $students)
                ->where('program_id', $request->program_id);

            // Add the 'from_date' filter if it exists
            $query->when($request->filled('from_date'), function ($query) use ($request) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                return $query->where('created_at', '>=', $fromDate);
            });

            // Add the 'to_date' filter if it exists
            $query->when($request->filled('to_date'), function ($query) use ($request) {
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                return $query->where('created_at', '<=', $toDate);
            });

            // Check if query is empty
            if ($query->get()->isEmpty()) {
                $data['error'] = 'No Student Progress Found';
                return view('dashboard.reports.instructor.instructor_content_engagement_report', $data);
            }

            $student_progress = $query->get();

            $unitEngagementCount = 0;
            $lessonEngagementCount = 0;
            $gameEngagementCount = 0;
            $skillEngagementCount = 0;
            foreach ($student_progress as $progress) {
                $test = Test::with(['game.gameTypes.skills.skill'])->where('lesson_id', $progress->lesson_id)->find($progress->test_id);


                // Check if the test and its relationships are properly loaded
                if (!$test || !$test->game || !$test->game->gameTypes) {
                    continue; // Skip to the next progress record if any of these are null
                }

                // Get the game type (since each game has one game type)
                $gameType = $test->game->gameTypes;
                // dd($gameType);

                // Group by unit
                if (!isset($unitsEngagement[$progress->unit_id])) {
                    $unitsEngagement[$progress->unit_id] = [
                        'unit_id' => $progress->unit_id,
                        'name' => Unit::find($progress->unit_id)->name,
                        'lessons' => [],
                        'engagement_count' => 0,
                        'engagement_percentage' => 0,
                    ];
                }
                $unitsEngagement[$progress->unit_id]['engagement_count']++;
                $unitEngagementCount++;


                // Group by lesson
                if (!isset($lessonsEngagement[$progress->lesson_id])) {
                    $lessonsEngagement[$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'name' => Unit::find(Lesson::find($progress->lesson_id)->unit_id)->name . " | " . Lesson::find($progress->lesson_id)->name,
                        'games' => [],
                        'engagement_count' => 0,
                        'engagement_percentage' => 0,
                    ];
                }
                $lessonsEngagement[$progress->lesson_id]['engagement_count']++;
                $lessonEngagementCount++;


                // Group by game type
                if (!isset($gameEngagement[$gameType->id])) {
                    $gameEngagement[$gameType->id] = [
                        'game_type_id' => $gameType->id,
                        'name' => GameType::find($gameType->id)->name,
                        'engagement_count' => 0,
                        'engagement_percentage' => 0,
                        'games' => [],
                    ];
                }
                $gameEngagement[$gameType->id]['engagement_count']++;
                $gameEngagementCount++;

                if ($gameType && $gameType->skills) {
                    foreach ($gameType->skills->where('lesson_id', $progress->lesson_id)->unique('skill') as $gameSkill) {
                        $skill = $gameSkill->skill;

                        if (!isset($skillsEngagement[$skill->id])) {
                            $skillsEngagement[$skill->id] = [
                                'skill_id' => $skill->id,
                                'name' => $skill->skill,
                                'engagement_count' => 0,
                                'engagement_percentage' => 0,
                            ];
                        }
                        $skillsEngagement[$skill->id]['engagement_count']++;
                        $skillEngagementCount++;
                    }
                }
            }

            if ($request->has('filter')) {
                switch ($request->filter) {
                    case 'Skill':
                        if (isset($skillsEngagement)) {
                            foreach ($skillsEngagement as $key => $Engagement) {
                                if ($skillEngagementCount > 0) {
                                    $skillsEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $skillEngagementCount * 100, 2);
                                } else {
                                    $skillsEngagement[$key]['engagement_percentage'] = 0;
                                }
                                $chartLabels[] = $skillsEngagement[$key]['name'];
                                $chartValues[] = $skillsEngagement[$key]['engagement_percentage'];
                            }
                            usort($skillsEngagement, function ($a, $b) {
                                return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                            });
                            $data['skillsEngagement'] = array_values($skillsEngagement);

                            $engagement_percentages = array_column($skillsEngagement, 'engagement_percentage');
                            sort($engagement_percentages);
                            $count = count($engagement_percentages);
                            $middleIndex = floor(($count - 1) / 2);
                            if ($count <= 1) {
                                $median = 0;
                            } else if ($count % 2) {
                                $median = $engagement_percentages[$middleIndex];
                            } else {
                                $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                            }

                            $highEngagementLabels = [];
                            $highEngagementValues = [];
                            $lowEngagementLabels = [];
                            $lowEngagementValues = [];

                            foreach ($skillsEngagement as $engagement) {
                                if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                    $highEngagementLabels[] = $engagement['name'];
                                    $highEngagementValues[] = $engagement['engagement_percentage'];
                                } else {
                                    $lowEngagementLabels[] = $engagement['name'];
                                    $lowEngagementValues[] = $engagement['engagement_percentage'];
                                }
                            }
                            $data['highEngagementLabels'] = $highEngagementLabels;
                            $data['highEngagementValues'] = $highEngagementValues;
                            $data['lowEngagementLabels'] = $lowEngagementLabels;
                            $data['lowEngagementValues'] = $lowEngagementValues;
                        } else {
                            $data['error'] = 'No Skills Found';
                            return view('dashboard.reports.instructor.instructor_content_engagement_report', $data);
                        }
                        break;
                    case 'Unit':
                        if (isset($unitsEngagement)) {
                            foreach ($unitsEngagement as $key => $Engagement) {
                                if ($unitEngagementCount > 0) {
                                    $unitsEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $unitEngagementCount * 100, 2);
                                } else {
                                    $unitsEngagement[$key]['engagement_percentage'] = 0;
                                }
                                $chartLabels[] = $unitsEngagement[$key]['name'];
                                $chartValues[] = $unitsEngagement[$key]['engagement_percentage'];
                            }
                            usort($unitsEngagement, function ($a, $b) {
                                return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                            });
                            $data['unitsEngagement'] = array_values($unitsEngagement);
                            $engagement_percentages = array_column($unitsEngagement, 'engagement_percentage');
                            sort($engagement_percentages);
                            $count = count($engagement_percentages);
                            $middleIndex = floor(($count - 1) / 2);
                            if ($count <= 1) {
                                $median = 0;
                            } else if ($count % 2) {
                                $median = $engagement_percentages[$middleIndex];
                            } else {
                                $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                            }

                            $highEngagementLabels = [];
                            $highEngagementValues = [];
                            $lowEngagementLabels = [];
                            $lowEngagementValues = [];

                            foreach ($unitsEngagement as $engagement) {
                                if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                    $highEngagementLabels[] = $engagement['name'];
                                    $highEngagementValues[] = $engagement['engagement_percentage'];
                                } else {
                                    $lowEngagementLabels[] = $engagement['name'];
                                    $lowEngagementValues[] = $engagement['engagement_percentage'];
                                }
                            }
                            $data['highEngagementLabels'] = $highEngagementLabels;
                            $data['highEngagementValues'] = $highEngagementValues;
                            $data['lowEngagementLabels'] = $lowEngagementLabels;
                            $data['lowEngagementValues'] = $lowEngagementValues;
                        } else {
                            $data['error'] = 'No Units Found';
                            return view('dashboard.reports.instructor.instructor_content_engagement_report', $data);
                        }
                        break;
                    case 'Lesson':
                        if (isset($lessonsEngagement)) {
                            foreach ($lessonsEngagement as $key => $Engagement) {
                                if ($lessonEngagementCount > 0) {
                                    $lessonsEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $lessonEngagementCount * 100, 2);
                                } else {
                                    $lessonsEngagement[$key]['engagement_percentage'] = 0;
                                }

                                $chartLabels[] = $lessonsEngagement[$key]['name'];
                                $chartValues[] = $lessonsEngagement[$key]['engagement_percentage'];
                            }
                            usort($lessonsEngagement, function ($a, $b) {
                                return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                            });
                            $data['lessonsEngagement'] = array_values($lessonsEngagement);
                            $engagement_percentages = array_column($lessonsEngagement, 'engagement_percentage');
                            sort($engagement_percentages);
                            $count = count($engagement_percentages);
                            $middleIndex = floor(($count - 1) / 2);
                            if ($count <= 1) {
                                $median = 0;
                            } else if ($count % 2) {
                                $median = $engagement_percentages[$middleIndex];
                            } else {
                                $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                            }

                            $highEngagementLabels = [];
                            $highEngagementValues = [];
                            $lowEngagementLabels = [];
                            $lowEngagementValues = [];

                            foreach ($lessonsEngagement as $engagement) {
                                if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                    $highEngagementLabels[] = $engagement['name'];
                                    $highEngagementValues[] = $engagement['engagement_percentage'];
                                } else {
                                    $lowEngagementLabels[] = $engagement['name'];
                                    $lowEngagementValues[] = $engagement['engagement_percentage'];
                                }
                            }
                            $data['highEngagementLabels'] = $highEngagementLabels;
                            $data['highEngagementValues'] = $highEngagementValues;
                            $data['lowEngagementLabels'] = $lowEngagementLabels;
                            $data['lowEngagementValues'] = $lowEngagementValues;
                        } else {
                            $data['error'] = 'No Lessons Found';
                            return view('dashboard.reports.instructor.instructor_content_engagement_report', $data);
                        }
                        break;
                    case 'Game':
                        if (isset($gameEngagement)) {
                            foreach ($gameEngagement as $key => $Engagement) {
                                if ($gameEngagementCount > 0) {
                                    $gameEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $gameEngagementCount * 100, 2);
                                } else {
                                    $gameEngagement[$key]['engagement_percentage'] = 0;
                                }
                                $chartLabels[] = $gameEngagement[$key]['name'];
                                $chartValues[] = $gameEngagement[$key]['engagement_percentage'];
                            }
                            usort($gameEngagement, function ($a, $b) {
                                return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                            });
                            $data['gameEngagement'] = array_values($gameEngagement);
                            $engagement_percentages = array_column($gameEngagement, 'engagement_percentage');
                            sort($engagement_percentages);
                            $count = count($engagement_percentages);
                            $middleIndex = floor(($count - 1) / 2);
                            if ($count <= 1) {
                                $median = 0;
                            } else if ($count % 2) {
                                $median = $engagement_percentages[$middleIndex];
                            } else {
                                $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                            }

                            $highEngagementLabels = [];
                            $highEngagementValues = [];
                            $lowEngagementLabels = [];
                            $lowEngagementValues = [];

                            foreach ($gameEngagement as $engagement) {
                                if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                    $highEngagementLabels[] = $engagement['name'];
                                    $highEngagementValues[] = $engagement['engagement_percentage'];
                                } else {
                                    $lowEngagementLabels[] = $engagement['name'];
                                    $lowEngagementValues[] = $engagement['engagement_percentage'];
                                }
                            }
                            $data['highEngagementLabels'] = $highEngagementLabels;
                            $data['highEngagementValues'] = $highEngagementValues;
                            $data['lowEngagementLabels'] = $lowEngagementLabels;
                            $data['lowEngagementValues'] = $lowEngagementValues;
                        } else {
                            $data['error'] = 'No Games Found';
                            return view('dashboard.reports.instructor.instructor_content_engagement_report', $data);
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        return view('dashboard.reports.instructor.instructor_content_engagement_report', $data);
    }
    public function studentContentEngagementReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $programs = Program::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $programs = Program::all();
        }

        $schools = School::all();
        // Initialize $data array with defaults
        $data = [
            'schools' => $schools,
            'programs' => $programs,
            'request' => $request->all(),
        ];
        if ($request->has('student_id')) {
            // Initialize query builder for student progress
            $query = StudentProgress::where('student_id', $request->student_id)
                ->where('program_id', $request->program_id);

            if ($query->get()->isEmpty()) {
                // return redirect()->back()->with('error', 'No Student Progress Found.');
                $data['error'] = 'No Student Progress Found';
                return view('dashboard.reports.student_content_engagement_report', $data);
            }

            if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            }

            // Add the 'from_date' filter if it exists
            $query->when($request->filled('from_date'), function ($query) use ($request) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
                return $query->where('created_at', '>=', $fromDate);
            });

            // Add the 'to_date' filter if it exists
            $query->when($request->filled('to_date'), function ($query) use ($request) {
                $toDate = Carbon::parse($request->to_date)->endOfDay();
                return $query->where('created_at', '<=', $toDate);
            });
            if ($query->get()->isEmpty()) {
                $data['error'] = 'No Results Found for this Date.';
                return view('dashboard.reports.student_content_engagement_report', $data);
            }

            $student_progress = $query->get();
            $unitEngagementCount = 0;
            $lessonEngagementCount = 0;
            $gameEngagementCount = 0;
            $skillEngagementCount = 0;
            foreach ($student_progress as $progress) {
                $test = Test::with(['game.gameTypes.skills.skill'])->where('lesson_id', $progress->lesson_id)->find($progress->test_id);

                // Check if the test and its relationships are properly loaded
                if (!$test || !$test->game || !$test->game->gameTypes) {
                    continue; // Skip to the next progress record if any of these are null
                }

                // Get the game type (since each game has one game type)
                $gameType = $test->game->gameTypes;
                // dd($gameType);

                // Group by unit
                if (!isset($unitsEngagement[$progress->unit_id])) {
                    $unitsEngagement[$progress->unit_id] = [
                        'unit_id' => $progress->unit_id,
                        'name' => Unit::find($progress->unit_id)->name,
                        'lessons' => [],
                        'engagement_count' => 0,
                        'engagement_percentage' => 0,
                    ];
                }
                $unitsEngagement[$progress->unit_id]['engagement_count']++;
                $unitEngagementCount++;


                // Group by lesson
                if (!isset($lessonsEngagement[$progress->lesson_id])) {
                    $lessonsEngagement[$progress->lesson_id] = [
                        'lesson_id' => $progress->lesson_id,
                        'name' => Unit::find(Lesson::find($progress->lesson_id)->unit_id)->name . " | " . Lesson::find($progress->lesson_id)->name,
                        'games' => [],
                        'engagement_count' => 0,
                        'engagement_percentage' => 0,
                    ];
                }
                $lessonsEngagement[$progress->lesson_id]['engagement_count']++;
                $lessonEngagementCount++;


                // Group by game type
                if (!isset($gameEngagement[$gameType->id])) {
                    $gameEngagement[$gameType->id] = [
                        'game_type_id' => $gameType->id,
                        'name' => GameType::find($gameType->id)->name,
                        'engagement_count' => 0,
                        'engagement_percentage' => 0,
                        'games' => [],
                    ];
                }
                $gameEngagement[$gameType->id]['engagement_count']++;
                $gameEngagementCount++;

                if ($gameType && $gameType->skills) {
                    foreach ($gameType->skills->where('lesson_id', $progress->lesson_id)->unique('skill') as $gameSkill) {
                        $skill = $gameSkill->skill;

                        if (!isset($skillsEngagement[$skill->id])) {
                            $skillsEngagement[$skill->id] = [
                                'skill_id' => $skill->id,
                                'name' => $skill->skill,
                                'engagement_count' => 0,
                                'engagement_percentage' => 0,
                            ];
                        }
                        $skillsEngagement[$skill->id]['engagement_count']++;
                        $skillEngagementCount++;
                    }
                }
            }

            if ($request->has('filter')) {
                switch ($request->filter) {
                    case 'Skill':
                        if (isset($skillsEngagement)) {
                            foreach ($skillsEngagement as $key => $Engagement) {
                                if ($skillEngagementCount > 0) {
                                    $skillsEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $skillEngagementCount * 100, 2);
                                } else {
                                    $skillsEngagement[$key]['engagement_percentage'] = 0;
                                }
                                $chartLabels[] = $skillsEngagement[$key]['name'];
                                $chartValues[] = $skillsEngagement[$key]['engagement_percentage'];
                            }
                            usort($skillsEngagement, function ($a, $b) {
                                return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                            });
                            $data['skillsEngagement'] = array_values($skillsEngagement);

                            $engagement_percentages = array_column($skillsEngagement, 'engagement_percentage');
                            sort($engagement_percentages);
                            $count = count($engagement_percentages);
                            $middleIndex = floor(($count - 1) / 2);
                            if ($count <= 1) {
                                $median = 0;
                            } else if ($count % 2) {
                                $median = $engagement_percentages[$middleIndex];
                            } else {
                                $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                            }

                            $highEngagementLabels = [];
                            $highEngagementValues = [];
                            $lowEngagementLabels = [];
                            $lowEngagementValues = [];

                            foreach ($skillsEngagement as $engagement) {
                                if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                    $highEngagementLabels[] = $engagement['name'];
                                    $highEngagementValues[] = $engagement['engagement_percentage'];
                                } else {
                                    $lowEngagementLabels[] = $engagement['name'];
                                    $lowEngagementValues[] = $engagement['engagement_percentage'];
                                }
                            }
                            $data['highEngagementLabels'] = $highEngagementLabels;
                            $data['highEngagementValues'] = $highEngagementValues;
                            $data['lowEngagementLabels'] = $lowEngagementLabels;
                            $data['lowEngagementValues'] = $lowEngagementValues;
                        } else {
                            $data['error'] = 'No Skills Found';
                            return view('dashboard.reports.student_content_engagement_report', $data);
                        }

                        break;
                    case 'Unit':
                        foreach ($unitsEngagement as $key => $Engagement) {
                            if ($unitEngagementCount > 0) {
                                $unitsEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $unitEngagementCount * 100, 2);
                            } else {
                                $unitsEngagement[$key]['engagement_percentage'] = 0;
                            }
                            $chartLabels[] = $unitsEngagement[$key]['name'];
                            $chartValues[] = $unitsEngagement[$key]['engagement_percentage'];
                        }
                        usort($unitsEngagement, function ($a, $b) {
                            return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                        });
                        $data['unitsEngagement'] = array_values($unitsEngagement);
                        $engagement_percentages = array_column($unitsEngagement, 'engagement_percentage');
                        sort($engagement_percentages);
                        $count = count($engagement_percentages);
                        $middleIndex = floor(($count - 1) / 2);
                        if ($count <= 1) {
                            $median = 0;
                        } else if ($count % 2) {
                            $median = $engagement_percentages[$middleIndex];
                        } else {
                            $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                        }

                        $highEngagementLabels = [];
                        $highEngagementValues = [];
                        $lowEngagementLabels = [];
                        $lowEngagementValues = [];

                        foreach ($unitsEngagement as $engagement) {
                            if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                $highEngagementLabels[] = $engagement['name'];
                                $highEngagementValues[] = $engagement['engagement_percentage'];
                            } else {
                                $lowEngagementLabels[] = $engagement['name'];
                                $lowEngagementValues[] = $engagement['engagement_percentage'];
                            }
                        }
                        $data['highEngagementLabels'] = $highEngagementLabels;
                        $data['highEngagementValues'] = $highEngagementValues;
                        $data['lowEngagementLabels'] = $lowEngagementLabels;
                        $data['lowEngagementValues'] = $lowEngagementValues;
                        break;
                    case 'Lesson':
                        foreach ($lessonsEngagement as $key => $Engagement) {
                            if ($lessonEngagementCount > 0) {
                                $lessonsEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $lessonEngagementCount * 100, 2);
                            } else {
                                $lessonsEngagement[$key]['engagement_percentage'] = 0;
                            }

                            $chartLabels[] = $lessonsEngagement[$key]['name'];
                            $chartValues[] = $lessonsEngagement[$key]['engagement_percentage'];
                        }
                        usort($lessonsEngagement, function ($a, $b) {
                            return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                        });
                        $data['lessonsEngagement'] = array_values($lessonsEngagement);
                        $engagement_percentages = array_column($lessonsEngagement, 'engagement_percentage');
                        sort($engagement_percentages);
                        $count = count($engagement_percentages);
                        $middleIndex = floor(($count - 1) / 2);
                        if ($count <= 1) {
                            $median = 0;
                        } else if ($count % 2) {
                            $median = $engagement_percentages[$middleIndex];
                        } else {
                            $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                        }

                        $highEngagementLabels = [];
                        $highEngagementValues = [];
                        $lowEngagementLabels = [];
                        $lowEngagementValues = [];

                        foreach ($lessonsEngagement as $engagement) {
                            if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                $highEngagementLabels[] = $engagement['name'];
                                $highEngagementValues[] = $engagement['engagement_percentage'];
                            } else {
                                $lowEngagementLabels[] = $engagement['name'];
                                $lowEngagementValues[] = $engagement['engagement_percentage'];
                            }
                        }
                        $data['highEngagementLabels'] = $highEngagementLabels;
                        $data['highEngagementValues'] = $highEngagementValues;
                        $data['lowEngagementLabels'] = $lowEngagementLabels;
                        $data['lowEngagementValues'] = $lowEngagementValues;
                        break;
                    case 'Game':
                        foreach ($gameEngagement as $key => $Engagement) {
                            if ($gameEngagementCount > 0) {
                                $gameEngagement[$key]['engagement_percentage'] = round($Engagement['engagement_count'] / $gameEngagementCount * 100, 2);
                            } else {
                                $gameEngagement[$key]['engagement_percentage'] = 0;
                            }
                            $chartLabels[] = $gameEngagement[$key]['name'];
                            $chartValues[] = $gameEngagement[$key]['engagement_percentage'];
                        }
                        usort($gameEngagement, function ($a, $b) {
                            return $a['engagement_percentage'] <=> $b['engagement_percentage'];
                        });
                        $data['gameEngagement'] = array_values($gameEngagement);
                        $engagement_percentages = array_column($gameEngagement, 'engagement_percentage');
                        sort($engagement_percentages);
                        $count = count($engagement_percentages);
                        $middleIndex = floor(($count - 1) / 2);
                        if ($count <= 1) {
                            $median = 0;
                        } else if ($count % 2) {
                            $median = $engagement_percentages[$middleIndex];
                        } else {
                            $median = ($engagement_percentages[$middleIndex] + $engagement_percentages[$middleIndex + 1]) / 2;
                        }

                        $highEngagementLabels = [];
                        $highEngagementValues = [];
                        $lowEngagementLabels = [];
                        $lowEngagementValues = [];

                        foreach ($gameEngagement as $engagement) {
                            if ($engagement['engagement_percentage'] > $median || $engagement['engagement_percentage'] >= 50) {
                                $highEngagementLabels[] = $engagement['name'];
                                $highEngagementValues[] = $engagement['engagement_percentage'];
                            } else {
                                $lowEngagementLabels[] = $engagement['name'];
                                $lowEngagementValues[] = $engagement['engagement_percentage'];
                            }
                        }
                        $data['highEngagementLabels'] = $highEngagementLabels;
                        $data['highEngagementValues'] = $highEngagementValues;
                        $data['lowEngagementLabels'] = $lowEngagementLabels;
                        $data['lowEngagementValues'] = $lowEngagementValues;
                        break;
                    default:

                        break;
                }
            }
        }
        return view('dashboard.reports.student_content_engagement_report', $data);
    }

    public function classContentUsageReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $groups = Group::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $groups = Group::all();
        }

        // Initialize $data array with defaults
        $data = [
            'groups' => $groups,
            'request' => $request->all(),
        ];
        if ($request->has('group_id')) {
            if ($request->filled('program_id')) {
                $selectedPrograms = Program::where('id', $request->program_id)->get();
            } else {
                $selectedPrograms = Program::whereIn('id', Group::with(['groupCourses'])->findOrFail($request->group_id)->groupCourses->pluck('program_id'))->get();
            }

            $students = GroupStudent::where('group_id', $request->group_id)->pluck('student_id');
            // $students = GroupStudent::whereIn('student_id', [239, 504])->pluck('student_id');
            if ($students->isEmpty()) {
                $data['error'] = 'No Students Found.';

                // Return the view with the $data array
                return view('dashboard.reports.class.class_content_usage_report', $data);
            }

            // Fetch student tests and filter for valid test IDs
            $student_tests = StudentTest::whereIn('student_id', $students)
                ->whereIn('program_id', $selectedPrograms->pluck('id'))
                ->when($request->filled('from_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '<=', $request->to_date);
                })
                ->whereNotNull('test_id')
                ->get();
            // dd($student_tests);

            // Check if there are multiple students
            if ($students->count() > 1) {
                // Group the results by test_id
                $grouped_tests = $student_tests->groupBy('test_id');
                $common_tests = [];

                foreach ($grouped_tests as $test) {
                    $studentsTest = [];
                    foreach ($test as $testStudent) {
                        if (!in_array($testStudent->student_id, $studentsTest))
                            $studentsTest[] = $testStudent->student_id;
                    }
                    if (count($studentsTest) == $students->count()) {
                        $common_tests[] = $testStudent->test_id;
                    }
                }
                // dd($common_tests);
            } else {
                // If there's only one student, skip filtering
                $common_tests = $student_tests->pluck('test_id');
            }


            // Fetch the actual test records for the common tests
            $student_assignments = Test::whereIn('id', $common_tests)->get();
            // dd($student_assignments);

            if ($student_assignments->isEmpty()) {
                // Add an error message to the data array
                $data['error'] = 'No Assignments Found for All Students';
                return view('dashboard.reports.class.class_content_usage_report', $data);
            }


            $unitsUsage = [];
            $lessonsUsage = [];
            $gamesUsage = [];
            $skillsUsage = [];
            $programsUsage = [];

            foreach ($selectedPrograms as $program) {
                $totalProgramGames = 0;
                if (!isset($programsUsage[$program->id])) {
                    $programsUsage[$program->id] = [
                        'program_id' => $program->id,
                        'name' => $program->course->name . ' - ' . $program->stage->name,
                        'units' => [],
                        'usage_count' => 0,
                        'usage_percentage' => 0,
                        'total_games' => 0,
                        'total_assigned_games' => 0,
                    ];
                }

                $unitsUsage = []; // Initialize unitsUsage for each program

                foreach ($program->units as $unit) {
                    $totalUnitGames = 0;
                    if (!isset($unitsUsage[$unit->id])) {
                        $unitsUsage[$unit->id] = [
                            'unit_id' => $unit->id,
                            'name' => $unit->name,
                            'lessons' => [],
                            'usage_count' => 0,
                            'usage_percentage' => 0,
                            'total_games' => 0,
                            'total_assigned_games' => 0,
                        ];
                    }

                    $lessonsUsage = []; // Initialize lessonsUsage for each unit

                    foreach ($unit->lessons as $lesson) {
                        $gamesCount = Game::where('lesson_id', $lesson->id)->get()->count();
                        $totalProgramGames += $gamesCount;
                        $totalUnitGames += $gamesCount;
                        if (!isset($lessonsUsage[$lesson->id])) {
                            $lessonsUsage[$lesson->id] = [
                                'lesson_id' => $lesson->id,
                                'name' => $lesson->name,
                                'games' => [],
                                'assigned' => 0,
                                'usage_percentage' => 0,
                                'total_games' => $gamesCount,
                                'assigned_games' => [],
                                'total_assigned_games' => 0,
                            ];
                        }

                        $gamesUsage = [];
                        $skillsUsage = [];

                        foreach ($lesson->game as $game) {
                            if (!isset($gamesUsage[$game->game_type_id])) {
                                $gameType = GameType::find($game->game_type_id);
                                $gamesUsage[$game->game_type_id] = [
                                    'game_type_id' => $game->game_type_id,
                                    'name' => $gameType->name,
                                    'skills' => [],
                                    'assigned' => 0,
                                    'usage_percentage' => 0,
                                ];

                                // Assign skills to the game type using skill IDs as keys
                                if ($gameType->skills) {
                                    foreach ($gameType->skills->where('lesson_id', $lesson->id)->unique('skill') as $gameSkill) {
                                        $skill = $gameSkill->skill;

                                        if (!isset($skillsUsage[$skill->id])) {
                                            $skillsUsage[$skill->id] = [
                                                'skill_id' => $skill->id,
                                                'name' => $skill->skill,
                                                'usage_count' => 0,
                                                'usage_percentage' => 0,
                                            ];
                                        }
                                    }
                                    // Assign the collected skills to the game's 'skills'
                                    $gamesUsage[$game->game_type_id]['skills'] = $skillsUsage;
                                }
                            }
                        }
                        // Assign the collected games to the lesson's 'games'
                        $lessonsUsage[$lesson->id]['games'] = $gamesUsage;
                    }
                    // Assign the collected lessons to the unit's 'lessons'
                    $unitsUsage[$unit->id]['lessons'] = $lessonsUsage;
                    $unitsUsage[$unit->id]['total_games'] = $totalUnitGames;
                }
                // Assign the collected units to the program's 'units'
                $programsUsage[$program->id]['units'] = $unitsUsage;
                $programsUsage[$program->id]['total_games'] = $totalProgramGames;
            }

            foreach ($student_assignments as $assignment) {
                // $testGames = Game::whereIn('id', TestQuestion::where('test_id', $assignment->id)->pluck('game_id'))->get();
                $testGame = Game::find($assignment->game_id);
                // dd($testGames);
                $lesson = Lesson::find($assignment->lesson_id);
                $unit_id = $lesson->unit_id;
                $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned'] = 1;
                $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['games'][$testGame->game_type_id]['assigned'] = 1;

                if (!in_array($testGame->id,    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'])) {
                    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'][] = $testGame->id;
                }
            }

            foreach ($programsUsage as $program) {
                $programAssignedGames = 0;
                foreach ($program['units'] as $unit) {
                    $assignedCount = 0;
                    $unitAssignedGames = 0;
                    foreach ($unit['lessons'] as $lesson) {
                        if ($lesson['assigned'] == 1) {
                            $assignedCount++;
                            $totalGamesCount  = $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_games'];
                            $assignedGamesCount = count($programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['assigned_games']);
                            $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['usage_percentage'] = round(($assignedGamesCount / $totalGamesCount) * 100, 2);
                            $unitAssignedGames += $assignedGamesCount;
                            $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_assigned_games'] = $assignedGamesCount;
                        }
                    }
                    if ($programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_games']  != 0) {
                        $programAssignedGames += $unitAssignedGames;
                        $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_assigned_games'] = $unitAssignedGames;
                        $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['usage_percentage'] = round($unitAssignedGames /  $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_games'] * 100, 2);
                    }
                }

                $programsUsage[$program['program_id']]['total_assigned_games'] = $programAssignedGames;
                if ($programsUsage[$program['program_id']]['total_games'] != 0) {
                    $programsUsage[$program['program_id']]['usage_percentage'] = round($programAssignedGames / $programsUsage[$program['program_id']]['total_games'] * 100, 2);
                }
            }
            // dd($programsUsage);


            $chartLabels = [];
            $chartValues = [];
            if ($request->filled('filter') && $request->filled('program_id')) {
                switch ($request->filter) {
                    case 'Unit':
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels[] = $unit['name'];
                                $chartValues[] = $unit['usage_percentage'];
                            }
                        }
                        $data['unitsUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['chartLabels'] =  $chartLabels;
                        $data['chartValues'] =  $chartValues;
                        break;
                    case 'Lesson':
                        $lessonsData =  [];
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels[] = '-';
                                $chartValues[] = '-';
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels[] = $lesson['name'];
                                    $chartValues[] = $lesson['usage_percentage'];
                                    if ($lesson['assigned'] == 1) {
                                        $assignedCount++;
                                    }
                                }
                            }
                        }

                        $data['lessonsUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['chartLabels'] =  $chartLabels;
                        $data['chartValues'] =  $chartValues;
                        break;
                    case 'Game':
                        $gameData = [];
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels[] = '-';
                                    $chartValues[] = '-';
                                    foreach ($lesson['games'] as $game) {
                                        $gameData[] = $game;
                                        $chartLabels[] = $game['name'];
                                        $chartValues[] = $game['assigned'];
                                    }
                                }
                            }
                        }
                        $data['gamesUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['gamesLabels'] =  $chartLabels;
                        $data['gamesValues'] =  $chartValues;
                        break;
                    default:

                        break;
                }
            } else if ($request->filled('filter') && !$request->filled('program_id')) {
                $data['error'] = 'Please Select a Program to Use filters';
                return view('dashboard.reports.class.class_content_usage_report', $data);
            } elseif (!$request->filled('program_id')) {
                foreach ($programsUsage as $program) {
                    $chartLabels[] = $program['name'];
                    $chartValues[] = $program['usage_percentage'];
                }
                $data['programsUsage'] =  $programsUsage;
                $data['chartLabels'] =  $chartLabels;
                $data['chartValues'] =  $chartValues;
            } else {
                $data['error'] = 'Please Select a Filter for this Program';
                return view('dashboard.reports.class.class_content_usage_report', $data);
            }
        }
        return view('dashboard.reports.class.class_content_usage_report', $data);
    }

    public function teacherContentCoverageReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $programs = Program::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();

            $data['programs'] = $programs;
        }

        $schools = School::all();

        $data = [
            'schools' => $schools,
            'request' => $request->all(),
        ];

        if ($request->filled('school_id') && $request->filled('teacher_id') && $request->filled('program_id')) {
            $teacherStudentTests = StudentTest::where('teacher_id', $request->teacher_id)
                ->where('program_id', $request->program_id)
                ->when($request->filled('from_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '<=', $request->to_date);
                })
                ->distinct()
                ->pluck('test_id');

            if ($teacherStudentTests->isEmpty()) {
                // Add an error message to the data array
                $data['error'] = "No Assignments Found for All Teacher's Students";
                return view('dashboard.reports.instructor.instructor_content_coverage_report', $data);
            }
            $teacherTests =  Test::whereIn('id', $teacherStudentTests)->get();
            $program = Program::find($request->program_id);

            $unitsCoverage = [];
            $totalGames = 0;

            foreach ($program->units as $unit) {
                $unitGames = 0;
                if (!isset($unitsCoverage[$unit->id])) {
                    $unitsCoverage[$unit->id] = [
                        'unit_id' => $unit->id,
                        'name' => $unit->name,
                        'lessons' => [],
                        'coverage_percentage' => 0,
                        'total_games_count' => 0,
                        'assigned_games_count' => 0,

                    ];
                }

                $lessonsCoverage = [];

                foreach ($unit->lessons as $lesson) {
                    $gamesCount = Game::where('lesson_id', $lesson->id)->get()->count();
                    $totalGames += $gamesCount;
                    $unitGames += $gamesCount;

                    if (!isset($lessonsCoverage[$lesson->id])) {
                        $lessonsCoverage[$lesson->id] = [
                            'lesson_id' => $lesson->id,
                            'name' => $lesson->name,
                            'games_count' => $gamesCount,
                            'assigned' => 0,
                            'assigned_games' => [],
                            'coverage_percentage' => 0,
                        ];
                    }
                }
                // Assign the collected lessons to the unit's 'lessons'
                $unitsCoverage[$unit->id]['lessons'] = $lessonsCoverage;
                $unitsCoverage[$unit->id]['total_games_count'] = $unitGames;
            }

            foreach ($teacherTests as $test) {
                $lesson_id = Lesson::find($test->lesson_id);
                $unit_id = $lesson_id->unit_id;
                $testGameId = $test->game_id;
                // dd($test->lesson_id, $unit_id);
                $unitsCoverage[$unit_id]['lessons'][$test->lesson_id]['assigned'] = 1;

                if (!in_array($testGameId,  $unitsCoverage[$unit_id]['lessons'][$test->lesson_id]['assigned_games'])) {
                    $unitsCoverage[$unit_id]['lessons'][$test->lesson_id]['assigned_games'][] = $testGameId;
                }
            }
            $totalAssignedGames = 0;
            foreach ($unitsCoverage as $unit) {
                $unitAssignedGames = 0;

                foreach ($unit['lessons'] as $lesson) {
                    $assignedGamesCount = 0;
                    if ($lesson['assigned'] == 1) {
                        $assignedGamesCount++;

                        $totalGamesCount  = $unitsCoverage[$unit['unit_id']]['lessons'][$lesson['lesson_id']]['games_count'];
                        $assignedGamesCount = count($unitsCoverage[$unit['unit_id']]['lessons'][$lesson['lesson_id']]['assigned_games']);
                        $unitsCoverage[$unit['unit_id']]['lessons'][$lesson['lesson_id']]['coverage_percentage'] = round(($assignedGamesCount / $totalGamesCount) * 100, 2);
                        $unitAssignedGames += $assignedGamesCount;
                    }
                }
                if ($unitsCoverage[$unit['unit_id']]['total_games_count'] != 0) {
                    $totalAssignedGames += $unitAssignedGames;
                    $unitsCoverage[$unit['unit_id']]['assigned_games_count'] = $unitAssignedGames;
                    $unitsCoverage[$unit['unit_id']]['coverage_percentage'] = round($unitAssignedGames / $unitsCoverage[$unit['unit_id']]['total_games_count'] * 100, 2);
                }
            }
            // dd($unitsCoverage);
            if ($request->filled('filter') && $request->filled('program_id')) {
                switch ($request->filter) {
                    case 'Unit':
                        $chartLabels = [];
                        $chartValues = [];
                        $total_percentage = 0;
                        foreach ($unitsCoverage as $unit) {
                            $chartLabels[] = $unit['name'];
                            $chartValues[] = $unit['coverage_percentage'];
                            $total_percentage += $unit['coverage_percentage'];
                        }

                        $data['unitsCoverage'] = $unitsCoverage;
                        $data['chartLabels'] = $chartLabels;
                        $data['chartValues'] = $chartValues;
                        $data['programCoverage'] = round($totalAssignedGames / $totalGames * 100, 2);
                        break;
                    case 'Lesson':
                        $chartLabels = [];
                        $chartValues = [];
                        $total_percentage = 0;
                        $assignedCount = 0;
                        $unassignedCount = 0;

                        foreach ($unitsCoverage as $unit) {
                            $chartLabels[] = '-';
                            $chartValues[] = '-';
                            foreach ($unit['lessons'] as $lesson) {
                                $chartLabels[] = $lesson['name'];
                                $chartValues[] = $lesson['coverage_percentage'];
                                if ($lesson['assigned'] == 1) {
                                    $assignedCount++;
                                } else {
                                    $unassignedCount++;
                                }
                            }
                            $total_percentage += $unit['coverage_percentage'];
                        }

                        $data['lessonsCoverage'] = $unitsCoverage;
                        $data['chartLabels'] = $chartLabels;
                        $data['chartValues'] = $chartValues;
                        $data['programCoverage'] = round($totalAssignedGames / $totalGames * 100, 2);
                        break;
                    default:
                        break;
                }
            }
        }
        // dd($unitsCoverage);

        return view('dashboard.reports.instructor.instructor_content_coverage_report', $data);
    }

    public function classContentGapReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $groups = Group::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $groups = Group::all();
        }

        // Initialize $data array with defaults
        $data = [
            'groups' => $groups,
            'request' => $request->all(),
        ];
        if ($request->has('group_id')) {
            if ($request->filled('program_id')) {
                $selectedPrograms = Program::where('id', $request->program_id)->get();
            } else {
                $selectedPrograms = Program::whereIn('id', Group::with(['groupCourses'])->findOrFail($request->group_id)->groupCourses->pluck('program_id'))->get();
            }

            // $students = GroupStudent::whereIn('student_id', [239, 504])->pluck('student_id');
            $students = GroupStudent::where('group_id', $request->group_id)->pluck('student_id');
            if ($students->isEmpty()) {
                $data['error'] = 'No Students Found.';
                // Return the view with the $data array
                return view('dashboard.reports.class.class_content_gap_report', $data);
            }

            // Fetch student tests and filter for valid test IDs
            $student_tests = StudentTest::whereIn('student_id', $students)
                ->whereIn('program_id', $selectedPrograms->pluck('id'))
                ->when($request->filled('from_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '<=', $request->to_date);
                })
                ->whereNotNull('test_id')
                ->pluck('test_id');


            // Fetch the actual test records for the common tests
            $student_assignments = Test::whereIn('id', $student_tests)->get();
            // dd($student_assignments);

            if ($student_assignments->isEmpty()) {
                // Add an error message to the data array
                $data['error'] = 'No Assignments Found for All Students';
                return view('dashboard.reports.class.class_content_gap_report', $data);
            }


            $unitsUsage = [];
            $lessonsUsage = [];
            $gamesUsage = [];
            $skillsUsage = [];
            $programsUsage = [];

            foreach ($selectedPrograms as $program) {
                $totalProgramGames = 0;
                if (!isset($programsUsage[$program->id])) {

                    $programsUsage[$program->id] = [
                        'program_id' => $program->id,
                        'name' => $program->course->name . ' - ' . $program->stage->name,
                        'units' => [],
                        'usage_count' => 0,
                        'gap_percentage' => 100,
                        'total_games' => 0,
                        'total_assigned_games' => 0,
                    ];
                }

                $unitsUsage = []; // Initialize unitsUsage for each program

                foreach ($program->units as $unit) {
                    $totalUnitGames = 0;
                    if (!isset($unitsUsage[$unit->id])) {
                        $unitsUsage[$unit->id] = [
                            'unit_id' => $unit->id,
                            'name' => $unit->name,
                            'lessons' => [],
                            'usage_count' => 0,
                            'gap_percentage' => 100,
                            'total_games' => 0,
                            'total_assigned_games' => 0,
                        ];
                    }

                    $lessonsUsage = []; // Initialize lessonsUsage for each unit

                    foreach ($unit->lessons as $lesson) {
                        $gamesCount = Game::where('lesson_id', $lesson->id)->get()->count();
                        $totalProgramGames += $gamesCount;
                        $totalUnitGames += $gamesCount;
                        if (!isset($lessonsUsage[$lesson->id])) {
                            $lessonsUsage[$lesson->id] = [
                                'lesson_id' => $lesson->id,
                                'name' => $lesson->name,
                                'games' => [],
                                'assigned' => 0,
                                'gap_percentage' => 100,
                                'total_games' => $gamesCount,
                                'assigned_games' => [],
                                'total_assigned_games' => 0,
                            ];
                        }

                        $gamesUsage = [];
                        $skillsUsage = [];

                        foreach ($lesson->game as $game) {
                            if (!isset($gamesUsage[$game->game_type_id])) {
                                $gameType = GameType::find($game->game_type_id);
                                $gamesUsage[$game->game_type_id] = [
                                    'game_type_id' => $game->game_type_id,
                                    'name' => $gameType->name,
                                    'skills' => [],
                                    'assigned' => 0,
                                    'gap_percentage' => 100,
                                ];

                                // Assign skills to the game type using skill IDs as keys
                                if ($gameType->skills) {
                                    foreach ($gameType->skills->where('lesson_id', $lesson->id)->unique('skill') as $gameSkill) {
                                        $skill = $gameSkill->skill;

                                        if (!isset($skillsUsage[$skill->id])) {
                                            $skillsUsage[$skill->id] = [
                                                'skill_id' => $skill->id,
                                                'name' => $skill->skill,
                                                'usage_count' => 0,
                                                'gap_percentage' => 100,
                                            ];
                                        }
                                    }
                                    // Assign the collected skills to the game's 'skills'
                                    $gamesUsage[$game->game_type_id]['skills'] = $skillsUsage;
                                }
                            }
                        }
                        // Assign the collected games to the lesson's 'games'
                        $lessonsUsage[$lesson->id]['games'] = $gamesUsage;
                    }
                    // Assign the collected lessons to the unit's 'lessons'
                    $unitsUsage[$unit->id]['lessons'] = $lessonsUsage;
                    $unitsUsage[$unit->id]['total_games'] = $totalUnitGames;
                }
                // Assign the collected units to the program's 'units'
                $programsUsage[$program->id]['units'] = $unitsUsage;
                $programsUsage[$program->id]['total_games'] = $totalProgramGames;
            }

            foreach ($student_assignments as $assignment) {
                if (isset($programsUsage[$assignment->program_id])) {
                    $testGame = Game::find($assignment->game_id);

                    $lesson = Lesson::find($assignment->lesson_id);
                    $unit_id = $lesson->unit_id;
                    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned'] = 1;
                    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['games'][$testGame->game_type_id]['assigned'] = 1;

                    if (!in_array($testGame->id,    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'])) {
                        $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'][] = $testGame->id;
                    }
                }
            }

            foreach ($programsUsage as $program) {
                $programAssignedGames = 0;
                foreach ($program['units'] as $unit) {
                    $assignedCount = 0;
                    $unitAssignedGames = 0;
                    foreach ($unit['lessons'] as $lesson) {
                        if ($lesson['assigned'] == 1) {
                            $assignedCount++;
                            $totalGamesCount  = $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_games'];
                            $assignedGamesCount = count($programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['assigned_games']);
                            $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['gap_percentage'] = $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['gap_percentage'] - round(($assignedGamesCount / $totalGamesCount) * 100, 2);
                            $unitAssignedGames += $assignedGamesCount;
                            $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_assigned_games'] = $assignedGamesCount;
                        }
                    }
                    if ($programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_games']  != 0) {
                        $programAssignedGames += $unitAssignedGames;
                        $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_assigned_games'] = $unitAssignedGames;
                        $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['gap_percentage'] = $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['gap_percentage'] - round($unitAssignedGames /  $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_games'] * 100, 2);
                    }
                }

                $programsUsage[$program['program_id']]['total_assigned_games'] = $programAssignedGames;
                if ($programsUsage[$program['program_id']]['total_games'] != 0) {
                    $programsUsage[$program['program_id']]['gap_percentage'] = $programsUsage[$program['program_id']]['gap_percentage'] - round($programAssignedGames / $programsUsage[$program['program_id']]['total_games'] * 100, 2);
                }
            }

            $chartLabels = [];
            $chartValues = [];
            if ($request->filled('filter') && $request->filled('program_id')) {
                switch ($request->filter) {
                    case 'Unit':
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels[] = $unit['name'];
                                $chartValues[] = $unit['gap_percentage'];
                            }
                        }

                        $data['unitsUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['chartLabels'] =  $chartLabels;
                        $data['chartValues'] =  $chartValues;

                        break;
                    case 'Lesson':
                        $lessonsData =  [];
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels[] = '-';
                                $chartValues[] = '-';
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels[] = $lesson['name'];
                                    $chartValues[] = $lesson['gap_percentage'];
                                    if ($lesson['assigned'] == 1) {
                                        $assignedCount++;
                                    }
                                }
                            }
                        }

                        $data['lessonsUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['chartLabels'] =  $chartLabels;
                        $data['chartValues'] =  $chartValues;
                        break;
                    case 'Game':
                        $gameData = [];
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels[] = '-';
                                    $chartValues[] = '-';
                                    foreach ($lesson['games'] as $game) {
                                        $gameData[] = $game;
                                        $chartLabels[] = $game['name'];
                                        $chartValues[] = $game['assigned'];
                                    }
                                }
                            }
                        }
                        $data['gamesUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['gamesLabels'] =  $chartLabels;
                        $data['gamesValues'] =  $chartValues;
                        break;
                    default:

                        break;
                }
            } else if ($request->filled('filter') && !$request->filled('program_id')) {
                $data['error'] = 'Please Select a Program to Use filters';
                return view('dashboard.reports.class.class_content_gap_report', $data);
            } elseif (!$request->filled('program_id')) {
                foreach ($programsUsage as $program) {
                    $chartLabels[] = $program['name'];
                    $chartValues[] = $program['gap_percentage'];
                }
                $data['programsUsage'] =  $programsUsage;
                $data['chartLabels'] =  $chartLabels;
                $data['chartValues'] =  $chartValues;
            } else {
                $data['error'] = 'Please Select a Filter for this Program';
                return view('dashboard.reports.class.class_content_gap_report', $data);
            }
        }
        return view('dashboard.reports.class.class_content_gap_report', $data);
    }
    public function schoolContentGapReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $schoolId = Auth::user()->school_id;
            $programs = Program::with(['course', 'stage'])
                ->whereHas('schoolProgram', function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                })->get();
        } else {
            $programs = Program::all();
        }
        $schools = School::all();
        $data = [
            'schools' => $schools,
            'programs' => $programs,
            'request' => $request->all(),
        ];
        if ($request->filled('school_id')) {
            $schoolId = $request->school_id;
            if ($request->filled('program_id')) {
                $selectedPrograms = Program::where('id', $request->program_id)->get();
            } else {
                $selectedPrograms = Program::whereHas('schoolProgram', function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                })
                    ->with(['schoolProgram' => function ($query) use ($schoolId) {
                        $query->where('school_id', $schoolId);
                    }])
                    ->get();
            }
            // dd($selectedPrograms->pluck('id'));

            $students = User::where('role', '2')->where("school_id", $schoolId)->pluck('id');

            $student_tests = StudentTest::whereIn('student_id', $students)
                ->whereIn('program_id', $selectedPrograms->pluck('id'))
                ->when($request->filled('from_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '<=', $request->to_date);
                })
                ->pluck('test_id');

            $student_assignments = Test::whereIn('id', $student_tests)->get();
            // dd($student_assignments);

            if ($student_assignments->isEmpty()) {
                // Add an error message to the data array
                $data['error'] = 'No Assignments Found for All Students';
                return view('dashboard.reports.school.school_content_gap_report', $data);
            }

            $unitsUsage = [];
            $lessonsUsage = [];
            $gamesUsage = [];
            $skillsUsage = [];
            $programsUsage = [];

            foreach ($selectedPrograms as $program) {
                $totalProgramGames = 0;
                if (!isset($programsUsage[$program->id])) {

                    $programsUsage[$program->id] = [
                        'program_id' => $program->id,
                        'name' => $program->course->name . ' - ' . $program->stage->name,
                        'units' => [],
                        'usage_count' => 0,
                        'gap_percentage' => 100,
                        'total_games' => 0,
                        'total_assigned_games' => 0,
                    ];
                }

                $unitsUsage = []; // Initialize unitsUsage for each program

                foreach ($program->units as $unit) {
                    $totalUnitGames = 0;
                    if (!isset($unitsUsage[$unit->id])) {
                        $unitsUsage[$unit->id] = [
                            'unit_id' => $unit->id,
                            'name' => $unit->name,
                            'lessons' => [],
                            'usage_count' => 0,
                            'gap_percentage' => 100,
                            'total_games' => 0,
                            'total_assigned_games' => 0,
                        ];
                    }

                    $lessonsUsage = []; // Initialize lessonsUsage for each unit

                    foreach ($unit->lessons as $lesson) {
                        $gamesCount = Game::where('lesson_id', $lesson->id)->get()->count();
                        $totalProgramGames += $gamesCount;
                        $totalUnitGames += $gamesCount;
                        if (!isset($lessonsUsage[$lesson->id])) {
                            $lessonsUsage[$lesson->id] = [
                                'lesson_id' => $lesson->id,
                                'name' => $lesson->name,
                                'games' => [],
                                'assigned' => 0,
                                'gap_percentage' => 100,
                                'total_games' => $gamesCount,
                                'assigned_games' => [],
                                'total_assigned_games' => 0,
                            ];
                        }

                        $gamesUsage = [];
                        $skillsUsage = [];

                        foreach ($lesson->game as $game) {
                            if (!isset($gamesUsage[$game->game_type_id])) {
                                $gameType = GameType::find($game->game_type_id);
                                $gamesUsage[$game->game_type_id] = [
                                    'game_type_id' => $game->game_type_id,
                                    'name' => $gameType->name,
                                    'skills' => [],
                                    'assigned' => 0,
                                    'gap_percentage' => 100,
                                ];

                                // Assign skills to the game type using skill IDs as keys
                                if ($gameType->skills) {
                                    foreach ($gameType->skills->where('lesson_id', $lesson->id)->unique('skill') as $gameSkill) {
                                        $skill = $gameSkill->skill;

                                        if (!isset($skillsUsage[$skill->id])) {
                                            $skillsUsage[$skill->id] = [
                                                'skill_id' => $skill->id,
                                                'name' => $skill->skill,
                                                'usage_count' => 0,
                                                'gap_percentage' => 100,
                                            ];
                                        }
                                    }
                                    // Assign the collected skills to the game's 'skills'
                                    $gamesUsage[$game->game_type_id]['skills'] = $skillsUsage;
                                }
                            }
                        }
                        // Assign the collected games to the lesson's 'games'
                        $lessonsUsage[$lesson->id]['games'] = $gamesUsage;
                    }
                    // Assign the collected lessons to the unit's 'lessons'
                    $unitsUsage[$unit->id]['lessons'] = $lessonsUsage;
                    $unitsUsage[$unit->id]['total_games'] = $totalUnitGames;
                }
                // Assign the collected units to the program's 'units'
                $programsUsage[$program->id]['units'] = $unitsUsage;
                $programsUsage[$program->id]['total_games'] = $totalProgramGames;
            }

            foreach ($student_assignments as $assignment) {
                if (isset($programsUsage[$assignment->program_id])) {
                    $testGame = Game::find($assignment->game_id);

                    $lesson = Lesson::find($assignment->lesson_id);
                    $unit_id = $lesson->unit_id;
                    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned'] = 1;
                    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['games'][$testGame->game_type_id]['assigned'] = 1;

                    if (!in_array($testGame->id,    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'])) {
                        $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'][] = $testGame->id;
                    }
                }
            }

            foreach ($programsUsage as $program) {
                $programAssignedGames = 0;
                foreach ($program['units'] as $unit) {
                    $assignedCount = 0;
                    $unitAssignedGames = 0;
                    foreach ($unit['lessons'] as $lesson) {
                        if ($lesson['assigned'] == 1) {
                            $assignedCount++;
                            $totalGamesCount  = $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_games'];
                            $assignedGamesCount = count($programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['assigned_games']);
                            $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['gap_percentage'] = $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['gap_percentage'] - round(($assignedGamesCount / $totalGamesCount) * 100, 2);
                            $unitAssignedGames += $assignedGamesCount;
                            $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_assigned_games'] = $assignedGamesCount;
                        }
                    }
                    if ($programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_games']  != 0) {
                        $programAssignedGames += $unitAssignedGames;
                        $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_assigned_games'] = $unitAssignedGames;
                        $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['gap_percentage'] = $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['gap_percentage'] - round($unitAssignedGames /  $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_games'] * 100, 2);
                    }
                }

                $programsUsage[$program['program_id']]['total_assigned_games'] = $programAssignedGames;
                if ($programsUsage[$program['program_id']]['total_games'] != 0) {
                    $programsUsage[$program['program_id']]['gap_percentage'] = $programsUsage[$program['program_id']]['gap_percentage'] - round($programAssignedGames / $programsUsage[$program['program_id']]['total_games'] * 100, 2);
                }
            }

            $chartLabels = [];
            $chartValues = [];
            if ($request->filled('filter') && $request->filled('program_id')) {
                switch ($request->filter) {
                    case 'Unit':
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels[] = $unit['name'];
                                $chartValues[] = $unit['gap_percentage'];
                            }
                        }

                        $data['unitsUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['chartLabels'] =  $chartLabels;
                        $data['chartValues'] =  $chartValues;

                        break;
                    case 'Lesson':
                        $lessonsData =  [];
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels[] = '-';
                                $chartValues[] = '-';
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels[] = $lesson['name'];
                                    $chartValues[] = $lesson['gap_percentage'];
                                    if ($lesson['assigned'] == 1) {
                                        $assignedCount++;
                                    }
                                }
                            }
                        }

                        $data['lessonsUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['chartLabels'] =  $chartLabels;
                        $data['chartValues'] =  $chartValues;
                        break;
                    case 'Game':
                        $gameData = [];
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels[] = '-';
                                    $chartValues[] = '-';
                                    foreach ($lesson['games'] as $game) {
                                        $gameData[] = $game;
                                        $chartLabels[] = $game['name'];
                                        $chartValues[] = $game['assigned'];
                                    }
                                }
                            }
                        }
                        $data['gamesUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['gamesLabels'] =  $chartLabels;
                        $data['gamesValues'] =  $chartValues;
                        break;
                    default:

                        break;
                }
            } else if ($request->filled('filter') && !$request->filled('program_id')) {
                $data['error'] = 'Please Select a Program to Use filters';
                return view('dashboard.reports.school.school_content_gap_report', $data);
            } elseif (!$request->filled('program_id')) {
                foreach ($programsUsage as $program) {
                    $chartLabels[] = $program['name'];
                    $chartValues[] = $program['gap_percentage'];
                }
                $data['programsUsage'] =  $programsUsage;
                $data['chartLabels'] =  $chartLabels;
                $data['chartValues'] =  $chartValues;
            } else {
                $data['error'] = 'Please Select a Filter for this Program';
                return view('dashboard.reports.school.school_content_gap_report', $data);
            }
        }


        return view('dashboard.reports.school.school_content_gap_report', $data);
    }

    public function classHeatmapReport(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $groups = Group::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        } else {
            $groups = Group::all();
        }

        // Initialize $data array with defaults
        $data = [
            'groups' => $groups,
            'request' => $request->all(),
        ];

        if ($request->has('group1_id') && $request->has('group2_id')) {
            if ($request->filled('program_id')) {
                $selectedPrograms = Program::where('id', $request->program_id)->get();
            } else {
                // $selectedPrograms = Program::whereIn('id', Group::with(['groupCourses'])->findOrFail($request->group1_id)->groupCourses->pluck('program_id'))->get();
                $group1ProgramIds = Group::with('groupCourses')
                    ->findOrFail($request->group1_id)
                    ->groupCourses
                    ->pluck('program_id')
                    ->toArray();

                $group2ProgramIds = Group::with('groupCourses')
                    ->findOrFail($request->group2_id)
                    ->groupCourses
                    ->pluck('program_id')
                    ->toArray();

                $commonProgramIds = array_intersect($group1ProgramIds, $group2ProgramIds);
                $selectedPrograms = Program::whereIn('id', $commonProgramIds)->get();
            }
            $students1 = GroupStudent::where('group_id', $request->group1_id)->pluck('student_id');
            $students2 = GroupStudent::where('group_id', $request->group2_id)->pluck('student_id');
            // $students1 = GroupStudent::whereIn('student_id', [239, 504])->pluck('student_id');
            // $students2 = GroupStudent::whereIn('student_id', [239, 504])->pluck('student_id');

            if ($students1->isEmpty()) {
                $groupName = Group::find($request->group1_id)->name;
                $data['error'] = 'No Students Found in Class: ' . $groupName . '.';
                // Return the view with the $data arrays
                return view('dashboard.reports.heatmap.class_heatmap_report', $data);
            }
            if ($students2->isEmpty()) {
                $groupName = Group::find($request->group2_id)->name;
                $data['error'] = 'No Students Found in Class: ' . $groupName . '.';
                // Return the view with the $data array
                return view('dashboard.reports.heatmap.class_heatmap_report', $data);
            }

            // Fetch student tests and filter for valid test IDs in Group 1
            $student_tests1 = StudentTest::whereIn('student_id', $students1)
                ->whereIn('program_id', $selectedPrograms->pluck('id'))
                ->when($request->filled('from_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '<=', $request->to_date);
                })
                ->whereNotNull('test_id')
                ->get();
            // dd($student_tests);

            // Check if there are multiple students
            if ($students1->count() > 1) {
                // Group the results by test_id
                $grouped_tests1 = $student_tests1->groupBy('test_id');
                $common_tests1 = [];

                foreach ($grouped_tests1 as $test) {
                    $studentsTest1 = [];
                    foreach ($test as $testStudent) {
                        if (!in_array($testStudent->student_id, $studentsTest1))
                            $studentsTest1[] = $testStudent->student_id;
                    }
                    if (count($studentsTest1) == $students1->count()) {
                        $common_tests1[] = $testStudent->test_id;
                    }
                }
                // dd($common_tests);
            } else {
                // If there's only one student, skip filtering
                $common_tests1 = $student_tests1->pluck('test_id');
            }


            // Fetch the actual test records for the common tests
            $student_assignments1 = Test::whereIn('id', $common_tests1)->get();
            // dd($student_assignments);

            if ($student_assignments1->isEmpty()) {
                // Add an error message to the data array
                $groupName = Group::find($request->group1_id)->name;
                $data['error'] = 'No Assignments Found for All Students in Class ' . $groupName . '.';
                return view('dashboard.reports.heatmap.class_heatmap_report', $data);
            }


            // Fetch student tests and filter for valid test IDs in Group 2
            $student_tests2 = StudentTest::whereIn('student_id', $students2)
                ->whereIn('program_id', $selectedPrograms->pluck('id'))
                ->when($request->filled('from_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '<=', $request->to_date);
                })
                ->whereNotNull('test_id')
                ->get();
            // dd($student_tests);

            // Check if there are multiple students
            if ($students2->count() > 1) {
                // Group the results by test_id
                $grouped_tests2 = $student_tests2->groupBy('test_id');
                $common_tests2 = [];

                foreach ($grouped_tests2 as $test) {
                    $studentsTest2 = [];
                    foreach ($test as $testStudent) {
                        if (!in_array($testStudent->student_id, $studentsTest2))
                            $studentsTest2[] = $testStudent->student_id;
                    }
                    if (count($studentsTest2) == $students2->count()) {
                        $common_tests2[] = $testStudent->test_id;
                    }
                }
                // dd($common_tests);
            } else {
                // If there's only one student, skip filtering
                $common_tests2 = $student_tests2->pluck('test_id');
            }


            // Fetch the actual test records for the common tests
            $student_assignments2 = Test::whereIn('id', $common_tests2)->get();
            // dd($student_assignments);

            if ($student_assignments2->isEmpty()) {
                // Add an error message to the data array
                $groupName = Group::find($request->group2_id)->name;
                $data['error'] = 'No Assignments Found for All Students in Class ' . $groupName . '.';
                return view('dashboard.reports.heatmap.class_heatmap_report', $data);
            }

            $unitsUsage = [];
            $lessonsUsage = [];
            $gamesUsage = [];
            $skillsUsage = [];
            $programsUsage = [];

            foreach ($selectedPrograms as $program) {
                $totalProgramGames = 0;
                if (!isset($programsUsage[$program->id])) {
                    $programsUsage[$program->id] = [
                        'program_id' => $program->id,
                        'name' => $program->course->name . ' - ' . $program->stage->name,
                        'units' => [],
                        'usage_count' => 0,
                        'usage_percentage' => 0,
                        'total_games' => 0,
                        'total_assigned_games' => 0,
                    ];
                }

                $unitsUsage = []; // Initialize unitsUsage for each program

                foreach ($program->units as $unit) {
                    $totalUnitGames = 0;
                    if (!isset($unitsUsage[$unit->id])) {
                        $unitsUsage[$unit->id] = [
                            'unit_id' => $unit->id,
                            'name' => $unit->name,
                            'lessons' => [],
                            'usage_count' => 0,
                            'usage_percentage' => 0,
                            'total_games' => 0,
                            'total_assigned_games' => 0,
                        ];
                    }

                    $lessonsUsage = []; // Initialize lessonsUsage for each unit

                    foreach ($unit->lessons as $lesson) {
                        $gamesCount = Game::where('lesson_id', $lesson->id)->get()->count();
                        $totalProgramGames += $gamesCount;
                        $totalUnitGames += $gamesCount;
                        if (!isset($lessonsUsage[$lesson->id])) {
                            $lessonsUsage[$lesson->id] = [
                                'lesson_id' => $lesson->id,
                                'name' => $lesson->name,
                                'games' => [],
                                'assigned' => 0,
                                'usage_percentage' => 0,
                                'total_games' => $gamesCount,
                                'assigned_games' => [],
                                'total_assigned_games' => 0,
                            ];
                        }

                        $gamesUsage = [];
                        $skillsUsage = [];

                        foreach ($lesson->game as $game) {
                            if (!isset($gamesUsage[$game->game_type_id])) {
                                $gameType = GameType::find($game->game_type_id);
                                $gamesUsage[$game->game_type_id] = [
                                    'game_type_id' => $game->game_type_id,
                                    'name' => $gameType->name,
                                    'skills' => [],
                                    'assigned' => 0,
                                    'usage_percentage' => 0,
                                ];

                                // Assign skills to the game type using skill IDs as keys
                                if ($gameType->skills) {
                                    foreach ($gameType->skills->where('lesson_id', $lesson->id)->unique('skill') as $gameSkill) {
                                        $skill = $gameSkill->skill;

                                        if (!isset($skillsUsage[$skill->id])) {
                                            $skillsUsage[$skill->id] = [
                                                'skill_id' => $skill->id,
                                                'name' => $skill->skill,
                                                'usage_count' => 0,
                                                'usage_percentage' => 0,
                                            ];
                                        }
                                    }
                                    // Assign the collected skills to the game's 'skills'
                                    $gamesUsage[$game->game_type_id]['skills'] = $skillsUsage;
                                }
                            }
                        }
                        // Assign the collected games to the lesson's 'games'
                        $lessonsUsage[$lesson->id]['games'] = $gamesUsage;
                    }
                    // Assign the collected lessons to the unit's 'lessons'
                    $unitsUsage[$unit->id]['lessons'] = $lessonsUsage;
                    $unitsUsage[$unit->id]['total_games'] = $totalUnitGames;
                }
                // Assign the collected units to the program's 'units'
                $programsUsage[$program->id]['units'] = $unitsUsage;
                $programsUsage[$program->id]['total_games'] = $totalProgramGames;
            }
            $programsUsage2 = $programsUsage;

            // Group 1 Usage Calculations
            foreach ($student_assignments1 as $assignment) {
                // $testGames = Game::whereIn('id', TestQuestion::where('test_id', $assignment->id)->pluck('game_id'))->get();
                $testGame = Game::find($assignment->game_id);
                // dd($testGames);
                $lesson = Lesson::find($assignment->lesson_id);
                $unit_id = $lesson->unit_id;
                $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned'] = 1;
                $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['games'][$testGame->game_type_id]['assigned'] = 1;

                if (!in_array($testGame->id,    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'])) {
                    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'][] = $testGame->id;
                }
            }

            foreach ($programsUsage as $program) {
                $programAssignedGames = 0;
                foreach ($program['units'] as $unit) {
                    $assignedCount = 0;
                    $unitAssignedGames = 0;
                    foreach ($unit['lessons'] as $lesson) {
                        if ($lesson['assigned'] == 1) {
                            $assignedCount++;
                            $totalGamesCount  = $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_games'];
                            $assignedGamesCount = count($programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['assigned_games']);
                            $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['usage_percentage'] = round(($assignedGamesCount / $totalGamesCount) * 100, 2);
                            $unitAssignedGames += $assignedGamesCount;
                            $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_assigned_games'] = $assignedGamesCount;
                        }
                    }
                    if ($programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_games']  != 0) {
                        $programAssignedGames += $unitAssignedGames;
                        $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_assigned_games'] = $unitAssignedGames;
                        $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['usage_percentage'] = round($unitAssignedGames /  $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_games'] * 100, 2);
                    }
                }

                $programsUsage[$program['program_id']]['total_assigned_games'] = $programAssignedGames;
                if ($programsUsage[$program['program_id']]['total_games'] != 0) {
                    $programsUsage[$program['program_id']]['usage_percentage'] = round($programAssignedGames / $programsUsage[$program['program_id']]['total_games'] * 100, 2);
                }
            }

            // Group 2 Usage Calculations
            foreach ($student_assignments2 as $assignment) {
                // $testGames = Game::whereIn('id', TestQuestion::where('test_id', $assignment->id)->pluck('game_id'))->get();
                $testGame = Game::find($assignment->game_id);
                // dd($testGames);
                $lesson = Lesson::find($assignment->lesson_id);
                $unit_id = $lesson->unit_id;
                $programsUsage2[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned'] = 1;
                $programsUsage2[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['games'][$testGame->game_type_id]['assigned'] = 1;

                if (!in_array($testGame->id,    $programsUsage2[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'])) {
                    $programsUsage2[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'][] = $testGame->id;
                }
            }

            foreach ($programsUsage2 as $program) {
                $programAssignedGames = 0;
                foreach ($program['units'] as $unit) {
                    $assignedCount = 0;
                    $unitAssignedGames = 0;
                    foreach ($unit['lessons'] as $lesson) {
                        if ($lesson['assigned'] == 1) {
                            $assignedCount++;
                            $totalGamesCount  = $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_games'];
                            $assignedGamesCount = count($programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['assigned_games']);
                            $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['usage_percentage'] = round(($assignedGamesCount / $totalGamesCount) * 100, 2);
                            $unitAssignedGames += $assignedGamesCount;
                            $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_assigned_games'] = $assignedGamesCount;
                        }
                    }
                    if ($programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['total_games']  != 0) {
                        $programAssignedGames += $unitAssignedGames;
                        $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['total_assigned_games'] = $unitAssignedGames;
                        $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['usage_percentage'] = round($unitAssignedGames /  $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['total_games'] * 100, 2);
                    }
                }

                $programsUsage2[$program['program_id']]['total_assigned_games'] = $programAssignedGames;
                if ($programsUsage2[$program['program_id']]['total_games'] != 0) {
                    $programsUsage2[$program['program_id']]['usage_percentage'] = round($programAssignedGames / $programsUsage2[$program['program_id']]['total_games'] * 100, 2);
                }
            }

            $chartLabels = [];
            $chartValues = [];
            $chartLabels2 = [];
            $chartValues2 = [];
            if ($request->filled('filter') && $request->filled('program_id')) {
                switch ($request->filter) {
                    case 'Unit':
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels[] = $unit['name'];
                                $chartValues[] = $unit['usage_percentage'];
                            }
                        }
                        foreach ($programsUsage2 as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels2[] = $unit['name'];
                                $chartValues2[] = $unit['usage_percentage'];
                            }
                        }
                        $data['unitsUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['unitsUsage2'] =  $programsUsage2[$request->program_id]['units'];
                        $data['chartLabels'] =  $chartLabels;
                        $data['chartValues'] =  $chartValues;
                        $data['chartLabels2'] =  $chartLabels2;
                        $data['chartValues2'] =  $chartValues2;
                        $data['groupName1'] = Group::find($request->group1_id)->name;
                        $data['groupName2'] = Group::find($request->group2_id)->name;
                        break;
                    case 'Lesson':
                        $lessonsData =  [];
                        $assignedCount = 0;
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels[] = '-';
                                $chartValues[] = '-';
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels[] = $lesson['name'];
                                    $chartValues[] = $lesson['usage_percentage'];
                                    if ($lesson['assigned'] == 1) {
                                        $assignedCount++;
                                    }
                                }
                            }
                        }
                        $assignedCount = 0;
                        foreach ($programsUsage2 as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels2[] = '-';
                                $chartValues2[] = '-';
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels2[] = $lesson['name'];
                                    $chartValues2[] = $lesson['usage_percentage'];
                                    if ($lesson['assigned'] == 1) {
                                        $assignedCount++;
                                    }
                                }
                            }
                        }

                        $data['lessonsUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['lessonsUsage2'] =  $programsUsage2[$request->program_id]['units'];
                        $data['chartLabels'] =  $chartLabels;
                        $data['chartValues'] =  $chartValues;
                        $data['chartLabels2'] =  $chartLabels2;
                        $data['chartValues2'] =  $chartValues2;
                        $data['groupName1'] = Group::find($request->group1_id)->name;
                        $data['groupName2'] = Group::find($request->group2_id)->name;
                        break;
                    case 'Game':
                        $gameData = [];
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels[] = '-';
                                    $chartValues[] = '-';
                                    foreach ($lesson['games'] as $game) {
                                        $gameData[] = $game;
                                        $chartLabels[] = $game['name'];
                                        $chartValues[] = $game['assigned'];
                                    }
                                }
                            }
                        }
                        foreach ($programsUsage2 as $program) {
                            foreach ($program['units'] as $unit) {
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels2[] = '-';
                                    $chartValues2[] = '-';
                                    foreach ($lesson['games'] as $game) {
                                        $gameData[] = $game;
                                        $chartLabels2[] = $game['name'];
                                        $chartValues2[] = $game['assigned'];
                                    }
                                }
                            }
                        }
                        $data['gamesUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['gamesUsage2'] =  $programsUsage2[$request->program_id]['units'];
                        $data['gamesLabels'] =  $chartLabels;
                        $data['gamesValues'] =  $chartValues;
                        $data['gamesLabels2'] =  $chartLabels2;
                        $data['gamesValues2'] =  $chartValues2;
                        $data['groupName1'] = Group::find($request->group1_id)->name;
                        $data['groupName2'] = Group::find($request->group2_id)->name;
                        break;
                    default:
                        break;
                }
            } else if ($request->filled('filter') && !$request->filled('program_id')) {
                $data['error'] = 'Please Select a Program to Use filters';
                return view('dashboard.reports.heatmap.class_heatmap_report', $data);
            } elseif (!$request->filled('program_id')) {
                foreach ($programsUsage as $program) {
                    $chartLabels[] = $program['name'];
                    $chartValues[] = $program['usage_percentage'];
                }
                foreach ($programsUsage2 as $program) {
                    $chartLabels2[] = $program['name'];
                    $chartValues2[] = $program['usage_percentage'];
                }
                $data['programsUsage'] =  $programsUsage;
                $data['programsUsage2'] =  $programsUsage2;
                $data['chartLabels'] =  $chartLabels;
                $data['chartValues'] =  $chartValues;
                $data['chartLabels2'] =  $chartLabels2;
                $data['chartValues2'] =  $chartValues2;
                $data['groupName1'] = Group::find($request->group1_id)->name;
                $data['groupName2'] = Group::find($request->group2_id)->name;
            } else {
                $data['error'] = 'Please Select a Filter for this Program';
                return view('dashboard.reports.heatmap.class_heatmap_report', $data);
            }
        }


        return view('dashboard.reports.heatmap.class_heatmap_report', $data);
    }

    public function teacherHeatmapReport(Request $request)
    {
        $schools = School::all();
        // Initialize $data array with defaults
        $data = [
            'schools' => $schools,
            'request' => $request->all(),
        ];
        if ($request->has('teacher1_id') && $request->has('teacher2_id')) {
            if ($request->filled('program_id')) {
                $selectedPrograms = Program::where('id', $request->program_id)->get();
            } else {
                $teacherPrograms1 = TeacherProgram::where('teacher_id', $request->teacher1_id)->pluck('program_id')->toArray();
                $teacherPrograms2 = TeacherProgram::where('teacher_id', $request->teacher2_id)->pluck('program_id')->toArray();

                $commonProgramIds = array_intersect($teacherPrograms1, $teacherPrograms2);
                $selectedPrograms = Program::whereIn('id', $commonProgramIds)->get();
            }

            ##################################
            $teacherStudentTests1 = StudentTest::where('teacher_id', $request->teacher1_id)
                ->whereIn('program_id', $selectedPrograms->pluck('id'))
                ->when($request->filled('from_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '<=', $request->to_date);
                })
                ->distinct()
                ->pluck('test_id');


            if ($teacherStudentTests1->isEmpty()) {
                // Add an error message to the data array
                $teacherName = User::find($request->teacher1_id)->name;
                $data['error'] = "No Assignments Found for teacher " . $teacherName . " Students";
                return view('dashboard.reports.heatmap.teacher_heatmap_report', $data);
            }
            $teacherStudentTests2 = StudentTest::where('teacher_id', $request->teacher2_id)
                ->whereIn('program_id', $selectedPrograms->pluck('id'))
                ->when($request->filled('from_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '<=', $request->to_date);
                })
                ->distinct()
                ->pluck('test_id');

            if ($teacherStudentTests2->isEmpty()) {
                // Add an error message to the data array
                $teacherName = User::find($request->teacher2_id)->name;
                $data['error'] = "No Assignments Found for teacher " . $teacherName . " Students";
                return view('dashboard.reports.heatmap.teacher_heatmap_report', $data);
            }

            $student_assignments1 = Test::whereIn('id', $teacherStudentTests1)->get();
            $student_assignments2 = Test::whereIn('id', $teacherStudentTests2)->get();


            ##################################

            $unitsUsage = [];
            $lessonsUsage = [];
            $gamesUsage = [];
            $skillsUsage = [];
            $programsUsage = [];

            foreach ($selectedPrograms as $program) {
                $totalProgramGames = 0;
                if (!isset($programsUsage[$program->id])) {
                    $programsUsage[$program->id] = [
                        'program_id' => $program->id,
                        'name' => $program->course->name . ' - ' . $program->stage->name,
                        'units' => [],
                        'usage_count' => 0,
                        'usage_percentage' => 0,
                        'total_games' => 0,
                        'total_assigned_games' => 0,
                    ];
                }

                $unitsUsage = []; // Initialize unitsUsage for each program

                foreach ($program->units as $unit) {
                    $totalUnitGames = 0;
                    if (!isset($unitsUsage[$unit->id])) {
                        $unitsUsage[$unit->id] = [
                            'unit_id' => $unit->id,
                            'name' => $unit->name,
                            'lessons' => [],
                            'usage_count' => 0,
                            'usage_percentage' => 0,
                            'total_games' => 0,
                            'total_assigned_games' => 0,
                        ];
                    }

                    $lessonsUsage = []; // Initialize lessonsUsage for each unit

                    foreach ($unit->lessons as $lesson) {
                        $gamesCount = Game::where('lesson_id', $lesson->id)->get()->count();
                        $totalProgramGames += $gamesCount;
                        $totalUnitGames += $gamesCount;
                        if (!isset($lessonsUsage[$lesson->id])) {
                            $lessonsUsage[$lesson->id] = [
                                'lesson_id' => $lesson->id,
                                'name' => $lesson->name,
                                'games' => [],
                                'assigned' => 0,
                                'usage_percentage' => 0,
                                'total_games' => $gamesCount,
                                'assigned_games' => [],
                                'total_assigned_games' => 0,
                            ];
                        }

                        $gamesUsage = [];
                        $skillsUsage = [];

                        foreach ($lesson->game as $game) {
                            if (!isset($gamesUsage[$game->game_type_id])) {
                                $gameType = GameType::find($game->game_type_id);
                                $gamesUsage[$game->game_type_id] = [
                                    'game_type_id' => $game->game_type_id,
                                    'name' => $gameType->name,
                                    'skills' => [],
                                    'assigned' => 0,
                                    'usage_percentage' => 0,
                                ];

                                // Assign skills to the game type using skill IDs as keys
                                if ($gameType->skills) {
                                    foreach ($gameType->skills->where('lesson_id', $lesson->id)->unique('skill') as $gameSkill) {
                                        $skill = $gameSkill->skill;

                                        if (!isset($skillsUsage[$skill->id])) {
                                            $skillsUsage[$skill->id] = [
                                                'skill_id' => $skill->id,
                                                'name' => $skill->skill,
                                                'usage_count' => 0,
                                                'usage_percentage' => 0,
                                            ];
                                        }
                                    }
                                    // Assign the collected skills to the game's 'skills'
                                    $gamesUsage[$game->game_type_id]['skills'] = $skillsUsage;
                                }
                            }
                        }
                        // Assign the collected games to the lesson's 'games'
                        $lessonsUsage[$lesson->id]['games'] = $gamesUsage;
                    }
                    // Assign the collected lessons to the unit's 'lessons'
                    $unitsUsage[$unit->id]['lessons'] = $lessonsUsage;
                    $unitsUsage[$unit->id]['total_games'] = $totalUnitGames;
                }
                // Assign the collected units to the program's 'units'
                $programsUsage[$program->id]['units'] = $unitsUsage;
                $programsUsage[$program->id]['total_games'] = $totalProgramGames;
            }
            $programsUsage2 = $programsUsage;

            // Group 1 Usage Calculations
            foreach ($student_assignments1 as $assignment) {
                // $testGames = Game::whereIn('id', TestQuestion::where('test_id', $assignment->id)->pluck('game_id'))->get();
                $testGame = Game::find($assignment->game_id);
                // dd($testGames);
                $lesson = Lesson::find($assignment->lesson_id);
                $unit_id = $lesson->unit_id;
                $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned'] = 1;
                $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['games'][$testGame->game_type_id]['assigned'] = 1;

                if (!in_array($testGame->id,    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'])) {
                    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'][] = $testGame->id;
                }
            }

            foreach ($programsUsage as $program) {
                $programAssignedGames = 0;
                foreach ($program['units'] as $unit) {
                    $assignedCount = 0;
                    $unitAssignedGames = 0;
                    foreach ($unit['lessons'] as $lesson) {
                        if ($lesson['assigned'] == 1) {
                            $assignedCount++;
                            $totalGamesCount  = $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_games'];
                            $assignedGamesCount = count($programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['assigned_games']);
                            $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['usage_percentage'] = round(($assignedGamesCount / $totalGamesCount) * 100, 2);
                            $unitAssignedGames += $assignedGamesCount;
                            $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_assigned_games'] = $assignedGamesCount;
                        }
                    }
                    if ($programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_games']  != 0) {
                        $programAssignedGames += $unitAssignedGames;
                        $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_assigned_games'] = $unitAssignedGames;
                        $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['usage_percentage'] = round($unitAssignedGames /  $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_games'] * 100, 2);
                    }
                }

                $programsUsage[$program['program_id']]['total_assigned_games'] = $programAssignedGames;
                if ($programsUsage[$program['program_id']]['total_games'] != 0) {
                    $programsUsage[$program['program_id']]['usage_percentage'] = round($programAssignedGames / $programsUsage[$program['program_id']]['total_games'] * 100, 2);
                }
            }

            // Group 2 Usage Calculations
            foreach ($student_assignments2 as $assignment) {
                // $testGames = Game::whereIn('id', TestQuestion::where('test_id', $assignment->id)->pluck('game_id'))->get();
                $testGame = Game::find($assignment->game_id);
                // dd($testGames);
                $lesson = Lesson::find($assignment->lesson_id);
                $unit_id = $lesson->unit_id;
                $programsUsage2[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned'] = 1;
                $programsUsage2[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['games'][$testGame->game_type_id]['assigned'] = 1;

                if (!in_array($testGame->id,    $programsUsage2[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'])) {
                    $programsUsage2[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'][] = $testGame->id;
                }
            }

            foreach ($programsUsage2 as $program) {
                $programAssignedGames = 0;
                foreach ($program['units'] as $unit) {
                    $assignedCount = 0;
                    $unitAssignedGames = 0;
                    foreach ($unit['lessons'] as $lesson) {
                        if ($lesson['assigned'] == 1) {
                            $assignedCount++;
                            $totalGamesCount  = $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_games'];
                            $assignedGamesCount = count($programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['assigned_games']);
                            $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['usage_percentage'] = round(($assignedGamesCount / $totalGamesCount) * 100, 2);
                            $unitAssignedGames += $assignedGamesCount;
                            $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_assigned_games'] = $assignedGamesCount;
                        }
                    }
                    if ($programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['total_games']  != 0) {
                        $programAssignedGames += $unitAssignedGames;
                        $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['total_assigned_games'] = $unitAssignedGames;
                        $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['usage_percentage'] = round($unitAssignedGames /  $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['total_games'] * 100, 2);
                    }
                }

                $programsUsage2[$program['program_id']]['total_assigned_games'] = $programAssignedGames;
                if ($programsUsage2[$program['program_id']]['total_games'] != 0) {
                    $programsUsage2[$program['program_id']]['usage_percentage'] = round($programAssignedGames / $programsUsage2[$program['program_id']]['total_games'] * 100, 2);
                }
            }


            $chartLabels = [];
            $chartValues = [];
            $chartLabels2 = [];
            $chartValues2 = [];
            if ($request->filled('filter') && $request->filled('program_id')) {
                switch ($request->filter) {
                    case 'Unit':
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels[] = $unit['name'];
                                $chartValues[] = $unit['usage_percentage'];
                            }
                        }
                        foreach ($programsUsage2 as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels2[] = $unit['name'];
                                $chartValues2[] = $unit['usage_percentage'];
                            }
                        }
                        $data['unitsUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['unitsUsage2'] =  $programsUsage2[$request->program_id]['units'];
                        $data['chartLabels'] =  $chartLabels;
                        $data['chartValues'] =  $chartValues;
                        $data['chartLabels2'] =  $chartLabels2;
                        $data['chartValues2'] =  $chartValues2;

                        $data['teacherName1'] = User::find($request->teacher1_id)->name;
                        $data['teacherName2'] = User::find($request->teacher2_id)->name;

                        break;
                    case 'Lesson':
                        $lessonsData =  [];
                        $assignedCount = 0;
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels[] = '-';
                                $chartValues[] = '-';
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels[] = $lesson['name'];
                                    $chartValues[] = $lesson['usage_percentage'];
                                    if ($lesson['assigned'] == 1) {
                                        $assignedCount++;
                                    }
                                }
                            }
                        }
                        $assignedCount = 0;
                        foreach ($programsUsage2 as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels2[] = '-';
                                $chartValues2[] = '-';
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels2[] = $lesson['name'];
                                    $chartValues2[] = $lesson['usage_percentage'];
                                    if ($lesson['assigned'] == 1) {
                                        $assignedCount++;
                                    }
                                }
                            }
                        }

                        $data['lessonsUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['lessonsUsage2'] =  $programsUsage2[$request->program_id]['units'];
                        $data['chartLabels'] =  $chartLabels;
                        $data['chartValues'] =  $chartValues;
                        $data['chartLabels2'] =  $chartLabels2;
                        $data['chartValues2'] =  $chartValues2;
                        $data['teacherName1'] = User::find($request->teacher1_id)->name;
                        $data['teacherName2'] = User::find($request->teacher2_id)->name;
                        break;
                    case 'Game':
                        $gameData = [];
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels[] = '-';
                                    $chartValues[] = '-';
                                    foreach ($lesson['games'] as $game) {
                                        $gameData[] = $game;
                                        $chartLabels[] = $game['name'];
                                        $chartValues[] = $game['assigned'];
                                    }
                                }
                            }
                        }
                        foreach ($programsUsage2 as $program) {
                            foreach ($program['units'] as $unit) {
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels2[] = '-';
                                    $chartValues2[] = '-';
                                    foreach ($lesson['games'] as $game) {
                                        $gameData[] = $game;
                                        $chartLabels2[] = $game['name'];
                                        $chartValues2[] = $game['assigned'];
                                    }
                                }
                            }
                        }
                        $data['gamesUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['gamesUsage2'] =  $programsUsage2[$request->program_id]['units'];
                        $data['gamesLabels'] =  $chartLabels;
                        $data['gamesValues'] =  $chartValues;
                        $data['gamesLabels2'] =  $chartLabels2;
                        $data['gamesValues2'] =  $chartValues2;
                        $data['teacherName1'] = User::find($request->teacher1_id)->name;
                        $data['teacherName2'] = User::find($request->teacher2_id)->name;
                        break;
                    default:
                        break;
                }
            } else if ($request->filled('filter') && !$request->filled('program_id')) {
                $data['error'] = 'Please Select a Program to Use filters';
                return view('dashboard.reports.heatmap.teacher_heatmap_report', $data);
            } elseif (!$request->filled('program_id')) {
                foreach ($programsUsage as $program) {
                    $chartLabels[] = $program['name'];
                    $chartValues[] = $program['usage_percentage'];
                }
                foreach ($programsUsage2 as $program) {
                    $chartLabels2[] = $program['name'];
                    $chartValues2[] = $program['usage_percentage'];
                }
                $data['programsUsage'] =  $programsUsage;
                $data['programsUsage2'] =  $programsUsage2;
                $data['chartLabels'] =  $chartLabels;
                $data['chartValues'] =  $chartValues;
                $data['chartLabels2'] =  $chartLabels2;
                $data['chartValues2'] =  $chartValues2;
                $data['teacherName1'] = User::find($request->teacher1_id)->name;
                $data['teacherName2'] = User::find($request->teacher2_id)->name;
            } else {
                $data['error'] = 'Please Select a Filter for this Program';
                return view('dashboard.reports.heatmap.teacher_heatmap_report', $data);
            }
        }
        return view('dashboard.reports.heatmap.teacher_heatmap_report', $data);
    }
    public function studentHeatmapReport(Request $request)
    {
        $schools = School::all();
        // Initialize $data array with defaults
        $data = [
            'schools' => $schools,
            'request' => $request->all(),
        ];
        if ($request->has('student1_id') && $request->has('student2_id')) {
            if ($request->filled('program_id')) {
                $selectedPrograms = Program::where('id', $request->program_id)->get();
            } else {
                $studentPrograms1 = UserCourse::where('user_id', $request->student1_id)->pluck('program_id')->toArray();
                $studentPrograms2 = UserCourse::where('user_id', $request->student2_id)->pluck('program_id')->toArray();

                $commonProgramIds = array_intersect($studentPrograms1, $studentPrograms2);
                $selectedPrograms = Program::whereIn('id', $commonProgramIds)->get();
            }

            ##################################
            $studentTests1 = StudentTest::where('student_id', $request->student1_id)
                ->whereIn('program_id', $selectedPrograms->pluck('id'))
                ->when($request->filled('from_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '<=', $request->to_date);
                })
                ->pluck('test_id');


            if ($studentTests1->isEmpty()) {
                // Add an error message to the data array
                $studentName = User::find($request->student1_id)->name;
                $data['error'] = "No Assignments Found for student: " . $studentName;
                return view('dashboard.reports.heatmap.student_heatmap_report', $data);
            }
            $studentTests2 = StudentTest::where('student_id', $request->student2_id)
                ->whereIn('program_id', $selectedPrograms->pluck('id'))
                ->when($request->filled('from_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($query) use ($request) {
                    return $query->whereDate('created_at', '<=', $request->to_date);
                })
                ->pluck('test_id');
            if ($studentTests2->isEmpty()) {
                // Add an error message to the data array
                $studentName = User::find($request->student2_id)->name;
                $data['error'] = "No Assignments Found for student: " . $studentName;
                return view('dashboard.reports.heatmap.student_heatmap_report', $data);
            }

            $student_assignments1 = Test::whereIn('id', $studentTests1)->get();
            $student_assignments2 = Test::whereIn('id', $studentTests2)->get();


            ##################################

            $unitsUsage = [];
            $lessonsUsage = [];
            $gamesUsage = [];
            $skillsUsage = [];
            $programsUsage = [];

            foreach ($selectedPrograms as $program) {
                $totalProgramGames = 0;
                if (!isset($programsUsage[$program->id])) {
                    $programsUsage[$program->id] = [
                        'program_id' => $program->id,
                        'name' => $program->course->name . ' - ' . $program->stage->name,
                        'units' => [],
                        'usage_count' => 0,
                        'usage_percentage' => 0,
                        'total_games' => 0,
                        'total_assigned_games' => 0,
                    ];
                }

                $unitsUsage = []; // Initialize unitsUsage for each program

                foreach ($program->units as $unit) {
                    $totalUnitGames = 0;
                    if (!isset($unitsUsage[$unit->id])) {
                        $unitsUsage[$unit->id] = [
                            'unit_id' => $unit->id,
                            'name' => $unit->name,
                            'lessons' => [],
                            'usage_count' => 0,
                            'usage_percentage' => 0,
                            'total_games' => 0,
                            'total_assigned_games' => 0,
                        ];
                    }

                    $lessonsUsage = []; // Initialize lessonsUsage for each unit

                    foreach ($unit->lessons as $lesson) {
                        $gamesCount = Game::where('lesson_id', $lesson->id)->get()->count();
                        $totalProgramGames += $gamesCount;
                        $totalUnitGames += $gamesCount;
                        if (!isset($lessonsUsage[$lesson->id])) {
                            $lessonsUsage[$lesson->id] = [
                                'lesson_id' => $lesson->id,
                                'name' => $lesson->name,
                                'games' => [],
                                'assigned' => 0,
                                'usage_percentage' => 0,
                                'total_games' => $gamesCount,
                                'assigned_games' => [],
                                'total_assigned_games' => 0,
                            ];
                        }

                        $gamesUsage = [];
                        $skillsUsage = [];

                        foreach ($lesson->game as $game) {
                            if (!isset($gamesUsage[$game->game_type_id])) {
                                $gameType = GameType::find($game->game_type_id);
                                $gamesUsage[$game->game_type_id] = [
                                    'game_type_id' => $game->game_type_id,
                                    'name' => $gameType->name,
                                    'skills' => [],
                                    'assigned' => 0,
                                    'usage_percentage' => 0,
                                ];

                                // Assign skills to the game type using skill IDs as keys
                                if ($gameType->skills) {
                                    foreach ($gameType->skills->where('lesson_id', $lesson->id)->unique('skill') as $gameSkill) {
                                        $skill = $gameSkill->skill;

                                        if (!isset($skillsUsage[$skill->id])) {
                                            $skillsUsage[$skill->id] = [
                                                'skill_id' => $skill->id,
                                                'name' => $skill->skill,
                                                'usage_count' => 0,
                                                'usage_percentage' => 0,
                                            ];
                                        }
                                    }
                                    // Assign the collected skills to the game's 'skills'
                                    $gamesUsage[$game->game_type_id]['skills'] = $skillsUsage;
                                }
                            }
                        }
                        // Assign the collected games to the lesson's 'games'
                        $lessonsUsage[$lesson->id]['games'] = $gamesUsage;
                    }
                    // Assign the collected lessons to the unit's 'lessons'
                    $unitsUsage[$unit->id]['lessons'] = $lessonsUsage;
                    $unitsUsage[$unit->id]['total_games'] = $totalUnitGames;
                }
                // Assign the collected units to the program's 'units'
                $programsUsage[$program->id]['units'] = $unitsUsage;
                $programsUsage[$program->id]['total_games'] = $totalProgramGames;
            }
            $programsUsage2 = $programsUsage;

            // Group 1 Usage Calculations
            foreach ($student_assignments1 as $assignment) {
                if (isset($programsUsage[$assignment->program_id])) {
                    $testGame = Game::find($assignment->game_id);
                    // dd($testGames);
                    $lesson = Lesson::find($assignment->lesson_id);
                    $unit_id = $lesson->unit_id;
                    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned'] = 1;
                    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['games'][$testGame->game_type_id]['assigned'] = 1;

                    if (!in_array($testGame->id,    $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'])) {
                        $programsUsage[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'][] = $testGame->id;
                    }
                }
            }

            foreach ($programsUsage as $program) {
                $programAssignedGames = 0;
                foreach ($program['units'] as $unit) {
                    $assignedCount = 0;
                    $unitAssignedGames = 0;
                    foreach ($unit['lessons'] as $lesson) {
                        if ($lesson['assigned'] == 1) {
                            $assignedCount++;
                            $totalGamesCount  = $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_games'];
                            $assignedGamesCount = count($programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['assigned_games']);
                            $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['usage_percentage'] = round(($assignedGamesCount / $totalGamesCount) * 100, 2);
                            $unitAssignedGames += $assignedGamesCount;
                            $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_assigned_games'] = $assignedGamesCount;
                        }
                    }
                    if ($programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_games']  != 0) {
                        $programAssignedGames += $unitAssignedGames;
                        $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_assigned_games'] = $unitAssignedGames;
                        $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['usage_percentage'] = round($unitAssignedGames /  $programsUsage[$program['program_id']]['units'][$unit['unit_id']]['total_games'] * 100, 2);
                    }
                }

                $programsUsage[$program['program_id']]['total_assigned_games'] = $programAssignedGames;
                if ($programsUsage[$program['program_id']]['total_games'] != 0) {
                    $programsUsage[$program['program_id']]['usage_percentage'] = round($programAssignedGames / $programsUsage[$program['program_id']]['total_games'] * 100, 2);
                }
            }

            // Group 2 Usage Calculations
            foreach ($student_assignments2 as $assignment) {
                // $testGames = Game::whereIn('id', TestQuestion::where('test_id', $assignment->id)->pluck('game_id'))->get();
                $testGame = Game::find($assignment->game_id);
                // dd($testGames);
                $lesson = Lesson::find($assignment->lesson_id);
                $unit_id = $lesson->unit_id;
                $programsUsage2[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned'] = 1;
                $programsUsage2[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['games'][$testGame->game_type_id]['assigned'] = 1;

                if (!in_array($testGame->id,    $programsUsage2[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'])) {
                    $programsUsage2[$assignment->program_id]['units'][$unit_id]['lessons'][$assignment->lesson_id]['assigned_games'][] = $testGame->id;
                }
            }

            foreach ($programsUsage2 as $program) {
                $programAssignedGames = 0;
                foreach ($program['units'] as $unit) {
                    $assignedCount = 0;
                    $unitAssignedGames = 0;
                    foreach ($unit['lessons'] as $lesson) {
                        if ($lesson['assigned'] == 1) {
                            $assignedCount++;
                            $totalGamesCount  = $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_games'];
                            $assignedGamesCount = count($programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['assigned_games']);
                            $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['usage_percentage'] = round(($assignedGamesCount / $totalGamesCount) * 100, 2);
                            $unitAssignedGames += $assignedGamesCount;
                            $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['lessons'][$lesson['lesson_id']]['total_assigned_games'] = $assignedGamesCount;
                        }
                    }
                    if ($programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['total_games']  != 0) {
                        $programAssignedGames += $unitAssignedGames;
                        $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['total_assigned_games'] = $unitAssignedGames;
                        $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['usage_percentage'] = round($unitAssignedGames /  $programsUsage2[$program['program_id']]['units'][$unit['unit_id']]['total_games'] * 100, 2);
                    }
                }

                $programsUsage2[$program['program_id']]['total_assigned_games'] = $programAssignedGames;
                if ($programsUsage2[$program['program_id']]['total_games'] != 0) {
                    $programsUsage2[$program['program_id']]['usage_percentage'] = round($programAssignedGames / $programsUsage2[$program['program_id']]['total_games'] * 100, 2);
                }
            }

            $chartLabels = [];
            $chartValues = [];
            $chartLabels2 = [];
            $chartValues2 = [];
            if ($request->filled('filter') && $request->filled('program_id')) {
                switch ($request->filter) {
                    case 'Unit':
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels[] = $unit['name'];
                                $chartValues[] = $unit['usage_percentage'];
                            }
                        }
                        foreach ($programsUsage2 as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels2[] = $unit['name'];
                                $chartValues2[] = $unit['usage_percentage'];
                            }
                        }
                        $data['unitsUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['unitsUsage2'] =  $programsUsage2[$request->program_id]['units'];
                        $data['chartLabels'] =  $chartLabels;
                        $data['chartValues'] =  $chartValues;
                        $data['chartLabels2'] =  $chartLabels2;
                        $data['chartValues2'] =  $chartValues2;

                        $data['studentName1'] = User::find($request->student1_id)->name;
                        $data['studentName2'] = User::find($request->student2_id)->name;

                        break;
                    case 'Lesson':
                        $lessonsData =  [];
                        $assignedCount = 0;
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels[] = '-';
                                $chartValues[] = '-';
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels[] = $lesson['name'];
                                    $chartValues[] = $lesson['usage_percentage'];
                                    if ($lesson['assigned'] == 1) {
                                        $assignedCount++;
                                    }
                                }
                            }
                        }
                        $assignedCount = 0;
                        foreach ($programsUsage2 as $program) {
                            foreach ($program['units'] as $unit) {
                                $chartLabels2[] = '-';
                                $chartValues2[] = '-';
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels2[] = $lesson['name'];
                                    $chartValues2[] = $lesson['usage_percentage'];
                                    if ($lesson['assigned'] == 1) {
                                        $assignedCount++;
                                    }
                                }
                            }
                        }

                        $data['lessonsUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['lessonsUsage2'] =  $programsUsage2[$request->program_id]['units'];
                        $data['chartLabels'] =  $chartLabels;
                        $data['chartValues'] =  $chartValues;
                        $data['chartLabels2'] =  $chartLabels2;
                        $data['chartValues2'] =  $chartValues2;
                        $data['studentName1'] = User::find($request->student1_id)->name;
                        $data['studentName2'] = User::find($request->student2_id)->name;
                        break;
                    case 'Game':
                        $gameData = [];
                        foreach ($programsUsage as $program) {
                            foreach ($program['units'] as $unit) {
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels[] = '-';
                                    $chartValues[] = '-';
                                    foreach ($lesson['games'] as $game) {
                                        $gameData[] = $game;
                                        $chartLabels[] = $game['name'];
                                        $chartValues[] = $game['assigned'];
                                    }
                                }
                            }
                        }
                        foreach ($programsUsage2 as $program) {
                            foreach ($program['units'] as $unit) {
                                foreach ($unit['lessons'] as $lesson) {
                                    $chartLabels2[] = '-';
                                    $chartValues2[] = '-';
                                    foreach ($lesson['games'] as $game) {
                                        $gameData[] = $game;
                                        $chartLabels2[] = $game['name'];
                                        $chartValues2[] = $game['assigned'];
                                    }
                                }
                            }
                        }
                        $data['gamesUsage'] =  $programsUsage[$request->program_id]['units'];
                        $data['gamesUsage2'] =  $programsUsage2[$request->program_id]['units'];
                        $data['gamesLabels'] =  $chartLabels;
                        $data['gamesValues'] =  $chartValues;
                        $data['gamesLabels2'] =  $chartLabels2;
                        $data['gamesValues2'] =  $chartValues2;
                        $data['studentName1'] = User::find($request->student1_id)->name;
                        $data['studentName2'] = User::find($request->student2_id)->name;
                        break;
                    default:
                        break;
                }
            } else if ($request->filled('filter') && !$request->filled('program_id')) {
                $data['error'] = 'Please Select a Program to Use filters';
                return view('dashboard.reports.heatmap.student_heatmap_report', $data);
            } elseif (!$request->filled('program_id')) {
                foreach ($programsUsage as $program) {
                    $chartLabels[] = $program['name'];
                    $chartValues[] = $program['usage_percentage'];
                }
                foreach ($programsUsage2 as $program) {
                    $chartLabels2[] = $program['name'];
                    $chartValues2[] = $program['usage_percentage'];
                }
                $data['programsUsage'] =  $programsUsage;
                $data['programsUsage2'] =  $programsUsage2;
                $data['chartLabels'] =  $chartLabels;
                $data['chartValues'] =  $chartValues;
                $data['chartLabels2'] =  $chartLabels2;
                $data['chartValues2'] =  $chartValues2;
                $data['studentName1'] = User::find($request->student1_id)->name;
                $data['studentName2'] = User::find($request->student2_id)->name;
            } else {
                $data['error'] = 'Please Select a Filter for this Program';
                return view('dashboard.reports.heatmap.student_heatmap_report', $data);
            }
        }
        return view('dashboard.reports.heatmap.student_heatmap_report', $data);
    }
    public function getTeacherAssignments($teacherId)
    {
        $assignments_ids = StudentTest::where('teacher_id', $teacherId)
            ->distinct()
            ->pluck('test_id');
        $assignments = Test::whereIn('id', $assignments_ids)->get();
        return response()->json($assignments);
    }
}
