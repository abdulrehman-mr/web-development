<?php

/*
 * Content pack: United Kingdom.
 * Import from Admin → Content packs. Prices are in USD and converted to your base currency on import.
 * Images are Wikipedia article references (wiki:Title) downloaded by the image importer.
 * Hotels, prices and itineraries are sample content: review them before selling.
 */

return [
    'key' => 'united-kingdom',
    'name' => 'United Kingdom',
    'country' => 'GB',
    'currency' => 'GBP',
    'cover' => 'wiki:Tower Bridge',
    'summary' => 'Royal London, Oxford, the Cotswolds and Edinburgh.',
    'destinations' => [
        [
            'name' => 'London',
            'region' => 'England',
            'tagline' => 'Royal palaces and world-class museums',
            'description' => 'Buckingham Palace, the Tower of London, free museums and theatres in the West End.',
            'highlights' => ['Tower of London', 'Buckingham Palace', 'British Museum', 'West End show'],
            'best_season' => 'May – Sep',
            'image' => 'wiki:Tower Bridge',
            'featured' => true,
        ],
        [
            'name' => 'Oxford',
            'region' => 'England',
            'tagline' => 'The city of dreaming spires',
            'description' => 'Golden-stone colleges, the Bodleian Library and punting on the river.',
            'highlights' => ['Christ Church', 'Bodleian Library', 'Punting'],
            'best_season' => 'Apr – Oct',
            'image' => 'wiki:Radcliffe Camera',
        ],
        [
            'name' => 'Cotswolds',
            'region' => 'England',
            'tagline' => 'Honey-stone villages',
            'description' => 'Rolling hills and storybook villages such as Bibury and Bourton-on-the-Water.',
            'highlights' => ['Bibury', 'Bourton-on-the-Water', 'Country pubs'],
            'best_season' => 'Apr – Oct',
            'image' => 'wiki:Cotswolds',
        ],
        [
            'name' => 'Edinburgh',
            'region' => 'Scotland',
            'tagline' => 'Castle, closes and festivals',
            'description' => 'Scotland’s capital, crowned by its castle and famous for the August festivals.',
            'highlights' => ['Edinburgh Castle', 'Royal Mile', 'Arthur’s Seat', 'Edinburgh Festival Fringe'],
            'best_season' => 'May – Sep',
            'image' => 'wiki:Edinburgh Castle',
        ],
    ],
    'hotels' => [
        [
            'name' => 'The Savoy',
            'destination' => 'London',
            'type' => 'Luxury hotel',
            'stars' => 5,
            'description' => 'London’s legendary hotel on the Strand, beside the Thames.',
            'address' => 'Strand, London',
            'amenities' => ['River views', 'Afternoon tea', 'Spa'],
        ],
        [
            'name' => 'The Lygon Arms',
            'destination' => 'Cotswolds',
            'type' => 'Heritage hotel',
            'stars' => 4,
            'description' => 'A 16th-century coaching inn in Broadway.',
            'address' => 'High Street, Broadway',
            'amenities' => ['Historic inn', 'Spa', 'Fireside bar'],
        ],
        [
            'name' => 'The Balmoral',
            'destination' => 'Edinburgh',
            'type' => 'Luxury hotel',
            'stars' => 5,
            'description' => 'Edinburgh’s grand hotel at the top of Princes Street.',
            'address' => '1 Princes Street, Edinburgh',
            'amenities' => ['Castle views', 'Spa', 'Whisky bar'],
        ],
    ],
    'packages' => [
        [
            'title' => 'London Classic',
            'category' => 'City breaks',
            'vibes' => ['City breaks', 'Heritage & history', 'Shopping'],
            'featured' => true,
            'summary' => 'Five days of royal palaces, the Tower, afternoon tea and a West End show.',
            'overview' => 'London at its best, with a Blue Badge guide for the sights and time for Oxford Street and Harrods.',
            'price' => 1990,
            'child_price' => 1190,
            'start_city' => 'London',
            'best_season' => 'May – Sep',
            'image' => 'wiki:Tower Bridge',
            'destinations' => ['London'],
            'highlights' => ['Tower of London and Crown Jewels', 'Changing of the Guard', 'Afternoon tea at The Savoy', 'West End show'],
            'extras' => [
                [
                    'name' => 'Warner Bros. Studio Tour – The Making of Harry Potter',
                    'description' => 'Return transfers and tickets.',
                    'price' => 140,
                    'per' => 'person',
                ],
            ],
            'itinerary' => [
                ['Arrival in London', 'Private transfer from Heathrow.', 'The Savoy', 'Dinner'],
                [
                    'Royal London',
                    'Changing of the Guard, Westminster Abbey and the Tower of London.',
                    'The Savoy',
                    'Breakfast',
                ],
                [
                    'Museums and tea',
                    'British Museum, afternoon tea and a West End show.',
                    'The Savoy',
                    'Breakfast, Afternoon tea',
                ],
                ['Shopping and markets', 'Borough Market, Covent Garden and Harrods.', 'The Savoy', 'Breakfast'],
                ['Departure', 'Transfer to Heathrow.', null, 'Breakfast'],
            ],
        ],
        [
            'title' => 'London, Cotswolds & Edinburgh',
            'category' => 'Heritage & culture',
            'vibes' => ['Heritage & history', 'Countryside', 'City breaks'],
            'summary' => 'Eight days: royal London, Oxford, a Cotswolds inn and Edinburgh by train.',
            'overview' => 'A classic British journey, ending with the LNER train up the east coast to Edinburgh.',
            'price' => 3290,
            'tiers' => [
                [
                    'min' => 6,
                    'price' => 3050,
                ],
            ],
            'start_city' => 'London',
            'best_season' => 'May – Sep',
            'image' => 'wiki:Cotswolds',
            'destinations' => ['London', 'Oxford', 'Cotswolds', 'Edinburgh'],
            'highlights' => ['Oxford colleges', 'Cotswolds villages', 'Train to Edinburgh', 'Edinburgh Castle'],
            'itinerary' => [
                ['Arrival in London', 'Private transfer.', 'The Savoy', 'Dinner'],
                ['London', 'Royal London highlights.', 'The Savoy', 'Breakfast'],
                ['Oxford', 'Colleges and the Bodleian, then on to the Cotswolds.', 'The Lygon Arms', 'Breakfast, Dinner'],
                ['Cotswolds', 'Bibury, Bourton-on-the-Water and Stow-on-the-Wold.', 'The Lygon Arms', 'Breakfast'],
                ['Train to Edinburgh', 'Back to London and the train north.', 'The Balmoral', 'Breakfast'],
                ['Edinburgh', 'Castle, Royal Mile and Holyrood.', 'The Balmoral', 'Breakfast'],
                ['Highlands day trip', 'Optional day trip to Loch Ness and Glencoe.', 'The Balmoral', 'Breakfast'],
                ['Departure', 'Transfer to Edinburgh airport.', null, 'Breakfast'],
            ],
        ],
    ],
];
