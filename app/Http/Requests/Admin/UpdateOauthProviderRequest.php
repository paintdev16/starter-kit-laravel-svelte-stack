<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOauthProviderRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'max:100', Rule::unique('oauth_providers', 'provider')->ignore($this->route('oauthProvider'))],
            'client_id' => 'required|string',
            'client_secret' => 'nullable|string',
            'redirect_uri' => 'required|url',
            'enabled' => 'boolean',
            'show_on_login' => 'boolean',
            'sort' => 'integer|min:0',
        ];
    }
}
