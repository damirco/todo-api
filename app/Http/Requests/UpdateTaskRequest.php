<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Разрешаем пока всем, как в StoreTaskRequest
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|required|date_format:Y-m-d\TH:i:s',
            'priority' => 'sometimes|required|in:низкий,средний,высокий',
            'category' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:выполнена,не выполнена',
        ];
    }
}
