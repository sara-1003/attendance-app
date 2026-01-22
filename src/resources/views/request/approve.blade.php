@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request/approve.css')}}">
@endsection


@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__heading">勤怠詳細</h1>
    <form class="detail-form" action="{{ route('request.approve', $request->id) }}" method="post">
        @csrf
        <div class="attendance-detail__card">
            <table class="detail-table">
                <tr class="detail-table__row">
                    <th class="detail-table__header">名前</th>
                    <td class="detail-table__item">{{ $attendance->user->name }}</td>
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
                    <td class="detail-table__item-">
                        <div class="time-box">
                                <span class="time-text">{{ \Carbon\Carbon::parse($request->new_clock_in)->format('H:i') }}</span>
                                <span class="time-text">〜</span>
                                <span class="time-text">{{ \Carbon\Carbon::parse($request->new_clock_out)->format('H:i') }}</span>
                        </div>
                    </td>
                </tr>
                @php
                    if($request && $request->attendanceRequestBreaks->count() > 0){
                        $breaks = $request->attendanceRequestBreaks;
                    }else{
                        $breaks = $attendance->attendanceBreaks;
                    }
                    $breaksForView = $breaks->values();
                    $breaksForView->push((object)['break_start' => null, 'break_end' => null]);
                @endphp
                <tr class="detail-table__row">
                    <th class="detail-table__header">休憩</th>
                    <td class="detail-table__item">
                        <div class="time-box">
                        @foreach($breaksForView as $break)
                            @if($break->break_start || $break->break_end)
                                <span class="time-text">{{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}</span>
                                <span>〜</span>
                                <span class="time-text">{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}</span>
                            @endif
                        @endforeach
                        </div>
                    </td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__header">備考</th>
                    <td class="detail-table__item">
                        <p class="detail-text">{{ $request->reason }}</p>
                    </td>
                </tr>
            </table>
        </div>
        <div class="approval__button">
            @if($approved)
                <button class="approved__button" type="button">承認済み</button>
            @else
                <button class="approval__button-submit" type="submit">承認</button>
            @endif
        </div>
    </form>
</div>
@endsection