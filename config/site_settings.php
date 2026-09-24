<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Site Settings Definition
    |--------------------------------------------------------------------------
    |
    | Each setting has:
    | - title: Human-readable label
    | - setting_type: Category (organization, menu, home, general)
    | - field_type: Dynamic field type (text, select, upload)
    | - options: Optional array of key => value for select dropdowns
    | - value: Default initial value
    |
    */
    'defaults' => [
        // Organization & Branding
        'site_title' => [
            'title' => 'Site Title',
            'setting_type' => 'organization',
            'field_type' => 'text',
            'options' => null,
            'value' => 'ABM Digital Lab',
        ],
        'site_subtitle' => [
            'title' => 'Site Subtitle',
            'setting_type' => 'organization',
            'field_type' => 'text',
            'options' => null,
            'value' => 'Software Architect',
        ],
        'logo_text' => [
            'title' => 'Logo Monogram',
            'setting_type' => 'organization',
            'field_type' => 'text',
            'options' => null,
            'value' => 'BM',
        ],
        'logo_image' => [
            'title' => 'Logo Image',
            'setting_type' => 'organization',
            'field_type' => 'upload',
            'options' => null,
            'value' => null,
        ],
        'contact_email' => [
            'title' => 'Contact Email',
            'setting_type' => 'organization',
            'field_type' => 'text',
            'options' => null,
            'value' => 'bhavesh@example.com',
        ],
        'contact_phone' => [
            'title' => 'Contact Phone',
            'setting_type' => 'organization',
            'field_type' => 'text',
            'options' => null,
            'value' => '+91 98765 43210',
        ],
        'location' => [
            'title' => 'Location / Timezone',
            'setting_type' => 'organization',
            'field_type' => 'text',
            'options' => null,
            'value' => 'Remote / Worldwide',
        ],
        'footer_text' => [
            'title' => 'Footer Bio Snippet',
            'setting_type' => 'organization',
            'field_type' => 'text',
            'options' => null,
            'value' => 'Senior Full-Stack Software Engineer',
        ],
        'copyright_text' => [
            'title' => 'Copyright Text',
            'setting_type' => 'organization',
            'field_type' => 'text',
            'options' => null,
            'value' => '© ' . date('Y') . ' ABM Digital Lab. All rights reserved.',
        ],

        // Developer & Social Networks
        'github_url' => [
            'title' => 'GitHub Profile URL',
            'setting_type' => 'social',
            'field_type' => 'text',
            'options' => null,
            'value' => 'https://github.com/bhavesh57',
        ],
        'github_username' => [
            'title' => 'GitHub Username',
            'setting_type' => 'social',
            'field_type' => 'text',
            'options' => null,
            'value' => 'bhavesh57',
        ],
        'linkedin_url' => [
            'title' => 'LinkedIn Profile URL',
            'setting_type' => 'social',
            'field_type' => 'text',
            'options' => null,
            'value' => 'https://linkedin.com/in/bhavesh-methaniya',
        ],
        'linkedin_username' => [
            'title' => 'LinkedIn Username',
            'setting_type' => 'social',
            'field_type' => 'text',
            'options' => null,
            'value' => 'bhavesh-methaniya',
        ],
        'stackoverflow_url' => [
            'title' => 'Stack Overflow Profile URL',
            'setting_type' => 'social',
            'field_type' => 'text',
            'options' => null,
            'value' => 'https://stackoverflow.com',
        ],
        'stackoverflow_username' => [
            'title' => 'Stack Overflow Username',
            'setting_type' => 'social',
            'field_type' => 'text',
            'options' => null,
            'value' => 'bhavesh-methaniya',
        ],
        'facebook_url' => [
            'title' => 'Facebook Profile URL',
            'setting_type' => 'social',
            'field_type' => 'text',
            'options' => null,
            'value' => 'https://facebook.com',
        ],
        'facebook_username' => [
            'title' => 'Facebook Username',
            'setting_type' => 'social',
            'field_type' => 'text',
            'options' => null,
            'value' => 'bhavesh.methaniya',
        ],
        'instagram_url' => [
            'title' => 'Instagram Profile URL',
            'setting_type' => 'social',
            'field_type' => 'text',
            'options' => null,
            'value' => 'https://instagram.com',
        ],
        'instagram_username' => [
            'title' => 'Instagram Username',
            'setting_type' => 'social',
            'field_type' => 'text',
            'options' => null,
            'value' => '@bhavesh_dev',
        ],
        'twitter_url' => [
            'title' => 'Twitter / X Profile URL',
            'setting_type' => 'social',
            'field_type' => 'text',
            'options' => null,
            'value' => 'https://twitter.com',
        ],
        'twitter_username' => [
            'title' => 'Twitter / X Username',
            'setting_type' => 'social',
            'field_type' => 'text',
            'options' => null,
            'value' => '@bhavesh',
        ],

        // Master Menu Enable / Disable
        'menu_home' => [
            'title' => 'Menu: Home Section',
            'setting_type' => 'menu',
            'field_type' => 'select',
            'options' => ['enabled' => 'Enabled', 'disabled' => 'Disabled'],
            'value' => 'enabled',
        ],
        'menu_about' => [
            'title' => 'Menu: About Section',
            'setting_type' => 'menu',
            'field_type' => 'select',
            'options' => ['enabled' => 'Enabled', 'disabled' => 'Disabled'],
            'value' => 'enabled',
        ],
        'menu_services' => [
            'title' => 'Menu: Services Section',
            'setting_type' => 'menu',
            'field_type' => 'select',
            'options' => ['enabled' => 'Enabled', 'disabled' => 'Disabled'],
            'value' => 'enabled',
        ],
        'menu_portfolio' => [
            'title' => 'Menu: Selected Work Section',
            'setting_type' => 'menu',
            'field_type' => 'select',
            'options' => ['enabled' => 'Enabled', 'disabled' => 'Disabled'],
            'value' => 'enabled',
        ],
        'menu_contact' => [
            'title' => 'Menu: Contact Section',
            'setting_type' => 'menu',
            'field_type' => 'select',
            'options' => ['enabled' => 'Enabled', 'disabled' => 'Disabled'],
            'value' => 'enabled',
        ],
        'menu_social' => [
            'title' => 'Menu: Social & Networks Section',
            'setting_type' => 'menu',
            'field_type' => 'select',
            'options' => ['enabled' => 'Enabled', 'disabled' => 'Disabled'],
            'value' => 'enabled',
        ],
        'menu_posts' => [
            'title' => 'Menu: Daily Posts & Updates Section',
            'setting_type' => 'menu',
            'field_type' => 'select',
            'options' => ['enabled' => 'Enabled', 'disabled' => 'Disabled'],
            'value' => 'enabled',
        ],
        'menu_newsletter' => [
            'title' => 'Menu: Newsletter Section',
            'setting_type' => 'menu',
            'field_type' => 'select',
            'options' => ['enabled' => 'Enabled', 'disabled' => 'Disabled'],
            'value' => 'enabled',
        ],

        // Front Home / Hero Setup
        'hero_badge' => [
            'title' => 'Hero Badge Text',
            'setting_type' => 'home',
            'field_type' => 'text',
            'options' => null,
            'value' => 'Senior Full-Stack Developer & Solutions Architect',
        ],
        'hero_title' => [
            'title' => 'Hero Main Headline',
            'setting_type' => 'home',
            'field_type' => 'text',
            'options' => null,
            'value' => 'Building Digital Products That Scale',
        ],
        'hero_highlight' => [
            'title' => 'Highlighted Headline Words',
            'setting_type' => 'home',
            'field_type' => 'text',
            'options' => null,
            'value' => 'Products That Scale',
        ],
        'hero_description' => [
            'title' => 'Hero Supporting Description',
            'setting_type' => 'home',
            'field_type' => 'text',
            'options' => null,
            'value' => 'Specializing in high-performance digital products, modern cloud architecture, resilient data systems, offline-first platforms, and mission-critical enterprise solutions.',
        ],
        'cta_primary_text' => [
            'title' => 'Primary CTA Button Label',
            'setting_type' => 'home',
            'field_type' => 'text',
            'options' => null,
            'value' => 'Hire Me',
        ],
        'cta_primary_link' => [
            'title' => 'Primary CTA Target Link',
            'setting_type' => 'home',
            'field_type' => 'text',
            'options' => null,
            'value' => '#contact',
        ],
        'cta_secondary_text' => [
            'title' => 'Secondary CTA Button Label',
            'setting_type' => 'home',
            'field_type' => 'text',
            'options' => null,
            'value' => 'View Projects',
        ],
        'cta_secondary_link' => [
            'title' => 'Secondary CTA Target Link',
            'setting_type' => 'home',
            'field_type' => 'text',
            'options' => null,
            'value' => '#portfolio',
        ],
        'availability_status' => [
            'title' => 'Availability Notice',
            'setting_type' => 'home',
            'field_type' => 'text',
            'options' => null,
            'value' => 'Available for contract & consulting',
        ],
        'resume_button_enabled' => [
            'title' => 'Download CV Button',
            'setting_type' => 'home',
            'field_type' => 'select',
            'options' => ['enabled' => 'Enabled', 'disabled' => 'Disabled'],
            'value' => 'enabled',
        ],
        'resume_button_text' => [
            'title' => 'Download CV Button Label',
            'setting_type' => 'home',
            'field_type' => 'text',
            'options' => null,
            'value' => 'Download CV',
        ],
    ],
];
