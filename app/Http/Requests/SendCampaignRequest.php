<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendCampaignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        \Log::info($this->request);

        return [
            // 'message' => ['required']
            'body' => ['required', 'string', 'max:255'],
            'sendToType' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:255']
        ];
    }
}
