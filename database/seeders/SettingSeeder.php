<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::put([
            'site_name' => 'Safarnama',
            'site_tagline' => 'Private journeys through Pakistan',
            'hero_eyebrow' => 'Private journeys through Pakistan, from Canada and beyond',
            'hero_title' => 'Pakistan, the way it deserves to be seen',
            'hero_subtitle' => 'Mughal Lahore, the Karakoram, the gurdwaras of Guru Nanak and the dunes of Cholistan. We meet you at the airport, handle every permit and stay, and travel with you the whole way.',
            'hero_image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/BADSHAHI_MOSQUE_LAHORE.jpg?width=2400',
            'story_title' => 'Hosted by people who call this country home',
            'story_body' => 'Safarnama means "travelogue". For over a decade our Lahore-based team has guided visitors from Canada, the UK and the Gulf through Pakistan: diaspora families returning to ancestral villages, Sikh sangat visiting the gurdwaras of Guru Nanak, and first-timers who arrive curious and leave planning their next trip. Every journey is private, every guide is licensed, and every hotel is one we have stayed in ourselves.',
            'story_image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Nanga_Parbat_%28Fairy_Meadows%29.jpg?width=1600',
            'stat_1' => '1,200+|guests hosted since 2014',
            'stat_2' => '38|cities and valleys covered',
            'stat_3' => '4.9/5|average guest rating',
            'about_title' => 'A Pakistan travel company built for visitors from abroad',
            'about_body' => "We started Safarnama after years of helping relatives from Toronto and Vancouver plan trips home. The questions were always the same: Is it safe? Which hotels are good? How do we get the visa? Who will meet us at the airport?\n\nToday we answer those questions for travellers from around the world. We handle e-visa guidance, airport meet-and-greet, private air-conditioned transport, licensed guides, and hand-picked stays from Mughal-era havelis to lodges on the Karakoram Highway.\n\nWe are registered with the Department of Tourist Services and work only with vetted drivers and hotels. Our team in Canada keeps Eastern-time office hours, so you can plan with a real person before you fly.",
            'about_image' => 'https://commons.wikimedia.org/wiki/Special:FilePath/K2_2006b.jpg?width=1600',
            'contact_phone' => '+92 42 3570 0000',
            'contact_phone_ca' => '+1 (416) 555-0142',
            'contact_whatsapp' => '923000000000',
            'contact_email' => 'hello@safarnama.travel',
            'contact_address' => "14-B Main Boulevard, Gulberg III\nLahore, Punjab, Pakistan",
            'office_hours' => 'Mon–Sat, 9am–7pm PKT · Toronto desk 9am–5pm ET',
            'base_currency' => 'USD',
            'default_currency' => 'USD',
            'secondary_currency' => 'CAD',
            'currency_switcher' => '1',
            'deposit_percent' => '25',
            'theme_preset' => 'pakistan',
            'mail_enabled' => '1',
            'mail_driver' => 'php',
            'mail_from_name' => 'Safarnama',
            'mail_from_address' => 'hello@safarnama.travel',
            'social_instagram' => 'https://instagram.com/',
            'social_facebook' => 'https://facebook.com/',
            'social_youtube' => 'https://youtube.com/',
        ]);
    }
}
