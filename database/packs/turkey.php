<?php

/*
 * Content pack: Turkey.
 * Import from Admin → Content packs. Prices are in USD and converted to your base currency on import.
 * Images are Wikipedia article references (wiki:Title) downloaded by the image importer.
 * Hotels, prices and itineraries are sample content: review them before selling.
 */

return [
    'key' => 'turkey',
    'name' => 'Turkey',
    'country' => 'TR',
    'currency' => 'TRY',
    'cover' => 'wiki:Hagia Sophia',
    'summary' => 'Istanbul’s mosques and bazaars, Cappadocia’s balloons, Pamukkale and Ephesus.',
    'destinations' => [
        [
            'name' => 'Istanbul',
            'region' => 'Marmara',
            'tagline' => 'Where Europe meets Asia',
            'description' => 'Hagia Sophia, the Blue Mosque, the Grand Bazaar and the Bosphorus.',
            'highlights' => ['Hagia Sophia', 'Blue Mosque', 'Grand Bazaar', 'Bosphorus cruise'],
            'best_season' => 'Apr – Jun, Sep – Nov',
            'image' => 'wiki:Hagia Sophia',
            'featured' => true,
        ],
        [
            'name' => 'Cappadocia',
            'region' => 'Central Anatolia',
            'tagline' => 'Fairy chimneys and hot-air balloons',
            'description' => 'A volcanic landscape of rock chimneys, cave hotels and underground cities.',
            'highlights' => ['Sunrise balloon flight', 'Göreme Open-Air Museum', 'Derinkuyu underground city'],
            'best_season' => 'Apr – Jun, Sep – Oct',
            'image' => 'wiki:Cappadocia',
            'featured' => true,
        ],
        [
            'name' => 'Pamukkale',
            'region' => 'Aegean',
            'tagline' => 'White travertine terraces',
            'description' => 'Cascading thermal pools beside the ruins of Hierapolis.',
            'highlights' => ['Travertine terraces', 'Hierapolis', 'Cleopatra’s Pool'],
            'best_season' => 'Apr – Oct',
            'image' => 'wiki:Pamukkale',
        ],
        [
            'name' => 'Ephesus',
            'region' => 'Aegean',
            'tagline' => 'The great Roman city',
            'description' => 'The Library of Celsus, the Great Theatre and the House of the Virgin Mary.',
            'highlights' => ['Library of Celsus', 'Great Theatre', 'House of the Virgin Mary'],
            'best_season' => 'Apr – Oct',
            'image' => 'wiki:Ephesus',
        ],
    ],
    'hotels' => [
        [
            'name' => 'Çırağan Palace Kempinski',
            'destination' => 'Istanbul',
            'type' => 'Heritage hotel',
            'stars' => 5,
            'description' => 'A former Ottoman palace on the shores of the Bosphorus.',
            'address' => 'Beşiktaş, Istanbul',
            'amenities' => ['Bosphorus views', 'Infinity pool', 'Spa'],
        ],
        [
            'name' => 'Museum Hotel Cappadocia',
            'destination' => 'Cappadocia',
            'type' => 'Boutique hotel',
            'stars' => 5,
            'description' => 'Cave suites filled with antiques, with balloons drifting past at dawn.',
            'address' => 'Uçhisar, Cappadocia',
            'amenities' => ['Cave suites', 'Balloon views', 'Pool'],
        ],
    ],
    'packages' => [
        [
            'title' => 'Istanbul & Cappadocia Balloons',
            'category' => 'Romantic escapes',
            'vibes' => ['Romantic', 'Heritage & history', 'Food & culture'],
            'featured' => true,
            'summary' => 'Seven days: Ottoman Istanbul, a Bosphorus cruise and a cave suite in Cappadocia.',
            'overview' => 'A romantic journey from the domes of Istanbul to the fairy chimneys of Cappadocia, with an optional sunrise balloon flight.',
            'price' => 1790,
            'child_price' => 990,
            'tiers' => [
                [
                    'min' => 6,
                    'price' => 1650,
                ],
            ],
            'start_city' => 'Istanbul',
            'best_season' => 'Apr – Jun, Sep – Oct',
            'image' => 'wiki:Cappadocia',
            'destinations' => ['Istanbul', 'Cappadocia'],
            'highlights' => ['Hagia Sophia and the Blue Mosque', 'Bosphorus cruise', 'Cave-suite hotel', 'Göreme Open-Air Museum'],
            'extras' => [
                [
                    'name' => 'Sunrise hot-air balloon flight',
                    'description' => 'One-hour flight with champagne toast (weather permitting).',
                    'price' => 260,
                    'per' => 'person',
                ],
                [
                    'name' => 'Turkish hammam experience',
                    'description' => 'Traditional bath in a historic hammam.',
                    'price' => 85,
                    'per' => 'person',
                ],
            ],
            'itinerary' => [
                ['Arrival in Istanbul', 'Private transfer.', 'Çırağan Palace Kempinski', 'Dinner'],
                [
                    'Old Istanbul',
                    'Hagia Sophia, Blue Mosque, Topkapı and the Grand Bazaar.',
                    'Çırağan Palace Kempinski',
                    'Breakfast',
                ],
                ['Bosphorus', 'Bosphorus cruise and the Spice Bazaar.', 'Çırağan Palace Kempinski', 'Breakfast'],
                ['Fly to Cappadocia', 'Sunset at Rose Valley.', 'Museum Hotel Cappadocia', 'Breakfast, Dinner'],
                [
                    'Cappadocia',
                    'Göreme Open-Air Museum and Derinkuyu underground city.',
                    'Museum Hotel Cappadocia',
                    'Breakfast',
                ],
                ['Cappadocia', 'Free day; optional balloon flight at dawn.', 'Museum Hotel Cappadocia', 'Breakfast'],
                ['Departure', 'Fly back to Istanbul for your connection.', null, 'Breakfast'],
            ],
        ],
        [
            'title' => 'Turkey Grand Tour',
            'category' => 'Heritage & culture',
            'vibes' => ['Heritage & history', 'Religious & spiritual', 'Nature & wildlife'],
            'summary' => 'Ten days: Istanbul, Cappadocia, Pamukkale and Ephesus.',
            'overview' => 'Turkey’s greatest hits, from Byzantine and Ottoman Istanbul to the Roman cities of the Aegean.',
            'price' => 2390,
            'tiers' => [
                [
                    'min' => 8,
                    'price' => 2190,
                ],
            ],
            'start_city' => 'Istanbul',
            'best_season' => 'Apr – Oct',
            'image' => 'wiki:Ephesus',
            'destinations' => ['Istanbul', 'Cappadocia', 'Pamukkale', 'Ephesus'],
            'highlights' => ['Istanbul’s mosques and bazaars', 'Cappadocia', 'Pamukkale terraces', 'Library of Celsus at Ephesus'],
            'itinerary' => [
                ['Arrival in Istanbul', 'Private transfer.', 'Çırağan Palace Kempinski', 'Dinner'],
                ['Istanbul', 'Old City highlights.', 'Çırağan Palace Kempinski', 'Breakfast'],
                ['Istanbul', 'Bosphorus and bazaars.', 'Çırağan Palace Kempinski', 'Breakfast'],
                ['Fly to Cappadocia', 'Rose Valley sunset.', 'Museum Hotel Cappadocia', 'Breakfast, Dinner'],
                ['Cappadocia', 'Valleys and underground city.', 'Museum Hotel Cappadocia', 'Breakfast'],
                ['To Pamukkale', 'Drive west via Konya and the Mevlana Museum.', null, 'Breakfast, Dinner'],
                ['Pamukkale', 'Travertines and Hierapolis.', null, 'Breakfast'],
                ['Ephesus', 'Library of Celsus and the House of the Virgin Mary.', null, 'Breakfast, Dinner'],
                ['Aegean coast', 'Şirince village and a free afternoon.', null, 'Breakfast'],
                ['Departure', 'Fly from Izmir.', null, 'Breakfast'],
            ],
        ],
    ],
];
