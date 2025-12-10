<div class="main-sidebar sidebar-style-2">
    <aside" id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ url('/admin/dashboard') }}">
                <img alt="image" src="{{ asset('public/admin/assets/img/logo.png') }}" class="header-logo" />
                {{-- <span class="logo-name">Crop Secure</span> --}}
            </a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Main</li>
            <li class="dropdown {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <a href="{{ url('/admin/dashboard') }}" class="nav-link"><i
                        data-feather="home"></i><span>Dashboard</span></a>
            </li>



            {{--  Roles --}}

            @if (Auth::guard('admin')->check() ||
                    ($sideMenuPermissions->has('Roles') && $sideMenuPermissions['Roles']->contains('view')))
                {{-- FAQS --}}
                <li class="dropdown {{ request()->is('admin/roles*') ? 'active' : '' }}">
                    <a href="{{ url('admin/roles') }}" class="nav-link"><i
                            data-feather="user"></i><span>Roles</span></a>
                </li>
            @endif



            {{--  SubAdmin --}}

            @if (Auth::guard('admin')->check() ||
                    ($sideMenuPermissions->has('Sub Admins') && $sideMenuPermissions['Sub Admins']->contains('view')))
                {{-- FAQS --}}
                <li class="dropdown {{ request()->is('admin/subadmin*') ? 'active' : '' }}">
                    <a href="{{ url('admin/subadmin') }}" class="nav-link"><i data-feather="user"></i><span>Sub
                            Admins</span></a>
                </li>
            @endif

            {{--  Users --}}

            @if (Auth::guard('admin')->check() ||
                    ($sideMenuPermissions->has('Users') && $sideMenuPermissions['Users']->contains('view')))
                <li class="dropdown {{ request()->is('admin/user*') ? 'active' : '' }}">
                    <a href="{{ url('admin/user') }}" class="nav-link">
                        <i data-feather="users"></i>
                        <span>Users</span>
                    </a>
                </li>
            @endif

			{{--  dynamic forms --}}

            <!-- @if (Auth::guard('admin')->check() ||
                    ($sideMenuPermissions->has('Users') && $sideMenuPermissions['Users']->contains('view'))) -->
                <li class="dropdown {{ request()->is('admin/companies*') ? 'active' : '' }}">
                    <a href="{{ url('admin/companies') }}" class="nav-link">
                        <i data-feather="file-text"></i>
                        <span>Companies Forms</span>
                    </a>
                </li>
            <!-- @endif -->


            {{--  Blogs --}}

            @if (Auth::guard('admin')->check() ||
                    ($sideMenuPermissions->has('Blogs') && $sideMenuPermissions['Blogs']->contains('view')))
                {{-- FAQS --}}
                <li class="dropdown {{ request()->is('admin/blogs*') ? 'active' : '' }}">
                    <a href="{{ url('admin/blogs-index') }}" class="nav-link"><i
                            data-feather="book-open"></i><span>Blogs</span></a>
                </li>
            @endif

             {{--  SEO --}}

            @if (Auth::guard('admin')->check() ||
                    ($sideMenuPermissions->has('Roles') && $sideMenuPermissions['seo']->contains('seo')))
                {{-- FAQS --}}
                <li class="dropdown {{ request()->is('admin/seo*') ? 'active' : '' }}">
                    <a href="{{ url('admin/seo') }}" class="nav-link"><i
                            data-feather="trending-up"></i><span>SEO</span></a>
                </li>
            @endif
            
             {{-- Notification --}}

            @if (Auth::guard('admin')->check() ||
                    ($sideMenuPermissions->has('Notifications') && $sideMenuPermissions['Notifications']->contains('view')))
                {{-- Notification --}}
                <li class="dropdown {{ request()->is('admin/notification*') ? 'active' : '' }}">
                    <a href="
                {{ route('notification.index') }}
                " class="nav-link">
                        <i data-feather="bell"></i><span>Notifications</span>
                    </a>
                </li>
            @endif

            {{--  FAQS --}}

            @if (Auth::guard('admin')->check() ||
                    ($sideMenuPermissions->has('Faqs') && $sideMenuPermissions['Faqs']->contains('view')))
                <li class="dropdown {{ request()->is('admin/faq*') ? 'active' : '' }}">
                    <a href="{{ url('admin/faq') }}" class="nav-link">
                        <i data-feather="settings"></i>
                        <span>FAQ's</span>
                    </a>
                </li>
            @endif
            
             {{-- Contact Us  --}}


            @if (Auth::guard('admin')->check() ||
                    ($sideMenuPermissions->has('Contact us') && $sideMenuPermissions['Contact us']->contains('view')))
                {{-- Contact Us --}}
                <li class="dropdown {{ request()->is('admin/admin/contact-us*') ? 'active' : '' }}">
                    <a href="{{ url('admin/admin/contact-us') }}" class="nav-link"><i
                            data-feather="mail"></i><span>Contact
                            Us</span></a>
                </li>
            @endif


            {{--  About Us --}}

            @if (Auth::guard('admin')->check() ||
                    ($sideMenuPermissions->has('About us') && $sideMenuPermissions['About us']->contains('view')))
                {{-- About Us --}}
                <li class="dropdown {{ request()->is('admin/about-us*') ? 'active' : '' }}">
                    <a href="{{ url('admin/about-us') }}" class="nav-link"><i
                            data-feather="help-circle"></i><span>About
                            Us</span></a>
                </li>
            @endif

            


            {{--  Privacy Policy --}}

            @if (Auth::guard('admin')->check() ||
                    ($sideMenuPermissions->has('Privacy & Policy') && $sideMenuPermissions['Privacy & Policy']->contains('view')))
                {{--  Privacy Policy --}}
                <li class="dropdown {{ request()->is('admin/privacy-policy*') ? 'active' : '' }}">
                    <a href="{{ url('admin/privacy-policy') }}" class="nav-link"><i
                            data-feather="shield"></i><span>Privacy
                            & Policy</span></a>
                </li>
            @endif




            {{--  Terms & Conditions --}}

            @if (Auth::guard('admin')->check() ||
                    ($sideMenuPermissions->has('Terms & Conditions') &&
                        $sideMenuPermissions['Terms & Conditions']->contains('view')))
                <li class="dropdown {{ request()->is('admin/term-condition*') ? 'active' : '' }}">
                    <a href="{{ url('admin/term-condition') }}" class="nav-link"><i
                            data-feather="file-text"></i><span>Terms
                            & Conditions</span></a>
                </li>
            @endif



        </ul>
        </aside>
</div>
