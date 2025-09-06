<?php

namespace MeShaon\RequestAnalytics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageViewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_range' => 'integer|min:1|max:365',
            'path' => 'string',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:10|max:100',
        ];
    }
}
