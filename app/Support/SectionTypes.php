<?php

namespace App\Support;

/**
 * Every kind of section the page builder can place on a page.
 *
 * A field is [type, label, default] plus optional keys:
 *  - tab:     admin tab it is shown on (Content by default)
 *  - options: value => label, for select / choice / checkboxes
 *  - show:    [otherField => [allowed values]], hides the field unless all match
 *  - help, min, max, step, fields (for repeaters), max_items
 *
 * Dynamic option lists (journeys, experiences, countries) use the types
 * package / category / country and are filled in when the form is built.
 */
class SectionTypes
{
    public const ICONS = ['plane', 'document', 'shield', 'phone', 'users', 'bed', 'star', 'check', 'heart', 'map-pin', 'calendar', 'clock', 'globe', 'sparkle', 'landmark', 'mountain', 'compass', 'wave', 'building', 'utensils', 'music', 'cpu', 'gem', 'bag', 'leaf', 'sun', 'moon', 'tree', 'chat', 'mail', 'ticket', 'tag'];

    private const MEDIA_LAYOUTS = ['background', 'slider', 'cards', 'banner'];

    private const BACKGROUNDS = ['white' => 'White', 'light' => 'Light grey', 'tint' => 'Brand tint', 'dark' => 'Brand dark'];

    /** @return array<string, array{label: string, description: string, icon: string, pages?: list<string>, fields: array<string, array<string, mixed>>}> */
    public static function all(): array
    {
        return [
            'hero' => [
                'label' => 'Hero',
                'description' => 'Big opening banner: photo, video or slider, with headline, buttons and search.',
                'icon' => 'image',
                'fields' => self::heroFields(),
            ],
            'experiences' => [
                'label' => 'Experiences',
                'description' => 'Your experience categories as photo tiles.',
                'icon' => 'tag',
                'fields' => [
                    'eyebrow' => self::f('text', 'Small line above heading', ''),
                    'title' => self::f('text', 'Heading', 'Choose your kind of journey'),
                    'intro' => self::f('textarea', 'Intro', 'Every journey is private and can be tailored around your dates, pace and family.'),
                    'link_label' => self::f('text', 'Link text', 'See all journeys', ['help' => 'Leave empty to hide the link.']),
                    'style' => self::f('choice', 'Tile style', 'mosaic', ['tab' => 'Layout', 'options' => ['mosaic' => 'Mosaic, first tile large', 'grid' => 'Even grid', 'pills' => 'Compact chips']]),
                    'limit' => self::f('number', 'How many', 9, ['tab' => 'Layout', 'min' => 1, 'max' => 24]),
                    'background' => self::f('select', 'Background', 'white', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'vibes' => [
                'label' => 'Travel vibes',
                'description' => 'Romantic, Heritage, Religious… links to journeys by mood.',
                'icon' => 'sparkle',
                'fields' => [
                    'eyebrow' => self::f('text', 'Small line above heading', ''),
                    'title' => self::f('text', 'Heading', 'Find your travel vibe'),
                    'intro' => self::f('textarea', 'Intro', 'Romantic escapes, pilgrimages, heritage trails or futuristic cities: start with how you want to feel.'),
                    'background' => self::f('select', 'Background', 'white', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'journeys' => [
                'label' => 'Journeys',
                'description' => 'A row of journey cards: featured, by experience or by country.',
                'icon' => 'map-pin',
                'fields' => [
                    'eyebrow' => self::f('text', 'Small line above heading', ''),
                    'title' => self::f('text', 'Heading', 'Most-loved journeys'),
                    'intro' => self::f('textarea', 'Intro', 'Prices include private transport, licensed guides, hand-picked stays and every entrance fee.'),
                    'link_label' => self::f('text', 'Link text', 'Browse all', ['help' => 'Leave empty to hide the link.']),
                    'source' => self::f('select', 'Show', 'featured', ['tab' => 'Layout', 'options' => ['featured' => 'Featured journeys', 'latest' => 'Newest journeys', 'cheapest' => 'Lowest price first', 'category' => 'One experience', 'country' => 'One country']]),
                    'category' => self::f('category', 'Experience', '', ['tab' => 'Layout', 'show' => ['source' => ['category']]]),
                    'country' => self::f('country', 'Country', '', ['tab' => 'Layout', 'show' => ['source' => ['country']]]),
                    'limit' => self::f('number', 'How many', 4, ['tab' => 'Layout', 'min' => 1, 'max' => 24]),
                    'columns' => self::f('select', 'Cards per row (desktop)', '4', ['tab' => 'Layout', 'options' => ['2' => '2', '3' => '3', '4' => '4']]),
                    'background' => self::f('select', 'Background', 'white', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'spotlight' => [
                'label' => 'Journey spotlight',
                'description' => 'One journey told big: photo, highlights, price and button.',
                'icon' => 'star',
                'fields' => [
                    'package' => self::f('package', 'Journey', '', ['help' => 'Leave empty to use the first featured journey.']),
                    'eyebrow' => self::f('text', 'Small line above heading', 'Featured journey'),
                    'button_label' => self::f('text', 'Button text', 'See the full journey'),
                    'image_side' => self::f('choice', 'Photo side', 'left', ['tab' => 'Layout', 'options' => ['left' => 'Left', 'right' => 'Right']]),
                    'background' => self::f('select', 'Background', 'dark', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'destinations' => [
                'label' => 'Destinations',
                'description' => 'Featured destinations as tall photo tiles.',
                'icon' => 'mountain',
                'fields' => [
                    'eyebrow' => self::f('text', 'Small line above heading', ''),
                    'title' => self::f('text', 'Heading', 'Destinations we love'),
                    'intro' => self::f('textarea', 'Intro', ''),
                    'link_label' => self::f('text', 'Link text', 'All destinations', ['help' => 'Leave empty to hide the link.']),
                    'country' => self::f('country', 'Only this country', '', ['tab' => 'Layout']),
                    'featured_only' => self::f('toggle', 'Featured destinations only', true, ['tab' => 'Layout']),
                    'limit' => self::f('number', 'How many', 6, ['tab' => 'Layout', 'min' => 1, 'max' => 24]),
                    'shape' => self::f('choice', 'Photo shape', 'arch', ['tab' => 'Layout', 'options' => ['arch' => 'Arch', 'rounded' => 'Rounded', 'circle' => 'Circle', 'leaf' => 'Leaf']]),
                    'background' => self::f('select', 'Background', 'white', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'story' => [
                'label' => 'Image & text',
                'description' => 'A photo beside a heading, paragraph, numbers and a button.',
                'icon' => 'document',
                'fields' => [
                    'image' => self::f('image', 'Photo', null),
                    'eyebrow' => self::f('text', 'Small line above heading', ''),
                    'title' => self::f('text', 'Heading', 'Hosted by people who call this country home'),
                    'body' => self::f('textarea', 'Text', '', ['help' => 'Leave a blank line between paragraphs.']),
                    'stats' => self::f('repeater', 'Numbers', [], ['fields' => ['value' => self::f('text', 'Number', ''), 'label' => self::f('text', 'Label', '')], 'max_items' => 4]),
                    'button_label' => self::f('text', 'Button text', ''),
                    'button_url' => self::f('text', 'Button link', ''),
                    'image_side' => self::f('choice', 'Photo side', 'left', ['tab' => 'Layout', 'options' => ['left' => 'Left', 'right' => 'Right']]),
                    'image_mask' => self::f('choice', 'Photo shape', 'rounded', ['tab' => 'Layout', 'options' => self::masks()]),
                    'background' => self::f('select', 'Background', 'light', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'text' => [
                'label' => 'Text',
                'description' => 'Heading and paragraphs, with optional numbers beside them.',
                'icon' => 'pencil',
                'fields' => [
                    'eyebrow' => self::f('text', 'Small line above heading', ''),
                    'title' => self::f('text', 'Heading', ''),
                    'body' => self::f('textarea', 'Text', '', ['help' => 'Leave a blank line between paragraphs.', 'rows' => 8]),
                    'stats' => self::f('repeater', 'Numbers beside the text', [], ['fields' => ['value' => self::f('text', 'Number', ''), 'label' => self::f('text', 'Label', '')], 'max_items' => 4]),
                    'align' => self::f('choice', 'Alignment', 'left', ['tab' => 'Layout', 'options' => ['left' => 'Left', 'center' => 'Centre']]),
                    'background' => self::f('select', 'Background', 'white', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'features' => [
                'label' => 'Icon grid',
                'description' => 'What’s included, why book with us: icon, title and text tiles.',
                'icon' => 'grid',
                'fields' => [
                    'eyebrow' => self::f('text', 'Small line above heading', ''),
                    'title' => self::f('text', 'Heading', 'What every journey includes'),
                    'intro' => self::f('textarea', 'Intro', ''),
                    'items' => self::f('repeater', 'Tiles', [], ['fields' => ['icon' => self::f('icon', 'Icon', 'check'), 'title' => self::f('text', 'Title', ''), 'text' => self::f('textarea', 'Text', '')], 'max_items' => 12]),
                    'columns' => self::f('select', 'Tiles per row (desktop)', '3', ['tab' => 'Layout', 'options' => ['2' => '2', '3' => '3', '4' => '4']]),
                    'background' => self::f('select', 'Background', 'dark', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'steps' => [
                'label' => 'Steps',
                'description' => 'A numbered “how it works” sequence.',
                'icon' => 'check',
                'fields' => [
                    'eyebrow' => self::f('text', 'Small line above heading', ''),
                    'title' => self::f('text', 'Heading', 'How it works'),
                    'intro' => self::f('textarea', 'Intro', ''),
                    'items' => self::f('repeater', 'Steps', [], ['fields' => ['title' => self::f('text', 'Title', ''), 'text' => self::f('textarea', 'Text', '')], 'max_items' => 8]),
                    'background' => self::f('select', 'Background', 'white', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'testimonials' => [
                'label' => 'Reviews',
                'description' => 'Guest reviews from Admin → Reviews.',
                'icon' => 'quote',
                'fields' => [
                    'eyebrow' => self::f('text', 'Small line above heading', ''),
                    'title' => self::f('text', 'Heading', 'What our guests say'),
                    'rating_text' => self::f('text', 'Rating line', '', ['help' => 'e.g. “4.9/5 average from 1,200+ guests”. Leave empty to hide.']),
                    'limit' => self::f('number', 'How many', 3, ['tab' => 'Layout', 'min' => 1, 'max' => 12]),
                    'background' => self::f('select', 'Background', 'light', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'faq' => [
                'label' => 'FAQs',
                'description' => 'Questions from Admin → FAQs as an accordion.',
                'icon' => 'help',
                'fields' => [
                    'eyebrow' => self::f('text', 'Small line above heading', ''),
                    'title' => self::f('text', 'Heading', 'Questions we hear most'),
                    'intro' => self::f('textarea', 'Intro', 'Can’t find your answer? Our team is happy to help.'),
                    'button_label' => self::f('text', 'Button text', 'Ask us anything'),
                    'button_url' => self::f('text', 'Button link', '/contact'),
                    'layout' => self::f('choice', 'Layout', 'side', ['tab' => 'Layout', 'options' => ['side' => 'Heading beside questions', 'center' => 'Centred']]),
                    'background' => self::f('select', 'Background', 'white', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'gallery' => [
                'label' => 'Photo gallery',
                'description' => 'A grid of photos with optional captions.',
                'icon' => 'image',
                'fields' => [
                    'eyebrow' => self::f('text', 'Small line above heading', ''),
                    'title' => self::f('text', 'Heading', 'Moments from the road'),
                    'images' => self::f('repeater', 'Photos', [], ['fields' => ['image' => self::f('image', 'Photo', null), 'caption' => self::f('text', 'Caption', '')], 'max_items' => 24]),
                    'style' => self::f('choice', 'Style', 'grid', ['tab' => 'Layout', 'options' => ['grid' => 'Even grid', 'mosaic' => 'Mosaic', 'strip' => 'Scrolling strip']]),
                    'background' => self::f('select', 'Background', 'white', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'video' => [
                'label' => 'Video',
                'description' => 'A YouTube, Vimeo or uploaded video with a heading.',
                'icon' => 'eye',
                'fields' => [
                    'eyebrow' => self::f('text', 'Small line above heading', ''),
                    'title' => self::f('text', 'Heading', ''),
                    'intro' => self::f('textarea', 'Intro', ''),
                    'video_url' => self::f('text', 'YouTube or Vimeo link', '', ['help' => 'Or upload a file below.']),
                    'video' => self::f('video', 'Video file (MP4)', null),
                    'image' => self::f('image', 'Cover photo', null),
                    'background' => self::f('select', 'Background', 'white', ['tab' => 'Layout', 'options' => self::BACKGROUNDS]),
                ],
            ],
            'cta' => [
                'label' => 'Call to action',
                'description' => 'A bold banner asking guests to get in touch.',
                'icon' => 'chat',
                'fields' => [
                    'title' => self::f('text', 'Heading', 'Ready to plan your journey?'),
                    'text' => self::f('textarea', 'Text', 'Tell us when you would like to travel and who is coming. We will send a tailored itinerary and price within one working day, with no obligation.'),
                    'primary_label' => self::f('text', 'Main button text', 'Start planning'),
                    'primary_url' => self::f('text', 'Main button link', '/contact'),
                    'secondary_label' => self::f('text', 'Second button text', 'Browse journeys'),
                    'secondary_url' => self::f('text', 'Second button link', '/journeys'),
                    'image' => self::f('image', 'Background photo', null),
                    'style' => self::f('choice', 'Style', 'image', ['tab' => 'Layout', 'options' => ['image' => 'Photo background', 'brand' => 'Brand colour', 'light' => 'Light']]),
                ],
            ],
            'contact_form' => [
                'label' => 'Contact form',
                'description' => 'Enquiry form with your phone numbers, email and address.',
                'icon' => 'mail',
                'fields' => [
                    'title' => self::f('text', 'Heading', 'Let’s plan your journey'),
                    'intro' => self::f('textarea', 'Intro', 'Tell us when you would like to travel, who is coming and what you hope to see. A travel designer will reply within one working day.'),
                    'button_label' => self::f('text', 'Button text', 'Send message'),
                    'privacy_note' => self::f('text', 'Note under the form', 'We never share your details.'),
                    'show_details' => self::f('toggle', 'Show phone, email and address', true),
                    'heading_level' => self::f('select', 'Heading size', 'h1', ['tab' => 'Layout', 'options' => ['h1' => 'Page title', 'h2' => 'Section heading']]),
                ],
            ],
            'destinations_list' => [
                'label' => 'All destinations',
                'description' => 'Every destination, grouped by country.',
                'icon' => 'globe',
                'fields' => [
                    'shape' => self::f('choice', 'Photo shape', 'arch', ['tab' => 'Layout', 'options' => ['arch' => 'Arch', 'rounded' => 'Rounded', 'circle' => 'Circle', 'leaf' => 'Leaf']]),
                ],
            ],
            'stays_list' => [
                'label' => 'All stays',
                'description' => 'Every hotel, grouped by type.',
                'icon' => 'bed',
                'fields' => [
                    'title' => self::f('text', 'Heading (optional)', ''),
                ],
            ],
            'journeys_list' => [
                'label' => 'Journey search & results',
                'description' => 'The filter bar and every matching journey.',
                'icon' => 'search',
                'pages' => ['journeys'],
                'fields' => [
                    'show_vibes' => self::f('toggle', 'Show vibe chips', true),
                    'empty_title' => self::f('text', 'Heading when nothing matches', 'No journeys match those filters'),
                    'empty_text' => self::f('textarea', 'Text when nothing matches', 'Every trip we run is tailor-made, so tell us what you are looking for and we will design it.'),
                    'empty_button' => self::f('text', 'Button when nothing matches', 'Request a custom journey'),
                ],
            ],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private static function heroFields(): array
    {
        $media = ['layout' => self::MEDIA_LAYOUTS];

        return [
            // Layout
            'layout' => self::f('layout', 'Layout', 'background', ['tab' => 'Layout', 'options' => [
                'background' => 'Photo or video behind the text',
                'split' => 'Text beside a photo',
                'slider' => 'Slideshow',
                'mosaic' => 'Photo collage',
                'cards' => 'Photo with destination cards',
                'banner' => 'Compact page banner',
            ]]),
            'image_side' => self::f('choice', 'Photo side', 'right', ['tab' => 'Layout', 'options' => ['left' => 'Left', 'right' => 'Right'], 'show' => ['layout' => ['split', 'mosaic']]]),
            'width' => self::f('choice', 'Width', 'full', ['tab' => 'Layout', 'options' => ['full' => 'Full width', 'contained' => 'Boxed'], 'show' => $media]),
            'height' => self::f('choice', 'Height', 'large', ['tab' => 'Layout', 'options' => ['auto' => 'Fit content', 'medium' => 'Medium', 'large' => 'Large', 'screen' => 'Full screen'], 'show' => ['layout' => ['background', 'slider', 'cards']]]),
            'content_x' => self::f('choice', 'Text position (across)', 'left', ['tab' => 'Layout', 'options' => ['left' => 'Left', 'center' => 'Centre', 'right' => 'Right'], 'show' => ['layout' => ['background', 'slider', 'banner']]]),
            'content_y' => self::f('choice', 'Text position (up/down)', 'center', ['tab' => 'Layout', 'options' => ['top' => 'Top', 'center' => 'Middle', 'bottom' => 'Bottom'], 'show' => ['layout' => ['background', 'slider', 'banner']]]),
            'header_overlay' => self::f('toggle', 'Menu sits on top of the hero', false, ['tab' => 'Layout', 'help' => 'The menu turns see-through with white text until the visitor scrolls. Works with full-width photo layouts on the first section of a page.', 'show' => ['layout' => ['background', 'slider', 'cards'], 'width' => ['full']]]),
            'mobile_background' => self::f('toggle', 'On phones, show the photo behind the text', true, ['tab' => 'Layout', 'show' => ['layout' => ['split']]]),

            // Media
            'media_type' => self::f('choice', 'Background', 'image', ['tab' => 'Media', 'options' => ['image' => 'Photo', 'video' => 'Uploaded video', 'youtube' => 'YouTube', 'vimeo' => 'Vimeo'], 'show' => ['layout' => ['background', 'cards', 'banner']]]),
            'image' => self::f('image', 'Photo', null, ['tab' => 'Media', 'help' => 'Wide landscape photo, at least 2000px. Also used as the video’s cover while it loads.', 'show' => ['layout' => ['background', 'split', 'cards', 'banner', 'mosaic']]]),
            'video' => self::f('video', 'Video file (MP4 or WebM)', null, ['tab' => 'Media', 'help' => 'Short, silent loops of 10–30 seconds work best. Keep it under 20 MB.', 'show' => ['media_type' => ['video'], 'layout' => ['background', 'cards', 'banner']]]),
            'video_url' => self::f('text', 'YouTube or Vimeo link', '', ['tab' => 'Media', 'help' => 'Plays muted on a loop behind the text.', 'show' => ['media_type' => ['youtube', 'vimeo'], 'layout' => ['background', 'cards', 'banner']]]),
            'image_mask' => self::f('choice', 'Photo shape', 'arch', ['tab' => 'Media', 'options' => self::masks(), 'show' => ['layout' => ['split']]]),
            'slides' => self::f('repeater', 'Slides', [], ['tab' => 'Media', 'show' => ['layout' => ['slider']], 'max_items' => 10, 'help' => 'Each slide can have its own headline; leave it empty to use the main headline.', 'fields' => [
                'image' => self::f('image', 'Photo', null),
                'title' => self::f('text', 'Headline', ''),
                'subtitle' => self::f('text', 'Text', ''),
            ]]),
            'autoplay' => self::f('number', 'Seconds per slide', 6, ['tab' => 'Media', 'min' => 0, 'max' => 30, 'help' => '0 stops autoplay.', 'show' => ['layout' => ['slider']]]),
            'gallery' => self::f('repeater', 'Collage photos', [], ['tab' => 'Media', 'show' => ['layout' => ['mosaic']], 'max_items' => 9, 'help' => 'Leave empty to use your featured destinations.', 'fields' => [
                'image' => self::f('image', 'Photo', null),
            ]]),
            'cards_source' => self::f('select', 'Cards show', 'destinations', ['tab' => 'Media', 'options' => ['destinations' => 'Featured destinations', 'journeys' => 'Featured journeys', 'experiences' => 'Experiences'], 'show' => ['layout' => ['cards']]]),
            'feature_package' => self::f('package', 'Floating journey card', '', ['tab' => 'Media', 'help' => 'A small card over the photo linking to one journey.', 'show' => ['layout' => ['split', 'mosaic']]]),

            // Content
            'eyebrow' => self::f('text', 'Small line above the headline', ''),
            'title' => self::f('text', 'Headline', 'Your next journey starts here'),
            'subtitle' => self::f('textarea', 'Intro paragraph', ''),
            'primary_label' => self::f('text', 'Main button text', ''),
            'primary_url' => self::f('text', 'Main button link', '/journeys'),
            'secondary_label' => self::f('text', 'Second button text', ''),
            'secondary_url' => self::f('text', 'Second button link', '/contact'),
            'proof_text' => self::f('text', 'Trust line', '', ['help' => 'e.g. “1,200+ guests hosted since 2014”. Shown with review stars.']),
            'badges' => self::f('repeater', 'Small selling points', [], ['max_items' => 6, 'fields' => [
                'icon' => self::f('icon', 'Icon', 'check'),
                'text' => self::f('text', 'Text', ''),
            ]]),

            // Search
            'search' => self::f('toggle', 'Show the journey search', false, ['tab' => 'Search']),
            'search_style' => self::f('choice', 'Search style', 'panel', ['tab' => 'Search', 'options' => ['panel' => 'Card with dropdowns', 'bar' => 'One-line bar', 'minimal' => 'Keyword box'], 'show' => ['search' => [true]]]),
            'search_fields' => self::f('checkboxes', 'Dropdowns to include', ['category', 'where', 'duration'], ['tab' => 'Search', 'options' => ['category' => 'Experience', 'where' => 'Where', 'duration' => 'Trip length', 'vibe' => 'Vibe'], 'show' => ['search' => [true], 'search_style' => ['panel', 'bar']]]),
            'search_heading' => self::f('text', 'Search heading', 'Find your journey', ['tab' => 'Search', 'show' => ['search' => [true], 'search_style' => ['panel']]]),
            'search_button' => self::f('text', 'Search button text', 'Search journeys', ['tab' => 'Search', 'show' => ['search' => [true]]]),
            'quick_links' => self::f('select', 'Quick-filter tabs above the search', 'none', ['tab' => 'Search', 'options' => ['none' => 'None', 'categories' => 'Experiences', 'vibes' => 'Vibes', 'countries' => 'Countries'], 'show' => ['search' => [true]]]),

            // Style
            'text_color' => self::f('choice', 'Text colour', 'auto', ['tab' => 'Style', 'options' => ['auto' => 'Automatic', 'light' => 'White', 'dark' => 'Dark']]),
            'title_size' => self::f('choice', 'Headline size', 'large', ['tab' => 'Style', 'options' => ['medium' => 'Medium', 'large' => 'Large', 'xlarge' => 'Extra large']]),
            'background' => self::f('select', 'Section background', 'white', ['tab' => 'Style', 'options' => self::BACKGROUNDS, 'show' => ['layout' => ['split', 'mosaic']]]),
            'overlay' => self::f('choice', 'Photo shade', 'gradient', ['tab' => 'Style', 'options' => ['none' => 'None', 'solid' => 'Even', 'gradient' => 'Fade from bottom', 'side' => 'Fade from the text side', 'vignette' => 'Dark edges'], 'show' => $media]),
            'overlay_color' => self::f('choice', 'Shade colour', 'dark', ['tab' => 'Style', 'options' => ['dark' => 'Black', 'brand' => 'Brand colour', 'light' => 'White'], 'show' => $media]),
            'overlay_opacity' => self::f('range', 'Shade strength', 55, ['tab' => 'Style', 'min' => 0, 'max' => 95, 'step' => 5, 'show' => $media]),
            'edge' => self::f('choice', 'Bottom edge', 'straight', ['tab' => 'Style', 'options' => ['straight' => 'Straight', 'curve' => 'Curve', 'wave' => 'Wave', 'slant' => 'Slant'], 'show' => ['layout' => ['background', 'slider', 'cards'], 'width' => ['full']]]),
        ];
    }

    /** @return array<string, string> */
    public static function masks(): array
    {
        return ['none' => 'Square', 'rounded' => 'Rounded', 'arch' => 'Arch', 'circle' => 'Circle', 'blob' => 'Blob', 'leaf' => 'Leaf', 'tilt' => 'Tilted card'];
    }

    /** @return array<string, mixed>|null */
    public static function get(string $type): ?array
    {
        return self::all()[$type] ?? null;
    }

    /** Types that can be added to the given page. */
    public static function forPage(string $slug): array
    {
        return array_filter(self::all(), fn (array $t) => ! isset($t['pages']) || in_array($slug, $t['pages'], true));
    }

    /** @return array<string, mixed> */
    public static function defaults(string $type): array
    {
        return array_map(fn (array $field) => $field['default'], self::get($type)['fields'] ?? []);
    }

    /** @param  array<string, mixed>  $extra */
    private static function f(string $type, string $label, mixed $default, array $extra = []): array
    {
        return ['type' => $type, 'label' => $label, 'default' => $default] + $extra + ['tab' => 'Content'];
    }
}
