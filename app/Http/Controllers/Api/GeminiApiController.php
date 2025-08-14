<?php
// app/Http/Controllers/Api/GeminiApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB; // <-- BARU: Impor DB Facade

class GeminiApiController extends Controller
{
    public function processCommand(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:255',
            'context' => 'required|string|in:homepage,detail_iklan,signup_page',
            'data' => 'nullable|array'
        ]);

        $textFromUser = $validated['text'];
        $context = $validated['context'];
        $prompt = '';

        // ... (Logika Prompt Engineering tetap sama) ...
        switch ($context) {
            case 'homepage':
                $prompt = "Anda adalah AI properti Waisaka. Pengguna ingin mencari properti. Dari teks '{$textFromUser}', identifikasi tipe properti (rumah, apartemen, tanah) dan lokasi. Jika tidak relevan dengan pencarian properti, jawab 'off_topic'. Jika relevan, berikan jawaban HANYA dalam format JSON: {\"action\": \"search\", \"filters\": {\"type\": \"...\", \"location\": \"...\"}}";
                break;
            case 'detail_iklan':
                $propertyId = $validated['data']['property_id'] ?? 'tidak diketahui';
                $prompt = "Anda adalah AI properti Waisaka. Pengguna ada di halaman iklan properti ID {$propertyId}. Dari teks '{$textFromUser}', identifikasi apakah pengguna ingin 'menghubungi marketing', 'share ke whatsapp', atau 'share ke facebook'. Jika tidak relevan, jawab 'off_topic'. Jika relevan, berikan jawaban HANYA dalam format JSON: {\"action\": \"contact_agent\" atau \"share_whatsapp\" atau \"share_facebook\"}";
                break;
        }

        $apiKey = env('GEMINI_API_KEY');
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

        try {
            $response = Http::post($apiUrl, [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            if ($response->successful()) {
                $geminiResult = $response->json();
                $responseText = $geminiResult['candidates'][0]['content']['parts'][0]['text'] ?? '';
                // Ambil data penggunaan token jika tersedia
                $tokensUsed = $geminiResult['usageMetadata']['totalTokenCount'] ?? null;

                // --- BARU: SIMPAN KE DATABASE LOG ---
                DB::table('ai_logs')->insert([
                    'user_id' => $request->user()->id_user, // Mengambil ID user yang sedang login
                    'context' => $context,
                    'user_text' => $textFromUser,
                    'gemini_response' => $responseText,
                    'tokens_used' => $tokensUsed,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // --- AKHIR BLOK LOGGING ---

                if (str_contains(strtolower($responseText), 'off_topic')) {
                    return response()->json(['action' => 'show_message', 'message' => 'Maaf, saya hanya bisa membantu terkait properti.']);
                }

                $jsonResponse = json_decode(trim($responseText), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return response()->json($jsonResponse);
                } else {
                    return response()->json(['action' => 'show_message', 'message' => $responseText]);
                }

            } else {
                return response()->json(['error' => 'Gagal menghubungi AI Service.'], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan pada server: ' . $e->getMessage()], 500);
        }
    }
}