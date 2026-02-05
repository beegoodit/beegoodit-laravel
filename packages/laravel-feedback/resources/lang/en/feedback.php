<?php

return [
    'navigation' => [
        'label' => 'Feedback',
    ],

    'model' => [
        'label' => 'Item',
        'plural' => 'Items',
    ],

    'menu_item' => 'Feedback',

    'button' => [
        'open' => 'Feedback',
    ],

    'modal' => [
        'title' => 'Submit Feedback',
        'description' => 'We value your input! Please share your thoughts, suggestions, or report any issues you\'ve encountered.',
    ],

    'form' => [
        'subject' => 'Subject',
        'subject_placeholder' => 'Brief summary of your feedback',
        'description' => 'Description',
        'description_placeholder' => 'Please provide details about your feedback...',
        'submit' => 'Submit Feedback',
        'cancel' => 'Cancel',
    ],

    'table' => [
        'subject' => 'Subject',
        'description' => 'Description',
        'creator' => 'Creator',
        'created_at' => 'Created At',
        'ip_address' => 'IP Address',
        'user_agent' => 'User Agent',
    ],

    'filters' => [
        'created_from' => 'Created From',
        'created_until' => 'Created Until',
        'ip_address' => 'IP Address',
    ],

    'infolist' => [
        'metadata' => 'Metadata',
        'user_agent' => 'User Agent',
        'ip_address' => 'IP Address',
    ],

    'submit' => [
        'success' => 'Thank you for your feedback!',
        'error' => 'Failed to submit feedback',
        'error_body' => 'An error occurred while submitting your feedback. Please try again later.',
    ],
];
