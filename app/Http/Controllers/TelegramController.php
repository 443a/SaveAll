<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\TelegramUser;

class TelegramController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $update = Telegram::commandsHandler(true);

        $chatId = $update->getChat()->getId();
        $firstName = $update->getChat()->getFirstName();
        $lastName = $update->getChat()->getLastName();
        $username = $update->getChat()->getUsername();

        // Register or update user
        TelegramUser::updateOrCreate(
            ['chat_id' => $chatId], // Search by chat_id
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $username,
            ]
        );

        // Send a hello message
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "Hello, $firstName! You have been registered.",
        ]);

        return response()->json(['status' => 'success']);
    }
}
