<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RestoreUploadedBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-backups');
    }

    public function rules(): array
    {
        return [
            // 512MB ceiling — generous for a shop this size (database +
            // uploaded photos), while still catching an obviously wrong
            // file picked by mistake.
            'backup' => ['required', 'file', 'mimes:zip', 'max:524288'],
        ];
    }
}
