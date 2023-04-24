<?php

namespace Thoss\GapSort\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SortRequest extends FormRequest
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
        switch ($this->method()) {
            case 'POST':
                return [
                    'main' => [
                        'required',
                        'different:previous',
                        'different:next',
                    ],
                    'previous' => [
                        'nullable',
                        'different:main',
                        'different:next',
                    ],
                    'next' => [
                        'nullable',
                        'different:main',
                        'different:previous',
                    ],
                ];
        }
    }
}
