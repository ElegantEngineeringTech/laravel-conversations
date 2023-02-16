<?php

namespace Finller\Conversation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string'],
            'media' => ['nullable', 'sometimes', 'array'],
            'user_id' => ['required', 'numeric', 'exists:users,id'],
            'conversation_id' => ['required', 'numeric', 'exists:conversations,id'],
        ];
    }
}
