<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreFolderRequest extends ParentIdBasedRequest
{
  
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(),
            [
                'name' => [
                    'required',
                    Rule::unique(File::class, 'name')
                        ->where(function ($query) {
                            $query->where('created_at', Auth::id())
                                ->where('parent_id', $this->parent_id)
                                ->whereNull('deleted_at');
                        }),
                ],
            ] 
        );
    }

    public function messages()
    {
        return [
            'name.unique' => 'folder ":input" alredy exists'
        ];
    }
}
