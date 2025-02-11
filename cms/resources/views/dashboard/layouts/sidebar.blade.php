<div class="nk-sidebar nk-sidebar-fixed " data-content="sidebarMenu">
        <div class="nk-sidebar-element nk-sidebar-head">
                <div class="nk-sidebar-brand"><a href="{{ route('dashboard') }}" class="logo-link nk-sidebar-logo"><img
                                        class="logo-light logo-img" src="{{ asset('assets/images/logo.png') }}"
                                        srcset="{{ asset('assets/images/logo.png') }}" alt="logo"><img class="logo-dark logo-img"
                                        src="{{ asset('assets/images/logo.png') }}" srcset="{{ asset('assets/images/logo.png') }} 2x"
                                        alt="logo-dark"><img class="logo-small logo-img logo-img-small"
                                        src="{{ asset('assets/images/logo.png') }}" srcset="{{ asset('assets/images/logo.png') }} 2x"
                                        alt="logo-small"></a></div>

        </div>
        <div class="nk-sidebar-element">
                <div class="nk-sidebar-content">
                        <div class="nk-sidebar-menu" data-simplebar>
                                <ul class="nk-menu">
                                        @if (!Auth::user()->hasRole('Cordinator'))
                                        <li class="nk-menu-item"><a href="{{ route('dashboard') }}" class="nk-menu-link"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-dashboard-fill"></em></span><span
                                                                class="nk-menu-text">Dashboard</span></a></li>
                                        @endif
                                        @can('school-list')
                                        @if(Auth::user()->hasRole('Admin'))
                                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-building"></em></span><span
                                                                class="nk-menu-text">Users</span></a>
                                                <ul class="nk-menu-sub">
                                                        @can('school-create')
                                                        <li class="nk-menu-item"><a href="{{ route('users.create') }}" class="nk-menu-link"><span
                                                                                class="nk-menu-text">Add User
                                                                        </span></a></li>
                                                        @endcan
                                                        @can('school-list')
                                                        <li class="nk-menu-item"><a href="{{ route('users.index') }}" class="nk-menu-link"><span
                                                                                class="nk-menu-text">User
                                                                                List</span></a></li>
                                                        @endcan
                                                </ul>
                                        </li>
                                        @endif
                                        @endcan
                                        @can('school-list')
                                        @if(!Auth::user()->hasRole('Cordinator'))
                                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-building"></em></span><span
                                                                class="nk-menu-text">School</span></a>
                                                <ul class="nk-menu-sub">
                                                        @can('school-create')
                                                        <li class="nk-menu-item"><a href="{{ route('schools.create') }}" class="nk-menu-link"><span
                                                                                class="nk-menu-text">Add School
                                                                        </span></a></li>
                                                        @endcan
                                                        @can('school-list')
                                                        <li class="nk-menu-item"><a href="{{ route('schools.index') }}" class="nk-menu-link"><span
                                                                                class="nk-menu-text">Schools
                                                                                List</span></a></li>
                                                        @endcan
                                                </ul>
                                        </li>
                                        @endif
                                        @endcan

                                        @can('class-list')
                                        @if(!Auth::user()->hasRole('Cordinator'))
                                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-user-circle"></em></span><span
                                                                class="nk-menu-text">Class</span></a>
                                                <ul class="nk-menu-sub">
                                                        @can('class-create')
                                                        <li class="nk-menu-item"><a href="{{ route('classes.create') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Add Class
                                                                        </span></a></li>
                                                        @endcan
                                                        @can('class-list')
                                                        <li class="nk-menu-item"><a href="{{ route('classes.index') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Class
                                                                                List</span></a></li>
                                                        @endcan
                                                </ul>
                                        </li>
                                        @endif
                                        @endcan

                                        @can('student-list')
                                        @if(!Auth::user()->hasRole('Cordinator'))
                                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-users-fill"></em></span><span
                                                                class="nk-menu-text">Students</span></a>
                                                <ul class="nk-menu-sub">
                                                        @can('student-create')
                                                        <li class="nk-menu-item"><a href="{{ route('students.create') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Add Students</span></a>
                                                        </li>
                                                        @endcan
                                                        <li class="nk-menu-item"><a href="{{ route('students.index') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Student
                                                                                List</span></a></li>
                                                </ul>
                                        </li>
                                        @endif
                                        @endcan

                                        @can('instructor-list')
                                        @if(!Auth::user()->hasRole('Cordinator'))
                                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-user-fill"></em></span><span
                                                                class="nk-menu-text">Teachers</span></a>
                                                <ul class="nk-menu-sub">
                                                        @can('instructor-create')
                                                        <li class="nk-menu-item"><a href="{{ route('instructors.create') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Add Teacher
                                                                        </span></a></li>
                                                        @endcan
                                                        <li class="nk-menu-item"><a href="{{ route('instructors.index') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Teacher
                                                                                List</span></a></li>
                                                </ul>
                                        </li>
                                        @endif
                                        @endcan


                                        @if(Auth::user()->can('student-list') || Auth::user()->hasRole('Cordinator'))
                                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-property-add"></em></span><span
                                                                class="nk-menu-text">Student Reports</span></a>
                                                <ul class="nk-menu-sub">
                                                        <!-- <li class="nk-menu-item"><a href="{{ route('reports.index') }}"
                                                                class="nk-menu-link"><span class="nk-menu-text">Progress Reports</span></a></li> -->
                                                        <li class="nk-menu-item"><a href="{{ route('reports.completionReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Completion Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.masteryReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Mastery Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.numOfTrialsReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Number of Trials Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.studentLoginReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Number of Logins Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.studentContentEngagementReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Content Engagement Report</span></a>
                                                        </li>
                                                </ul>
                                        </li>
                                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-property-add"></em></span><span
                                                                class="nk-menu-text">Teacher Reports</span></a>
                                                <ul class="nk-menu-sub">
                                                        <li class="nk-menu-item"><a href="{{ route('reports.teacherCompletionReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Completion Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.teacherStudentsMasteryLevel') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Students Mastery Level Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.teacherContentEngagementReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Content Engagement Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.teacherLoginReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Number of Logins Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.teacherContentCoverageReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Content Coverage Report</span></a>
                                                        </li>
                                                </ul>
                                        </li>
                                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-property-add"></em></span><span
                                                                class="nk-menu-text">Class Reports</span></a>
                                                <ul class="nk-menu-sub">
                                                        <li class="nk-menu-item"><a href="{{ route('reports.classCompletionReportWeb') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Completion
                                                                                Report</span></a></li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.classMasteryReportWeb') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Mastery Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.classNumOfTrialsReportWeb') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Number of Trials Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.classContentEngagementReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Content Engagement Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.classContentUsageReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Content Usage Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.classContentGapReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Content Gap Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.classLoginReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Number of Logins Report</span></a>
                                                        </li>
                                                </ul>
                                        </li>
                                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-property-add"></em></span><span
                                                                class="nk-menu-text">School Reports</span></a>
                                                <ul class="nk-menu-sub">
                                                        <li class="nk-menu-item"><a href="{{ route('reports.schoolCompletionReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Completion Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.schoolContentEngagementReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Content Engagement Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.schoolContentGapReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Content Gap Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.schoolLoginReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Number of Logins Report</span></a>
                                                        </li>
                                                </ul>
                                        </li>
                                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-property-add"></em></span><span
                                                                class="nk-menu-text">Heatmap Reports</span></a>
                                                <ul class="nk-menu-sub">
                                                        <!-- <li class="nk-menu-item"><a href="{{ route('reports.classGenderReportWeb') }}"
                                                                class="nk-menu-link"><span class="nk-menu-text">Class Gender Report</span></a>
                                                </li>
                                                <li class="nk-menu-item"><a href="{{ route('reports.schoolGenderReportWeb') }}"
                                                                class="nk-menu-link"><span class="nk-menu-text">School Gender Report</span></a>
                                                </li> -->
                                                        <li class="nk-menu-item"><a href="{{ route('reports.classHeatmapReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Class Heatmap Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.teacherHeatmapReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Teacher Heatmap Report</span></a>
                                                        </li>
                                                        <li class="nk-menu-item"><a href="{{ route('reports.studentHeatmapReport') }}"
                                                                        class="nk-menu-link"><span class="nk-menu-text">Student Heatmap Report</span></a>
                                                        </li>
                                                        <!-- <li class="nk-menu-item"><a href="{{ route('reports.schoolGenderReportWeb') }}"
                                                                class="nk-menu-link"><span class="nk-menu-text">School Gender </span></a>
                                                </li>
                                                <li class="nk-menu-item"><a href="{{ route('reports.schoolGenderReportWeb') }}"
                                                                class="nk-menu-link"><span class="nk-menu-text">School Gender Report</span></a>
                                                </li> -->
                                                </ul>
                                        </li>
                                        @endif
                                        @can('role-list')
                                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-book-fill"></em></span><span
                                                                class="nk-menu-text">Roles</span></a>
                                                <ul class="nk-menu-sub">
                                                        @can('role-create')
                                                        <li class="nk-menu-item"><a href="{{ route('roles.create') }}" class="nk-menu-link"><span
                                                                                class="nk-menu-text">Add Role</span></a></li>
                                                        @endcan
                                                        @can('role-list')
                                                        <li class="nk-menu-item"><a href="{{ route('roles.index') }}" class="nk-menu-link"><span
                                                                                class="nk-menu-text">Roles List</span></a></li>
                                                        @endcan
                                                </ul>
                                        </li>
                                        @endcan
                                </ul>
                        </div>
                </div>
        </div>
</div>