@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/index.css')}}">
@endsection

@section('content')
<div class="attendance-index">
    <h1 class="attendance-index__heading">{{ $displayDate->format('Y年m月d日') }}の勤怠</h1>
    <div class="attendance-index__date">
        <a class="date__item" href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}">
            <img class="date__item--left" src="{{ asset('images/yajirushi.png') }}" alt="前日">前日</a>
        <div class="date__current">
            <img src="{{ asset('images/data.png') }}" alt="calendar">
            {{ $displayDate->format('Y/m/d') }}
        </div>
        <a class="date__item" href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">翌日
            <img class="date__item--right" src="{{ asset('images/yajirushi.png') }}" alt="翌日"></a>
    </div>
    <div class="attendance-table">
        <table class="attendance-table__inner">
            <tr class="attendance-table__row">
                <th class="attendance-table__header">名前</th>
                <th class="attendance-table__header">出勤</th>
                <th class="attendance-table__header">退勤</th>
                <th class="attendance-table__header">休憩</th>
                <th class="attendance-table__header">合計</th>
                <th class="attendance-table__header">詳細</th>
            </tr>
            @foreach($attendances as $attendance)
            <tr class="attendance-table__row">
                <td class="attendance-table__item">{{ $attendance->user->name }}</td>
                <td class="attendance-table__item">{{ optional($attendance->clock_in)->format('H:i') }}</td>
                <td class="attendance-table__item">{{ optional($attendance->clock_out)->format('H:i') }}</td>
                <td class="attendance-table__item">{{ $attendance->total_break_time ?? '0:00' }}</td>
                <td class="attendance-table__item">{{ $attendance->work_time }}</td>
                <td class="attendance-table__item"><a href="{{ route('attendance.show', $attendance->id) }}">詳細</a></td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection