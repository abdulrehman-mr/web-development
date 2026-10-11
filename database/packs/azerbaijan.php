<?php

/*
 * Content pack: Azerbaijan.
 * Import from Admin → Content packs. Prices are in USD and converted to your base currency on import.
 * Images are Wikipedia article references (wiki:Title) downloaded by the image importer.
 * Hotels, prices and itineraries are sample content: review them before selling.
 */

return [
    'key' => 'azerbaijan',
    'name' => 'Azerbaijan',
    'country' => 'AZ',
    'currency' => 'AZN',
    'cover' => 'wiki:Flame Towers',
    'summary' => 'Baku’s Flame Towers and Old City, Gobustan, Gabala and Sheki.',
    'destinations' => [
        [
            'name' => 'Baku',
            'region' => 'Absheron',
            'tagline' => 'Flame Towers and the Caspian',
            'description' => 'A city of futuristic towers, a walled UNESCO Old City and a long seaside boulevard.',
            'highlights' => ['Flame Towers', 'Old City (Icherisheher)', 'Heydar Aliyev Center', 'Caspian boulevard'],
            'best_season' => 'Apr – Jun, Sep – Oct',
            'image' => 'wiki:Flame Towers',
            'featured' => true,
        ],
        [
            'name' => 'Gobustan',
            'region' => 'Absheron',
            'tagline' => 'Rock art and mud volcanoes',
            'description' => 'Stone Age petroglyphs and bubbling mud volcanoes south of Baku.',
            'highlights' => ['Petroglyphs', 'Mud volcanoes'],
            'best_season' => 'Apr – Oct',
            'image' => 'wiki:Gobustan National Park',
        ],
        [
            'name' => 'Gabala',
            'region' => 'Shaki-Zaqatala',
            'tagline' => 'Mountain air and cable cars',
            'description' => 'A green mountain resort in the Greater Caucasus.',
            'highlights' => ['Tufandag cable car', 'Nohur Lake', 'Seven Beauties waterfall'],
            'best_season' => 'May – Oct',
            'image' => 'wiki:Qabala',
        ],
        [
            'name' => 'Sheki',
            'region' => 'Shaki-Zaqatala',
            'tagline' => 'Silk Road palace town',
            'description' => 'A Silk Road town famous for the stained-glass Palace of the Sheki Khans.',
            'highlights' => ['Palace of Shaki Khans', 'Caravanserai', 'Halva makers'],
            'best_season' => 'May – Oct',
            'image' => 'wiki:Palace of Shaki Khans',
        ],
    ],
    'hotels' => [
        [
            'name' => 'Fairmont Baku, Flame Towers',
            'destination' => 'Baku',
            'type' => 'Luxury hotel',
            'stars' => 5,
            'description' => 'Inside one of the three Flame Towers, with views over the Caspian.',
            'address' => 'Flame Towers, Baku',
            'amenities' => ['Caspian views', 'Spa', 'Pool'],
        ],
        [
            'name' => 'Marxal Resort & Spa',
            'destination' => 'Sheki',
            'type' => 'Mountain lodge',
            'stars' => 5,
            'description' => 'A forested mountain resort above Sheki.',
            'address' => 'Sheki',
            'amenities' => ['Mountain views', 'Spa', 'Pool'],
        ],
    ],
    'packages' => [
        [
            'title' => 'Baku City Break',
            'category' => 'City breaks',
            'vibes' => ['City breaks', 'Tech-savvy', 'Food & culture'],
            'featured' => true,
            'summary' => 'Four days of Flame Towers, the walled Old City, Gobustan rock art and mud volcanoes.',
            'overview' => 'A short break that mixes futuristic Baku with ancient Absheron, just a few hours’ flight from the Gulf.',
            'price' => 890,
            'child_price' => 490,
            'start_city' => 'Baku',
            'best_season' => 'Apr – Jun, Sep – Oct',
            'image' => 'wiki:Flame Towers',
            'destinations' => ['Baku', 'Gobustan'],
            'highlights' => ['Flame Towers light show', 'Old City and Maiden Tower', 'Heydar Aliyev Center', 'Gobustan mud volcanoes'],
            'itinerary' => [
                [
                    'Arrival in Baku',
                    'Private transfer and evening on the boulevard.',
                    'Fairmont Baku, Flame Towers',
                    'Dinner',
                ],
                ['Baku', 'Old City, Maiden Tower and the Heydar Aliyev Center.', 'Fairmont Baku, Flame Towers', 'Breakfast'],
                [
                    'Gobustan',
                    'Petroglyphs, mud volcanoes and the Fire Temple.',
                    'Fairmont Baku, Flame Towers',
                    'Breakfast, Lunch',
                ],
                ['Departure', 'Transfer to the airport.', null, 'Breakfast'],
            ],
        ],
        [
            'title' => 'Baku, Gabala & Sheki',
            'category' => 'Heritage & culture',
            'vibes' => ['Heritage & history', 'Nature & wildlife', 'Countryside'],
            'summary' => 'Seven days from the Caspian to the Caucasus mountains and the Silk Road palace of Sheki.',
            'overview' => 'Baku’s city life, then the green mountains of the north with a cable car in Gabala and a night in Sheki.',
            'price' => 1390,
            'tiers' => [
                [
                    'min' => 6,
                    'price' => 1250,
                ],
            ],
            'start_city' => 'Baku',
            'best_season' => 'May – Oct',
            'image' => 'wiki:Palace of Shaki Khans',
            'destinations' => ['Baku', 'Gabala', 'Sheki'],
            'highlights' => ['Tufandag cable car', 'Palace of the Sheki Khans', 'Nohur Lake', 'Baku Old City'],
            'itinerary' => [
                ['Arrival in Baku', 'Private transfer.', 'Fairmont Baku, Flame Towers', 'Dinner'],
                ['Baku', 'City highlights.', 'Fairmont Baku, Flame Towers', 'Breakfast'],
                ['To Gabala', 'Drive into the Caucasus.', null, 'Breakfast, Dinner'],
                ['Gabala', 'Tufandag cable car and Nohur Lake.', null, 'Breakfast'],
                ['Sheki', 'Palace of the Sheki Khans and the caravanserai.', 'Marxal Resort & Spa', 'Breakfast, Dinner'],
                ['Back to Baku', 'Return drive through Shamakhi.', 'Fairmont Baku, Flame Towers', 'Breakfast'],
                ['Departure', 'Transfer to the airport.', null, 'Breakfast'],
            ],
        ],
    ],
];
