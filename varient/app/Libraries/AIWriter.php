<?php

namespace App\Libraries;

use InvalidArgumentException;
use Exception;

/**
 * Class AIWriter
 *
 * A unified library to handle content generation using advanced AI models.
 * Supports OpenAI (ChatGPT) and Google Gemini APIs, enforcing structured
 * JSON outputs optimized for CMS usage (Title, Summary, Keywords, Content).
 */
class AIWriter
{
    /** @var string The selected AI provider */
    protected string $provider;

    /** @var string The authentication API key for the selected provider */
    protected string $apiKey;

    /** @var string The specific model string to be used */
    protected string $model;

    // API Endpoints
    private const URL_OPENAI = 'https://api.openai.com/v1/chat/completions';
    private const URL_GEMINI = 'https://generativelanguage.googleapis.com/v1beta/models/';

    /**
     * Defined models with their readable names categorized by provider
     * @var array
     */
    public static array $models = [
        'chatgpt' => [
            'gpt-5.4'      => 'GPT-5.4 (Standard)',
            'gpt-5.4-mini' => 'GPT-5.4 Mini (Fast)',
            'gpt-5.4-nano' => 'GPT-5.4 Nano (Budget)',
            'gpt-5.2'      => 'GPT-5.2 (Legacy Pro)',
            'gpt-4.1'      => 'GPT-4.1 (Legacy Stable)',
            'gpt-4.1-mini' => 'GPT-4.1 Mini (Legacy Budget)'
        ],
        'gemini'  => [
            'gemini-3.1-pro-preview'        => 'Gemini 3.1 Pro (Best)',
            'gemini-3.1-flash-lite-preview' => 'Gemini 3.1 Flash-Lite (Budget)',
            'gemini-3-flash-preview'        => 'Gemini 3.0 Flash (Fast)',
            'gemini-2.5-pro'                => 'Gemini 2.5 Pro (Stable)',
            'gemini-2.5-flash'              => 'Gemini 2.5 Flash (Legacy Fast)',
            'gemini-2.5-flash-lite'         => 'Gemini 2.5 Flash-Lite (Legacy Budget)'
        ]
    ];

    /**
     * Available writing tones
     * @var array
     */
    public static array $tones = [
        'neutral',
        'professional',
        'academic',
        'casual',
        'critical',
        'formal',
        'humorous',
        'inspirational',
        'persuasive'
    ];

    /**
     * Available content length configurations
     * @var array
     */
    public static array $lengths = [
        'very_short',
        'short',
        'medium',
        'long',
        'very_long'
    ];

    /**
     * Constructor
     *
     * Initializes the AI provider credentials and validates the selected model
     *
     * @param object $config The global configuration object containing 'ai_writer' settings.
     * @throws InvalidArgumentException If the configured model is not in the whitelist.
     */
    public function __construct($config)
    {
        $settings = $config->ai_writer;

        $this->provider = $settings->provider ?? 'chatgpt';

        // Select credentials based on provider
        if ($this->provider === 'gemini') {
            $this->apiKey = trim($settings->gemini_api_key ?? '');
            $this->model = $settings->gemini_model ?? 'gemini-1.5-flash';
        } else {
            $this->apiKey = trim($settings->chatgpt_api_key ?? '');
            $this->model = $settings->chatgpt_model ?? 'gpt-4o-mini';
        }

        // Security: Strict Whitelist Validation
        if (!isset(self::$models[$this->provider][$this->model])) {
            throw new InvalidArgumentException("Invalid or unsupported AI model selected: {$this->model}");
        }
    }

