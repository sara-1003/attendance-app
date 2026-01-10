@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/store.css')}}">
@endsection

@section('content')
<div class="attendance__content">
    <!-- 勤怠状態表示 -->
    <div class="attendance__now">{{ $status }}</div>
    <div class="attendance__date">
        {{ now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)') }}
    </div>
    <div class="attendance__time" id="clock"></div>
    <div class="attendance__buttons">
        @if($status === '勤務外')
        <form action="{{ route('attendance.start') }}" method="post">
            @csrf
            <button class="attendance__button" type="submit">出勤</button>
        </form>
        @elseif($status === '出勤中')
        <div class="attendance__button-group">
            <form action="{{ route('attendance.end') }}" method="post">
            @csrf
                <button class="attendance__button" type="submit">退勤</button>
            </form>
            <form action="{{ route('attendance.break') }}" method="post">
                @csrf
                <button class="attendance__button--white" type="submit">休憩入</button>
            </form>
        </div>
        @elseif($status === '休憩中')
        <form action="{{ route('attendance.resume') }}" method="post">
            @csrf
            <button class="attendance__button--white" type="submit">休憩戻</button>
        </form>
        @elseif($status === '退勤済')
        <p class="attendance__message">お疲れ様でした。</p>
        @endif
    </div>
</div>
@endsection

@section('js')
<script>
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('clock').textContent = `${h}:${m}`;
}
updateClock();
setInterval(updateClock, 1000);
</script>
@endsection
