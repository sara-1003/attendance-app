@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css')}}">
@endsection

@section('content')
<div class="attendance-index">
    <h1 class="attendance-index__heading">勤怠一覧</h1>
    <div class="attendance-index__month">
        <a class="month__item" href="{{ route('attendance.index', ['month' => $prevMonth]) }}">
            <img src="{{ asset('images/yajirushi.png') }}" alt="前月">前月</a>
        <div class="month__current">
            <img src="{{ asset('images/data.png') }}" alt="calendar">
            {{ $displayMonth->format('Y/m') }}
        </div>
        <a class="month__item" href="{{ route('attendance.index', ['month' => $nextMonth]) }}">
            <img src="{{ asset('images/yajirushi.png') }}" alt="翌月">翌月</a>
    </div>
    <div class="attendance-table">
        <table class="attendance-table__inner">
            <tr class="attendance-table__row">
                <th class="attendance-table__header">日付</th>
                <th class="attendance-table__header">出勤</th>
                <th class="attendance-table__header">退勤</th>
                <th class="attendance-table__header">休憩</th>
                <th class="attendance-table__header">合計</th>
                <th class="attendance-table__header">詳細</th>
            </tr>
            @foreach($attendances as $attendance)
            @php
                $date = \Carbon\Carbon::parse($attendance->date);
            @endphp
            <tr class="attendance-table__row">
                <td class="attendance-table__item"> {{ $date->format('m/d') }}({{ $date->isoFormat('ddd') }})</td>
                <td class="attendance-table__item">{{ optional($attendance->clock_in)->format('H:i') }}</td>
                <td class="attendance-table__item">{{ optional($attendance->clock_out)->format('H:i') }}</td>
                <td class="attendance-table__item">{{ $attendance->total_break_time ?? '0:00' }}</td>
                <td class="attendance-table__item">{{ $attendance->work_time }}</td>
                <td class="attendance-table__item"><a href="/attendance/detail/{id}"></a>詳細</td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection