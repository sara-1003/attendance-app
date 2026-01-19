@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff.css')}}">
@endsection

@section('content')
<div class="staff-index">
    <h1 class="staff-index__heading">{{ $staff->name }}さんの勤怠</h1>
    <div class="staff-index__month">
        <a class="month__item" href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $prevMonth]) }}">
            <img class="month__item--left" src="{{ asset('images/yajirushi.png') }}" alt="前月">前月</a>
        <div class="month__current">
            <img src="{{ asset('images/data.png') }}" alt="calendar">
            {{ $displayMonth->format('Y/m') }}
        </div>
        <a class="month__item" href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $nextMonth]) }}">翌月
            <img class="month__item--right" src="{{ asset('images/yajirushi.png') }}" alt="翌月"></a>
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
            <tr class="attendance-table__row">
                <td class="attendance-table__item">{{ \Carbon\Carbon::parse($attendance->date)->format('m/d') }}
                ({{ \Carbon\Carbon::parse($attendance->date)->isoFormat('ddd') }})</td>
                <td class="attendance-table__item">{{ optional($attendance->clock_in)->format('H:i') }}</td>
                <td class="attendance-table__item">{{ optional($attendance->clock_out)->format('H:i') }}</td>
                <td class="attendance-table__item">{{ $attendance->total_break_time ?? '0:00' }}</td>
                <td class="attendance-table__item">{{ $attendance->work_time }}</td>
                <td class="attendance-table__item"><a href="{{ route('admin.attendance.show', $attendance->id) }}">詳細</a></td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
<div class="output__button">
    <button class="output__button-submit" type="submit">CSV出力</button>
</div>
@endsection