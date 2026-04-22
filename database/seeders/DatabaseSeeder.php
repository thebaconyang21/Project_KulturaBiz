<?php

// namespace Database\Seeders;

// use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Seeder;

// class DatabaseSeeder extends Seeder
// {
//     use WithoutModelEvents;

//     /**
//      * Seed the application's database.
//      */
//     public function run(): void
//     {
//         // User::factory(10)->create();

//         User::factory()->create([
//             'name' => 'Test User',
//             'email' => 'test@example.com',
//         ]);
//     }
// }

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\CulturalStory;
use App\Models\Order;
use App\Models\OrderItem;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with sample Mindanaoan artisan data.
     */
    public function run(): void
    {
        // ==========================================
        // 1. CREATE ADMIN
        // ==========================================
        $admin = User::create([
            'name'     => 'Admin KulturaBiz',
            'email'    => 'admin@kulturabiz.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'status'   => 'approved',
        ]);

        // ==========================================
        // 2. CREATE ARTISANS
        // ==========================================
        $artisans = [
            [
                'name'      => 'Fatima Al-Rashid',
                'email'     => 'fatima@kulturabiz.com',
                'shop_name' => 'Maranao Weavers Co.',
                'tribe'     => 'Maranao',
                'region'    => 'Lanao del Sur',
                'bio'       => 'Third-generation weaver from Marawi City, specializing in okir-inspired textiles and traditional malong.',
            ],
            [
                'name'      => 'Jose Manaloto',
                'email'     => 'jose@kulturabiz.com',
                'shop_name' => 'Higaonon Craft House',
                'tribe'     => 'Higaonon',
                'region'    => 'Bukidnon',
                'bio'       => 'Indigenous artisan creating traditional Higaonon baskets and ceremonial accessories from Bukidnon highlands.',
            ],
            [
                'name'      => 'Aiyana Tagalambung',
                'email'     => 'aiyana@kulturabiz.com',
                'shop_name' => "T'boli Heritage Studio",
                'tribe'     => "T'boli",
                'region'    => 'South Cotabato',
                'bio'       => "Master T'nalak weaver from Lake Sebu, South Cotabato. UNESCO-recognized craft tradition.",
            ],
        ];

        $artisanModels = [];
        foreach ($artisans as $data) {
            $artisanModels[] = User::create([
                ...$data,
                'password' => Hash::make('password'),
                'role'     => 'artisan',
                'status'   => 'approved',
            ]);
        }

        // ==========================================
        // 3. CREATE CUSTOMERS
        // ==========================================
        $customers = [
            ['name' => 'Maria Santos',   'email' => 'maria@example.com'],
            ['name' => 'John Reyes',     'email' => 'john@example.com'],
            ['name' => 'Ana Villanueva', 'email' => 'ana@example.com'],
        ];

        $customerModels = [];
        foreach ($customers as $data) {
            $customerModels[] = User::create([
                ...$data,
                'password' => Hash::make('password'),
                'role'     => 'customer',
                'status'   => 'approved',
                'address'  => 'Manila, Philippines',
            ]);
        }

        // ==========================================
        // 4. CREATE CATEGORIES
        // ==========================================
        $categories = [
            ['name' => 'Textiles & Weaves',  'slug' => 'textiles',    'description' => 'Hand-woven fabrics including malong, t\'nalak, and inabel.'],
            ['name' => 'Baskets & Pottery',  'slug' => 'baskets',     'description' => 'Traditional handwoven baskets and earthenware pots.'],
            ['name' => 'Accessories',         'slug' => 'accessories', 'description' => 'Necklaces, bracelets, and ceremonial jewelry.'],
            ['name' => 'Wood Crafts',         'slug' => 'woodcrafts',  'description' => 'Carved wooden sculptures, utensils, and okir decor.'],
            ['name' => 'Bags & Pouches',     'slug' => 'bags',        'description' => 'Handwoven bags, pouches, and tote bags.'],
            ['name' => 'Home Decor',          'slug' => 'homedecor',   'description' => 'Decorative pieces for the home inspired by Mindanaoan heritage.'],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[] = Category::create($cat);
        }

        // ==========================================
        // 5. CREATE PRODUCTS
        // ==========================================
        $products = [
            // Fatima's products (Maranao)
            [
                'user_id'             => $artisanModels[0]->id,
                'category_id'         => $categoryModels[0]->id,
                'name'                => 'Traditional Maranao Malong',
                'slug'                => 'traditional-maranao-malong',
                'description'         => 'A stunning traditional Maranao malong featuring intricate okir patterns in deep red and gold. Hand-woven using a wooden loom by master weavers in Marawi City. Each piece takes 3-5 days to complete. The malong is a versatile garment worn as a skirt, dress, or shawl.',
                'price'               => 2500.00,
                'stock'               => 15,
                'origin_location'     => 'Marawi City, Lanao del Sur',
                'materials_used'      => 'Cotton thread, natural dyes, abaca fiber',
                'cultural_background' => 'The malong is central to Maranao identity and is used in everyday life, ceremonies, and rituals. Its okir patterns represent the Tree of Life and are believed to ward off evil spirits.',
                'status'              => 'active',
                'images'              => [],
            ],
            [
                'user_id'             => $artisanModels[0]->id,
                'category_id'         => $categoryModels[5]->id,
                'name'                => 'Okir-Carved Sarimanok Figurine',
                'slug'                => 'okir-carved-sarimanok-figurine',
                'description'         => 'Hand-carved wooden Sarimanok — the legendary bird of the Maranao people. Adorned with traditional okir geometric patterns painted in gold and red. Perfect as a statement home decor piece or cultural gift.',
                'price'               => 1800.00,
                'stock'               => 8,
                'origin_location'     => 'Marawi City, Lanao del Sur',
                'materials_used'      => 'Narra wood, acrylic paint, gold leaf',
                'cultural_background' => 'The Sarimanok is a mythical rooster that serves as the symbol of the Maranao people. It represents good luck, nobility, and divine protection in Maranao folklore.',
                'status'              => 'active',
                'images'              => [],
            ],
            // Jose's products (Higaonon)
            [
                'user_id'             => $artisanModels[1]->id,
                'category_id'         => $categoryModels[1]->id,
                'name'                => 'Higaonon Ritual Basket',
                'slug'                => 'higaonon-ritual-basket',
                'description'         => 'A beautifully crafted Higaonon basket used in traditional ceremonies and as a home storage piece. Woven from locally-sourced rattan and bamboo strips, featuring geometric patterns that tell the story of the Higaonon\'s connection to the land.',
                'price'               => 950.00,
                'stock'               => 20,
                'origin_location'     => 'Bukidnon Highlands, Bukidnon',
                'materials_used'      => 'Rattan, bamboo, natural dyes from local plants',
                'cultural_background' => 'Higaonon baskets are not mere containers — they are vessels of blessing. Used in "Panubad" rituals, the basket holds offerings to the spirits of the mountains and ancestors.',
                'status'              => 'active',
                'images'              => [],
            ],
            [
                'user_id'             => $artisanModels[1]->id,
                'category_id'         => $categoryModels[2]->id,
                'name'                => 'Beaded Tribal Necklace',
                'slug'                => 'beaded-tribal-necklace',
                'description'         => 'Handmade Higaonon beaded necklace featuring traditional patterns and colors. Each bead is carefully selected and strung by hand using traditional techniques. Comes with a certificate of authenticity.',
                'price'               => 650.00,
                'stock'               => 30,
                'origin_location'     => 'Bukidnon, Region X',
                'materials_used'      => 'Glass beads, natural seeds, leather cord',
                'cultural_background' => 'Beadwork among the Higaonon signifies social status and spiritual protection. The patterns are passed down from grandmothers to granddaughters and each design has a specific meaning.',
                'status'              => 'active',
                'images'              => [],
            ],
            // Aiyana's products (T'boli)
            [
                'user_id'             => $artisanModels[2]->id,
                'category_id'         => $categoryModels[0]->id,
                "name"                => "T'nalak Dream Cloth",
                'slug'                => 'tnalak-dream-cloth',
                'description'         => "Authentic T'nalak cloth hand-woven by a Fu Dalu (master weaver) from Lake Sebu. This sacred abaca cloth features traditional geometric patterns received through dreams — a spiritual practice unique to the T'boli people. Each piece is a one-of-a-kind artwork.",
                'price'               => 8500.00,
                'stock'               => 3,
                'origin_location'     => 'Lake Sebu, South Cotabato',
                'materials_used'      => "Abaca fiber, natural dyes from roots and leaves",
                'cultural_background' => "T'nalak is considered the most sacred cloth in Mindanao. The T'boli believe that Fu Dalu (dream weavers) receive the patterns directly from 'Fu Dalu' — the spirit of the cloth — in their dreams. No two pieces are ever identical.",
                'status'              => 'active',
                'images'              => [],
            ],
            [
                'user_id'             => $artisanModels[2]->id,
                'category_id'         => $categoryModels[4]->id,
                "name"                => "T'boli Woven Shoulder Bag",
                'slug'                => 'tboli-woven-shoulder-bag',
                'description'         => "A functional and beautiful shoulder bag woven in the T'boli tradition. Features traditional geometric designs and natural earth tones. Lined with cotton fabric inside. Adjustable strap. Perfect for everyday use or as a cultural statement piece.",
                'price'               => 1200.00,
                'stock'               => 12,
                'origin_location'     => 'Lake Sebu, South Cotabato',
                'materials_used'      => 'Abaca fiber, cotton lining, brass buckles',
                'cultural_background' => "T'boli weaving has been recognized by UNESCO as an Intangible Cultural Heritage of Humanity. Every bag carries centuries of artistic tradition.",
                'status'              => 'active',
                'images'              => [],
            ],
        ];

        $productModels = [];
        foreach ($products as $prod) {
            $productModels[] = Product::create($prod);
        }

        // ==========================================
        // 6. CREATE CULTURAL STORIES
        // ==========================================
        $stories = [
            [
                'product_id'           => $productModels[0]->id,
                'user_id'              => $artisanModels[0]->id,
                'title'                => 'The Sacred Patterns of the Maranao Malong',
                'slug'                 => 'sacred-patterns-maranao-malong',
                'story'                => 'In the heart of Lanao del Sur, where the Lanao Lake reflects the golden sunsets, the Maranao people have woven their identity into cloth for over 500 years. The malong is more than a garment — it is a language of symbols, a prayer in thread. The okir patterns — those elegant scrollwork designs that dance across every malong — represent the interconnectedness of all living things. The spiral symbolizes the unending cycle of life. The flowing vines represent growth and prosperity. When a Maranao child is born, they are wrapped in a malong. When they marry, the malong is part of the ceremony. And when they pass on, the malong accompanies them into the next world. Master weaver Fatima Al-Rashid learned this art from her grandmother at age 7, and has spent 30 years perfecting patterns that some families have guarded for generations.',
                'tribe_community'      => 'Maranao',
                'location'             => 'Marawi City, Lanao del Sur',
                'cultural_significance'=> 'Central to all Maranao rites of passage and ceremonies',
                'historical_background'=> 'Maranao weaving dates back to the pre-colonial period, with records from Spanish missionaries in the 17th century describing the elaborate cloth of the "Lake people."',
                'is_featured'          => true,
                'is_published'         => true,
            ],
            [
                'product_id'           => $productModels[4]->id,
                'user_id'              => $artisanModels[2]->id,
                "title"                => "T'nalak: Cloth Born from Dreams",
                'slug'                 => 'tnalak-cloth-born-from-dreams',
                "story"                => "In the misty highlands above Lake Sebu, something extraordinary happens when a T'boli Fu Dalu closes her eyes to sleep. The spirit of T'nalak — called 'Fu Dalu' — visits her in her dreams and reveals new patterns. These are not mere aesthetic choices; they are sacred communications from the spirit world. The Fu Dalu wakes before dawn, recalls every detail of her dream, and begins weaving immediately, before the vision fades. This is why no two T'nalak cloths are ever the same. Aiyana Tagalambung, our master weaver, has been receiving these dream-patterns since she was 17. She describes it as 'a conversation with ancestors I have never met, but whose hands guide mine.' The abaca fiber is harvested from plantations around Lake Sebu, processed by hand, naturally dyed with plants from the forest, and woven on a backstrap loom — exactly as it has been done for thousands of years.",
                'tribe_community'      => "T'boli",
                'location'             => 'Lake Sebu, South Cotabato',
                'cultural_significance'=> "UNESCO Intangible Cultural Heritage of Humanity",
                'historical_background'=> "T'nalak weaving is believed to be over 2,000 years old. Archaeological evidence suggests the T'boli people of Lake Sebu have been practicing this craft since before recorded history in Southeast Asia.",
                'is_featured'          => true,
                'is_published'         => true,
            ],
            [
                'product_id'           => $productModels[2]->id,
                'user_id'              => $artisanModels[1]->id,
                'title'                => 'The Higaonon Basket and the Language of Weaving',
                'slug'                 => 'higaonon-basket-language-of-weaving',
                'story'                => "High in the mountains of Bukidnon, where the clouds touch the ridges and ancient diwata spirits are said to walk among the living, the Higaonon people weave their prayers into baskets. Every geometric pattern in a Higaonon basket is a word in a language older than the Philippines itself. The diamond shape represents the eye of the crocodile — watchfulness and protection. The zigzag line represents the river — the source of life. The repeating triangle means 'we are many, we are one.' Jose Manaloto grew up watching his grandfather weave by firelight, singing the old songs that invoke the diwata to bless the craft. 'When I weave,' Jose says, 'I am not alone. My grandfather's hands are in my hands. His grandfather's hands are in his. We go back and back until we reach the first weaver, who learned from the spirits themselves.'",
                'tribe_community'      => 'Higaonon',
                'location'             => 'Bukidnon Highlands, Bukidnon',
                'cultural_significance'=> 'Used in Panubad ceremonies to communicate with ancestral spirits',
                'historical_background'=> 'The Higaonon are among the oldest indigenous groups in Mindanao, with oral histories going back over 40 generations.',
                'is_featured'          => false,
                'is_published'         => true,
            ],
        ];

        foreach ($stories as $story) {
            CulturalStory::create($story);
        }

        // ==========================================
        // 7. CREATE SAMPLE ORDERS
        // ==========================================
        $order1 = Order::create([
            'user_id'            => $customerModels[0]->id,
            'order_number'       => 'KB-DEMO0001',
            'recipient_name'     => 'Maria Santos',
            'delivery_address'   => '123 Rizal Street, Ermita',
            'contact_number'     => '09171234567',
            'city'               => 'Manila',
            'province'           => 'Metro Manila',
            'postal_code'        => '1000',
            'payment_method'     => 'cod',
            'payment_status'     => 'pending',
            'status'             => 'delivered',
            'subtotal'           => 3450.00,
            'shipping_fee'       => 150.00,
            'total_amount'       => 3600.00,
            'courier_name'       => 'J&T Express',
            'tracking_number'    => 'KBJET1234567',
            'estimated_delivery' => now()->subDays(2),
            'processing_at'      => now()->subDays(6),
            'shipped_at'         => now()->subDays(4),
            'delivered_at'       => now()->subDays(2),
        ]);

        OrderItem::create([
            'order_id'     => $order1->id,
            'product_id'   => $productModels[0]->id,
            'product_name' => $productModels[0]->name,
            'price'        => 2500.00,
            'quantity'     => 1,
            'subtotal'     => 2500.00,
        ]);

        OrderItem::create([
            'order_id'     => $order1->id,
            'product_id'   => $productModels[3]->id,
            'product_name' => $productModels[3]->name,
            'price'        => 650.00,
            'quantity'     => 1,
            'subtotal'     => 650.00,
        ]);

        $order2 = Order::create([
            'user_id'            => $customerModels[1]->id,
            'order_number'       => 'KB-DEMO0002',
            'recipient_name'     => 'John Reyes',
            'delivery_address'   => '456 Mabini Avenue, Malate',
            'contact_number'     => '09181234567',
            'city'               => 'Manila',
            'province'           => 'Metro Manila',
            'payment_method'     => 'gcash',
            'payment_status'     => 'paid',
            'status'             => 'shipped',
            'subtotal'           => 8500.00,
            'shipping_fee'       => 150.00,
            'total_amount'       => 8650.00,
            'courier_name'       => 'LBC Express',
            'tracking_number'    => 'KBLBC9876543',
            'estimated_delivery' => now()->addDays(3),
            'processing_at'      => now()->subDays(2),
            'shipped_at'         => now()->subDay(),
        ]);

        OrderItem::create([
            'order_id'     => $order2->id,
            'product_id'   => $productModels[4]->id,
            'product_name' => $productModels[4]->name,
            'price'        => 8500.00,
            'quantity'     => 1,
            'subtotal'     => 8500.00,
        ]);

        $this->command->info('✅ KulturaBiz seeded successfully!');
        $this->command->info('');
        $this->command->info('📋 Login Credentials:');
        $this->command->info('  Admin:    admin@kulturabiz.com / password');
        $this->command->info('  Artisan:  fatima@kulturabiz.com / password');
        $this->command->info('  Artisan:  jose@kulturabiz.com / password');
        $this->command->info("  Artisan:  aiyana@kulturabiz.com / password");
        $this->command->info('  Customer: maria@example.com / password');
        $this->command->info('');
        $this->command->info('🚀 Run: php artisan serve');
    }
}
