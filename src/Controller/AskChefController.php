<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AskChefController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {}

    #[Route('/ask-chef', name: 'app_ask_chef')]
    public function askChef(): Response
    {
        return $this->render('frontend/ask_chef.html.twig');
    }

    #[Route('/api/ask-chef', name: 'api_ask_chef', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';

        if (empty($message)) {
            return $this->json(['error' => 'Message is required'], 400);
        }

        $apiKey = $this->getParameter('kernel.environment') === 'dev'
            ? ($_ENV['GROQ_API_KEY'] ?? '')
            : '';
        $model = $_ENV['GROQ_MODEL'] ?? 'llama-3.3-70b-versatile';

        // If no API key is configured, fall back to local mock responses
        if (empty($apiKey) || str_starts_with($apiKey, 'gsk_your_')) {
            return $this->json(['response' => $this->mockResponse($message)]);
        }

        try {
            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are Chef Karim, a warm and knowledgeable Tunisian chef with 20+ years of experience. You specialize in Tunisian cuisine — recipes, techniques, spices, and regional traditions. Answer in a friendly, conversational tone. Keep responses concise (2-3 paragraphs max). If asked about something unrelated to Tunisian food or cooking, gently steer the conversation back to cuisine.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 512,
                ],
            ]);

            $result = $response->toArray();
            $reply = $result['choices'][0]['message']['content'] ?? 'I apologize, I am having trouble connecting to my kitchen right now.';

            return $this->json(['response' => $reply]);
        } catch (\Exception $e) {
            return $this->json(['response' => $this->mockResponse($message)]);
        }
    }

    private function mockResponse(string $message): string
    {
        $lower = mb_strtolower($message);

        $knowledge = [
            'harissa' => 'Harissa is the fiery soul of Tunisian cuisine — a vibrant red paste made from roasted hot chili peppers, garlic, caraway, coriander, and olive oil. Use it as a condiment, stir it into stews, rub it on meats before grilling, or blend it with yogurt for a spicy dip. Start with a small amount; its heat builds quietly but strongly!',
            'couscous' => 'True Tunisian couscous is steamed, never boiled. Here is the secret: toss dry semolina with olive oil, then steam it uncovered three times. Between each steam, break up the grains with your fingers and sprinkle with salted water. The result should be light, fluffy, and each grain separate. Never use instant couscous for a real Tunisian feast.',
            'tabil' => 'Tabil (تابل) is a signature Tunisian spice blend of coriander seeds, caraway, garlic powder, and dried chili. The word simply means "seasoning" in Tunisian dialect. Toast whole spices lightly, then grind them fresh. It is the heartbeat of Tunisian stews, grilled meats, and even egg dishes.',
            'brik' => 'Brik (or Brick) is the crown jewel of Tunisian street food — a paper-thin malsouka wrapper folded around a runny egg, tuna, capers, and parsley, then deep-fried until shatteringly crisp. The key is assembling seconds before frying and splashing hot oil over the top to cook the egg white before flipping. Serve with a squeeze of lemon.',
            'tagine' => 'A Tunisian tagine is nothing like the Moroccan clay-pot dish. Here, it is a rich, slow-cooked frittata-like bake with layers of meat, cheese, vegetables, and beaten eggs, finished in the oven until golden. The spices that define it are tabil, harissa, and a generous grating of nutmeg.',
            'mloukhia' => 'Mloukhia is a deeply loved Tunisian stew of jute mallow leaves — either fresh or dried powder — cooked into a silky, mucilaginous green sauce with lamb or beef. The secret is patience: simmer it low and slow for up to five hours until the oil separates and floats to the top. Serve over bread to soak up every drop.',
            'merguez' => 'Merguez are spicy North African sausages made from lamb or beef, heavily spiced with harissa, tabil, cumin, and garlic. They are grilled, pan-fried, or added to couscous and stews. For authentic flavor, let the meat rest with spices overnight before stuffing into casings.',
            'lablabi' => 'Lablabi is Tunisia\'s most beloved street food — a hearty chickpea soup poured over chunks of day-old bread and topped with cumin, harissa, and a poached egg. The trick is using stale bread (it becomes a sturdy sponge) and toasting caraway seeds fresh before grinding.',
            'makroudh' => 'Makroudh is a traditional date-filled semolina pastry from Kairouan, deep-fried and soaked in floral syrup. The semolina dough is kneaded with olive oil until it resembles wet sand, filled with a fragrant date paste, shaped into diamonds, fried until crisp, and then bathed in orange blossom-scented syrup.',
            'olive oil' => 'Tunisia is one of the world\'s largest producers of olive oil, and it is the foundation of nearly every dish. Use a robust extra-virgin olive oil for cooking your qalia (spice base) and a delicate one for finishing salads and drizzling over couscous. Never underestimate its role — it carries the flavor.',
            'preserved lemon' => 'Preserved lemons are salt-cured whole lemons that develop a mellow, fermented brightness essential to Tunisian tagines and salads. Rinse the salt off before using, then dice the rind finely. The flesh can be discarded or blended into dressings. They keep for months in the fridge.',
            'mint tea' => 'Tunisian mint tea is a ritual of hospitality. Green tea is brewed strong, then fresh mint and generous sugar are added. The crucial step is pouring from a height — at least 30cm — to oxygenate the tea and create the signature froth on top. It is never served just once; expect three rounds.',
        ];

        foreach ($knowledge as $keyword => $response) {
            if (str_contains($lower, $keyword)) {
                return $response;
            }
        }

        if (str_contains($lower, 'hello') || str_contains($lower, 'hi') || str_contains($lower, 'marhaba')) {
            return 'Marhaba! Welcome to my kitchen. I am Chef Karim, your guide to the rich world of Tunisian cuisine. Ask me about recipes, techniques, ingredients, or the stories behind our beloved dishes.';
        }

        return 'That is a wonderful question! While I do not have a specific answer for that in my kitchen notes, I would love to explore it with you. Could you rephrase, or ask about a specific Tunisian dish, spice, or technique? I know quite a lot about harissa, couscous, tabil, brik, and our regional specialties.';
    }
}
