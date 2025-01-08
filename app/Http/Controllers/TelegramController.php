<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\TelegramUser;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $update = Telegram::commandsHandler(true);

        $chatId = $update->getChat()->getId();
        $firstName = $update->getChat()->getFirstName();
        $lastName = $update->getChat()->getLastName();
        $username = $update->getChat()->getUsername();
        $message = $update->getMessage();
        $text = $message->getText();

        // Register or update user
        TelegramUser::updateOrCreate(
            ['chat_id' => $chatId], // Search by chat_id
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $username,
            ]
        );

        // Handle button clicks
        switch ($text) {
            case 'Help ❓':
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Here is some help information...',
                ]);
                break;

            case 'Invite Friends 🗽':
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Invite your friends using this link: https://example.com/invite',
                ]);
                break;

            case 'Support 🛟':
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Contact support at support@example.com.',
                ]);
                break;

            case 'Advertising 🍭':
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'For advertising inquiries, email ads@example.com.',
                ]);
                break;

            case 'About US 🌀':
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'We are a company that does amazing things! Learn more at https://example.com/about.',
                ]);
                break;

            default:
                // Send a custom keyboard if the message is not a button click
                $keyboard = Keyboard::make()
                    ->row([
                        Keyboard::button('Help ❓'),
                        Keyboard::button('Invite Friends 🗽'),
                    ])
                    ->row([
                        Keyboard::button('Support 🛟'),
                        Keyboard::button('Advertising 🍭'),
                    ])
                    ->row([
                        Keyboard::button('About US 🌀'),
                    ])
                    ->setResizeKeyboard(true) // Automatically resize the keyboard to fit the buttons
                    ->setOneTimeKeyboard(false); // Keep the keyboard visible after a button is pressed

                // Send a hello message with the custom keyboard
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Hello, $firstName! How can I help you?",
                    'reply_markup' => $keyboard,
                ]);
                break;
        }

        return response()->json(['status' => 'success']);
    }
}
