<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class StoreFileRequest extends ParentIdBasedRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'files.*' => [
                'required',
                'file',
                function($attribute, $value, $fail) {
                    $file = File::query()
                        ->where('name', $value->getClientOriginalName())
                        ->where('created_by', Auth::id())
                        ->where('parent_id', $this->parent_id)
                        ->whereNull('deleted_at')
                        ->exists();

                    if($file) {
                        $fail('File "' . $value->getClientOriginalName() . '" already exists');
                    }

                }
            ]
        ]);
    }
}