    /**
     * Main execution method
     *
     * @param object $options Object containing parameters like 'topic', 'langName', 'tone', 'generateType', 'length'.
     * @return array Returns an associative array with 'status', 'title', 'content', etc. or an error message.
     */
    public function write(object $options): array
    {
        if (empty($this->apiKey)) {
            return ['status' => 'error', 'message' => "API Key is missing for {$this->provider}."];
        }

        try {
            $prompt = $this->buildPrompt($options);

            if ($this->provider === 'gemini') {
                return $this->runGemini($prompt);
            }

            return $this->runChatGPT($prompt);

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Builds the system prompt with dynamic JSON structure
     *
     * @param object $options The configuration object for the generation request.
     * @return string The generated prompt ready to be sent to the AI API.
     * @throws InvalidArgumentException If the 'topic' is missing from the options.
     */
    private function buildPrompt(object $options): string
    {
        if (empty($options->topic)) {
            throw new InvalidArgumentException('Topic is required');
        }

        $topic = $options->topic;
        $language = $options->langName ?? 'English';
        $tone = $options->tone ?? 'neutral';
        $genType = $options->generateType ?? 'all';
        $rawLength = $options->length ?? 'medium';

        $lengthMap = [
            'very_short' => 'STRICT MAX 100 words. 1 short paragraph only.',
            'short'      => 'STRICT 200–300 words. Quick news update. No filler.',
            'medium'     => 'STRICT 500–600 words. Standard blog post. Balanced detail.',
            'long'       => 'STRICT 900–1000 words. Detailed article. Comprehensive.',
            'very_long'  => 'MINIMUM 1200 words. In-depth analysis. Multiple sections.'
        ];
        $targetLength = $lengthMap[$rawLength] ?? 'STRICT 500–600 words';

        $seoRules = '';

        if ($genType === 'content_only') {
            $jsonStructure = <<<JSON
{
  "content": "HTML string"
}
JSON;
        } elseif ($genType === 'content_and_title') {
            $jsonStructure = <<<JSON
{
  "title": "SEO-friendly title (max 70 chars)",
  "content": "HTML string"
}
JSON;
            $seoRules = <<<RULES
SEO Requirements:
- Title: Catchy, SEO-optimized.
RULES;

        } else {
            $jsonStructure = <<<JSON
{
  "title": "SEO-friendly title (max 70 chars)",
  "summary": "Meta description (max 160 chars)",
  "keywords": ["tag1", "tag2", "tag3"],
  "content": "HTML string"
}
JSON;
            $seoRules = <<<RULES
SEO Requirements:
- Title: Catchy, SEO-optimized.
- Keywords: 3-5 relevant, specific, high-traffic tags (No generic terms).
- Summary: Compelling meta description.
RULES;
        }

        return <<<PROMPT
Role: Expert Content Writer API. 
Constraint: Output ONLY valid JSON. No markdown blocks, no conversational text.

Task: Generate professional, ready-to-publish content for: "{$topic}"

Settings:
- Language: {$language} (Write ALL content values in this language. JSON keys MUST remain in English. Do NOT translate keys.)
- Tone: {$tone}
- Length: {$targetLength}

HTML & Structure Rules:
- CRITICAL: DO NOT repeat the Title at the start. Start directly with the text.
- CRITICAL: Organic Flow. Do not force subheadings. Use them only for distinct topic shifts.
- HTML Tags: Use <p>, <h3>, <ul>, <ol>, <li>, <strong> naturally and ONLY when necessary.
- Restricted Tags: STRICTLY NO <h1>, <h2>, <blockquote>.
- Format: Return a pure HTML string ready for CMS.

{$seoRules}

Output Format:
Return a single valid JSON object matching this structure:
{$jsonStructure}
PROMPT;
    }

    /**
     * Executes the request against the OpenAI API
     *
     * @param string $prompt The formatted system and user prompt.
     * @return array The standardized response array from the API.
     */
    private function runChatGPT(string $prompt): array
    {
        $payload = [
            'model'       => $this->model,
            'messages'    => [
                ['role' => 'system', 'content' => 'You are a JSON generator.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7
        ];

        // Strict JSON Mode Check
        if (in_array($this->model, array_keys(self::$models['chatgpt']), true)) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

        return $this->sendRequest(self::URL_OPENAI, $payload, $headers);
    }

    /**
     * Executes the request against the Google Gemini API
     *
     * @param string $prompt The formatted prompt.
     * @return array The standardized response array from the API.
     */
    private function runGemini(string $prompt): array
    {
        $url = self::URL_GEMINI . $this->model . ':generateContent?key=' . $this->apiKey;

        $payload = [
            'contents'         => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature'      => 0.7,
                'responseMimeType' => 'application/json'
            ]
        ];

        $headers = ['Content-Type: application/json'];

        return $this->sendRequest($url, $payload, $headers);
    }

    /**
     * Shared cURL Handler for HTTP requests to AI APIs
     *
     * @param string $url The endpoint URL.
     * @param array $data The payload to be JSON encoded.
     * @param array $headers The HTTP headers.
     * @return array Returns the decoded response or an error array.
     */
    private function sendRequest(string $url, array $data, array $headers): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Timeout settings to prevent hanging processes
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AIWriter/2.1');

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Connection Error Handling
        if ($curlError) {
            return ['status' => 'error', 'message' => 'Connection Error: ' . $curlError];
        }

        $result = json_decode($response, true);

        // JSON Integrity Check
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['status' => 'error', 'message' => 'Invalid JSON received from API Provider.'];
        }

        // API Error Handling (HTTP != 200)
        if ($httpCode !== 200) {
            $msg = $result['error']['message'] ?? 'Unknown API Error';
            return ['status' => 'error', 'message' => "API Error ($httpCode): $msg"];
        }

        return $this->parseResponse($result);
    }

    /**
     * Response Parser & Cleaner
     *
     * @param array $result The raw decoded JSON array from the API.
     * @return array Returns a mapped associative array containing status, title, content, etc.
     */
    private function parseResponse(array $result): array
    {
        $rawContent = '';

        if ($this->provider === 'gemini') {
            // Robust Gemini path extraction (handles text or inlineData)
            $rawContent = $result['candidates'][0]['content']['parts'][0]['text']
                ?? $result['candidates'][0]['content']['text']
                ?? '';
        } else {
            // OpenAI standard path
            $rawContent = $result['choices'][0]['message']['content'] ?? '';
        }

        if (empty($rawContent)) {
            return ['status' => 'error', 'message' => 'Empty response from AI Provider'];
        }

        // Advanced Regex: Removes ```json ... ``` wrappers strictly
        if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $rawContent, $matches)) {
            $rawContent = $matches[1];
        }

