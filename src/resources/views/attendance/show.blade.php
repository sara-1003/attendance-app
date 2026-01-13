@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css')}}">
@endsection


@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__heading">勤怠詳細</h1>
    <form class="detail-form" action="/attendance/{{ $attendance->id }}/request" method="post">
        @csrf
        <div class="attendance-detail__card">
            <table class="detail-table">
                <tr class="detail-table__row">
                    <th class="detail-table__header">名前</th>
                    <td class="detail-table__item--name">{{ $attendance->user->name }}</td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">日付</th>
                    <td class="detail-table__item">
                    @php
                        $date = \Carbon\Carbon::parse($attendance->date);
                    @endphp
                        <div class="date-box">
                            <span>{{ $date->format('Y年') }}</span>
                            <span>{{ $date->format('n月j日') }}</span>
                        </div>
                    </td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">出勤・退勤</th>
                    <td class="detail-table__item">
                        <div class="time-box">
                            <input type="time" name="clock_in" value="{{ optional($attendance->clock_in)->format('H:i') }}">
                        </div>
                        <span>〜</span>
                        <div class="time-box">
                            <input type="time" name="clock_out"
                            value="{{ optional($attendance->clock_out)->format('H:i') }}">
                        </div>
                    </td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">休憩</th>
                    <td class="detail-table__item">
                        @if($attendance->attendanceBreaks->count() > 0)
                        <div class="time-box">
                            <input type="time" name="breaks[0][break_start]" value="{{ \Carbon\Carbon::parse($attendance->attendanceBreaks[0]->break_start)->format('H:i') }}">
                        </div>
                        <span>〜</span>
                        <div class="time-box">
                            <input type="time" name="breaks[0][break_end]" value="{{ \Carbon\Carbon::parse($attendance->attendanceBreaks[0]->break_end)->format('H:i') }}">
                        </div>
                        @endif
                    </td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">休憩2</th>
                    <td class="detail-table__item">
                        @if($attendance->attendanceBreaks->count() > 1)
                        <div class="time-box">
                            <input type="time" name="breaks[1][break_start]" value="{{ \Carbon\Carbon::parse($attendance->attendanceBreaks[1]->break_start)->format('H:i') }}">
                        </div>
                        <span>〜</span>
                        <div class="time-box">
                            <input type="time" name="breaks[1][break_end]" value="{{ \Carbon\Carbon::parse($attendance->attendanceBreaks[1]->break_end)->format('H:i') }}">
                        </div>
                        @endif
                    </td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">備考</th>
                    <td class="detail-table__item">
                        <textarea class="detail-table__textarea" name="reason" id="reason"></textarea>
                    </td>
                </tr>
            </table>
        </div>
        <div class="detail__button">
                <button class="detail__button-submit">修正</button>
        </div>
    </form>
</div>
@endsection