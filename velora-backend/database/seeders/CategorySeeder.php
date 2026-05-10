<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // ── Main Categories ──────────────────────────────────────────────
        $mainCategories = [
            [
                'slug'        => 'food',
                'name'        => 'Food',
                'description' => 'Main meals including a variety of freshly prepared dishes.',
                'image_url'   => '/storage/categories/food.png',
                'translations' => [
                    'ru' => ['name' => 'Еда',           'description' => 'Основные блюда, включая разнообразные свежеприготовленные блюда.'],
                    'tm' => ['name' => 'Nahar',         'description' => 'Dürli görnüşli täze taýýarlanan naharlar.'],
                ],
                'subcategories' => [
                    [
                        'slug'        => 'pizza',
                        'name'        => 'Pizza',
                        'description' => 'Delicious oven-baked pizzas topped with fresh ingredients and rich flavors.',
                        'image_url'   => '/storage/categories/pizza.png',
                        'translations' => [
                            'ru' => ['name' => 'Пицца',   'description' => 'Вкусные запечённые пиццы с богатыми начинками.'],
                            'tm' => ['name' => 'Pizza',   'description' => 'Täze önümler bilen pişirilen lezzetli pizza.'],
                        ],
                    ],
                    [
                        'slug'        => 'burgers',
                        'name'        => 'Burgers',
                        'description' => 'Juicy burgers made with premium ingredients, served fresh and satisfying.',
                        'image_url'   => '/storage/categories/burgers.png',
                        'translations' => [
                            'ru' => ['name' => 'Бургеры',  'description' => 'Сочные бургеры из свежих ингредиентов.'],
                            'tm' => ['name' => 'Burgerler','description' => 'Täze önümlerden taýýarlanan şireli burgerler.'],
                        ],
                    ],
                    [
                        'slug'        => 'doner-wraps',
                        'name'        => 'Doner & Wraps',
                        'description' => 'Traditional doner and wraps packed with bold flavors and street-style taste.',
                        'image_url'   => '/storage/categories/doner-wraps.png',
                        'translations' => [
                            'ru' => ['name' => 'Донер и Врапы',  'description' => 'Традиционный донер и врапы с насыщенным вкусом.'],
                            'tm' => ['name' => 'Döner we Wraplar','description' => 'Adaty döner we wraplar.'],
                        ],
                    ],
                    [
                        'slug'        => 'grill-kebab',
                        'name'        => 'Grill & Kebab',
                        'description' => 'Grilled meats including kebabs and kofta, cooked to perfection.',
                        'image_url'   => '/storage/categories/grill-kebab.png',
                        'translations' => [
                            'ru' => ['name' => 'Гриль и Кебаб', 'description' => 'Мясо на гриле, кебабы и кофта.'],
                            'tm' => ['name' => 'Grill we Kebab', 'description' => 'Grill etler, kebablar we köfte.'],
                        ],
                    ],
                    [
                        'slug'        => 'pasta',
                        'name'        => 'Pasta',
                        'description' => 'Classic and modern pasta dishes served with rich and creamy sauces.',
                        'image_url'   => '/storage/categories/pasta.png',
                        'translations' => [
                            'ru' => ['name' => 'Паста', 'description' => 'Классические и современные блюда из пасты.'],
                            'tm' => ['name' => 'Pasta',  'description' => 'Klassik we döwrebap pasta naharlar.'],
                        ],
                    ],
                    [
                        'slug'        => 'rice-bowls',
                        'name'        => 'Rice & Bowls',
                        'description' => 'Hearty rice-based meals and balanced bowls with meat, veggies, and sauces.',
                        'image_url'   => '/storage/categories/rice-bowls.png',
                        'translations' => [
                            'ru' => ['name' => 'Рис и Боулы', 'description' => 'Сытные блюда с рисом и сбалансированные боулы.'],
                            'tm' => ['name' => 'Tüwi we Bowllar', 'description' => 'Doýurmaly tüwi naharlary we bowllar.'],
                        ],
                    ],
                ],
            ],
            [
                'slug'        => 'light-meals',
                'name'        => 'Light Meals',
                'description' => 'Lighter and healthier food options.',
                'image_url'   => '/storage/categories/light-meals.png',
                'translations' => [
                    'ru' => ['name' => 'Лёгкие Блюда', 'description' => 'Более лёгкие и здоровые варианты питания.'],
                    'tm' => ['name' => 'Ýeňil Naharlar', 'description' => 'Ýeňil we sagdyn iýmit görnüşleri.'],
                ],
                'subcategories' => [
                    [
                        'slug'        => 'salads',
                        'name'        => 'Salads',
                        'description' => 'Fresh and healthy salads with a variety of toppings and dressings.',
                        'image_url'   => '/storage/categories/salads.png',
                        'translations' => [
                            'ru' => ['name' => 'Салаты',  'description' => 'Свежие и полезные салаты с разнообразными заправками.'],
                            'tm' => ['name' => 'Salatlar','description' => 'Täze we sagdyn salatlar.'],
                        ],
                    ],
                    [
                        'slug'        => 'soups',
                        'name'        => 'Soups',
                        'description' => 'Warm and comforting soups made daily with fresh ingredients.',
                        'image_url'   => '/storage/categories/soups.png',
                        'translations' => [
                            'ru' => ['name' => 'Супы',    'description' => 'Тёплые супы из свежих ингредиентов.'],
                            'tm' => ['name' => 'Çorbalar','description' => 'Täze önümlerden taýýarlanan ýyly çorbalar.'],
                        ],
                    ],
                ],
            ],
            [
                'slug'        => 'sides-extras',
                'name'        => 'Sides & Extras',
                'description' => 'Snacks and additional items to complement your meal.',
                'image_url'   => '/storage/categories/sides-extras.png',
                'translations' => [
                    'ru' => ['name' => 'Гарниры и Добавки', 'description' => 'Закуски и дополнительные позиции к основному блюду.'],
                    'tm' => ['name' => 'Garnirler we Goşmaçalar', 'description' => 'Esasy nahara goşmaça zatlar.'],
                ],
                'subcategories' => [
                    [
                        'slug'        => 'sides',
                        'name'        => 'Sides',
                        'description' => 'Crispy fries, nuggets, and small bites perfect as a snack or side.',
                        'image_url'   => '/storage/categories/sides.png',
                        'translations' => [
                            'ru' => ['name' => 'Гарниры',  'description' => 'Хрустящий картофель фри, наггетсы и закуски.'],
                            'tm' => ['name' => 'Garnirler','description' => 'Çişik fri kartopy, naggets we kiçi iýmitler.'],
                        ],
                    ],
                    [
                        'slug'        => 'sauces-extras',
                        'name'        => 'Sauces & Extras',
                        'description' => 'A selection of sauces and extras to enhance your meal.',
                        'image_url'   => '/storage/categories/sauces-extras.png',
                        'translations' => [
                            'ru' => ['name' => 'Соусы и Добавки',     'description' => 'Выбор соусов и дополнений к блюдам.'],
                            'tm' => ['name' => 'Souslar we Goşmaçalar','description' => 'Naharlar üçin souslar we goşmaçalar.'],
                        ],
                    ],
                ],
            ],
            [
                'slug'        => 'desserts',
                'name'        => 'Desserts',
                'description' => 'Sweet treats and desserts to complete your meal.',
                'image_url'   => '/storage/categories/desserts.png',
                'translations' => [
                    'ru' => ['name' => 'Десерты',  'description' => 'Сладкие угощения для завершения трапезы.'],
                    'tm' => ['name' => 'Desertler','description' => 'Nahardan soň süýji desertler.'],
                ],
                'subcategories' => [
                    [
                        'slug'        => 'desserts-sub',
                        'name'        => 'Desserts',
                        'description' => 'Delicious sweet treats and baked desserts to satisfy your cravings.',
                        'image_url'   => '/storage/categories/desserts-sub.png',
                        'translations' => [
                            'ru' => ['name' => 'Десерты',  'description' => 'Вкусные сладости и выпечка для сладкоежек.'],
                            'tm' => ['name' => 'Desertler','description' => 'Lezzetli süýji iýmitler we bişirilen desertler.'],
                        ],
                    ],
                ],
            ],
            [
                'slug'        => 'drinks',
                'name'        => 'Drinks',
                'description' => 'Hot and cold beverages for every taste.',
                'image_url'   => '/storage/categories/drinks.png',
                'translations' => [
                    'ru' => ['name' => 'Напитки',  'description' => 'Горячие и холодные напитки на любой вкус.'],
                    'tm' => ['name' => 'Içgiler',  'description' => 'Her tagam üçin gyzgyn we sowuk içgiler.'],
                ],
                'subcategories' => [
                    [
                        'slug'        => 'hot-drinks',
                        'name'        => 'Hot Drinks',
                        'description' => 'Freshly prepared coffee, tea, and other warm beverages.',
                        'image_url'   => '/storage/categories/hot-drinks.png',
                        'translations' => [
                            'ru' => ['name' => 'Горячие Напитки',  'description' => 'Свежий кофе, чай и другие горячие напитки.'],
                            'tm' => ['name' => 'Gyzgyn Içgiler',   'description' => 'Täze taýýarlanan kofe, çaý we beýleki ýyly içgiler.'],
                        ],
                    ],
                    [
                        'slug'        => 'cold-drinks',
                        'name'        => 'Cold Drinks',
                        'description' => 'Refreshing cold drinks including juices and soft beverages.',
                        'image_url'   => '/storage/categories/cold-drinks.png',
                        'translations' => [
                            'ru' => ['name' => 'Холодные Напитки', 'description' => 'Освежающие холодные напитки, соки и газировка.'],
                            'tm' => ['name' => 'Sowuk Içgiler',    'description' => 'Serginlediji sowuk içgiler, şireler we gazly içgiler.'],
                        ],
                    ],
                ],
            ],
            [
                'slug'        => 'special-diet',
                'name'        => 'Special Diet',
                'description' => 'Meals designed for specific dietary preferences.',
                'image_url'   => '/storage/categories/special-diet.png',
                'translations' => [
                    'ru' => ['name' => 'Специальная Диета', 'description' => 'Блюда для особых диетических предпочтений.'],
                    'tm' => ['name' => 'Ýörite Iýmit',     'description' => 'Ýörite iýmit islegleri üçin naharlar.'],
                ],
                'subcategories' => [
                    [
                        'slug'        => 'vegetarian',
                        'name'        => 'Vegetarian',
                        'description' => 'Meat-free dishes made with fresh and flavorful ingredients.',
                        'image_url'   => '/storage/categories/vegetarian.png',
                        'translations' => [
                            'ru' => ['name' => 'Вегетарианское', 'description' => 'Блюда без мяса из свежих ингредиентов.'],
                            'tm' => ['name' => 'Wegetarian',     'description' => 'Etsiz täze önümlerden taýýarlanan naharlar.'],
                        ],
                    ],
                    [
                        'slug'        => 'vegan',
                        'name'        => 'Vegan',
                        'description' => '100% plant-based meals crafted for a healthy lifestyle.',
                        'image_url'   => '/storage/categories/vegan.png',
                        'translations' => [
                            'ru' => ['name' => 'Веганское', 'description' => '100% растительные блюда для здорового образа жизни.'],
                            'tm' => ['name' => 'Vegan',     'description' => 'Sagdyn durmuş üçin 100% ösümlik esasly naharlar.'],
                        ],
                    ],
                ],
            ],
        ];

        $now = now();

        foreach ($mainCategories as $mainData) {
            // Insert main category (parent_id = null)
            $mainId = DB::table('categories')->insertGetId([
                'parent_id'   => null,
                'name'        => $mainData['name'],
                'description' => $mainData['description'],
                'image_url'   => $mainData['image_url'],
                'is_active'   => true,
                'created_at'  => $now,
            ]);

            // Insert main category translations
            foreach ($mainData['translations'] as $locale => $trans) {
                DB::table('category_translations')->insert([
                    'category_id' => $mainId,
                    'locale'      => $locale,
                    'name'        => $trans['name'],
                    'description' => $trans['description'],
                    'created_at'  => $now,
                ]);
            }

            // Insert subcategories
            foreach ($mainData['subcategories'] as $subData) {
                $subId = DB::table('categories')->insertGetId([
                    'parent_id'   => $mainId,
                    'name'        => $subData['name'],
                    'description' => $subData['description'],
                    'image_url'   => $subData['image_url'],
                    'is_active'   => true,
                    'created_at'  => $now,
                ]);

                foreach ($subData['translations'] as $locale => $trans) {
                    DB::table('category_translations')->insert([
                        'category_id' => $subId,
                        'locale'      => $locale,
                        'name'        => $trans['name'],
                        'description' => $trans['description'],
                        'created_at'  => $now,
                    ]);
                }
            }
        }
    }
}