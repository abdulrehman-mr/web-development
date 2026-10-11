<?php

/*
 * Content pack: Maldives.
 * Import from Admin → Content packs. Prices are in USD and converted to your base currency on import.
 * Images are Wikipedia article references (wiki:Title) downloaded by the image importer.
 * Hotels, prices and itineraries are sample content: review them before selling.
 */

return [
    'key' => 'maldives',
    'name' => 'Maldives',
    'country' => 'MV',
    'currency' => 'USD',
    'cover' => 'wiki:Baa Atoll',
    'summary' => 'Overwater villas, house reefs and island-hopping across the atolls.',
    'destinations' => [
        [
            'name' => 'Malé',
            'region' => 'Kaafu',
            'tagline' => 'The capital and gateway to the atolls',
            'description' => 'The compact capital is where every Maldives holiday begins, with seaplane and speedboat transfers from the airport island.',
            'highlights' => ['Old Friday Mosque', 'Malé fish market', 'Artificial Beach'],
            'best_season' => 'Nov – Apr',
            'image' => 'wiki:Malé',
        ],
        [
            'name' => 'Baa Atoll',
            'region' => 'Baa',
            'tagline' => 'A UNESCO biosphere reserve',
            'description' => 'Home to Hanifaru Bay, where manta rays gather in their hundreds between June and November.',
            'highlights' => ['Manta rays at Hanifaru Bay', 'House-reef snorkelling', 'Sandbank picnics'],
            'best_season' => 'Year-round',
            'image' => 'wiki:Baa Atoll',
            'featured' => true,
        ],
        [
            'name' => 'South Ari Atoll',
            'region' => 'Alif Dhaal',
            'tagline' => 'Whale sharks and underwater dining',
            'description' => 'South Ari is one of the few places in the world to snorkel with whale sharks all year round.',
            'highlights' => ['Whale-shark snorkelling', 'Underwater restaurant', 'Overwater villas'],
            'best_season' => 'Year-round',
            'image' => 'wiki:Ari Atoll',
            'featured' => true,
        ],
    ],
    'hotels' => [
        [
            'name' => 'Soneva Fushi',
            'destination' => 'Baa Atoll',
            'type' => 'Beach resort',
            'stars' => 5,
            'description' => 'Barefoot luxury villas hidden in the jungle, with an observatory and open-air cinema.',
            'address' => 'Kunfunadhoo Island, Baa Atoll',
            'amenities' => ['Private pools', 'Observatory', 'Kids’ den'],
        ],
        [
            'name' => 'Conrad Maldives Rangali Island',
            'destination' => 'South Ari Atoll',
            'type' => 'Beach resort',
            'stars' => 5,
            'description' => 'Two islands joined by a bridge, with overwater villas and the famous Ithaa undersea restaurant.',
            'address' => 'Rangali Island, South Ari Atoll',
            'amenities' => ['Overwater villas', 'Undersea restaurant', 'Spa'],
        ],
        [
            'name' => 'Kurumba Maldives',
            'destination' => 'Malé',
            'type' => 'Beach resort',
            'stars' => 5,
            'description' => 'The Maldives’ first resort, ten minutes by speedboat from the airport.',
            'address' => 'Vihamanaafushi, North Malé Atoll',
            'amenities' => ['Family villas', 'Eight restaurants', 'Dive centre'],
        ],
    ],
    'packages' => [
        [
            'title' => 'Maldives Overwater Honeymoon',
            'category' => 'Islands & beaches',
            'category_tagline' => 'Turquoise water and white sand',
            'vibes' => ['Romantic', 'Beach & islands', 'Luxury'],
            'featured' => true,
            'summary' => 'Five nights in an overwater villa with a seaplane arrival, sunset cruise and dinner beneath the waves.',
            'overview' => 'The honeymoon everyone imagines. You arrive by seaplane, stay in a villa over the lagoon and have the house reef as your back garden.',
            'price' => 2950,
            'start_city' => 'Malé',
            'best_season' => 'Nov – Apr',
            'image' => 'wiki:South Ari Atoll',
            'destinations' => ['South Ari Atoll'],
            'highlights' => [
                'Seaplane transfer',
                'Overwater villa with lagoon steps',
                'Dinner at an undersea restaurant',
                'Whale-shark snorkelling trip',
            ],
            'extras' => [
                [
                    'name' => 'Sunset dolphin cruise',
                    'description' => 'Two hours on a traditional dhoni at sunset.',
                    'price' => 180,
                    'per' => 'booking',
                ],
                [
                    'name' => 'Upgrade to all-inclusive',
                    'description' => 'All meals, drinks and one excursion daily.',
                    'price' => 640,
                    'per' => 'person',
                ],
            ],
            'itinerary' => [
                [
                    'Arrival and seaplane',
                    'Meet-and-greet at Velana airport, then a scenic seaplane flight to your resort.',
                    'Conrad Maldives Rangali Island',
                    'Dinner',
                ],
                [
                    'House reef',
                    'Snorkel the house reef with a marine biologist.',
                    'Conrad Maldives Rangali Island',
                    'Breakfast, Dinner',
                ],
                [
                    'Whale sharks',
                    'Morning boat trip to snorkel with whale sharks.',
                    'Conrad Maldives Rangali Island',
                    'Breakfast, Dinner',
                ],
                [
                    'Under the sea',
                    'Free day, then dinner in the undersea restaurant.',
                    'Conrad Maldives Rangali Island',
                    'Breakfast, Dinner',
                ],
                ['Departure', 'Seaplane back to Malé for your flight.', null, 'Breakfast'],
            ],
        ],
        [
            'title' => 'Family Island Escape',
            'category' => 'Islands & beaches',
            'vibes' => ['Family-friendly', 'Beach & islands', 'Nature & wildlife'],
            'summary' => 'Six days on a family-friendly island with a kids’ club, gentle snorkelling and a sandbank picnic.',
            'overview' => 'A quick speedboat ride from the airport means no long transfers with tired children. Family villas sleep four, and the lagoon is shallow and calm.',
            'price' => 2190,
            'child_price' => 990,
            'start_city' => 'Malé',
            'best_season' => 'Nov – Apr',
            'image' => 'wiki:Malé',
            'destinations' => ['Malé'],
            'highlights' => [
                'Speedboat transfer (no seaplane needed)',
                'Kids’ club and family villas',
                'Sandbank picnic',
                'Turtle snorkelling',
            ],
            'itinerary' => [
                ['Arrival', '15-minute speedboat to the resort.', 'Kurumba Maldives', 'Dinner'],
                ['Lagoon day', 'Beginner snorkelling lesson for the whole family.', 'Kurumba Maldives', 'Breakfast, Dinner'],
                ['Sandbank picnic', 'Boat to a private sandbank for a picnic lunch.', 'Kurumba Maldives', 'All meals'],
                ['Turtles', 'Snorkel trip to see green turtles.', 'Kurumba Maldives', 'Breakfast, Dinner'],
                ['Malé visit', 'Half day in Malé: fish market and Friday Mosque.', 'Kurumba Maldives', 'Breakfast, Dinner'],
                ['Departure', 'Speedboat to the airport.', null, 'Breakfast'],
            ],
        ],
    ],
];
