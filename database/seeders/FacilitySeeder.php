<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Language\Models\LanguageMeta;
use Botble\RealEstate\Models\Facility;
use Botble\RealEstate\Models\Property;
use Illuminate\Support\Facades\DB;

class FacilitySeeder extends BaseSeeder
{
    public function run()
    {
        Facility::truncate();
        DB::table('re_facilities_translations')->truncate();
        LanguageMeta::where('reference_type', Facility::class)->delete();

        $facilities = [
            [
                'name' => 'Hospital',
                'icon' => 'far fa-hospital',
            ],
            [
                'name' => 'Super Market',
                'icon' => 'fas fa-cart-plus',
            ],
            [
                'name' => 'School',
                'icon' => 'fas fa-school',
            ],
            [
                'name' => 'Entertainment',
                'icon' => 'fas fa-hotel',
            ],
            [
                'name' => 'Pharmacy',
                'icon' => 'fas fa-prescription-bottle-alt',
            ],
            [
                'name' => 'Airport',
                'icon' => 'fas fa-plane-departure',
            ],
            [
                'name' => 'Railways',
                'icon' => 'fas fa-subway',
            ],
            [
                'name' => 'Bus Stop',
                'icon' => 'fas fa-bus',
            ],
            [
                'name' => 'Beach',
                'icon' => 'fas fa-umbrella-beach',
            ],
            [
                'name' => 'Mall',
                'icon' => 'fas fa-cart-plus',
            ],
            [
                'name' => 'Bank',
                'icon' => 'fas fa-university',
            ],
        ];

        foreach ($facilities as $facility) {
            Facility::create($facility);
        }

        foreach (Property::get() as $property) {
            $property->facilities()->detach();
            for ($i = 1; $i < 12; $i++) {
                $property->facilities()->attach($i, ['distance' => rand(1, 20) . 'km']);
            }
        }

        $translations = [
            [
                'name' => 'B???nh vi???n',
            ],
            [
                'name' => 'Si??u th???',
            ],
            [
                'name' => 'Tr?????ng h???c',
            ],
            [
                'name' => 'Trung t??m gi???i tr??',
            ],
            [
                'name' => 'Hi???u thu???c',
            ],
            [
                'name' => 'S??n bay',
            ],
            [
                'name' => 'T??u ??i???n ng???m',
            ],
            [
                'name' => 'Tr???m xe bu??t',
            ],
            [
                'name' => 'B??i bi???n',
            ],
            [
                'name' => 'Trung t??m mua s???m',
            ],
            [
                'name' => 'Ng??n h??ng',
            ],
        ];

        foreach ($translations as $index => $item) {
            $item['lang_code'] = 'vi';
            $item['re_facilities_id'] = $index + 9;

            DB::table('re_facilities_translations')->insert($item);
        }
    }
}
