@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/show.css')}}">
@endsection


@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__heading">勤怠詳細</h1>
    <form class="detail-form" action="{{ route('admin.attendance.update', $attendance->id) }}" method="post">
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
                        <div class="time-wrapper">
                            <div class="time-row">
                            @if(!$approved)
                            <div class="time-box">
                                <input type="text" name="clock_in"value="{{ old('clock_in',$request
                                ? \Carbon\Carbon::parse($request->new_clock_in)->format('H:i')
                                : optional($attendance->clock_in)->format('H:i')) }}">
                            </div>
                            @else
                            <span class="time-text">
                                {{ \Carbon\Carbon::parse(
                                    $request ? $request->new_clock_in : $attendance->clock_in
                                )->format('H:i') }}
                            </span>
                            @endif
                                <span>〜</span>
                            @if(!$approved)
                            <div class="time-box">
                                <input type="text" name="clock_out"value="{{ old('clock_out',$request
                                ? \Carbon\Carbon::parse($request->new_clock_out)->format('H:i')
                                : optional($attendance->clock_out)->format('H:i')) }}">
                            </div>
                            @else
                            <span class="time-text">
                                {{ \Carbon\Carbon::parse(
                                    $request ? $request->new_clock_out : $attendance->clock_out
                                )->format('H:i') }}
                            </span>
                            @endif
                            </div>
                            <div class="error">
                                @if ($errors->has('clock_in') || $errors->has('clock_out'))
                                <p class="form__error">
                                    {{ $errors->first('clock_in') ?: $errors->first('clock_out') }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @php
                    if($request && $request->attendanceRequestBreaks->count() > 0){
                        $breaks = $request->attendanceRequestBreaks;
                    }else{
                        $breaks = $attendance->attendanceBreaks;
                    }
                    $realBreakCount = $breaks->filter(function($b){
                        return !empty($b->break_start) || !empty($b->break_end);
                    })->count();

                    $breaksForView = $breaks->values();
                    $breaksForView->push((object)['break_start' => null, 'break_end' => null]);
                @endphp

                @foreach($breaksForView as $i => $break)
                    @if($request && empty($break->break_start) && empty($break->break_end))
                    @continue
                    @endif
                <tr class="detail-table__row">
                    <th class="detail-table__header">休憩{{ $i === 0 ? '' : $i + 1 }}</th>
                    <td class="detail-table__item">
                        <div class="time-row">
                        @if(!$approved)
                        <div class="time-box">
                            <input type="text"
                            name="breaks[{{ $i }}][break_start]"value="{{ old("breaks.$i.break_start",
                            $break->break_start
                            ? \Carbon\Carbon::parse($break->break_start)->format('H:i')
                            : ''
                        ) }}">
                    </div>
                    @else
                    <span class="time-text">
                        {{ $break->break_start
                            ? \Carbon\Carbon::parse($break->break_start)->format('H:i')
                        : '-' }}
                    </span>
                    @endif
                    <span>〜</span>
                    @if(!$approved)
                    <div class="time-box">
                        <input type="text" name="breaks[{{ $i }}][break_end]"value="{{ old("breaks.$i.break_end",
                        $break->break_end
                            ? \Carbon\Carbon::parse($break->break_end)->format('H:i')
                            : ''
                        ) }}">
                    </div>
                    @else
                    <span class="time-text">
                        {{ $break->break_end
                            ? \Carbon\Carbon::parse($break->break_end)->format('H:i')
                        : '-' }}
                    </span>
                    @endif
                        </div>
                        <div class="error">
                            @if ($errors->has("breaks.$i.break_start") || $errors->has("breaks.$i.break_end"))
                            <p class="form__error">{{ $errors->first("breaks.$i.break_start") ?: $errors->first("breaks.$i.break_end") }}</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr class="detail-table__row">
                    <th class="detail-table__header">備考</th>
                    <td class="detail-table__item--reason">
                    @if(!$approved)
                    <textarea class="detail-table__textarea" name="reason">{{ old('reason', $request->reason ?? '') }}</textarea>
                    @else
                    <p class="detail-text">{{ $request->reason ?? '' }}</p>
                    @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="approval__button">
            @if($approved)
                <button class="approved__button" type="button">承認済み</button>
            @else
                <button class="detail__button-submit">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection