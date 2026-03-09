<div id="header" class="header navbar navbar-default navbar-fixed-top">
    <form id="logout-form" action="{{ url('/logout') }}" method="POST">
        @csrf
    <div class="container-fluid">
        <div class="navbar-header">
            <a href="{{ url('/') }}" class="navbar-brand"><img  src="{{ asset('app/img/icon/hanochem.png') }}" style="width: 120px;" /></a>
            <button type="button" class="navbar-toggle" data-click="sidebar-toggled">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <ul class="nav navbar-nav navbar-right">
            @if(count($Header["notification"]) > 0)
            <li class="dropdown">
                <a href="javascript:;" data-toggle="dropdown" class="dropdown-toggle f-s-14">
                    <i class="fa fa-bell-o"></i>
                    <span class="label">{{ count($Header["notification"]) }}</span>
                </a>
                <ul class="dropdown-menu media-list pull-right animated fadeInDown">
                    <li class="dropdown-header">Notifications ({{ count($Header["notification"]) }})</li>
                    @foreach($Header["notification"] as $row)
                    <li class="media">
                        <a href="{{ url('/inbox/' . $row->id) }}">
                            <div class="media-left"><i class="fa fa-envelope media-object bg-blue"></i></div>
                            <div class="media-body">
                                <h6 class="media-heading">{{ $row->sender_name }}</h6>
                                <div class="text-muted f-s-11">{{ $row->mail_subject }}</div>
                            </div>
                        </a>
                    </li>
                    @endforeach
                    <!-- <li class="media">
                        <a href="javascript:;">
                            <div class="media-left"><img src="{{ asset('app/img/unknown.png') }}" class="media-object" alt="abc" /></div>
                            <div class="media-body">
                                <h6 class="media-heading">John Smith</h6>
                                <p>Quisque pulvinar tellus sit amet sem scelerisque tincidunt.</p>
                                <div class="text-muted f-s-11">25 minutes ago</div>
                            </div>
                        </a>
                    </li>
                    <li class="media">
                        <a href="javascript:;">
                            <div class="media-left"><img src="assets/img/user-2.jpg" class="media-object" alt="" /></div>
                            <div class="media-body">
                                <h6 class="media-heading">Olivia</h6>
                                <p>Quisque pulvinar tellus sit amet sem scelerisque tincidunt.</p>
                                <div class="text-muted f-s-11">35 minutes ago</div>
                            </div>
                        </a>
                    </li>
                    <li class="media">
                        <a href="javascript:;">
                            <div class="media-left"><i class="fa fa-plus media-object bg-green"></i></div>
                            <div class="media-body">
                                <h6 class="media-heading"> New User Registered</h6>
                                <div class="text-muted f-s-11">1 hour ago</div>
                            </div>
                        </a>
                    </li> -->
                    <!-- <li class="media">
                        <a href="javascript:;">
                            <div class="media-left"><i class="fa fa-envelope media-object bg-blue"></i></div>
                            <div class="media-body">
                                <h6 class="media-heading"> New Email From John</h6>
                                <div class="text-muted f-s-11">2 hour ago</div>
                            </div>
                        </a>
                    </li> -->
                    <!-- <li class="dropdown-footer text-center">
                        <a href="javascript:;">View more</a>
                    </li> -->
                </ul>
            </li>
            @endif
            <li class="dropdown navbar-user">
                <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
                    <img src="{{ asset('app/img/unknown.png') }}" alt="" />
                    <span class="hidden-xs">Welcome, {{ Auth::user()->full_name }}</span> <b class="caret"></b>
                </a>
                <ul class="dropdown-menu animated fadeInLeft">
                    <li class="arrow"></li>
                    <!-- <li><a href="javascript:;">Edit Profile</a></li> -->
                    <li><a href="{{ url('/user/changepassword') }}">Change Password</a></li>
                    <li class="divider"></li>
                    <li><a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="icon-switch2"></i> Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
    </form>
</div>
