<?php

return [
    'templates' => [
        'lead_welcome' => [
            'label' => 'Lead welcome',
            'description' => 'Sent when a new lead is captured.',
            'placeholders' => [':name'],
            'default' => 'Hi :name, thanks for contacting CrowHub! We received your inquiry and will share your quotation shortly. Reply here if you need urgent help.',
        ],
        'quote_sent' => [
            'label' => 'Quote sent',
            'description' => 'Sent when a draft quote is emailed/sent to the lead.',
            'placeholders' => [':name', ':quote_no', ':valid_until'],
            'default' => 'Hi :name, your CrowHub quotation :quote_no is ready. Please review it before :valid_until and let us know if you need any tweaks.',
        ],
        'quote_expiring' => [
            'label' => 'Quote expiring soon',
            'description' => 'Reminder that a sent quote expires soon.',
            'placeholders' => [':quote_no', ':valid_until'],
            'default' => 'Friendly reminder that quotation :quote_no expires on :valid_until. Reply to confirm, extend, or request updates.',
        ],
        'quote_expired' => [
            'label' => 'Quote expired',
            'description' => 'Follow-up when a quote has passed the validity date.',
            'placeholders' => [':quote_no', ':valid_until'],
            'default' => 'Quotation :quote_no has expired, but we can refresh it anytime. Reply YES if you would like us to reopen the proposal.',
        ],
        'quote_accepted' => [
            'label' => 'Quote accepted thanks',
            'description' => 'Sent after the customer accepts a quote.',
            'placeholders' => [':quote_no'],
            'default' => 'Thank you for accepting quotation :quote_no! We will lock in the schedule and share the next steps shortly.',
        ],
        'quote_rejected' => [
            'label' => 'Quote rejected follow-up',
            'description' => 'Used to capture feedback when a quote is rejected.',
            'placeholders' => [':quote_no'],
            'default' => 'Thanks for the feedback on quotation :quote_no. We are happy to rework it—just reply with any blockers or a good time to chat.',
        ],
        'payment_advance' => [
            'label' => 'Advance payment receipt',
            'description' => 'Acknowledges an advance payment.',
            'placeholders' => [':amount'],
            'default' => 'Advance payment of Rs :amount received—thank you! We will lock your slot and keep you posted on prep updates.',
        ],
        'payment_progress' => [
            'label' => 'Progress payment receipt',
            'description' => 'Confirms a non-final payment.',
            'placeholders' => [':amount', ':pending'],
            'default' => 'Payment of Rs :amount received. Your remaining balance is Rs :pending. Let us know if you need a fresh statement.',
        ],
        'payment_balance_cleared' => [
            'label' => 'Balance cleared',
            'description' => 'Confirms that all invoices are settled.',
            'placeholders' => [],
            'default' => 'All CrowHub invoices for this project are settled. Thanks again for trusting our team!',
        ],
        'payment_reminder' => [
            'label' => 'Balance reminder',
            'description' => 'Sent during scheduled payment reminders until the bill is closed.',
            'placeholders' => [':pending'],
            'default' => 'Friendly reminder: Rs :pending remains outstanding on your CrowHub project. Reply if you need an invoice copy or payment options.',
        ],
        'project_scheduled' => [
            'label' => 'Project scheduled',
            'description' => 'Confirm upcoming work once the customer is onboarded.',
            'placeholders' => [':schedule_date'],
            'default' => 'You are on the CrowHub calendar for :schedule_date. We will send the prep checklist and arrival window soon.',
        ],
        'post_delivery_feedback' => [
            'label' => 'Post-delivery feedback',
            'description' => 'Send after completion to gather feedback or reviews.',
            'placeholders' => [],
            'default' => 'Hope you are loving the results! We would value a quick review or any feedback so we can keep improving.',
        ],
        'support_welcome' => [
            'label' => 'Maintenance welcome',
            'description' => 'Introduces maintenance/support contracts.',
            'placeholders' => [':start_date'],
            'default' => 'Your CrowHub maintenance plan is active from :start_date. Reply here or call us anytime you need support.',
        ],
    ],
    'reminders' => [
        'quote_expiring_days' => 2,
        'payment_reminder_cooldown_days' => 7,
    ],
];
