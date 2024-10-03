<div class="nk-header ">
    <div class="container-fluid">
        <div class="nk-header-wrap">
            <div class="nk-menu-trigger d-xl-none ms-n1">
                <a href="{{ route('dashboard') }}" class="nk-nav-toggle nk-quick-nav-icon" data-target="sidebarMenu"><em
                        class="icon ni ni-menu"></em>
                </a>
            </div>
            <div class="nk-header-brand d-xl-none">
                <a href="{{ route('dashboard') }}" class="logo-link">
                    <img class="logo-light logo-img" src="{{ asset('assets/images/logo.png') }}"
                        srcset="{{ asset('assets/images/logo.png') }}" alt="logo">
                    <img class="logo-dark logo-img" src="{{ asset('assets/images/logo.png') }}"
                        srcset="{{ asset('assets/images/logo.png') }}" alt="logo-dark">
                </a>
            </div>

            <div class="nk-header-tools">
                <ul class="nk-quick-nav">



                    <li class="dropdown user-dropdown"><a href="#" class="dropdown-toggle me-n1"
                            data-bs-toggle="dropdown">
                            <div class="user-toggle">
                                <div class="user-avatar sm"><em class="icon ni ni-user-alt"></em></div>
                                <div class="user-info d-none d-xl-block">
                                    <div class="user-status user-status-active">
                                    </div>
                                    <div class="user-name dropdown-indicator">{{ Auth::user()->name }}</div>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-md dropdown-menu-end">
                            <div class="dropdown-inner user-card-wrap bg-lighter d-none d-md-block">
                                <div class="user-card">
                                    {{-- <div class="user-avatar"><span>AB</span></div> --}}
                                    <div class="user-info"><span class="lead-text">{{ Auth::user()->name }}</span><span
                                            class="sub-text">{{ Auth::user()->email }}</span></div>
                                </div>
                            </div>
                            {{-- <div class="dropdown-inner">
                                <ul class="link-list">
                                    <li><a href="admin-profile.html"><em class="icon ni ni-user-alt"></em><span>View
                                                Profile</span></a></li>
                                    <li><a href="admin-profile-setting.html"><em
                                                class="icon ni ni-setting-alt"></em><span>Account
                                                Setting</span></a></li>
                                    <li><a href="admin-profile-activity.html"><em
                                                class="icon ni ni-activity-alt"></em><span>Login
                                                Activity</span></a></li>
                                </ul>
                            </div> --}}
                            <div class="dropdown-inner">
                                <ul class="link-list">
                                    <li>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                            style="display: none;">
                                            @csrf
                                        </form>

                                        <a href="#"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <em class="icon ni ni-signout"></em><span>Sign
                                                out</span>
                                        </a>



                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>