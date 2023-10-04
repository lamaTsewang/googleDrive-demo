<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; 
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;


class ParentIdBaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public ?File $parent = null;


    public function authorize(): bool           
    {
        $this->parent = File::query()->where('id', $this->input('parent_id'))->first();

        if ($this->parent && !$this->parent->isOwnedBy(Auth::id())) {
            return false;
        }
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parent_id' => [
                Rule::exists(File::class, 'id')
                    ->where(function (Builder $query) {
                        return $query
                            ->where('is_folder', '=', '1')
                            ->where('created_by', '=', Auth::id());
                    })
            
            ]
        ];
    }
}
