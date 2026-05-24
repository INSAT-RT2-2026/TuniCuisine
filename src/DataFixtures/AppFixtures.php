<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Entity\Region;
use App\Entity\Step;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ===== REGIONS =====
        $regionsData = [
            ['name' => 'Tunis', 'type' => 'Capital/Urban', 'subtitle' => 'Coastal capital with access to both Mediterranean seafood and inland ingredients', 'description' => 'The capital and largest city, Tunis is the culinary melting pot of Tunisia. Here you will find traditional dishes alongside French-influenced cafés and restaurants.', 'iconClass' => 'fa-solid fa-location-dot', 'image' => null, 'didYouKnow' => 'The medina of Tunis is a UNESCO World Heritage site with countless food stalls and traditional eateries'],
            ['name' => 'Sfax', 'type' => 'Coastal', 'subtitle' => 'Major Mediterranean port city surrounded by olive groves', 'description' => 'Tunisia\'s second-largest city and an important port. Sfax is known for its olive oil production and exceptional seafood dishes.', 'iconClass' => 'fa-solid fa-water', 'image' => null, 'didYouKnow' => 'Sfax produces some of the world\'s finest olive oil, with trees dating back centuries'],
            ['name' => 'Sousse', 'type' => 'Coastal', 'subtitle' => 'Beautiful Mediterranean coastline with fertile agricultural surroundings', 'description' => 'A coastal city known as the "Pearl of the Sahel." Sousse combines beach resort culture with authentic Tunisian culinary traditions.', 'iconClass' => 'fa-solid fa-water', 'image' => null, 'didYouKnow' => 'The Sousse medina has ancient fortifications and traditional spice markets'],
            ['name' => 'Kairouan', 'type' => 'Desert/Oasis', 'subtitle' => 'Inland city in the central plains, known for date palm oases', 'description' => 'The fourth holiest city in Islam and the historical capital. Kairouan is famous for its traditional sweets and pastries.', 'iconClass' => 'fa-solid fa-tree', 'image' => null, 'didYouKnow' => 'Makroudh from Kairouan is considered the finest in all of Tunisia'],
            ['name' => 'Djerba', 'type' => 'Coastal', 'subtitle' => 'Island paradise with Mediterranean beaches and traditional agriculture', 'description' => 'An island in the Gulf of Gabès, Djerba has a unique multicultural heritage reflected in its distinctive cuisine.', 'iconClass' => 'fa-solid fa-water', 'image' => null, 'didYouKnow' => 'Djerba is home to one of the oldest Jewish communities in North Africa, influencing local cuisine'],
        ];

        $regions = [];
        foreach ($regionsData as $data) {
            $region = new Region();
            $region->setName($data['name'])
                ->setType($data['type'])
                ->setSubtitle($data['subtitle'])
                ->setDescription($data['description'])
                ->setIconClass($data['iconClass'])
                ->setImage($data['image'])
                ->setDidYouKnow($data['didYouKnow']);
            $manager->persist($region);
            $regions[$data['name']] = $region;
        }

        // ===== INGREDIENTS =====
        $ingredientsData = [
            ['name' => 'Harissa', 'category' => 'Spices & Condiments', 'subtitle' => 'Chili & Oil', 'description' => 'The soul of Tunisian cuisine. A fiery red paste made from hot chili peppers blended with garlic, olive oil, caraway, and coriander. Foundational to almost every Tunisian meal.', 'image' => 'https://salimaskitchen.com/wp-content/uploads/2022/07/Homemade-Harissa-A-Moroccan-Cooking-Staple-Salimas-Kitchen-12.jpg', 'badgeClass' => 'badge-spice'],
            ['name' => 'Tabil', 'category' => 'Spices & Condiments', 'subtitle' => null, 'description' => 'A unique Tunisian spice blend combining coriander seeds, caraway, garlic powder, dried chili peppers. The name means "seasoning" in Tunisian dialect.', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS1KDBjrrq0roVqDz-yPFAjaaESCXGERmu9va2jFmFb6yssNIpg5piLTqFfZhRtJGVOZ0z6ZNkjYZKDGKo2hL4jdh86R36eXVLvO8Ulxv_rig&s=10', 'badgeClass' => 'badge-spice'],
            ['name' => 'Olive Oil', 'category' => 'Oils & Fats', 'subtitle' => 'Liquid Gold', 'description' => 'Tunisia is one of the world\'s biggest producers of high quality olive oil. Used prominently in cooking, dressing, and as a finishing oil in Tunisian cuisine.', 'image' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=400&q=80', 'badgeClass' => 'badge-oil'],
            ['name' => 'Caraway Seeds', 'category' => 'Spices & Condiments', 'subtitle' => null, 'description' => 'Aromatic seeds used in distinctly warm, slightly sweet flavour. Caraway is a signature spice in many Tunisian dishes, especially soups and stews.', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSbR9PZaQcBwWuaoyWRhSQSfl5r_uutdW8Y5Ff4Zhefl3BhyMILcJAjFHK_Lb5e6LrPnXTwNBfVcnpt96VKAnNJcBG8nGfUolqBdpIvpg&s=10', 'badgeClass' => 'badge-spice'],
            ['name' => 'Merguez', 'category' => 'Meats & Proteins', 'subtitle' => null, 'description' => 'Spicy North African lamb or beef sausages flavoured with harissa, tabil, and aromatic spices. A staple in Tunisian grills and stews.', 'image' => 'https://i.ytimg.com/vi/4iWjJI-pCgI/maxresdefault.jpg', 'badgeClass' => 'badge-meat'],
            ['name' => 'Dates', 'category' => 'Fruits & Vegetables', 'subtitle' => null, 'description' => 'Tunisia grows over 100 varieties of dates. They\'re eaten fresh, stuffed with almonds, or used in sweet dishes. Deglet Nour is the most famous variety.', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRdSHPSjPWmGmOT3oL_uhbvmF5YdVN_C9_zOA&s', 'badgeClass' => 'badge-fruit'],
            ['name' => 'Semolina', 'category' => 'Grains & Pastry', 'subtitle' => null, 'description' => 'The foundational grain used to make couscous, the national dish. Fine semolina is also used to bake traditional sweet and savoury Tunisian breads and pastries.', 'image' => 'https://cdn.salla.sa/KjBPn/3dyQ3CaD0J9J12UTPLBR3FdV4ZuClUj2TTZvFpop.jpg', 'badgeClass' => 'badge-grain'],
            ['name' => 'Chickpeas', 'category' => 'Legumes', 'subtitle' => null, 'description' => 'A cornerstone of Tunisian cuisine, chickpeas are used extensively in the popular street food lablabi, couscous, and salads throughout the country.', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSrYOnr2vO_9tvNcJMJKdEGML-rtqp3oPv1xA&s', 'badgeClass' => 'badge-legume'],
            ['name' => 'Coriander', 'category' => 'Spices & Condiments', 'subtitle' => null, 'description' => 'Both the fresh leaves and dried seeds are used extensively. The seed variety is a key component in tabil spice blend, while fresh coriander garnishes many dishes.', 'image' => 'https://malekicommercialco.com/wp-content/uploads/2020/01/a1.jpg', 'badgeClass' => 'badge-spice'],
            ['name' => 'Preserved Lemons', 'category' => 'Pickles & Preserves', 'subtitle' => null, 'description' => 'Whole lemons salt-cured over time, developing a mellow, fermented flavour with deep salty brightness. Used in tajines and couscous to brighten rich slow-cooked dishes.', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQxhCcuWa_Kfp1Qm9i09PFWKbPWJc6psBOQLA&s', 'badgeClass' => 'badge-pickle'],
            ['name' => 'Orange Blossom Water', 'category' => 'Aromatics', 'subtitle' => null, 'description' => 'A delicate floral water distilled from orange blossoms, cherished throughout Maghreb cuisine. The iconic aromatic used in pastries and sweets to impart a floral, perfumed essence.', 'image' => 'https://cdn.salla.sa/WlZpxV/6Y786T7hYxbPWKXvogTG4N5AcxEsDsP8gRSwXOfM.jpg', 'badgeClass' => 'badge-aromatic'],
            ['name' => 'Malsouka', 'category' => 'Grains & Pastry', 'subtitle' => null, 'description' => 'Ultra-thin pastry sheets similar to phyllo, used to wrap fillings for making brik and other Tunisian pastries. Requires skill to make by hand.', 'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=400&q=80', 'badgeClass' => 'badge-grain'],
        ];

        $ingredients = [];
        foreach ($ingredientsData as $data) {
            $ing = new Ingredient();
            $ing->setName($data['name'])
                ->setCategory($data['category'])
                ->setSubtitle($data['subtitle'])
                ->setDescription($data['description'])
                ->setImage($data['image'])
                ->setBadgeClass($data['badgeClass']);
            $manager->persist($ing);
            $ingredients[$data['name']] = $ing;
        }

        // ===== RECIPES =====
        $recipesData = [
            [
                'name' => 'Couscous Royal',
                'region' => 'Tunis',
                'difficulty' => 'Medium',
                'prepTime' => 45,
                'cookTime' => 120,
                'servings' => 6,
                'description' => 'The crown jewel of Tunisian cuisine — steamed semolina topped with lamb, chicken, merguez, and a medley of vegetables in a rich, spiced broth. A Friday tradition in every Tunisian home.',
                'image' => 'https://images.unsplash.com/photo-1541518763669-27fef04b14ea?w=800&q=80',
                'ingredients' => [
                    ['Semolina', '500', 'g'],
                    ['Olive Oil', '3', 'tbsp'],
                    ['Harissa', '2', 'tbsp'],
                    ['Tabil', '1', 'tsp'],
                    ['Caraway Seeds', '1', 'tsp'],
                    ['Chickpeas', '200', 'g'],
                ],
                'steps' => [
                    ['Toss the semolina with olive oil and rub between your palms to coat every grain. This prevents clumping during steaming.', 'Use your fingers, not a spoon — the mechanical action separates the grains.'],
                    ['Steam the semolina in a couscoussier for 20 minutes uncovered. Do not pack it — let it cascade loosely.', 'Never cover the couscous while steaming; condensation creates a soggy top layer.'],
                    ['Remove from heat, sprinkle with salted water, and break up clumps with your fingers. Return to steam for another 20 minutes.', 'The water should be warm, not boiling.'],
                    ['Brown the lamb and chicken in a heavy pot with onions, garlic, and tabil. Add vegetables, chickpeas, and broth. Simmer for 90 minutes.', 'Sear the meat hard — caramelization equals flavor.'],
                    ['Steam the couscous a third and final time. Pile it high on a large platter, make a well in the center, and ladle the meat and vegetables over the top.', 'Serve the broth on the side in small bowls for each guest.'],
                ],
            ],
            [
                'name' => 'Brik à l\'Oeuf',
                'region' => 'Tunis',
                'difficulty' => 'Medium',
                'prepTime' => 20,
                'cookTime' => 5,
                'servings' => 4,
                'description' => 'A paper-thin malsouka wrapper folded around a runny egg, tuna, capers, and parsley, then deep-fried until shatteringly crisp. The ultimate Tunisian street food.',
                'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=800&q=80',
                'ingredients' => [
                    ['Malsouka', '4', 'sheets'],
                    ['Olive Oil', 'for frying', ''],
                    ['Harissa', '1', 'tsp'],
                    ['Preserved Lemons', '1', 'tbsp'],
                ],
                'steps' => [
                    ['Lay a malsouka sheet on a clean surface in a diamond orientation. Place a spoonful of tuna, capers, and chopped parsley in the center.', 'Work quickly — the thin dough dries out in seconds.'],
                    ['Make a small well in the filling and crack a whole egg into it. Season with salt and pepper.', 'The egg should be room temperature so it cooks evenly.'],
                    ['Fold the bottom point up over the filling, then fold in the left and right sides to form a neat envelope.', 'Press gently to seal — do not tear the delicate wrapper.'],
                    ['Heat olive oil to 350°F (175°C). Slide the brik in carefully and immediately spoon hot oil over the top surface.', 'This "spoon technique" cooks the egg white before you flip it.'],
                    ['Flip once, fry until deep golden, then transfer to paper towels. Serve with a generous squeeze of lemon.', 'Eat immediately — brik is a fleeting pleasure, best consumed within minutes of frying.'],
                ],
            ],
            [
                'name' => 'Lablabi',
                'region' => 'Sfax',
                'difficulty' => 'Easy',
                'prepTime' => 10,
                'cookTime' => 45,
                'servings' => 4,
                'description' => 'Tunisia\'s most beloved street food — a hearty chickpea soup poured over chunks of day-old bread and topped with cumin, harissa, and a poached egg.',
                'image' => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=800&q=80',
                'ingredients' => [
                    ['Chickpeas', '400', 'g'],
                    ['Caraway Seeds', '1', 'tbsp'],
                    ['Harissa', '1', 'tbsp'],
                    ['Olive Oil', '3', 'tbsp'],
                ],
                'steps' => [
                    ['Soak dried chickpeas overnight, then simmer in salted water with a bay leaf until completely tender — about 45 minutes.', 'Do not salt the water initially; salt toughens chickpea skins.'],
                    ['Toast caraway seeds in a dry pan for 60 seconds until fragrant, then grind coarsely.', 'This unlocks volatile oils that make all the difference.'],
                    ['Ladle hot chickpeas and broth over chunks of stale bread in deep bowls.', 'Day-old bread is essential — fresh bread turns to mush.'],
                    ['Top each bowl with a poached egg, a drizzle of olive oil, ground caraway, and a dollop of harissa.', 'Break the yolk and stir everything together before eating.'],
                ],
            ],
            [
                'name' => 'Mloukhia',
                'region' => 'Djerba',
                'difficulty' => 'Hard',
                'prepTime' => 30,
                'cookTime' => 300,
                'servings' => 6,
                'description' => 'A deeply loved Tunisian stew of jute mallow leaves cooked into a silky green sauce with lamb. The secret is patience — simmer it low and slow for up to five hours.',
                'image' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=800&q=80',
                'ingredients' => [
                    ['Olive Oil', '4', 'tbsp'],
                    ['Coriander', '1', 'bunch'],
                    ['Harissa', '1', 'tsp'],
                    ['Tabil', '1', 'tsp'],
                ],
                'steps' => [
                    ['Whisk the dried mloukhia powder with cold olive oil to create a smooth lipid dispersion before adding any liquid.', 'This prevents the formation of impenetrable clumps.'],
                    ['Brown lamb chunks in a heavy pot, then add chopped onions, garlic, and coriander stems.', 'Use the stems too — they contain triple the flavor of the leaves.'],
                    ['Add the mloukhia paste, boiling water, and spices. Bring to a gentle simmer.', 'The broth should be dark green and slightly viscous.'],
                    ['Simmer uncovered for 4–5 hours, stirring only occasionally. The stew is done when the oil separates and floats to the top.', 'Do not constantly stir — let the oil separate naturally.'],
                    ['Serve over thick slices of country bread to soak up every drop of the sauce.', 'Mloukhia is always eaten with bread, never a spoon.'],
                ],
            ],
            [
                'name' => 'Makroudh',
                'region' => 'Kairouan',
                'difficulty' => 'Hard',
                'prepTime' => 60,
                'cookTime' => 30,
                'servings' => 24,
                'description' => 'A traditional date-filled semolina pastry from Kairouan, deep-fried and soaked in floral syrup. Considered the finest in all of Tunisia.',
                'image' => 'https://images.unsplash.com/photo-1609127973307-2e7f89c7e530?w=800&q=80',
                'ingredients' => [
                    ['Semolina', '500', 'g'],
                    ['Dates', '300', 'g'],
                    ['Orange Blossom Water', '2', 'tbsp'],
                    ['Olive Oil', '100', 'ml'],
                ],
                'steps' => [
                    ['Knead fine semolina with warm olive oil until it resembles wet sand. Rest for 30 minutes.', 'The oil must be worked into every grain; this is what creates the characteristic crumbly texture.'],
                    ['Pit and mash the dates with orange blossom water until you have a smooth, fragrant paste.', 'Deglet Nour dates are traditional and provide the best sweetness.'],
                    ['Roll the semolina dough into a log, make a groove down the center, and pipe the date filling inside. Seal and shape into diamond-cut fingers.', 'Work gently — the dough is fragile and cracks easily.'],
                    ['Deep-fry in oil at 340°F until deep golden brown and crisp.', 'Do not overcrowd the pan; the temperature will drop and the makroudh will absorb oil.'],
                    ['Immerse the hot pastries in cooled sugar syrup scented with orange blossom water. Let them absorb the syrup for at least an hour before serving.', 'The contrast between crisp exterior and syrupy interior is the signature texture.'],
                ],
            ],
        ];

        foreach ($recipesData as $rData) {
            $recipe = new Recipe();
            $recipe->setName($rData['name'])
                ->setRegion($regions[$rData['region']] ?? null)
                ->setDifficulty($rData['difficulty'])
                ->setPrepTime($rData['prepTime'])
                ->setCookTime($rData['cookTime'])
                ->setServings($rData['servings'])
                ->setDescription($rData['description'])
                ->setImage($rData['image']);

            foreach ($rData['ingredients'] as $ingData) {
                $ri = new RecipeIngredient();
                $ri->setRecipe($recipe)
                    ->setIngredient($ingredients[$ingData[0]])
                    ->setQuantity($ingData[1])
                    ->setUnit($ingData[2]);
                $manager->persist($ri);
                $recipe->addRecipeIngredient($ri);
            }

            foreach ($rData['steps'] as $idx => $stepData) {
                $step = new Step();
                $step->setRecipe($recipe)
                    ->setStepOrder($idx + 1)
                    ->setDescription($stepData[0])
                    ->setTip($stepData[1] ?? null);
                $manager->persist($step);
                $recipe->addStep($step);
            }

            $manager->persist($recipe);
        }

        $manager->flush();
    }
}
