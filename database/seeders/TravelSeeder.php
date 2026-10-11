<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\Hotel;
use App\Models\Package;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sample content so the site looks complete on first run.
 * Everything here is editable from the admin panel. Images point at verified
 * Wikimedia Commons files; `php artisan images:import` (also run by
 * `npm install` and Admin → Media library) copies them to local storage.
 */
class TravelSeeder extends Seeder
{
    private function img(string $file, int $width = 1600): string
    {
        return 'https://commons.wikimedia.org/wiki/Special:FilePath/'.rawurlencode($file).'?width='.$width;
    }

    public function run(): void
    {
        // ── Categories ──────────────────────────────────────────────
        $categories = collect([
            ['Mughal & Heritage', 'Forts, mosques and five thousand years of history', 'From the Indus cities of Mohenjo-daro and Taxila to the Mughal splendour of Lahore Fort and the Badshahi Mosque.', 'Lahore_Fort_view_from_Baradari.jpg'],
            ['Sikh Yatra', 'The gurdwaras of Guru Nanak Dev Ji', 'Nankana Sahib, Kartarpur, Panja Sahib and Dera Sahib — guided with care for sangat travelling from Canada, the UK and the US.', 'The_Gurdwara_Janam_Asthan_in_Nankana_Sahib,_Pakistan._01.jpg'],
            ['Northern Highlands', 'The Karakoram, Hunza and the eight-thousanders', 'Glacier lakes, apricot orchards and the highest paved border crossing in the world.', 'Attabad_Lake,_Hunza.jpg'],
            ['Desert & Cholistan', 'Nawabi palaces and the forts of the Thar', 'Bahawalpur’s palaces, Derawar Fort at sunset and a night under the stars in a private desert camp.', 'Derawar_Fort,_Cholistan,_Bahawalpur.jpg'],
            ['Coast & Marine', 'The Makran coast and the Arabian Sea', 'Mud volcanoes, the Princess of Hope, the empty beaches of Kund Malir and turtles at Astola Island.', 'Kund_Malir_Beach,_Balochistan.jpg'],
            ['Sufi & Spiritual', 'Shrines, qawwali and living tradition', 'The shrines of Multan, Uch Sharif and Sehwan — with Thursday-night qawwali at Data Darbar.', 'Tomb_of_Shah_Rukn-e-Alam,_Multan,_Pakistan_2016.jpg'],
        ])->mapWithKeys(fn ($c, $i) => [$c[0] => Category::create([
            'name' => $c[0], 'slug' => Str::slug($c[0]), 'tagline' => $c[1], 'description' => $c[2],
            'image' => $this->img($c[3]), 'sort_order' => $i,
        ])]);

        // ── Destinations ────────────────────────────────────────────
        $destinations = collect([
            ['Lahore', 'Punjab', 'The cultural heart of Pakistan', 'Lahore is Mughal gardens, the Walled City’s food streets and the Badshahi Mosque glowing at dusk. It is also home to Gurdwara Dera Sahib and the Samadhi of Maharaja Ranjit Singh.', 'BADSHAHI_MOSQUE_LAHORE.jpg', ['Lahore Fort & Sheesh Mahal', 'Badshahi Mosque', 'Walled City food walk', 'Wagah border ceremony', 'Gurdwara Dera Sahib'], 'Oct – Mar', true],
            ['Nankana Sahib', 'Punjab', 'Birthplace of Guru Nanak Dev Ji', 'A small Punjabi city, and the holiest place in Sikhism. Gurdwara Janam Asthan marks where Guru Nanak was born in 1469, and eight other gurdwaras around it mark episodes from his childhood.', 'The_Gurdwara_Janam_Asthan_in_Nankana_Sahib,_Pakistan._01.jpg', ['Gurdwara Janam Asthan', 'Gurdwara Bal Lila', 'Gurdwara Tambu Sahib', 'Gurpurab celebrations'], 'Nov (Gurpurab) & Apr (Vaisakhi)', true],
            ['Kartarpur', 'Punjab', 'Where Guru Nanak spent his final years', 'Gurdwara Darbar Sahib Kartarpur stands on the banks of the Ravi, where Guru Nanak farmed and taught for eighteen years. It is one of the largest gurdwara complexes in the world.', 'Gurdwara_Darbar_Sahib_Kartarpur_04.jpg', ['Gurdwara Darbar Sahib', 'Guru Nanak’s fields', 'Langar hall'], 'Oct – Apr', false],
            ['Hasan Abdal', 'Punjab', 'Gurdwara Panja Sahib', 'Gurdwara Panja Sahib holds the rock said to bear the hand-print of Guru Nanak. It sits beside a spring on the old road between Taxila and Peshawar.', 'Gudwara_Panja_Sahib_-_Flickr_-_Al_Jazeera_English_(2).jpg', ['Gurdwara Panja Sahib', 'The sacred spring', 'Vaisakhi mela'], 'Year-round', false],
            ['Islamabad', 'Islamabad Capital', 'A green capital beneath the Margalla Hills', 'Pakistan’s calm, leafy capital is usually where journeys north begin. The Faisal Mosque, Pakistan Monument and the hilltop viewpoints of Daman-e-Koh are all minutes apart.', 'Faisal_Mosque,_Islamabad_III.jpg', ['Faisal Mosque', 'Pakistan Monument', 'Daman-e-Koh', 'Saidpur Village'], 'Year-round', false],
            ['Taxila', 'Punjab', 'Gandharan cities and Buddhist monasteries', 'A UNESCO World Heritage Site where three ancient cities and dozens of Buddhist stupas trace a thousand years of Gandharan civilisation.', 'Dharmarajika_stupa,Taxila.jpg', ['Dharmarajika Stupa', 'Sirkap', 'Jaulian Monastery', 'Taxila Museum'], 'Oct – Apr', false],
            ['Hunza Valley', 'Gilgit-Baltistan', 'Apricot orchards beneath Rakaposhi', 'Hunza is the jewel of the Karakoram Highway: thousand-year-old forts, turquoise Attabad Lake and views of four peaks above 7,000 metres from a single village.', 'Attabad_Lake,_Hunza.jpg', ['Baltit & Altit Forts', 'Attabad Lake', 'Passu Cones', 'Khunjerab Pass'], 'Apr – Oct', true],
            ['Skardu', 'Gilgit-Baltistan', 'Gateway to K2 and the Deosai Plains', 'Skardu sits where the Indus widens into a high, sandy valley. From here roads lead to the cold desert of Katpana, Shangrila’s lakes and the wildflower plateau of Deosai.', 'Shangrila_Resort,_Lower_Kachura_Lake,_Pakistan.jpg', ['Shangrila & Upper Kachura Lakes', 'Deosai National Park', 'Katpana cold desert', 'Shigar Fort'], 'May – Sep', true],
            ['Fairy Meadows', 'Gilgit-Baltistan', 'Face to face with Nanga Parbat', 'A high alpine meadow reached by jeep and a short hike, with an uninterrupted view of the north face of Nanga Parbat, the ninth-highest mountain on earth.', 'Nanga_Parbat_(Fairy_Meadows).jpg', ['Nanga Parbat viewpoint', 'Beyal Camp hike', 'Raikot Bridge jeep road'], 'Jun – Sep', false],
            ['Bahawalpur & Cholistan', 'Punjab', 'Palaces of the Nawabs and forts of the desert', 'Bahawalpur’s Noor Mahal and Darbar Mahal recall the princely state’s grandeur. Beyond the city, the Cholistan desert hides Derawar Fort and the yearly Cholistan Jeep Rally.', 'Derawar_Fort,_Cholistan,_Bahawalpur.jpg', ['Noor Mahal', 'Derawar Fort', 'Desert camp under the stars', 'Uch Sharif shrines'], 'Nov – Feb', true],
            ['Multan', 'Punjab', 'The City of Saints', 'One of the oldest living cities in South Asia, famous for blue-tiled Sufi shrines, Multani pottery and the tomb of Shah Rukn-e-Alam.', 'Tomb_of_Shah_Rukn-e-Alam,_Multan,_Pakistan_2016.jpg', ['Shah Rukn-e-Alam', 'Bahauddin Zakariya shrine', 'Blue pottery workshops'], 'Nov – Feb', false],
            ['Makran Coast', 'Balochistan', 'Where the desert meets the Arabian Sea', 'The Makran Coastal Highway runs past wind-carved cliffs, the Princess of Hope rock formation and the near-empty sands of Kund Malir before reaching Gwadar.', 'Kund_Malir_Beach,_Balochistan.jpg', ['Hingol National Park', 'Princess of Hope', 'Kund Malir beach', 'Gwadar Hammerhead'], 'Oct – Mar', true],
            ['Karachi', 'Sindh', 'Pakistan’s city by the sea', 'A vast, energetic port city with colonial architecture, the Quaid’s Mausoleum and some of the best seafood in the country.', 'Mazar-e-Quaid_Karachi.jpg', ['Mazar-e-Quaid', 'Mohatta Palace', 'Clifton Beach', 'Burns Road food street'], 'Nov – Feb', false],
            ['Mohenjo-daro', 'Sindh', 'A 4,500-year-old city of the Indus', 'One of the world’s earliest planned cities, with a great bath, granaries and grid streets laid out around 2500 BCE.', 'Mohenjo-daro.jpg', ['The Great Bath', 'Stupa mound', 'Site museum'], 'Nov – Feb', false],
        ])->mapWithKeys(fn ($d, $i) => [$d[0] => Destination::create([
            'name' => $d[0], 'slug' => Str::slug($d[0]), 'region' => $d[1], 'tagline' => $d[2], 'description' => $d[3],
            'image' => $this->img($d[4]), 'highlights' => $d[5], 'best_season' => $d[6], 'is_featured' => $d[7], 'sort_order' => $i,
        ])]);

        // ── Stays ───────────────────────────────────────────────────
        $hotels = collect([
            ['Pearl-Continental Lahore', 'Lahore', 'Luxury hotel', 5, 'A landmark five-star hotel on The Mall, close to the Walled City and the Lahore Gymkhana.', 'Shara-e-Quaid-e-Azam, Lahore', ['Pool', 'Spa', 'Airport transfer', 'Halal & vegetarian dining']],
            ['Haveli Heritage Rooms', 'Lahore', 'Heritage hotel', 4, 'A restored Mughal-era haveli in the Walled City with a rooftop facing the Badshahi Mosque.', 'Walled City, Lahore', ['Rooftop dining', 'Courtyard', 'Guided old-city walks']],
            ['Islamabad Serena Hotel', 'Islamabad', 'Luxury hotel', 5, 'A garden hotel at the foot of the Margalla Hills, known for its Islamic-inspired architecture.', 'Khayaban-e-Suhrwardy, Islamabad', ['Pool', 'Spa', 'Fitness centre', 'Business lounge']],
            ['Nankana Sahib Yatri Niwas', 'Nankana Sahib', 'Guesthouse', 3, 'Clean, simple rooms within walking distance of Gurdwara Janam Asthan, arranged for visiting sangat.', 'Gurdwara Janam Asthan Road, Nankana Sahib', ['Walk to gurdwara', 'Vegetarian meals', 'Family rooms']],
            ['Luxus Hunza Attabad Lake Resort', 'Hunza Valley', 'Mountain lodge', 5, 'Glass-fronted rooms right on the turquoise water of Attabad Lake, with boat access and mountain views from every bed.', 'Attabad Lake, Gojal, Hunza', ['Lake-view rooms', 'Boating', 'Heated rooms', 'Restaurant']],
            ['Hunza Serena Inn', 'Hunza Valley', 'Boutique hotel', 4, 'Terraced gardens in Karimabad, a short walk from Baltit Fort, with uninterrupted views of Rakaposhi.', 'Karimabad, Hunza', ['Garden', 'Mountain views', 'Local cuisine']],
            ['Shangrila Resort Skardu', 'Skardu', 'Mountain lodge', 4, 'The famous lakeside resort at Lower Kachura, with pagoda-style chalets and a red-roofed restaurant on the water.', 'Lower Kachura Lake, Skardu', ['Lakeside chalets', 'Gardens', 'Boating']],
            ['Fairy Meadows Cottages', 'Fairy Meadows', 'Mountain lodge', 3, 'Wooden cottages on the meadow with front-row views of Nanga Parbat at sunrise.', 'Fairy Meadows, Diamer', ['Nanga Parbat views', 'Bonfire evenings', 'Hot meals']],
            ['Darbar Mahal Guest Wing', 'Bahawalpur & Cholistan', 'Heritage hotel', 4, 'Rooms in the style of the Nawabi era, a short drive from Noor Mahal.', 'Bahawalpur', ['Heritage interiors', 'Courtyard dining']],
            ['Cholistan Luxury Desert Camp', 'Bahawalpur & Cholistan', 'Desert camp', 4, 'Private canvas tents with proper beds and en-suite bathrooms, set in the dunes within sight of Derawar Fort.', 'Near Derawar Fort, Cholistan', ['En-suite tents', 'Bonfire & folk music', 'Camel rides', 'Stargazing']],
            ['Gwadar Pearl-Continental', 'Makran Coast', 'Beach resort', 5, 'On Koh-e-Batil above the bay, with sweeping views across the Arabian Sea.', 'Koh-e-Batil, Gwadar', ['Sea views', 'Pool', 'Seafood restaurant']],
            ['Kund Malir Beach Huts', 'Makran Coast', 'Guesthouse', 3, 'Simple, comfortable beach huts on the sand at Kund Malir, with dinner cooked over the fire.', 'Kund Malir, Hingol', ['Beachfront', 'Fresh seafood']],
            ['Mövenpick Karachi', 'Karachi', 'Luxury hotel', 5, 'A central five-star hotel near Club Road, ideal for arrival and departure nights.', 'Club Road, Karachi', ['Pool', 'Airport transfer', 'Spa']],
        ])->mapWithKeys(fn ($h) => [$h[0] => Hotel::create([
            'name' => $h[0], 'slug' => Str::slug($h[0]), 'destination_id' => $destinations[$h[1]]->id, 'type' => $h[2],
            'stars' => $h[3], 'description' => $h[4], 'address' => $h[5], 'amenities' => $h[6],
            'image' => $destinations[$h[1]]->image,
        ])]);

        $commonInclusions = [
            'Airport meet-and-greet with flower garland',
            'Private air-conditioned vehicle and driver',
            'Licensed English & Punjabi-speaking guide',
            'Hotel accommodation as listed (twin sharing)',
            'Daily breakfast and meals as noted',
            'All monument entrance fees and permits',
            '24/7 WhatsApp support from our Lahore and Toronto teams',
        ];
        $commonExclusions = ['International flights', 'Pakistan e-visa fee (we help you apply)', 'Travel insurance', 'Tips and personal expenses'];

        // ── Packages ────────────────────────────────────────────────
        $packages = [
            [
                'title' => 'Footsteps of Guru Nanak: Sikh Yatra',
                'category' => 'Sikh Yatra',
                'summary' => 'A nine-day pilgrimage to Nankana Sahib, Kartarpur, Panja Sahib and the gurdwaras of Lahore, with comfortable stays and a Punjabi-speaking guide.',
                'overview' => "Designed for Sikh families and sangat travelling from Canada, the UK and the US, this yatra visits the gurdwaras most closely linked to Guru Nanak Dev Ji and Maharaja Ranjit Singh.\n\nWe handle the paperwork for visits to every gurdwara, plan around ardas and langar times, and keep driving days short. Departures can be timed for Gurpurab in November or Vaisakhi in April.",
                'days' => 9, 'price' => 2450, 'original' => 2790, 'group' => 'Private · 2–20 guests', 'difficulty' => 'Easy',
                'season' => 'Oct – Apr', 'start' => 'Lahore', 'image' => 'The_Gurdwara_Janam_Asthan_in_Nankana_Sahib,_Pakistan._01.jpg', 'featured' => true, 'rating' => 5.0,
                'destinations' => ['Lahore', 'Nankana Sahib', 'Kartarpur', 'Hasan Abdal', 'Islamabad'],
                'highlights' => ['Ardas at Gurdwara Janam Asthan, Nankana Sahib', 'A full day at Kartarpur’s Gurdwara Darbar Sahib', 'Panja Sahib and the sacred spring at Hasan Abdal', 'Samadhi of Maharaja Ranjit Singh and Gurdwara Dera Sahib', 'Optional visit to your ancestral village'],
                'itinerary' => [
                    ['Arrival in Lahore', 'Our team meets you at Allama Iqbal International Airport with garlands and drives you to your hotel. The rest of the day is free to rest.', 'Pearl-Continental Lahore', 'Dinner'],
                    ['Gurdwara Dera Sahib & the Walled City', 'Morning at Gurdwara Dera Sahib, where Guru Arjan Dev Ji attained martyrdom, and the Samadhi of Maharaja Ranjit Singh. Afternoon at Lahore Fort and the Badshahi Mosque.', 'Pearl-Continental Lahore', 'Breakfast, Lunch'],
                    ['To Nankana Sahib', 'A two-hour drive to the birthplace of Guru Nanak. Evening ardas at Gurdwara Janam Asthan.', 'Nankana Sahib Yatri Niwas', 'Breakfast, Dinner'],
                    ['The gurdwaras of Nankana', 'A full day visiting Gurdwara Bal Lila, Patti Sahib, Kiara Sahib, Mal Ji Sahib and Tambu Sahib, each marking a moment from Guru Ji’s childhood. Langar at Janam Asthan.', 'Nankana Sahib Yatri Niwas', 'All meals'],
                    ['Eminabad & Kartarpur', 'Visit Gurdwara Rori Sahib at Eminabad, then continue to Narowal. Afternoon and evening at Gurdwara Darbar Sahib Kartarpur.', 'Pearl-Continental Lahore', 'Breakfast, Lunch'],
                    ['Free day or ancestral village', 'Rest, shop in Anarkali, or let us take you to your family’s ancestral village. Our researchers can help trace the location before you travel.', 'Pearl-Continental Lahore', 'Breakfast'],
                    ['To Hasan Abdal', 'Drive the Grand Trunk Road north, stopping at Rohtas Fort. Evening at Gurdwara Panja Sahib.', 'Islamabad Serena Hotel', 'Breakfast, Dinner'],
                    ['Panja Sahib & Islamabad', 'Morning ardas at Panja Sahib, then return to Islamabad for the Faisal Mosque and a farewell dinner at Daman-e-Koh.', 'Islamabad Serena Hotel', 'Breakfast, Dinner'],
                    ['Departure', 'Transfer to Islamabad International Airport for your flight home.', null, 'Breakfast'],
                ],
            ],
            [
                'title' => 'Hunza & the Karakoram Highway',
                'category' => 'Northern Highlands',
                'summary' => 'Ten days up the Karakoram Highway to Hunza, Attabad Lake and the Khunjerab Pass on the China border, with a night facing Nanga Parbat.',
                'overview' => "The Karakoram Highway is one of the great road journeys on earth. We drive it slowly, with stops at petroglyphs, the confluence of three mountain ranges and the best viewpoints of Rakaposhi.\n\nStays include a lakeside resort on Attabad Lake and a garden inn in Karimabad. Flights between Islamabad and Gilgit can be added on request to shorten the drive.",
                'days' => 10, 'price' => 2890, 'original' => null, 'group' => 'Private · 2–12 guests', 'difficulty' => 'Moderate',
                'season' => 'Apr – Oct', 'start' => 'Islamabad', 'image' => 'Attabad_Lake,_Hunza.jpg', 'featured' => true, 'rating' => 4.9,
                'destinations' => ['Islamabad', 'Fairy Meadows', 'Hunza Valley'],
                'highlights' => ['Sunrise over Nanga Parbat from Fairy Meadows', 'Baltit and Altit forts in Karimabad', 'Boat ride on Attabad Lake', 'Khunjerab Pass at 4,693 m', 'Passu Cones and the Hussaini suspension bridge'],
                'itinerary' => [
                    ['Arrival in Islamabad', 'Airport welcome and transfer. Evening drive up to Daman-e-Koh for city views.', 'Islamabad Serena Hotel', 'Dinner'],
                    ['Islamabad to Chilas', 'Drive through the Kaghan Valley and over Babusar Pass (seasonal) to the Indus at Chilas.', null, 'Breakfast, Dinner'],
                    ['Jeep to Fairy Meadows', 'A jeep ride up the Raikot road and an easy two-hour walk to the meadow. Evening light on Nanga Parbat.', 'Fairy Meadows Cottages', 'All meals'],
                    ['Beyal Camp hike', 'Optional walk towards Beyal Camp and the Nanga Parbat viewpoint, or a lazy day on the meadow.', 'Fairy Meadows Cottages', 'All meals'],
                    ['To Hunza', 'Descend and drive north past the junction of the Himalaya, Karakoram and Hindu Kush. Arrive in Karimabad by evening.', 'Hunza Serena Inn', 'Breakfast, Dinner'],
                    ['Karimabad’s forts', 'Guided visits to Baltit and Altit forts, then the Eagle’s Nest viewpoint for sunset over Rakaposhi and Ultar.', 'Hunza Serena Inn', 'Breakfast, Dinner'],
                    ['Attabad Lake & Passu', 'Boat ride on Attabad Lake, the Hussaini bridge and the Passu Cones.', 'Luxus Hunza Attabad Lake Resort', 'Breakfast, Dinner'],
                    ['Khunjerab Pass', 'A day trip to the China border at 4,693 m through Khunjerab National Park, home to Marco Polo sheep and ibex.', 'Luxus Hunza Attabad Lake Resort', 'Breakfast, Dinner'],
                    ['Fly or drive to Islamabad', 'Gilgit to Islamabad by air (weather permitting) or a scenic drive. Farewell dinner.', 'Islamabad Serena Hotel', 'Breakfast, Dinner'],
                    ['Departure', 'Transfer to the airport.', null, 'Breakfast'],
                ],
            ],
            [
                'title' => 'Mughal Lahore & the Grand Trunk Road',
                'category' => 'Mughal & Heritage',
                'summary' => 'Six days of forts, mosques, gardens and food: Lahore’s Walled City, Rohtas Fort, Gandharan Taxila and the capital.',
                'overview' => 'A compact heritage journey for first-time visitors. You will stay in a restored haveli inside Lahore’s Walled City, eat your way down the food streets, and follow the Grand Trunk Road north through two thousand years of history.',
                'days' => 6, 'price' => 1690, 'original' => null, 'group' => 'Private · 2–16 guests', 'difficulty' => 'Easy',
                'season' => 'Oct – Mar', 'start' => 'Lahore', 'image' => 'Lahore_Fort_view_from_Baradari.jpg', 'featured' => true, 'rating' => 4.9,
                'destinations' => ['Lahore', 'Taxila', 'Islamabad'],
                'highlights' => ['Sheesh Mahal inside Lahore Fort', 'Rooftop dinner facing the Badshahi Mosque', 'Wagah border flag ceremony', 'Rohtas Fort, a UNESCO site', 'Buddhist stupas of Taxila'],
                'itinerary' => [
                    ['Arrival in Lahore', 'Airport welcome and transfer to your haveli in the Walled City.', 'Haveli Heritage Rooms', 'Dinner'],
                    ['Mughal Lahore', 'Lahore Fort, the Sheesh Mahal, Badshahi Mosque and the painted Wazir Khan Mosque. Evening food walk.', 'Haveli Heritage Rooms', 'Breakfast, Dinner'],
                    ['Shalimar Gardens & Wagah', 'Morning at the Shalimar Gardens, afternoon at the Lahore Museum, then the flag-lowering ceremony at Wagah.', 'Pearl-Continental Lahore', 'Breakfast'],
                    ['Rohtas Fort to Islamabad', 'Up the Grand Trunk Road to Sher Shah Suri’s Rohtas Fort, continuing to Islamabad.', 'Islamabad Serena Hotel', 'Breakfast, Lunch'],
                    ['Taxila', 'Dharmarajika Stupa, Sirkap and Jaulian Monastery, with the Taxila Museum’s Gandharan Buddhas.', 'Islamabad Serena Hotel', 'Breakfast, Dinner'],
                    ['Departure', 'Faisal Mosque on the way to the airport.', null, 'Breakfast'],
                ],
            ],
            [
                'title' => 'Skardu, Deosai & the Road to K2',
                'category' => 'Northern Highlands',
                'summary' => 'Eight days in Baltistan: lakes, a cold desert, a royal fort and the wildflower plains of Deosai, the “Land of Giants”.',
                'overview' => 'Fly into Skardu on one of the world’s most spectacular mountain flights, then explore Baltistan from a lakeside base. Deosai, at over 4,000 metres, is home to Himalayan brown bears and blankets of wildflowers in July.',
                'days' => 8, 'price' => 2650, 'original' => null, 'group' => 'Private · 2–10 guests', 'difficulty' => 'Moderate',
                'season' => 'May – Sep', 'start' => 'Islamabad', 'image' => 'K2_2006b.jpg', 'featured' => false, 'rating' => 4.8,
                'destinations' => ['Islamabad', 'Skardu'],
                'highlights' => ['Mountain flight past Nanga Parbat', 'Shangrila and Upper Kachura lakes', 'A full day on the Deosai Plains', 'Shigar Fort and Khaplu Palace', 'Sand dunes of the Katpana cold desert'],
                'itinerary' => [
                    ['Arrival in Islamabad', 'Airport welcome and transfer.', 'Islamabad Serena Hotel', 'Dinner'],
                    ['Fly to Skardu', 'A one-hour flight along the Karakoram. Afternoon at the lakes of Kachura.', 'Shangrila Resort Skardu', 'Breakfast, Dinner'],
                    ['Shigar Fort', 'The 17th-century Shigar Fort, restored by the Aga Khan Trust, and the village of Shigar.', 'Shangrila Resort Skardu', 'Breakfast, Dinner'],
                    ['Deosai Plains', 'A full day by jeep across Deosai to Sheosar Lake, watching for marmots and brown bears.', 'Shangrila Resort Skardu', 'All meals'],
                    ['Khaplu', 'Drive along the Shyok River to Khaplu Palace and the Chaqchan Mosque.', 'Shangrila Resort Skardu', 'Breakfast, Dinner'],
                    ['Katpana & Skardu bazaar', 'Sunrise on the cold desert dunes, then Skardu’s bazaar and Kharpocho Fort.', 'Shangrila Resort Skardu', 'Breakfast, Dinner'],
                    ['Fly to Islamabad', 'Return flight and farewell dinner.', 'Islamabad Serena Hotel', 'Breakfast, Dinner'],
                    ['Departure', 'Transfer to the airport.', null, 'Breakfast'],
                ],
            ],
            [
                'title' => 'Cholistan: Palaces & Desert Forts',
                'category' => 'Desert & Cholistan',
                'summary' => 'Five days from Lahore to the princely city of Bahawalpur and a private camp beneath the walls of Derawar Fort.',
                'overview' => 'Southern Punjab is rarely visited and endlessly rewarding. Explore the Nawabs’ palaces, the shrines of Uch Sharif and the forty bastions of Derawar Fort, then spend a night in a luxury desert camp with folk musicians and a sky full of stars.',
                'days' => 5, 'price' => 1450, 'original' => 1590, 'group' => 'Private · 2–12 guests', 'difficulty' => 'Easy',
                'season' => 'Nov – Feb', 'start' => 'Lahore', 'image' => 'Derawar_Fort,_Cholistan,_Bahawalpur.jpg', 'featured' => true, 'rating' => 4.8,
                'destinations' => ['Lahore', 'Bahawalpur & Cholistan', 'Multan'],
                'highlights' => ['Noor Mahal by night', 'Sunset at Derawar Fort', 'Private desert camp with folk music', 'The tiled tombs of Uch Sharif', 'Multan’s shrines on the way back'],
                'itinerary' => [
                    ['Lahore to Bahawalpur', 'Fly or drive south. Evening at Noor Mahal.', 'Darbar Mahal Guest Wing', 'Breakfast, Dinner'],
                    ['Uch Sharif', 'The blue-tiled tomb of Bibi Jawindi and the shrines of Uch Sharif.', 'Darbar Mahal Guest Wing', 'Breakfast, Dinner'],
                    ['Into the desert', 'Jeep into Cholistan, sunset at Derawar Fort, dinner and folk music at camp.', 'Cholistan Luxury Desert Camp', 'All meals'],
                    ['Multan', 'Camel ride at dawn, then drive to Multan for the shrine of Shah Rukn-e-Alam and the blue-pottery workshops.', 'Pearl-Continental Lahore', 'Breakfast, Lunch'],
                    ['Departure', 'Transfer to Lahore airport.', null, 'Breakfast'],
                ],
            ],
            [
                'title' => 'Makran Coast: Hingol to Gwadar',
                'category' => 'Coast & Marine',
                'summary' => 'Six days along the Makran Coastal Highway — mud volcanoes, the Princess of Hope, empty beaches and Gwadar’s hammerhead peninsula.',
                'overview' => 'Few travellers know Pakistan has 1,000 km of coastline. This road journey from Karachi follows the Arabian Sea through Hingol National Park to Gwadar, with a night in beach huts at Kund Malir.',
                'days' => 6, 'price' => 1890, 'original' => null, 'group' => 'Private · 2–8 guests', 'difficulty' => 'Moderate',
                'season' => 'Oct – Mar', 'start' => 'Karachi', 'image' => 'Kund_Malir_Beach,_Balochistan.jpg', 'featured' => false, 'rating' => 4.9,
                'destinations' => ['Karachi', 'Makran Coast'],
                'highlights' => ['The Princess of Hope and Sphinx of Balochistan', 'Chandragup mud volcano', 'Beach night at Kund Malir', 'Gwadar from Koh-e-Batil', 'Optional boat trip to Astola Island'],
                'itinerary' => [
                    ['Arrival in Karachi', 'Airport welcome, Mazar-e-Quaid and dinner on Burns Road.', 'Mövenpick Karachi', 'Dinner'],
                    ['To Kund Malir', 'Drive the coastal highway through Hingol National Park, stopping at the Chandragup mud volcano.', 'Kund Malir Beach Huts', 'All meals'],
                    ['Princess of Hope', 'Morning on the beach, then the Princess of Hope and the Sphinx rock formations. Continue to Ormara.', null, 'All meals'],
                    ['Gwadar', 'Arrive in Gwadar, sunset from Koh-e-Batil.', 'Gwadar Pearl-Continental', 'Breakfast, Dinner'],
                    ['Gwadar & Astola (optional)', 'Fish harbour, Hammerhead viewpoint, or a boat trip to Astola Island.', 'Gwadar Pearl-Continental', 'Breakfast, Dinner'],
                    ['Fly to Karachi & depart', 'Short flight to Karachi to connect with your international departure.', null, 'Breakfast'],
                ],
            ],
        ];

        foreach ($packages as $i => $p) {
            $package = Package::create([
                'category_id' => $categories[$p['category']]->id,
                'country' => 'PK',
                'title' => $p['title'], 'slug' => Str::slug($p['title']),
                'summary' => $p['summary'], 'overview' => $p['overview'],
                'duration_days' => $p['days'], 'price' => $p['price'], 'original_price' => $p['original'],
                'group_size' => $p['group'], 'difficulty' => $p['difficulty'], 'best_season' => $p['season'],
                'start_city' => $p['start'], 'hero_image' => $this->img($p['image'], 2000),
                'gallery' => collect($p['destinations'])->map(fn ($d) => $destinations[$d]->image)->all(),
                'highlights' => $p['highlights'], 'inclusions' => $commonInclusions, 'exclusions' => $commonExclusions,
                'is_featured' => $p['featured'], 'is_published' => true, 'rating' => $p['rating'], 'sort_order' => $i,
            ]);

            $package->destinations()->sync(collect($p['destinations'])->map(fn ($d) => $destinations[$d]->id));

            foreach ($p['itinerary'] as $n => [$title, $description, $hotel, $meals]) {
                $package->days()->create([
                    'day_number' => $n + 1, 'title' => $title, 'description' => $description,
                    'hotel_id' => $hotel ? $hotels[$hotel]->id : null, 'meals' => $meals,
                ]);
            }
        }

        // ── Prices & options ────────────────────────────────────────
        $commonExtras = [
            ['name' => 'Pakistan SIM card with 30 GB data', 'description' => 'Ready at the airport so you can WhatsApp home straight away.', 'price' => 15, 'per' => 'person'],
            ['name' => 'Private photographer for a day', 'description' => 'A local photographer joins you for one full day of the journey.', 'price' => 250, 'per' => 'booking'],
        ];
        $options = [
            'Footsteps of Guru Nanak: Sikh Yatra' => ['child' => 1450, 'tiers' => [['min' => 6, 'price' => 2290], ['min' => 12, 'price' => 2150]], 'extras' => [
                ['name' => 'Visit to your ancestral village', 'description' => 'An extra day with a private car and a researcher who helps locate the village beforehand.', 'price' => 180, 'per' => 'booking'],
            ]],
            'Hunza & the Karakoram Highway' => ['child' => null, 'tiers' => [['min' => 6, 'price' => 2690]], 'extras' => [
                ['name' => 'Fly Islamabad ↔ Gilgit', 'description' => 'Swap the two long drive days for the scenic mountain flight (weather permitting).', 'price' => 260, 'per' => 'person'],
            ]],
            'Mughal Lahore & the Grand Trunk Road' => ['child' => 990, 'tiers' => [['min' => 8, 'price' => 1550]], 'extras' => [
                ['name' => 'All Lahore nights in the haveli', 'description' => 'Stay every night in the Walled City haveli instead of the city hotel.', 'price' => 140, 'per' => 'person'],
            ]],
        ];
        foreach (Package::all() as $package) {
            $option = $options[$package->title] ?? ['child' => null, 'tiers' => [], 'extras' => []];
            $package->update([
                'child_price' => $option['child'],
                'group_tiers' => $option['tiers'],
                'extras' => [...$option['extras'], ...$commonExtras],
            ]);
        }

        // ── Testimonials ────────────────────────────────────────────
        $byTitle = Package::pluck('id', 'title');
        foreach ([
            ['Harpreet & Jaswinder K.', 'Brampton, Ontario', 'We had dreamed of Nankana Sahib our whole lives. Safarnama took care of everything, and our guide even found the village my grandfather left in 1947. We cried the whole drive back.', 'Footsteps of Guru Nanak: Sikh Yatra'],
            ['Daniel M.', 'Vancouver, BC', 'I was nervous about travelling in Pakistan. Within an hour of landing I wasn’t. The Karakoram Highway was the best road trip of my life, and the Attabad Lake hotel was unreal.', 'Hunza & the Karakoram Highway'],
            ['Ayesha R.', 'Mississauga, Ontario', 'I took my Canadian-born kids to see Lahore properly for the first time. The haveli, the food walk, Wagah — they haven’t stopped talking about it.', 'Mughal Lahore & the Grand Trunk Road'],
            ['Gurdeep S.', 'Surrey, BC', 'Sixteen of us from our gurdwara travelled together for Gurpurab. Every detail was handled with respect. We are already planning Vaisakhi.', 'Footsteps of Guru Nanak: Sikh Yatra'],
            ['Claire & Tom H.', 'Calgary, Alberta', 'The desert camp at Derawar was the most magical night of our travels anywhere. Real beds, a real bathroom, and a sky full of stars.', 'Cholistan: Palaces & Desert Forts'],
        ] as $i => [$name, $location, $quote, $pkg]) {
            Testimonial::create(['name' => $name, 'location' => $location, 'quote' => $quote, 'package_id' => $byTitle[$pkg] ?? null, 'rating' => 5, 'sort_order' => $i]);
        }

        // ── FAQs ────────────────────────────────────────────────────
        foreach ([
            ['Is Pakistan safe to visit?', 'The regions we travel in — Punjab, Islamabad, Gilgit-Baltistan, and our chosen routes in Sindh and Balochistan — are visited by thousands of foreign tourists every year. We monitor advisories daily, travel only with licensed guides and vetted drivers, and adjust routes when needed. You will have a 24/7 WhatsApp line to our operations team.'],
            ['How do I get a visa from Canada?', 'Canadian citizens apply online through Pakistan’s official e-visa portal. Once you book, we send a letter of invitation and walk you through the application. Most visas are issued within 7–10 working days.'],
            ['Can you arrange visits to gurdwaras for Sikh pilgrims?', 'Yes. Our Sikh Yatra journeys are planned around ardas and langar times, with Punjabi-speaking guides. We also arrange group yatras for Gurpurab and Vaisakhi, and can help you trace an ancestral village.'],
            ['Are the journeys private?', 'Every journey is private to your family or group, with your own vehicle and guide. Dates, pace and hotels can all be adjusted.'],
            ['What is included in the price?', 'Prices are per person, twin sharing, and include airport transfers, private transport, a licensed guide, hotels as listed, meals as noted and all entrance fees. International flights and visa fees are not included.'],
            ['How do I pay?', 'After you submit a booking request we confirm availability within 24 hours and send an itinerary with a payment link. A 25% deposit secures your dates, with the balance due 30 days before travel.'],
        ] as $i => [$q, $a]) {
            Faq::create(['question' => $q, 'answer' => $a, 'sort_order' => $i]);
        }
    }
}
