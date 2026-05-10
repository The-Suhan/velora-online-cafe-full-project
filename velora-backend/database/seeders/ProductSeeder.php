<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Category slug → DB id mapping (resolved at runtime).
     */
    private array $catIds = [];

    public function run(): void
    {
        // Resolve category IDs by name (unique enough for seeding)
        $cats = DB::table('categories')->get(['id', 'name']);
        foreach ($cats as $cat) {
            $this->catIds[$cat->name] = $cat->id;
        }

        $now = now();

        foreach ($this->products() as $product) {
            $categoryId = $this->catIds[$product['category']] ?? null;

            if (!$categoryId) {
                $this->command?->warn("Category not found: {$product['category']}");
                continue;
            }

            $productId = DB::table('products')->insertGetId([
                'category_id' => $categoryId,
                'name'        => $product['en']['name'],
                'description' => $product['en']['description'],
                'price'       => $product['price'],
                'image_url'   => '/storage/products/' . $product['image'] . '.png',
                'is_active'   => true,
                'avg_rating'  => 0.00,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            foreach (['ru', 'tm'] as $locale) {
                DB::table('product_translations')->insert([
                    'product_id'  => $productId,
                    'locale'      => $locale,
                    'name'        => $product[$locale]['name'],
                    'description' => $product[$locale]['description'],
                    'created_at'  => $now,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Product data
    // ─────────────────────────────────────────────────────────────────────────
    private function products(): array
    {
        return [
            // ── Pizza ────────────────────────────────────────────────────────
            [
                'category' => 'Pizza',
                'price'    => 8.50,
                'image'    => 'margherita',
                'en' => ['name' => 'Margherita',   'description' => 'Classic pizza with tomato sauce, mozzarella, and fresh basil.'],
                'ru' => ['name' => 'Маргарита',    'description' => 'Классическая пицца с томатным соусом, моцареллой и свежим базиликом.'],
                'tm' => ['name' => 'Margarita',    'description' => 'Pomidor sousy, mozzarella we täze reyhan bilen taýýarlanan klassik pizza.'],
            ],
            [
                'category' => 'Pizza',
                'price'    => 9.50,
                'image'    => 'pepperoni',
                'en' => ['name' => 'Pepperoni',    'description' => 'Loaded with spicy pepperoni and melted cheese.'],
                'ru' => ['name' => 'Пепперони',    'description' => 'Пицца с острой пепперони и расплавленным сыром.'],
                'tm' => ['name' => 'Pepperoni',    'description' => 'Ajy pepperoni we eredilen peýnir bilen taýýarlanan pizza.'],
            ],
            [
                'category' => 'Pizza',
                'price'    => 10.50,
                'image'    => 'bbq-chicken',
                'en' => ['name' => 'BBQ Chicken',  'description' => 'Grilled chicken with BBQ sauce and onions.'],
                'ru' => ['name' => 'BBQ Курица',   'description' => 'Курица на гриле с BBQ соусом и луком.'],
                'tm' => ['name' => 'BBQ Towukly',  'description' => 'BBQ sousy we sogan bilen grill edilen towukly pizza.'],
            ],
            [
                'category' => 'Pizza',
                'price'    => 9.90,
                'image'    => 'hawaiian',
                'en' => ['name' => 'Hawaiian',             'description' => 'Ham and pineapple with mozzarella cheese.'],
                'ru' => ['name' => 'Гавайская',            'description' => 'Ветчина и ананас с моцареллой.'],
                'tm' => ['name' => 'Gawaý Pizza',          'description' => 'Wetçina, ananas we mozzarella peýnirli pizza.'],
            ],
            [
                'category' => 'Pizza',
                'price'    => 10.90,
                'image'    => 'four-cheese',
                'en' => ['name' => 'Four Cheese',          'description' => 'Mozzarella, cheddar, parmesan, and blue cheese blend.'],
                'ru' => ['name' => 'Четыре Сыра',          'description' => 'Смесь моцареллы, чеддера, пармезана и голубого сыра.'],
                'tm' => ['name' => 'Dört Peýnirli',        'description' => 'Mozzarella, çedder, parmesan we gök peýnir garyndysy bilen pizza.'],
            ],

            // ── Burgers ──────────────────────────────────────────────────────
            [
                'category' => 'Burgers',
                'price'    => 7.90,
                'image'    => 'classic-beef-burger',
                'en' => ['name' => 'Classic Beef Burger',      'description' => 'Juicy beef patty with lettuce, tomato, and cheese.'],
                'ru' => ['name' => 'Классический Бургер',      'description' => 'Сочная говяжья котлета с салатом, помидором и сыром.'],
                'tm' => ['name' => 'Klassik Burger',           'description' => 'Salat, pomidor we peýnir bilen şireli sygyr etli burger.'],
            ],
            [
                'category' => 'Burgers',
                'price'    => 7.50,
                'image'    => 'chicken-burger',
                'en' => ['name' => 'Chicken Burger',           'description' => 'Crispy chicken with mayo and fresh veggies.'],
                'ru' => ['name' => 'Куриный Бургер',           'description' => 'Хрустящая курица с майонезом и свежими овощами.'],
                'tm' => ['name' => 'Towuk Burger',             'description' => 'Maýonez we täze gök önümler bilen gyzgyn towuk burgeri.'],
            ],
            [
                'category' => 'Burgers',
                'price'    => 9.90,
                'image'    => 'double-cheese-burger',
                'en' => ['name' => 'Double Cheese Burger',     'description' => 'Double beef with extra cheese.'],
                'ru' => ['name' => 'Двойной Чизбургер',        'description' => 'Двойная говядина с дополнительным сыром.'],
                'tm' => ['name' => 'Goşa Peýnirli Burger',    'description' => 'Goşa sygyr eti we goşmaça peýnir bilen burger.'],
            ],
            [
                'category' => 'Burgers',
                'price'    => 8.90,
                'image'    => 'mushroom-swiss-burger',
                'en' => ['name' => 'Mushroom Swiss Burger',    'description' => 'Beef burger with mushrooms and Swiss cheese.'],
                'ru' => ['name' => 'Бургер с Грибами и Швейцарским Сыром', 'description' => 'Говяжий бургер с грибами и швейцарским сыром.'],
                'tm' => ['name' => 'Kömelekli Şweýsar Peýnirli Burger',   'description' => 'Kömelek we şweýsar peýnirli sygyr etli burger.'],
            ],
            [
                'category' => 'Burgers',
                'price'    => 8.70,
                'image'    => 'spicy-jalapeno-burger',
                'en' => ['name' => 'Spicy Jalapeño Burger',   'description' => 'Spicy beef burger with jalapeños and hot sauce.'],
                'ru' => ['name' => 'Острый Бургер с Халапеньо','description' => 'Острый бургер с халапеньо и пикантным соусом.'],
                'tm' => ['name' => 'Ajy Jalapeno Burger',     'description' => 'Jalapeno we ajy sous bilen taýýarlanan burger.'],
            ],
            [
                'category' => 'Burgers',
                'price'    => 9.50,
                'image'    => 'bbq-bacon-burger',
                'en' => ['name' => 'BBQ Bacon Burger',        'description' => 'Beef burger with crispy bacon and BBQ sauce.'],
                'ru' => ['name' => 'BBQ Бекон Бургер',        'description' => 'Говяжий бургер с хрустящим беконом и BBQ соусом.'],
                'tm' => ['name' => 'BBQ Bekonly Burger',      'description' => 'Çişik bekon we BBQ sously sygyr etli burger.'],
            ],

            // ── Doner & Wraps ────────────────────────────────────────────────
            [
                'category' => 'Doner & Wraps',
                'price'    => 6.50,
                'image'    => 'chicken-doner-wrap',
                'en' => ['name' => 'Chicken Doner Wrap',      'description' => 'Grilled chicken wrapped with fresh vegetables.'],
                'ru' => ['name' => 'Куриный Донер Врап',      'description' => 'Курица на гриле в лаваше со свежими овощами.'],
                'tm' => ['name' => 'Towuk Döner Wrap',        'description' => 'Täze gök önümler bilen dürlenen grill towuk döneri.'],
            ],
            [
                'category' => 'Doner & Wraps',
                'price'    => 7.00,
                'image'    => 'beef-doner',
                'en' => ['name' => 'Beef Doner',              'description' => 'Traditional beef doner with sauce.'],
                'ru' => ['name' => 'Говяжий Донер',           'description' => 'Традиционный говяжий донер с соусом.'],
                'tm' => ['name' => 'Sygyr Etli Döner',        'description' => 'Sous bilen taýýarlanan adaty sygyr etli döner.'],
            ],
            [
                'category' => 'Doner & Wraps',
                'price'    => 6.00,
                'image'    => 'falafel-wrap',
                'en' => ['name' => 'Falafel Wrap',            'description' => 'Crispy falafel with tahini sauce.'],
                'ru' => ['name' => 'Фалафель Врап',           'description' => 'Хрустящий фалафель с тахини соусом.'],
                'tm' => ['name' => 'Falafel Wrap',            'description' => 'Tahini sously çişik falafel wrap.'],
            ],
            [
                'category' => 'Doner & Wraps',
                'price'    => 7.50,
                'image'    => 'mixed-doner-wrap',
                'en' => ['name' => 'Mixed Doner Wrap',        'description' => 'Chicken and beef doner mix with garlic sauce.'],
                'ru' => ['name' => 'Смешанный Донер Врап',    'description' => 'Смесь куриного и говяжьего донера с чесночным соусом.'],
                'tm' => ['name' => 'Garyşyk Döner Wrap',      'description' => 'Towuk we sygyr döneriniň sarymsak sously garyndysy.'],
            ],
            [
                'category' => 'Doner & Wraps',
                'price'    => 6.90,
                'image'    => 'spicy-chicken-wrap',
                'en' => ['name' => 'Spicy Chicken Wrap',      'description' => 'Spicy grilled chicken with vegetables.'],
                'ru' => ['name' => 'Острый Куриный Врап',     'description' => 'Острая курица с овощами в лаваше.'],
                'tm' => ['name' => 'Ajy Towuk Wrap',          'description' => 'Ajy towuk we gök önümler bilen wrap.'],
            ],

            // ── Grill & Kebab ────────────────────────────────────────────────
            [
                'category' => 'Grill & Kebab',
                'price'    => 11.00,
                'image'    => 'adana-kebab',
                'en' => ['name' => 'Adana Kebab',             'description' => 'Spicy minced meat grilled on skewers.'],
                'ru' => ['name' => 'Адана Кебаб',             'description' => 'Острый кебаб из рубленого мяса на шампурах.'],
                'tm' => ['name' => 'Adana Kebab',             'description' => 'Şampurda grill edilen ajy ownadylan et kebaby.'],
            ],
            [
                'category' => 'Grill & Kebab',
                'price'    => 10.50,
                'image'    => 'chicken-shish',
                'en' => ['name' => 'Chicken Shish',           'description' => 'Grilled chicken cubes with spices.'],
                'ru' => ['name' => 'Куриный Шиш',             'description' => 'Кусочки курицы на гриле со специями.'],
                'tm' => ['name' => 'Towuk Şiş',               'description' => 'Ysly zatlar bilen grill edilen towuk bölekleri.'],
            ],
            [
                'category' => 'Grill & Kebab',
                'price'    => 14.90,
                'image'    => 'mixed-grill',
                'en' => ['name' => 'Mixed Grill',             'description' => 'Combination of kebabs and meats.'],
                'ru' => ['name' => 'Микс Гриль',              'description' => 'Ассорти из кебабов и мяса.'],
                'tm' => ['name' => 'Garyşyk Grill',           'description' => 'Kebab we dürli etleriň garyndysy.'],
            ],
            [
                'category' => 'Grill & Kebab',
                'price'    => 12.90,
                'image'    => 'lamb-kebab',
                'en' => ['name' => 'Lamb Kebab',              'description' => 'Tender lamb skewers grilled to perfection.'],
                'ru' => ['name' => 'Бараний Кебаб',           'description' => 'Нежные шашлыки из баранины на гриле.'],
                'tm' => ['name' => 'Goýun Etli Kebab',        'description' => 'Ýumşak grill edilen goýun etli kebab.'],
            ],
            [
                'category' => 'Grill & Kebab',
                'price'    => 10.90,
                'image'    => 'kofta-grill',
                'en' => ['name' => 'Kofta Grill',             'description' => 'Seasoned grilled meatballs with herbs.'],
                'ru' => ['name' => 'Кёфта Гриль',             'description' => 'Пряные мясные котлеты на гриле с травами.'],
                'tm' => ['name' => 'Köfte Grill',             'description' => 'Otlar bilen tagamlandyrylan grill köfte.'],
            ],

            // ── Pasta ────────────────────────────────────────────────────────
            [
                'category' => 'Pasta',
                'price'    => 9.00,
                'image'    => 'alfredo-pasta',
                'en' => ['name' => 'Alfredo Pasta',           'description' => 'Creamy white sauce pasta with chicken.'],
                'ru' => ['name' => 'Паста Альфредо',          'description' => 'Паста в сливочном соусе с курицей.'],
                'tm' => ['name' => 'Alfredo Pasta',           'description' => 'Towukly kremli ak sously pasta.'],
            ],
            [
                'category' => 'Pasta',
                'price'    => 9.50,
                'image'    => 'bolognese-pasta',
                'en' => ['name' => 'Bolognese Pasta',         'description' => 'Pasta with rich meat sauce.'],
                'ru' => ['name' => 'Паста Болоньезе',         'description' => 'Паста с насыщенным мясным соусом.'],
                'tm' => ['name' => 'Bolonez Pasta',           'description' => 'Baý et sously pasta.'],
            ],
            [
                'category' => 'Pasta',
                'price'    => 8.50,
                'image'    => 'veggie-pasta',
                'en' => ['name' => 'Veggie Pasta',            'description' => 'Pasta with fresh vegetables.'],
                'ru' => ['name' => 'Овощная Паста',           'description' => 'Паста со свежими овощами.'],
                'tm' => ['name' => 'Gök Önümli Pasta',        'description' => 'Täze gök önümler bilen pasta.'],
            ],
            [
                'category' => 'Pasta',
                'price'    => 9.90,
                'image'    => 'carbonara',
                'en' => ['name' => 'Carbonara',               'description' => 'Creamy pasta with beef bacon and parmesan.'],
                'ru' => ['name' => 'Карбонара',               'description' => 'Сливочная паста с беконом и пармезаном.'],
                'tm' => ['name' => 'Karbonara',               'description' => 'Bekon we parmesan bilen kremli pasta.'],
            ],
            [
                'category' => 'Pasta',
                'price'    => 8.90,
                'image'    => 'pesto-pasta',
                'en' => ['name' => 'Pesto Pasta',             'description' => 'Basil pesto pasta with parmesan cheese.'],
                'ru' => ['name' => 'Паста Песто',             'description' => 'Паста с соусом песто и пармезаном.'],
                'tm' => ['name' => 'Pesto Pasta',             'description' => 'Pesto sousy we parmesan bilen taýýarlanan pasta.'],
            ],

            // ── Rice & Bowls ─────────────────────────────────────────────────
            [
                'category' => 'Rice & Bowls',
                'price'    => 8.90,
                'image'    => 'chicken-rice-bowl',
                'en' => ['name' => 'Chicken Rice Bowl',       'description' => 'Grilled chicken with rice and sauce.'],
                'ru' => ['name' => 'Рис Боул с Курицей',      'description' => 'Курица на гриле с рисом и соусом.'],
                'tm' => ['name' => 'Towukly Tüwi Bowl',       'description' => 'Grill towuk, tüwi we sous bilen bowl.'],
            ],
            [
                'category' => 'Rice & Bowls',
                'price'    => 9.90,
                'image'    => 'beef-bowl',
                'en' => ['name' => 'Beef Bowl',               'description' => 'Beef strips with vegetables and rice.'],
                'ru' => ['name' => 'Говяжий Боул',            'description' => 'Говядина с овощами и рисом.'],
                'tm' => ['name' => 'Sygyr Etli Bowl',         'description' => 'Gök önümler we tüwi bilen sygyr etli bowl.'],
            ],
            [
                'category' => 'Rice & Bowls',
                'price'    => 7.90,
                'image'    => 'veggie-bowl',
                'en' => ['name' => 'Veggie Bowl',             'description' => 'Mixed vegetables with rice.'],
                'ru' => ['name' => 'Овощной Боул',            'description' => 'Смесь овощей с рисом.'],
                'tm' => ['name' => 'Gök Önümli Bowl',         'description' => 'Tüwi bilen dürli gök önümler.'],
            ],
            [
                'category' => 'Rice & Bowls',
                'price'    => 9.50,
                'image'    => 'teriyaki-chicken-bowl',
                'en' => ['name' => 'Teriyaki Chicken Bowl',   'description' => 'Chicken with teriyaki glaze and rice.'],
                'ru' => ['name' => 'Терияки Курица Боул',     'description' => 'Курица терияки с рисом.'],
                'tm' => ['name' => 'Teriyaki Towuk Bowl',     'description' => 'Teriyaki sously towuk we tüwi.'],
            ],
            [
                'category' => 'Rice & Bowls',
                'price'    => 10.20,
                'image'    => 'spicy-beef-rice-bowl',
                'en' => ['name' => 'Spicy Beef Rice Bowl',    'description' => 'Spicy beef strips served over rice.'],
                'ru' => ['name' => 'Острый Говяжий Рис Боул','description' => 'Острая говядина с рисом.'],
                'tm' => ['name' => 'Ajy Sygyr Etli Tüwi Bowl','description' => 'Ajy sygyr eti we tüwi bilen bowl.'],
            ],

            // ── Salads ───────────────────────────────────────────────────────
            [
                'category' => 'Salads',
                'price'    => 7.50,
                'image'    => 'caesar-salad',
                'en' => ['name' => 'Caesar Salad',            'description' => 'Chicken, lettuce, parmesan, and Caesar dressing.'],
                'ru' => ['name' => 'Салат Цезарь',            'description' => 'Курица, салат, пармезан и соус Цезарь.'],
                'tm' => ['name' => 'Sezar Salady',            'description' => 'Towuk, salat ýapragy, parmesan we Sezar sousy.'],
            ],
            [
                'category' => 'Salads',
                'price'    => 6.90,
                'image'    => 'greek-salad',
                'en' => ['name' => 'Greek Salad',             'description' => 'Tomato, cucumber, olives, and feta cheese.'],
                'ru' => ['name' => 'Греческий Салат',         'description' => 'Помидоры, огурцы, оливки и сыр фета.'],
                'tm' => ['name' => 'Grek Salady',             'description' => 'Pomidor, hyýar, zeýtun we feta peýnirli salat.'],
            ],
            [
                'category' => 'Salads',
                'price'    => 7.90,
                'image'    => 'avocado-salad',
                'en' => ['name' => 'Avocado Salad',           'description' => 'Fresh greens with avocado slices.'],
                'ru' => ['name' => 'Салат с Авокадо',         'description' => 'Свежая зелень с ломтиками авокадо.'],
                'tm' => ['name' => 'Awokadoly Salat',         'description' => 'Awokado bölekli täze gök salat.'],
            ],
            [
                'category' => 'Salads',
                'price'    => 8.20,
                'image'    => 'tuna-salad',
                'en' => ['name' => 'Tuna Salad',              'description' => 'Tuna with mixed greens and lemon dressing.'],
                'ru' => ['name' => 'Салат с Тунцом',          'description' => 'Тунец с зеленью и лимонной заправкой.'],
                'tm' => ['name' => 'Tunetsli Salat',          'description' => 'Tunets we limon sously gök salat.'],
            ],
            [
                'category' => 'Salads',
                'price'    => 8.90,
                'image'    => 'chicken-avocado-salad',
                'en' => ['name' => 'Chicken Avocado Salad',   'description' => 'Grilled chicken with avocado and greens.'],
                'ru' => ['name' => 'Салат с Курицей и Авокадо','description' => 'Курица на гриле с авокадо и зеленью.'],
                'tm' => ['name' => 'Towukly Awokadoly Salat', 'description' => 'Grill towuk we awokado bilen salat.'],
            ],

            // ── Soups ────────────────────────────────────────────────────────
            [
                'category' => 'Soups',
                'price'    => 4.50,
                'image'    => 'lentil-soup',
                'en' => ['name' => 'Lentil Soup',             'description' => 'Traditional warm lentil soup.'],
                'ru' => ['name' => 'Чечевичный Суп',          'description' => 'Традиционный горячий чечевичный суп.'],
                'tm' => ['name' => 'Merjimek Çorbasy',        'description' => 'Adaty gyzgyn merjimek çorbasy.'],
            ],
            [
                'category' => 'Soups',
                'price'    => 5.00,
                'image'    => 'chicken-soup',
                'en' => ['name' => 'Chicken Soup',            'description' => 'Light chicken broth with vegetables.'],
                'ru' => ['name' => 'Куриный Суп',             'description' => 'Лёгкий куриный бульон с овощами.'],
                'tm' => ['name' => 'Towuk Çorbasy',           'description' => 'Gök önümler bilen ýeňil towuk çorbasy.'],
            ],
            [
                'category' => 'Soups',
                'price'    => 4.80,
                'image'    => 'tomato-soup',
                'en' => ['name' => 'Tomato Soup',             'description' => 'Creamy tomato soup with herbs.'],
                'ru' => ['name' => 'Томатный Суп',            'description' => 'Кремовый томатный суп с травами.'],
                'tm' => ['name' => 'Pomidor Çorbasy',         'description' => 'Otlar bilen kremli pomidor çorbasy.'],
            ],
            [
                'category' => 'Soups',
                'price'    => 5.20,
                'image'    => 'mushroom-soup',
                'en' => ['name' => 'Mushroom Soup',           'description' => 'Rich mushroom cream soup.'],
                'ru' => ['name' => 'Грибной Суп',             'description' => 'Насыщенный грибной крем-суп.'],
                'tm' => ['name' => 'Kömelek Çorbasy',         'description' => 'Baý tagamly kremli kömelek çorbasy.'],
            ],
            [
                'category' => 'Soups',
                'price'    => 4.70,
                'image'    => 'vegetable-soup',
                'en' => ['name' => 'Vegetable Soup',          'description' => 'Fresh seasonal vegetable soup.'],
                'ru' => ['name' => 'Овощной Суп',             'description' => 'Суп из свежих сезонных овощей.'],
                'tm' => ['name' => 'Gök Önüm Çorbasy',        'description' => 'Täze möwsüm gök önümlerinden taýýarlanan çorba.'],
            ],

            // ── Sides ────────────────────────────────────────────────────────
            [
                'category' => 'Sides',
                'price'    => 3.50,
                'image'    => 'french-fries',
                'en' => ['name' => 'French Fries',            'description' => 'Crispy golden fries.'],
                'ru' => ['name' => 'Картофель Фри',           'description' => 'Хрустящий золотистый картофель фри.'],
                'tm' => ['name' => 'Fri Kartopy',             'description' => 'Çişik altyn reňkli fri kartopy.'],
            ],
            [
                'category' => 'Sides',
                'price'    => 4.50,
                'image'    => 'chicken-nuggets',
                'en' => ['name' => 'Chicken Nuggets',         'description' => 'Fried chicken bites.'],
                'ru' => ['name' => 'Куриные Наггетсы',        'description' => 'Жареные кусочки курицы.'],
                'tm' => ['name' => 'Towuk Naggets',           'description' => 'Gowrulan towuk bölekleri.'],
            ],
            [
                'category' => 'Sides',
                'price'    => 3.90,
                'image'    => 'onion-rings',
                'en' => ['name' => 'Onion Rings',             'description' => 'Crispy onion rings.'],
                'ru' => ['name' => 'Луковые Кольца',          'description' => 'Хрустящие кольца лука.'],
                'tm' => ['name' => 'Sogan Halkalary',         'description' => 'Çişik sogan halkalary.'],
            ],
            [
                'category' => 'Sides',
                'price'    => 4.90,
                'image'    => 'mozzarella-sticks',
                'en' => ['name' => 'Mozzarella Sticks',       'description' => 'Fried mozzarella cheese sticks.'],
                'ru' => ['name' => 'Сырные Палочки',          'description' => 'Жареные палочки из моцареллы.'],
                'tm' => ['name' => 'Mozzarella Çöpleri',      'description' => 'Gowrulan mozzarella peýnir çöpleri.'],
            ],
            [
                'category' => 'Sides',
                'price'    => 4.20,
                'image'    => 'potato-wedges',
                'en' => ['name' => 'Potato Wedges',           'description' => 'Seasoned potato wedges.'],
                'ru' => ['name' => 'Картофельные Дольки',     'description' => 'Приправленные картофельные дольки.'],
                'tm' => ['name' => 'Kartop Dilimleri',        'description' => 'Ysly kartop dilimleri.'],
            ],

            // ── Sauces & Extras ──────────────────────────────────────────────
            [
                'category' => 'Sauces & Extras',
                'price'    => 1.00,
                'image'    => 'garlic-sauce',
                'en' => ['name' => 'Garlic Sauce',            'description' => 'Creamy garlic dip.'],
                'ru' => ['name' => 'Чесночный Соус',          'description' => 'Кремовый чесночный соус.'],
                'tm' => ['name' => 'Sarymsak Sousy',          'description' => 'Kremli sarymsak sousy.'],
            ],
            [
                'category' => 'Sauces & Extras',
                'price'    => 0.50,
                'image'    => 'ketchup',
                'en' => ['name' => 'Ketchup',                 'description' => 'Classic tomato sauce.'],
                'ru' => ['name' => 'Кетчуп',                  'description' => 'Классический томатный соус.'],
                'tm' => ['name' => 'Ketçup',                  'description' => 'Klassik pomidor sousy.'],
            ],
            [
                'category' => 'Sauces & Extras',
                'price'    => 1.20,
                'image'    => 'cheese-sauce',
                'en' => ['name' => 'Cheese Sauce',            'description' => 'Melted cheese dip.'],
                'ru' => ['name' => 'Сырный Соус',             'description' => 'Расплавленный сырный соус.'],
                'tm' => ['name' => 'Peýnir Sousy',            'description' => 'Eredilen peýnir sousy.'],
            ],
            [
                'category' => 'Sauces & Extras',
                'price'    => 1.00,
                'image'    => 'bbq-sauce',
                'en' => ['name' => 'BBQ Sauce',               'description' => 'Smoky barbecue sauce.'],
                'ru' => ['name' => 'BBQ Соус',                'description' => 'Копчёный соус барбекю.'],
                'tm' => ['name' => 'BBQ Sousy',               'description' => 'Tüsseli barbekýu sousy.'],
            ],

            // ── Desserts ─────────────────────────────────────────────────────
            [
                'category' => 'Desserts',
                'price'    => 4.90,
                'image'    => 'chocolate-cake',
                'en' => ['name' => 'Chocolate Cake',          'description' => 'Rich chocolate layered cake.'],
                'ru' => ['name' => 'Шоколадный Торт',         'description' => 'Насыщенный многослойный шоколадный торт.'],
                'tm' => ['name' => 'Şokolad Torty',           'description' => 'Gatlakly baý şokolad torty.'],
            ],
            [
                'category' => 'Desserts',
                'price'    => 5.50,
                'image'    => 'cheesecake',
                'en' => ['name' => 'Cheesecake',              'description' => 'Creamy cheesecake with biscuit base.'],
                'ru' => ['name' => 'Чизкейк',                 'description' => 'Нежный чизкейк с бисквитной основой.'],
                'tm' => ['name' => 'Çizkeýk',                 'description' => 'Biskwit düýpli kremli çizkeýk.'],
            ],
            [
                'category' => 'Desserts',
                'price'    => 5.90,
                'image'    => 'tiramisu',
                'en' => ['name' => 'Tiramisu',                'description' => 'Classic Italian dessert.'],
                'ru' => ['name' => 'Тирамису',                'description' => 'Классический итальянский десерт.'],
                'tm' => ['name' => 'Tiramisu',                'description' => 'Klassik italýan deserti.'],
            ],
            [
                'category' => 'Desserts',
                'price'    => 4.50,
                'image'    => 'brownie',
                'en' => ['name' => 'Brownie',                 'description' => 'Warm chocolate brownie.'],
                'ru' => ['name' => 'Брауни',                  'description' => 'Тёплый шоколадный брауни.'],
                'tm' => ['name' => 'Brauni',                  'description' => 'Gyzgyn şokoladly brauni.'],
            ],
            [
                'category' => 'Desserts',
                'price'    => 4.20,
                'image'    => 'ice-cream-sundae',
                'en' => ['name' => 'Ice Cream Sundae',        'description' => 'Vanilla ice cream with chocolate syrup.'],
                'ru' => ['name' => 'Мороженое Сандей',        'description' => 'Ванильное мороженое с шоколадным сиропом.'],
                'tm' => ['name' => 'Dondurma Sundae',         'description' => 'Şokolad siroby bilen wanilli dondurma.'],
            ],
            [
                'category' => 'Desserts',
                'price'    => 4.80,
                'image'    => 'apple-pie',
                'en' => ['name' => 'Apple Pie',               'description' => 'Fresh baked apple pie slice.'],
                'ru' => ['name' => 'Яблочный Пирог',          'description' => 'Свежий яблочный пирог.'],
                'tm' => ['name' => 'Almaly Pirog',            'description' => 'Täze bişirilen almaly pirog.'],
            ],
            [
                'category' => 'Desserts',
                'price'    => 5.20,
                'image'    => 'baklava',
                'en' => ['name' => 'Baklava',                 'description' => 'Traditional sweet pastry with nuts.'],
                'ru' => ['name' => 'Пахлава',                 'description' => 'Традиционная сладкая выпечка с орехами.'],
                'tm' => ['name' => 'Pahlawa',                 'description' => 'Hoşboý ysly däp bolan süýji pahlawa.'],
            ],

            // ── Hot Drinks ───────────────────────────────────────────────────
            [
                'category' => 'Hot Drinks',
                'price'    => 2.50,
                'image'    => 'espresso',
                'en' => ['name' => 'Espresso',                'description' => 'Strong and bold coffee shot.'],
                'ru' => ['name' => 'Эспрессо',                'description' => 'Крепкий и насыщенный кофе.'],
                'tm' => ['name' => 'Espresso',                'description' => 'Güýçli we goýy kofe.'],
            ],
            [
                'category' => 'Hot Drinks',
                'price'    => 3.50,
                'image'    => 'cappuccino',
                'en' => ['name' => 'Cappuccino',              'description' => 'Coffee with steamed milk foam.'],
                'ru' => ['name' => 'Капучино',                'description' => 'Кофе с молочной пенкой.'],
                'tm' => ['name' => 'Kapuçino',                'description' => 'Süýt köpürjikli kofe.'],
            ],
            [
                'category' => 'Hot Drinks',
                'price'    => 3.80,
                'image'    => 'latte',
                'en' => ['name' => 'Latte',                   'description' => 'Smooth coffee with milk.'],
                'ru' => ['name' => 'Латте',                   'description' => 'Нежный кофе с молоком.'],
                'tm' => ['name' => 'Latte',                   'description' => 'Süýtli ýumşak kofe.'],
            ],
            [
                'category' => 'Hot Drinks',
                'price'    => 2.90,
                'image'    => 'americano',
                'en' => ['name' => 'Americano',               'description' => 'Espresso diluted with hot water.'],
                'ru' => ['name' => 'Американо',               'description' => 'Эспрессо с горячей водой.'],
                'tm' => ['name' => 'Amerikano',               'description' => 'Gyzgyn suw goşulan espresso.'],
            ],
            [
                'category' => 'Hot Drinks',
                'price'    => 4.20,
                'image'    => 'mocha',
                'en' => ['name' => 'Mocha',                   'description' => 'Chocolate flavored coffee drink.'],
                'ru' => ['name' => 'Мокка',                   'description' => 'Кофейный напиток со вкусом шоколада.'],
                'tm' => ['name' => 'Mokka',                   'description' => 'Şokolad tagamly kofe içgisi.'],
            ],
            [
                'category' => 'Hot Drinks',
                'price'    => 3.90,
                'image'    => 'hot-chocolate',
                'en' => ['name' => 'Hot Chocolate',           'description' => 'Warm creamy chocolate drink.'],
                'ru' => ['name' => 'Горячий Шоколад',         'description' => 'Тёплый сливочный шоколадный напиток.'],
                'tm' => ['name' => 'Gyzgyn Şokolad',          'description' => 'Ýyly kremli şokolad içgisi.'],
            ],
            [
                'category' => 'Hot Drinks',
                'price'    => 2.00,
                'image'    => 'turkish-tea',
                'en' => ['name' => 'Turkish Tea',             'description' => 'Traditional black tea served hot.'],
                'ru' => ['name' => 'Турецкий Чай',            'description' => 'Традиционный чёрный чай.'],
                'tm' => ['name' => 'Türk Çaýy',               'description' => 'Adaty gara türk çaýy.'],
            ],

            // ── Cold Drinks ──────────────────────────────────────────────────
            [
                'category' => 'Cold Drinks',
                'price'    => 3.50,
                'image'    => 'iced-coffee',
                'en' => ['name' => 'Iced Coffee',             'description' => 'Chilled coffee with ice.'],
                'ru' => ['name' => 'Айс Кофе',                'description' => 'Охлаждённый кофе со льдом.'],
                'tm' => ['name' => 'Buzly Kofe',              'description' => 'Buz bilen sowadylan kofe.'],
            ],
            [
                'category' => 'Cold Drinks',
                'price'    => 2.90,
                'image'    => 'lemonade',
                'en' => ['name' => 'Lemonade',                'description' => 'Fresh lemon drink.'],
                'ru' => ['name' => 'Лимонад',                 'description' => 'Освежающий лимонный напиток.'],
                'tm' => ['name' => 'Limonad',                 'description' => 'Täze limonly serginlediji içgi.'],
            ],
            [
                'category' => 'Cold Drinks',
                'price'    => 2.50,
                'image'    => 'cola',
                'en' => ['name' => 'Cola',                    'description' => 'Classic soft drink.'],
                'ru' => ['name' => 'Кола',                    'description' => 'Классический газированный напиток.'],
                'tm' => ['name' => 'Kola',                    'description' => 'Klassik gazly içgi.'],
            ],
            [
                'category' => 'Cold Drinks',
                'price'    => 3.20,
                'image'    => 'orange-juice',
                'en' => ['name' => 'Orange Juice',            'description' => 'Fresh squeezed orange juice.'],
                'ru' => ['name' => 'Апельсиновый Сок',        'description' => 'Свежевыжатый апельсиновый сок.'],
                'tm' => ['name' => 'Apelsin Şiresi',          'description' => 'Täze sykylan apelsin şiresi.'],
            ],
            [
                'category' => 'Cold Drinks',
                'price'    => 4.50,
                'image'    => 'milkshake',
                'en' => ['name' => 'Milkshake',               'description' => 'Creamy vanilla milkshake.'],
                'ru' => ['name' => 'Милкшейк',                'description' => 'Сливочный ванильный милкшейк.'],
                'tm' => ['name' => 'Milkşeýk',                'description' => 'Kremli wanilli milkşeýk.'],
            ],
            [
                'category' => 'Cold Drinks',
                'price'    => 2.80,
                'image'    => 'iced-tea',
                'en' => ['name' => 'Iced Tea',                'description' => 'Refreshing cold tea drink.'],
                'ru' => ['name' => 'Холодный Чай',            'description' => 'Освежающий холодный чай.'],
                'tm' => ['name' => 'Sowuk Çaý',               'description' => 'Serginlediji sowuk çaý.'],
            ],

            // ── Vegetarian ───────────────────────────────────────────────────
            [
                'category' => 'Vegetarian',
                'price'    => 7.50,
                'image'    => 'veggie-burger',
                'en' => ['name' => 'Veggie Burger',           'description' => 'Plant-based burger with fresh veggies.'],
                'ru' => ['name' => 'Вегетарианский Бургер',   'description' => 'Растительный бургер со свежими овощами.'],
                'tm' => ['name' => 'Wegetarian Burger',       'description' => 'Täze gök önümler bilen ösümlik esasly burger.'],
            ],
            [
                'category' => 'Vegetarian',
                'price'    => 6.50,
                'image'    => 'grilled-veggies',
                'en' => ['name' => 'Grilled Veggies',         'description' => 'Mixed grilled vegetables.'],
                'ru' => ['name' => 'Овощи Гриль',             'description' => 'Ассорти овощей на гриле.'],
                'tm' => ['name' => 'Grill Göknönümleri',      'description' => 'Grill edilen dürli gök önümler.'],
            ],
            [
                'category' => 'Vegetarian',
                'price'    => 7.20,
                'image'    => 'stuffed-peppers',
                'en' => ['name' => 'Stuffed Peppers',         'description' => 'Bell peppers filled with rice and vegetables.'],
                'ru' => ['name' => 'Фаршированные Перцы',     'description' => 'Болгарские перцы с овощной начинкой.'],
                'tm' => ['name' => 'Doldurylan Burç',         'description' => 'Gök önümli içlik bilen taýýarlanan burçlar.'],
            ],

            // ── Vegan ────────────────────────────────────────────────────────
            [
                'category' => 'Vegan',
                'price'    => 8.50,
                'image'    => 'vegan-bowl',
                'en' => ['name' => 'Vegan Bowl',              'description' => 'Plant-based bowl with grains and veggies.'],
                'ru' => ['name' => 'Веган Боул',              'description' => 'Растительный боул с крупами и овощами.'],
                'tm' => ['name' => 'Wegetarian Bowl',         'description' => 'Däneler we gök önümler bilen vegan bowl.'],
            ],
            [
                'category' => 'Vegan',
                'price'    => 7.90,
                'image'    => 'vegan-wrap',
                'en' => ['name' => 'Vegan Wrap',              'description' => 'Wrap filled with vegan ingredients.'],
                'ru' => ['name' => 'Веган Врап',              'description' => 'Врап с веганской начинкой.'],
                'tm' => ['name' => 'Wegetarian Wrap',         'description' => 'Vegan önümler bilen taýýarlanan wrap.'],
            ],
            [
                'category' => 'Vegan',
                'price'    => 8.20,
                'image'    => 'vegan-pasta',
                'en' => ['name' => 'Vegan Pasta',             'description' => 'Pasta with tomato sauce and vegetables.'],
                'ru' => ['name' => 'Веган Паста',             'description' => 'Паста с томатным соусом и овощами.'],
                'tm' => ['name' => 'Wegetarian Pasta',        'description' => 'Pomidor sousy we gök önümler bilen pasta.'],
            ],
        ];
    }
}