        $parsedData = json_decode($rawContent, true);

        // Strict Validation: Fail if JSON is invalid
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status'  => 'error',
                'message' => 'AI Response Parsing Failed: Invalid JSON format.',
                'raw'     => $rawContent // Useful for debugging
            ];
        }

        // Format keywords (Array to String)
        $keywords = '';
        if (isset($parsedData['keywords'])) {
            $keywords = is_array($parsedData['keywords'])
                ? implode(', ', $parsedData['keywords'])
                : $parsedData['keywords'];
        }

        return [
            'status'   => 'success',
            'title'    => $parsedData['title'] ?? '',
            'summary'  => $parsedData['summary'] ?? '',
            'keywords' => $keywords,
            'content'  => $parsedData['content'] ?? ''
        ];
    }

    /**
     * Parse AI JSON data from a raw string
     *
     * @param string|array $text The raw text or array to extract JSON from.
     * @return array|null Returns the decoded associative array, or null if invalid.
     */
    public function extractJson($text)
    {
        if (is_array($text)) {
            return $text;
        }

        if (!is_string($text)) {
            return [];
        }

        $text = preg_replace('/^```json/m', '', $text);
        $text = preg_replace('/^```/m', '', $text);

        $text = trim($text);

        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start !== false && $end !== false) {
            $text = substr($text, $start, $end - $start + 1);
        }

        return json_decode($text, true);
    }
}