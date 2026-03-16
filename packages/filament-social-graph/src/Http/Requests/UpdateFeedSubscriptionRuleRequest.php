<?php

namespace BeegoodIT\FilamentSocialGraph\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'subscribable_type' => ['required', 'string', Rule::in(config('filament-social-graph.subscribable_models', []))],
            'subscribable_id' => ['required', 'uuid'],
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
