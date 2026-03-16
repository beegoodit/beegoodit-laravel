<?php

namespace BeegoodIT\FilamentSocialGraph\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeedSubscriptionRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'feed_id' => ['required', 'uuid', 'exists:feeds,id'],
            'scope' => StoreFeedSubscriptionRuleRequest::scopeValidationRules(),
            'auto_subscribe' => ['boolean'],
            'unsubscribable' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'scope.in' => __('filament-social-graph::feed_subscription_rule.scope_invalid'),
        ];
    }
}
