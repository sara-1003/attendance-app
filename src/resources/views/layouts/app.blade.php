<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo" href="{{ auth()->check() ? '/attendance' : '/login' }}">
                <img src="{{ asset('images/header.png') }}" alt="COACHTECH">
            </a>
            @auth
                <nav class="header-nav">
                    <ul class="header-nav__list">
                        <!-- 管理者ログインの場合 -->
                        @if(auth()->user()->role === 'admin')
                            <li class="header-nav__item"><a href="/admin/attendance/list">勤怠一覧</a></li>
                            <li class="header-nav__item"><a href="/admin/staff/list">スタッフ一覧</a></li>
                            <li class="header-nav__item"><a href="/stamp_correction_request/list">申請一覧</a></li>
                            <li class="header-nav__item">
                                <form action="{{ route('logout') }}" method="post">
                                @csrf
                                    <button type="submit">ログアウト</button>
                                </form>
                            </li>
                        @else
                        <!-- 一般ログインの場合 -->
                        @php
                            $isAttendancePage = request()->is('attendance');
                        @endphp

                        @if($isAttendancePage && $status === '退勤済')
                            <!-- 退勤後のナビ -->
                            <li class="header-nav__item"><a href="/attendance/list">今月の出勤一覧</a></li>
                            <li class="header-nav__item"><a href="/stamp_correction_request/list">申請一覧</a></li>
                            <li class="header-nav__item">
                                <form action="{{ route('logout') }}" method="post">
                                    @csrf
                                    <button type="submit">ログアウト</button>
                                </form>
                            </li>
                        @else
                            <!-- 勤務中・勤務外のナビ -->
                            <li class="header-nav__item"><a href="/attendance">勤怠</a></li>
                            <li class="header-nav__item"><a href="/attendance/list">勤怠一覧</a></li>
                            <li class="header-nav__item"><a href="/stamp_correction_request/list">申請</a></li>
                            <li class="header-nav__item">
                                <form action="{{ route('logout') }}" method="post">
                                    @csrf
                                    <button type="submit">ログアウト</button>
                                </form>
                            </li>
                        @endif
                        @endif
                    </ul>
                </nav>
            @endauth
        </div>
    </header>

    <main>
        @yield('content')
    </main>
    @yield('js')
</body>
</html>