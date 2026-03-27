<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Client;
use App\Models\Message;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();

        $phone = $data['from'] ?? null;
        $messageText = $data['message'] ?? null;
        $to = $data['to'] ?? null;

        if (!$phone || !$messageText) {
            return response()->json(['error' => 'Invalid data'], 400);
        }

        // Buscar negocio
        $business = Business::where('phone_number', $to)->first();

        if (!$business) {
            return response()->json(['error' => 'Business not found'], 404);
        }

        // Cliente
        $client = Client::firstOrCreate(
            ['phone' => $phone, 'business_id' => $business->id],
            ['name' => 'Cliente']
        );

        // Guardar mensaje usuario
        Message::create([
            'business_id' => $business->id,
            'client_id' => $client->id,
            'message' => $messageText,
            'role' => 'user'
        ]);

        // Llamada a OpenAI
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $business->openai_prompt ?? 'Eres un asistente útil'],
                ['role' => 'user', 'content' => $messageText]
            ]
        ]);

        $reply = $response['choices'][0]['message']['content'] ?? 'Error';

        // Guardar respuesta
        Message::create([
            'business_id' => $business->id,
            'client_id' => $client->id,
            'message' => $reply,
            'role' => 'assistant'
        ]);

        // Enviar a WhatsApp (Evolution API)
        Http::post(env('EVOLUTION_API_URL'), [
            'number' => $phone,
            'text' => $reply
        ]);

        return response()->json(['status' => 'ok']);
    }
}
