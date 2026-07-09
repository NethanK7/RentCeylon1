<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * "Rent anything and everything." Every category exists in the schema with a
 * feature flag (Global Constraint 07). Per the client's note that everything is
 * released now, all categories ship ENABLED. Each category declares its own
 * typed, filterable attributes — Vehicles gets the richest set (vehicle type,
 * transmission, fuel, seats, brand, year).
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->tree() as $node) {
            $this->createCategory($node);
        }
    }

    private function createCategory(array $node, ?int $parentId = null): void
    {
        $category = Category::updateOrCreate(
            ['slug' => $node['slug']],
            [
                'parent_id' => $parentId,
                'name' => $node['name'],
                'icon' => $node['icon'] ?? null,
                'description' => $node['description'] ?? null,
                'kind' => $node['kind'] ?? 'standard',
                'is_enabled' => $node['is_enabled'] ?? true,
                'sort_order' => $node['sort_order'] ?? 0,
            ]
        );

        foreach ($node['attributes'] ?? [] as $i => $attr) {
            CategoryAttribute::updateOrCreate(
                ['category_id' => $category->id, 'key' => $attr['key']],
                [
                    'label' => $attr['label'],
                    'type' => $attr['type'] ?? 'select',
                    'options' => $attr['options'] ?? null,
                    'unit' => $attr['unit'] ?? null,
                    'is_filterable' => $attr['is_filterable'] ?? true,
                    'is_required' => $attr['is_required'] ?? false,
                    'sort_order' => $i,
                ]
            );
        }

        foreach ($node['children'] ?? [] as $child) {
            $this->createCategory($child, $category->id);
        }
    }

    private function tree(): array
    {
        return [
            [
                'name' => 'Electronics & Gear', 'slug' => 'electronics', 'icon' => 'Camera',
                'sort_order' => 1,
                'description' => 'Cameras, drones, audio, computers and more.',
                'attributes' => [
                    ['key' => 'brand', 'label' => 'Brand', 'type' => 'text', 'is_filterable' => true],
                    ['key' => 'gear_type', 'label' => 'Type', 'type' => 'select',
                        'options' => ['Camera', 'Lens', 'Drone', 'Audio', 'Gaming', 'Computer', 'Projector', 'Other']],
                ],
                'children' => [
                    ['name' => 'Cameras', 'slug' => 'cameras', 'icon' => 'Camera'],
                    ['name' => 'Drones', 'slug' => 'drones', 'icon' => 'Plane'],
                    ['name' => 'Audio & DJ', 'slug' => 'audio-dj', 'icon' => 'Speaker'],
                    ['name' => 'Gaming', 'slug' => 'gaming', 'icon' => 'Gamepad2'],
                    ['name' => 'Computers', 'slug' => 'computers', 'icon' => 'Laptop'],
                ],
            ],
            [
                'name' => 'Vehicles', 'slug' => 'vehicles', 'icon' => 'Car', 'kind' => 'vehicle',
                'sort_order' => 2,
                'description' => 'Cars, vans, SUVs, motorbikes, tuk-tuks, lorries and more.',
                'attributes' => [
                    ['key' => 'vehicle_type', 'label' => 'Vehicle type', 'type' => 'select', 'is_required' => true,
                        'options' => ['Car', 'Van', 'SUV', 'Motorbike', 'Scooter', 'Tuk-tuk', 'Bicycle', 'Lorry', 'Bus', 'Pickup', 'Jeep']],
                    ['key' => 'transmission', 'label' => 'Transmission', 'type' => 'select',
                        'options' => ['Automatic', 'Manual']],
                    ['key' => 'fuel_type', 'label' => 'Fuel', 'type' => 'select',
                        'options' => ['Petrol', 'Diesel', 'Hybrid', 'Electric', 'None']],
                    ['key' => 'seats', 'label' => 'Seats', 'type' => 'number', 'unit' => 'seats'],
                    ['key' => 'brand', 'label' => 'Brand', 'type' => 'text'],
                    ['key' => 'year', 'label' => 'Year', 'type' => 'number'],
                    ['key' => 'with_driver', 'label' => 'With driver', 'type' => 'boolean'],
                    ['key' => 'ac', 'label' => 'Air conditioning', 'type' => 'boolean'],
                ],
                'children' => [
                    ['name' => 'Cars', 'slug' => 'cars', 'icon' => 'Car'],
                    ['name' => 'Vans', 'slug' => 'vans', 'icon' => 'Truck'],
                    ['name' => 'SUVs & Jeeps', 'slug' => 'suvs', 'icon' => 'Car'],
                    ['name' => 'Motorbikes', 'slug' => 'motorbikes', 'icon' => 'Bike'],
                    ['name' => 'Tuk-tuks', 'slug' => 'tuktuks', 'icon' => 'Car'],
                    ['name' => 'Bicycles', 'slug' => 'bicycles', 'icon' => 'Bike'],
                    ['name' => 'Lorries & Trucks', 'slug' => 'lorries', 'icon' => 'Truck'],
                ],
            ],
            [
                'name' => 'Tools & Equipment', 'slug' => 'tools', 'icon' => 'Wrench',
                'sort_order' => 3,
                'attributes' => [
                    ['key' => 'tool_type', 'label' => 'Type', 'type' => 'select',
                        'options' => ['Power tool', 'Hand tool', 'Generator', 'Construction', 'Gardening', 'Cleaning', 'Other']],
                    ['key' => 'power_source', 'label' => 'Power', 'type' => 'select',
                        'options' => ['Electric', 'Petrol', 'Battery', 'Manual']],
                ],
            ],
            [
                'name' => 'Events & Party', 'slug' => 'events', 'icon' => 'PartyPopper',
                'sort_order' => 4,
                'attributes' => [
                    ['key' => 'event_type', 'label' => 'Type', 'type' => 'select',
                        'options' => ['Sound system', 'Lighting', 'Tent / Marquee', 'Furniture', 'Decor', 'Catering gear', 'Other']],
                    ['key' => 'capacity', 'label' => 'Capacity', 'type' => 'number', 'unit' => 'people'],
                ],
            ],
            [
                'name' => 'Sports & Outdoor', 'slug' => 'sports', 'icon' => 'Tent',
                'sort_order' => 5,
                'attributes' => [
                    ['key' => 'activity', 'label' => 'Activity', 'type' => 'select',
                        'options' => ['Camping', 'Water sports', 'Cycling', 'Fishing', 'Fitness', 'Team sports', 'Other']],
                ],
            ],
            [
                'name' => 'Home & Appliances', 'slug' => 'home', 'icon' => 'Sofa',
                'sort_order' => 6,
                'attributes' => [
                    ['key' => 'appliance_type', 'label' => 'Type', 'type' => 'select',
                        'options' => ['Kitchen', 'Cleaning', 'Cooling', 'Furniture', 'Baby & Kids', 'Other']],
                ],
            ],
            [
                'name' => 'Fashion & Accessories', 'slug' => 'fashion', 'icon' => 'Shirt',
                'sort_order' => 7,
                'attributes' => [
                    ['key' => 'fashion_type', 'label' => 'Type', 'type' => 'select',
                        'options' => ['Designer wear', 'Wedding / Bridal', 'Jewellery', 'Watches', 'Bags', 'Costumes', 'Other']],
                    ['key' => 'size', 'label' => 'Size', 'type' => 'text'],
                ],
            ],
            [
                'name' => 'Musical Instruments', 'slug' => 'music', 'icon' => 'Music',
                'sort_order' => 8,
                'attributes' => [
                    ['key' => 'instrument', 'label' => 'Instrument', 'type' => 'select',
                        'options' => ['Guitar', 'Keyboard / Piano', 'Drums', 'Wind', 'Strings', 'DJ gear', 'Other']],
                ],
            ],
            [
                'name' => 'Spaces & Property', 'slug' => 'spaces', 'icon' => 'Building2', 'kind' => 'property',
                'sort_order' => 9,
                'description' => 'Rooms, halls, studios and short-term spaces.',
                'attributes' => [
                    ['key' => 'space_type', 'label' => 'Space type', 'type' => 'select',
                        'options' => ['Event hall', 'Studio', 'Office / Co-working', 'Room', 'Warehouse', 'Land', 'Other']],
                    ['key' => 'area_sqft', 'label' => 'Area', 'type' => 'number', 'unit' => 'sqft'],
                ],
            ],
        ];
    }
}
