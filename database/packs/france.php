<?php

/*
 * Content pack: France.
 * Import from Admin → Content packs. Prices are in USD and converted to your base currency on import.
 * Images are Wikipedia article references (wiki:Title) downloaded by the image importer.
 * Hotels, prices and itineraries are sample content: review them before selling.
 */

return [
    'key' => 'france',
    'name' => 'France',
    'country' => 'FR',
    'currency' => 'EUR',
    'cover' => 'wiki:Eiffel Tower',
    'summary' => 'Paris for two, Versailles, the Loire châteaux and Mont-Saint-Michel.',
    'destinations' => [
        [
            'name' => 'Paris',
            'region' => 'Île-de-France',
            'tagline' => 'The city of light and love',
            'description' => 'The Eiffel Tower, the Louvre, Montmartre and cafés on every corner.',
            'highlights' => ['Eiffel Tower', 'Louvre', 'Seine cruise', 'Montmartre'],
            'best_season' => 'Apr – Jun, Sep – Oct',
            'image' => 'wiki:Eiffel Tower',
            'featured' => true,
        ],
        [
            'name' => 'Versailles',
            'region' => 'Île-de-France',
            'tagline' => 'The Sun King’s palace',
            'description' => 'Louis XIV’s palace, the Hall of Mirrors and 800 hectares of gardens.',
            'highlights' => ['Hall of Mirrors', 'Gardens and fountains', 'Marie-Antoinette’s estate'],
            'best_season' => 'Apr – Oct',
            'image' => 'wiki:Palace of Versailles',
        ],
        [
            'name' => 'Loire Valley',
            'region' => 'Centre-Val de Loire',
            'tagline' => 'Châteaux and vineyards',
            'description' => 'A UNESCO valley of Renaissance châteaux, vineyards and riverside villages.',
            'highlights' => ['Château de Chambord', 'Château de Chenonceau', 'Wine tasting'],
            'best_season' => 'Apr – Oct',
            'image' => 'wiki:Château de Chambord',
        ],
        [
            'name' => 'Mont-Saint-Michel',
            'region' => 'Normandy',
            'tagline' => 'An abbey on a tidal island',
            'description' => 'A medieval abbey rising from the bay, cut off by some of Europe’s highest tides.',
            'highlights' => ['The abbey', 'Crossing the bay at low tide', 'Normandy cider'],
            'best_season' => 'Apr – Oct',
            'image' => 'wiki:Mont-Saint-Michel',
        ],
    ],
    'hotels' => [
        [
            'name' => 'Hôtel Plaza Athénée',
            'destination' => 'Paris',
            'type' => 'Luxury hotel',
            'stars' => 5,
            'description' => 'Red awnings, Eiffel Tower views and Avenue Montaigne on the doorstep.',
            'address' => '25 Avenue Montaigne, Paris',
            'amenities' => ['Eiffel Tower views', 'Dior spa', 'Michelin dining'],
        ],
        [
            'name' => 'Domaine des Hauts de Loire',
            'destination' => 'Loire Valley',
            'type' => 'Heritage hotel',
            'stars' => 5,
            'description' => 'A 19th-century hunting lodge in its own park near Amboise.',
            'address' => 'Onzain, Loire Valley',
            'amenities' => ['Park', 'Pool', 'Wine cellar'],
        ],
    ],
    'packages' => [
        [
            'title' => 'Paris in Love',
            'category' => 'Romantic escapes',
            'category_tagline' => 'Honeymoons and trips for two',
            'vibes' => ['Romantic', 'City breaks', 'Food & culture'],
            'featured' => true,
            'summary' => 'Five days with a Seine dinner cruise, the Louvre after hours and a private Montmartre walk.',
            'overview' => 'Paris done for two: a hotel with Eiffel Tower views, a private guide for the Louvre, an evening on the Seine and a morning in Montmartre before the crowds.',
            'price' => 2390,
            'start_city' => 'Paris',
            'best_season' => 'Apr – Jun, Sep – Oct',
            'image' => 'wiki:Eiffel Tower',
            'destinations' => ['Paris', 'Versailles'],
            'highlights' => [
                'Seine dinner cruise',
                'Private Louvre tour',
                'Versailles with skip-the-line tickets',
                'Sunrise in Montmartre',
            ],
            'extras' => [
                [
                    'name' => 'Moulin Rouge show',
                    'description' => 'Evening show with champagne.',
                    'price' => 190,
                    'per' => 'person',
                ],
                [
                    'name' => 'Private photoshoot at the Eiffel Tower',
                    'description' => 'One hour with a Paris photographer.',
                    'price' => 320,
                    'per' => 'booking',
                ],
            ],
            'itinerary' => [
                ['Arrival in Paris', 'Private transfer and evening Seine dinner cruise.', 'Hôtel Plaza Athénée', 'Dinner'],
                ['The Louvre and Marais', 'Private Louvre tour, then the Marais.', 'Hôtel Plaza Athénée', 'Breakfast'],
                ['Versailles', 'Palace and gardens with skip-the-line entry.', 'Hôtel Plaza Athénée', 'Breakfast'],
                ['Montmartre', 'Sunrise walk in Montmartre and a free afternoon.', 'Hôtel Plaza Athénée', 'Breakfast'],
                ['Departure', 'Private transfer to the airport.', null, 'Breakfast'],
            ],
        ],
        [
            'title' => 'Paris, Loire Châteaux & Mont-Saint-Michel',
            'category' => 'Heritage & culture',
            'vibes' => ['Heritage & history', 'Countryside', 'Food & culture'],
            'summary' => 'Eight days from the boulevards of Paris to Renaissance châteaux and the abbey in the bay.',
            'overview' => 'A private road trip through the heart of France, with wine tastings in the Loire and a night beside Mont-Saint-Michel.',
            'price' => 3190,
            'tiers' => [
                [
                    'min' => 6,
                    'price' => 2950,
                ],
            ],
            'start_city' => 'Paris',
            'best_season' => 'Apr – Oct',
            'image' => 'wiki:Château de Chambord',
            'destinations' => ['Paris', 'Loire Valley', 'Mont-Saint-Michel'],
            'highlights' => ['Chambord and Chenonceau', 'Loire wine tasting', 'Mont-Saint-Michel at low tide', 'Paris highlights'],
            'itinerary' => [
                ['Arrival in Paris', 'Private transfer.', 'Hôtel Plaza Athénée', 'Dinner'],
                ['Paris', 'Eiffel Tower, Louvre and a Seine cruise.', 'Hôtel Plaza Athénée', 'Breakfast'],
                ['To the Loire', 'Château de Chambord on the way.', 'Domaine des Hauts de Loire', 'Breakfast, Dinner'],
                ['Loire Valley', 'Chenonceau and a vineyard tasting.', 'Domaine des Hauts de Loire', 'Breakfast'],
                ['To Normandy', 'Drive to Mont-Saint-Michel.', null, 'Breakfast, Dinner'],
                ['Mont-Saint-Michel', 'The abbey and a guided walk across the bay.', null, 'Breakfast'],
                ['Back to Paris', 'Return to Paris via Rouen.', 'Hôtel Plaza Athénée', 'Breakfast'],
                ['Departure', 'Transfer to the airport.', null, 'Breakfast'],
            ],
        ],
    ],
];
