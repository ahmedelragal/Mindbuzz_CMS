<div class="nk-sidebar nk-sidebar-fixed " data-content="sidebarMenu">
        <div class="nk-sidebar-element nk-sidebar-head">
                <div class="nk-sidebar-brand"><a href="{{ route('dashboard') }}" class="logo-link nk-sidebar-logo"><img
                                        class="logo-light logo-img" src="{{ asset('assets/images/logo.png') }}"
                                        srcset="{{ asset('assets/images/logo.png') }}" alt="logo"><img class="logo-dark logo-img"
                                        src="{{ asset('assets/images/logo.png') }}" srcset="{{ asset('assets/images/logo.png') }} 2x"
                                        alt="logo-dark"><img class="logo-small logo-img logo-img-small"
                                        src="{{ asset('assets/images/logo.png') }}" srcset="{{ asset('assets/images/logo.png') }} 2x"
                                        alt="logo-small"></a></div>
                <div class="nk-menu-trigger me-n2"><a href="#" class="nk-nav-toggle nk-quick-nav-icon d-xl-none"
                                data-target="sidebarMenu"><em class="icon ni ni-arrow-left"></em></a><a href="#"
                                class="nk-nav-compact nk-quick-nav-icon d-none d-xl-inline-flex" data-target="sidebarMenu"><em
                                        class="icon ni ni-menu"></em></a></div>
        </div>
        <div class="nk-sidebar-element">
                <div class="nk-sidebar-content">
                        <div class="nk-sidebar-menu" data-simplebar>
                                <ul class="nk-menu">
                                        <li class="nk-menu-item"><a href="{{ route('dashboard') }}" class="nk-menu-link"><span
                                                                class="nk-menu-icon"><em class="icon ni ni-dashboard-fill"></em></span><span
                                                                class="nk-menu-text">Dashboard</span></a></li>
                                        {{-- @can('school-list')
                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                    class="nk-menu-icon"><em class="icon ni ni-user-fill"></em></span><span
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
                                @endcan --}}
                                @can('school-list')
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
                                @endcan
                                @can('school-list')
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

                                @endcan

                                {{-- @can('course-list')
                        <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                    class="nk-menu-icon"><em class="icon ni ni-file-docs"></em></span><span
                                    class="nk-menu-text">Courses</span></a>
                            <ul class="nk-menu-sub">
                                @can('course-create')
                                    <li class="nk-menu-item"><a href="{{ route('courses.create') }}" class="nk-menu-link"><span
                                        class="nk-menu-text">Add Course</span></a></li>
                                @endcan
                                @can('course-list')
                                <li class="nk-menu-item"><a href="{{ route('courses.index') }}" class="nk-menu-link"><span
                                                        class="nk-menu-text">Course List</span></a></li>
                                @endcan
                                </ul>
                                </li>
                                @endcan --}}

                                <!--@can('stage-list')-->
                                <!--    <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span-->
                                <!--                class="nk-menu-icon"><em class="icon ni ni-book-fill"></em></span><span-->
                                <!--                class="nk-menu-text">Stage</span></a>-->
                                <!--        <ul class="nk-menu-sub">-->
                                <!--            @can('stage-create')-->
                                <!--                <li class="nk-menu-item"><a href="{{ route('stages.create') }}" class="nk-menu-link"><span-->
                                <!--                            class="nk-menu-text">Add Stage</span></a></li>-->
                                <!--            @endcan-->
                                <!--            @can('stage-list')-->
                                <!--                <li class="nk-menu-item"><a href="{{ route('stages.index') }}" class="nk-menu-link"><span-->
                                <!--                            class="nk-menu-text">Stage List</span></a></li>-->
                                <!--            @endcan-->
                                <!--        </ul>-->
                                <!--    </li>-->
                                <!--@endcan-->
                                <!--@can('program-list')-->

                                <!--    <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span-->
                                <!--                class="nk-menu-icon"><em class="icon ni ni-book-fill"></em></span><span-->
                                <!--                class="nk-menu-text">Cluster</span></a>-->
                                <!--        <ul class="nk-menu-sub">-->
                                <!--@can('program-create')-->
                                <!--    <li class="nk-menu-item"><a href="{{ route('programs.create') }}"-->
                                <!--            class="nk-menu-link"><span class="nk-menu-text">Add Cluster</span></a></li>-->
                                <!--@endcan-->
                                <!--            @can('program-list')-->
                                <!--                <li class="nk-menu-item"><a href="{{ route('programs.index') }}"-->
                                <!--                        class="nk-menu-link"><span class="nk-menu-text">Cluster List</span></a></li>-->
                                <!--            @endcan-->

                                <!--        </ul>-->
                                <!--    </li>-->
                                <!--@endcan-->


                                @can('class-list')
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
                                                                class="nk-menu-link"><span class="nk-menu-text">Classes
                                                                        List</span></a></li>
                                                @endcan


                                        </ul>
                                </li>
                                @endcan
                                {{-- @can('view_user') --}}
                                @can('student-list')
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
                                                                class="nk-menu-link"><span class="nk-menu-text">Students
                                                                        List</span></a></li>
                                        </ul>
                                </li>
                                @endcan
                                {{-- @endcan --}}
                                @can('instructor-list')
                                <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                        class="nk-menu-icon"><em class="icon ni ni-user-fill"></em></span><span
                                                        class="nk-menu-text">Instructors</span></a>
                                        <ul class="nk-menu-sub">
                                                @can('instructor-create')
                                                <li class="nk-menu-item"><a href="{{ route('instructors.create') }}"
                                                                class="nk-menu-link"><span class="nk-menu-text">Add Instructors
                                                                </span></a></li>
                                                @endcan
                                                <li class="nk-menu-item"><a href="{{ route('instructors.index') }}"
                                                                class="nk-menu-link"><span class="nk-menu-text">Instructor
                                                                        List</span></a></li>
                                        </ul>
                                </li>
                                @endcan

                                @can('student-list')
                                <!-- <li class="nk-menu-item has-sub"><a href="{{ route('reports.index') }}"
                        class="nk-menu-link "></span><span
                            class="nk-menu-icon"><em class="icon ni ni-property-add"></em></span><span class="nk-menu-text">Student Reports</span></a>
                </li> -->

                                <li class="nk-menu-item has-sub"><a href="#" class="nk-menu-link nk-menu-toggle"><span
                                                        class="nk-menu-icon"><em class="icon ni ni-property-add"></em></span><span
                                                        class="nk-menu-text">Student Reports</span></a>
                                        <ul class="nk-menu-sub">
                                                <li class="nk-menu-item"><a href="{{ route('reports.index') }}"
                                                                class="nk-menu-link"><span class="nk-menu-text">Progress Reports</span></a></li>
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
                                                        class="nk-menu-text">Instructor Reports</span></a>
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
                                @endcan
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
                                {{-- <li class="nk-menu-item"><a href="message.html" class="nk-menu-link"><span
                                class="nk-menu-icon"><em class="icon ni ni-chat-fill"></em></span><span
                                class="nk-menu-text">Messages</span></a></li>
                    <li class="nk-menu-item"><a href="admin-profile.html" class="nk-menu-link"><span
                                class="nk-menu-icon"><em class="icon ni ni-account-setting-fill"></em></span><span
                                class="nk-menu-text">Admin profile</span></a></li>

                    <li class="nk-menu-item"><a href="settings.html" class="nk-menu-link"><span
                                class="nk-menu-icon"><em class="icon ni ni-setting-alt-fill"></em></span><span
                                class="nk-menu-text">Settings</span></a></li>
                    <li class="nk-menu-heading">
                        <h6 class="overline-title text-primary-alt">Return to</h6>
                    </li>
                    <li class="nk-menu-item"><a href="../index.html" class="nk-menu-link"><span
                                class="nk-menu-icon"><em class="icon ni ni-dashlite-alt"></em></span><span
                                class="nk-menu-text">Main
                                Dashboard</span></a></li>
                    <li class="nk-menu-item"><a href="../components.html" class="nk-menu-link"><span
                                class="nk-menu-icon"><em class="icon ni ni-layers-fill"></em></span><span
                                class="nk-menu-text">All Components</span></a></li> --}}
                                </ul>
                        </div>
                </div>
        </div>
</div>