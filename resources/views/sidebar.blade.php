<div id="sidebar" class="sidebar">
    <div data-scrollbar="true" data-height="100%">
        <ul class="nav">
            <li class="nav-profile">
                <div class="image">
                    <a href="javascript:;"><img src="{{ asset('app/img/unknown.png') }}" alt="" /></a>
                </div>
                <div class="info">
                    {{ Auth::user()->name }}
                    <small style="font-size: 8px">{{ $Profile->department_name }}</small>
                </div>
            </li>
        </ul>
        <ul class="nav">
            <li class="nav-header">Navigation</li>
            @php ($No = 1) @endphp
            @php ($Flag = "F") @endphp

            @foreach($Menu as $menu)
                @if (strtolower($menu['name']) == strtolower($SHR_Module))
                    @php ($Active = "active") @endphp
                @else
                    @php ($Active = "") @endphp
                @endif

                @if (strtolower($menu['parent']) == strtolower($SHR_Parent))
                    @php ($ActiveParent = "active") @endphp
                @else
                    @php ($ActiveParent = "") @endphp
                @endif

                @if($menu["level"] == 1)
                    @if($Flag == "T")
                        </ul></li>
                        @php ($Flag = "F") @endphp
                    @endif
                    @if($menu["child"] == 0)
                        <li class="{{ $Active }}"><a href="{{ url($menu['url']) }}"><i class="{{ $menu['icon'] }}"></i> <span>{{ $menu['name'] }}</span></a></li>
                    @else
                        <li class="has-sub {{ $ActiveParent }}">
                            <a href="javascript:void(0)">
                                <b class="caret pull-right"></b>
                                <i class="{{ $menu['icon'] }}"></i>
                                <span>{{ $menu['name'] }}</span>
                            </a>
                            <ul class="sub-menu">
                    @endif
                @else
                    <li class="{{ $Active }}"><a href="{{ url($menu['url']) }}">{{ $menu['name'] }}</a></li>
                    @php ($Flag = "T") @endphp
                @endif
                @php
                    $No = $No + 1
                @endphp
            @endforeach
            
    
    

            {{-- TUTUP submenu kalau masih terbuka dari loop di atas --}}
            @if ($Flag == "T")
                </ul></li>
                @php ($Flag = "F")
            @endif

            {{-- === MENU BARU: Transmittal – Incoming Company === --}}
            <li class="{{ request()->is('incoming_company/*') ? 'active' : '' }}">
                <a href="{{ url('incoming_company/index') }}">
                    <i class="fa fa-building"></i>
                    <span>Transmittal – Incoming Company</span>
                </a>
            </li>
            
            
<li>
<a href="{{ url('comment_company/list') }}">
<i class="fa fa-comments"></i>
<span>Comment Company</span>
</a>
</li>
            
            </ul>
            <li><a href="javascript:void(0)" class="sidebar-minify-btn" data-click="sidebar-minify"><i class="fa fa-angle-double-left"></i></a></li>
        </ul>
    </div>
</div>
<div class="sidebar-bg"></div>
