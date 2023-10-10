<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class FileActionRequest extends ParentIDBaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
  

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(),[
            'all' => 'nullable|boolean',
            'ids.*' => Rule::exists( 'files', 'id')->where(function($query){
                $query->where('created_by', Auth::id());
            })
        ]);
    }
}
