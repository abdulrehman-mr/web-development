<?php

/*
 * Content pack: Thailand.
 * Import from Admin → Content packs. Prices are in USD and converted to your base currency on import.
 * Images are Wikipedia article references (wiki:Title) downloaded by the image importer.
 * Hotels, prices and itineraries are sample content: review them before selling.
 */

return [
    'key' => 'thailand',
    'name' => 'Thailand',
    'country' => 'TH',
    'currency' => 'THB',
    'cover' => 'wiki:Wat Arun',
    'summary' => 'Bangkok’s temples, Chiang Mai’s lantern festivals and the Andaman islands.',
    'destinations' => [
        [
            'name' => 'Bangkok',
            'region' => 'Central Thailand',
            'tagline' => 'Temples, rooftops and street food',
            'description' => 'Golden temples beside the Chao Phraya, floating markets and some of the best street food on earth.',
            'highlights' => ['Grand Palace', 'Wat Arun', 'Chinatown street food', 'Rooftop bars'],
            'best_season' => 'Nov – Feb',
            'image' => 'wiki:Wat Arun',
            'featured' => true,
        ],
        [
            'name' => 'Chiang Mai',
            'region' => 'Northern Thailand',
            'tagline' => 'Mountain temples and night markets',
            'description' => 'The cultural capital of the north, ringed by mountains and famous for its lantern festival.',
            'highlights' => ['Doi Suthep', 'Old City temples', 'Ethical elephant sanctuary', 'Yi Peng lantern festival'],
            'best_season' => 'Nov – Feb',
            'image' => 'wiki:Wat Phra That Doi Suthep',
        ],
        [
            'name' => 'Phuket',
            'region' => 'Andaman Coast',
            'tagline' => 'Beaches and island-hopping',
            'description' => 'Thailand’s biggest island, and the base for boat trips to Phang Nga Bay and the Phi Phi Islands.',
            'highlights' => ['Phang Nga Bay', 'Old Phuket Town', 'Big Buddha'],
            'best_season' => 'Nov – Apr',
            'image' => 'wiki:Phuket province',
            'featured' => true,
        ],
        [
            'name' => 'Phi Phi Islands',
            'region' => 'Andaman Coast',
            'tagline' => 'Limestone cliffs and turquoise bays',
            'description' => 'Maya Bay and its sister islands are the postcard image of Thailand.',
            'highlights' => ['Maya Bay', 'Snorkelling', 'Viewpoint hike'],
            'best_season' => 'Nov – Apr',
            'image' => 'wiki:Phi Phi Islands',
        ],
    ],
    'hotels' => [
        [
            'name' => 'The Peninsula Bangkok',
            'destination' => 'Bangkok',
            'type' => 'Luxury hotel',
            'stars' => 5,
            'description' => 'Riverside rooms with views across the Chao Phraya, and a private boat to the city.',
            'address' => 'Charoennakorn Road, Bangkok',
            'amenities' => ['River views', 'Pool', 'Boat shuttle'],
        ],
        [
            'name' => 'Anantara Chiang Mai Resort',
            'destination' => 'Chiang Mai',
            'type' => 'Boutique hotel',
            'stars' => 5,
            'description' => 'A calm riverside retreat a short walk from the night bazaar.',
            'address' => 'Charoen Prathet Road, Chiang Mai',
            'amenities' => ['Pool', 'Spa', 'Cooking school'],
        ],
        [
            'name' => 'Banyan Tree Phuket',
            'destination' => 'Phuket',
            'type' => 'Beach resort',
            'stars' => 5,
            'description' => 'Pool villas on Bang Tao Bay.',
            'address' => 'Bang Tao Bay, Phuket',
            'amenities' => ['Pool villas', 'Spa', 'Beach'],
        ],
    ],
    'packages' => [
        [
            'title' => 'Bangkok, Chiang Mai & Phuket',
            'category' => 'Heritage & culture',
            'vibes' => ['Food & culture', 'Beach & islands', 'Heritage & history'],
            'featured' => true,
            'summary' => 'Nine days from Bangkok’s temples to northern mountains and Andaman beaches.',
            'overview' => 'The best of Thailand in one trip, linked by short domestic flights.',
            'price' => 1690,
            'child_price' => 990,
            'tiers' => [
                [
                    'min' => 6,
                    'price' => 1550,
                ],
            ],
            'start_city' => 'Bangkok',
            'best_season' => 'Nov – Feb',
            'image' => 'wiki:Grand Palace',
            'destinations' => ['Bangkok', 'Chiang Mai', 'Phuket'],
            'highlights' => [
                'Grand Palace and Wat Pho',
                'Chinatown food tour',
                'Ethical elephant sanctuary',
                'Phang Nga Bay by longtail boat',
            ],
            'extras' => [
                [
                    'name' => 'Thai cooking class in Chiang Mai',
                    'description' => 'Market visit and cooking on a farm.',
                    'price' => 45,
                    'per' => 'person',
                ],
            ],
            'itinerary' => [
                ['Arrival in Bangkok', 'Airport welcome and river cruise at sunset.', 'The Peninsula Bangkok', 'Dinner'],
                [
                    'Temples of Bangkok',
                    'Grand Palace, Wat Pho and Wat Arun, then a Chinatown food tour.',
                    'The Peninsula Bangkok',
                    'Breakfast, Dinner',
                ],
                [
                    'Floating market',
                    'Damnoen Saduak floating market and Maeklong railway market.',
                    'The Peninsula Bangkok',
                    'Breakfast',
                ],
                ['Fly to Chiang Mai', 'Doi Suthep at sunset.', 'Anantara Chiang Mai Resort', 'Breakfast'],
                ['Elephants', 'A day at an ethical elephant sanctuary.', 'Anantara Chiang Mai Resort', 'Breakfast, Lunch'],
                ['Fly to Phuket', 'Afternoon on the beach.', 'Banyan Tree Phuket', 'Breakfast'],
                ['Phang Nga Bay', 'Longtail boat among the limestone islands.', 'Banyan Tree Phuket', 'Breakfast, Lunch'],
                ['Beach day', 'Free day.', 'Banyan Tree Phuket', 'Breakfast'],
                ['Departure', 'Transfer to Phuket airport.', null, 'Breakfast'],
            ],
        ],
        [
            'title' => 'Loy Krathong & Yi Peng Lantern Festival',
            'category' => 'Festivals',
            'category_tagline' => 'Celebrations worth flying for',
            'vibes' => ['Festivals & events', 'Romantic', 'Food & culture'],
            'summary' => 'Five days in Chiang Mai for the November full moon, when thousands of lanterns rise into the sky.',
            'overview' => 'Timed for the November full moon, with reserved seats at an organised lantern release and a krathong-floating evening on the river.',
            'price' => 1250,
            'start_city' => 'Chiang Mai',
            'best_season' => 'November full moon',
            'image' => 'wiki:Yi Peng',
            'destinations' => ['Chiang Mai'],
            'highlights' => [
                'Reserved lantern-release ticket',
                'Krathong on the Ping River',
                'Old City temples by tuk-tuk',
                'Night bazaar',
            ],
            'itinerary' => [
                ['Arrival in Chiang Mai', 'Welcome dinner with a khantoke show.', 'Anantara Chiang Mai Resort', 'Dinner'],
                ['Temples', 'Old City temples and Doi Suthep.', 'Anantara Chiang Mai Resort', 'Breakfast'],
                [
                    'Yi Peng',
                    'Evening lantern release with reserved seating.',
                    'Anantara Chiang Mai Resort',
                    'Breakfast, Dinner',
                ],
                [
                    'Loy Krathong',
                    'Make a krathong and float it on the Ping River.',
                    'Anantara Chiang Mai Resort',
                    'Breakfast',
                ],
                ['Departure', 'Transfer to the airport.', null, 'Breakfast'],
            ],
        ],
    ],
];
