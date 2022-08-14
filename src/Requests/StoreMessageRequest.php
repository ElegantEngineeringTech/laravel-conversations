<?php

namespace Finller\Conversation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMessageRequest extends FormRequest
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

    protected function prepareForValidation()
    {
        //
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'content' => ['nullable', 'string'],
            'media' => ['nullable', 'sometimes', 'array'],
            'user_id' => ['required', 'numeric', 'exists:users,id'],
            'conversation_id' => ['required', 'numeric', 'exists:conversations,id'],
        ];
    }
}
