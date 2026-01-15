<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in'=>['required','date_format:H:i','before:clock_out'],
            'clock_out'=>['required','date_format:H:i','after:clock_in'],
            'breaks.*.break_start'=>['nullable','date_format:H:i','after_or_equal:clock_in','before_or_equal:clock_out'],
            'breaks.*.break_end'=>['nullable','date_format:H:i','after_or_equal:breaks.*.break_start','before_or_equal:clock_out'],
            'reason'=>['required'],
        ];
    }

    public function messages()
    {
        return[
            'clock_in.before'=>'出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.after'=>'出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_start.after_or_equal'=>'休憩時間が不適切な値です',
            'breaks.*.break_start.before_or_equal'=>'休憩時間が不適切な値です',
            'breaks.*.break_end.after_or_equal'   => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_end.before_or_equal'  => '休憩時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
        ];
    }
}
