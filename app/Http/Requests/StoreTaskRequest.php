<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Разрешаем пока всем
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date_format:Y-m-d\TH:i:s',
            'priority' => 'required|in:низкий,средний,высокий',
            'category' => 'required|string|max:255',
            'status' => 'nullable|in:выполнена,не выполнена',
        ];
    }
}
