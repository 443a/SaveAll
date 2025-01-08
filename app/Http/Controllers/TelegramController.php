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
        $message = $update->getMessage();
        $callbackQuery = $update->getCallbackQuery();

        // Get the user's language from the database (default to English if not set)
        $user = TelegramUser::firstOrCreate(
            ['chat_id' => $chatId],
            [
                'first_name' => $firstName,
                'last_name' => $update->getChat()->getLastName(),
                'username' => $update->getChat()->getUsername(),
                'language' => 'en', // Default language
            ]
        );
        $language = $user->language;

        // Handle callback queries (inline keyboard button clicks)
        if ($callbackQuery) {
            $data = $callbackQuery->getData();
            $chatId = $callbackQuery->getMessage()->getChat()->getId();

            // Handle language selection
            if (str_starts_with($data, 'lang_')) {
                $language = str_replace('lang_', '', $data);

                // Save the selected language to the database
                $user->update(['language' => $language]);

                // Send a confirmation message
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $this->getMessage('language_selected', $language),
                ]);

                // Show the main menu in the selected language
                $this->sendMainMenu($chatId, $language);
                return response()->json(['status' => 'success']);
            }
        }

        // Handle regular messages
        if ($message) {
            $text = $message->getText();

            // Show the language selection menu at the beginning
            if ($text === '/start') {
                $this->sendLanguageSelectionMenu($chatId);
                return response()->json(['status' => 'success']);
            }

            // Handle other commands or messages
            switch ($text) {
                case 'Help ❓':
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->getMessage('help', $language),
                    ]);
                    break;

                case 'Invite Friends 🗽':
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->getMessage('invite_friends', $language),
                    ]);
                    break;

                case 'Support 🛟':
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->getMessage('support', $language),
                    ]);
                    break;

                case 'Advertising 🍭':
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->getMessage('advertising', $language),
                    ]);
                    break;

                case 'About US 🌀':
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => $this->getMessage('about_us', $language),
                    ]);
                    break;

                default:
                    // Send the main menu
                    $this->sendMainMenu($chatId, $language);
                    break;
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Send the language selection menu.
     */
    private function sendLanguageSelectionMenu($chatId)
    {
        $keyboard = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton(['text' => 'English', 'callback_data' => 'lang_en']),
                Keyboard::inlineButton(['text' => 'فارسی', 'callback_data' => 'lang_fa']),
            ]);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => 'Please select your language:',
            'reply_markup' => $keyboard,
        ]);
    }

    /**
     * Send the main menu.
     */
    private function sendMainMenu($chatId, $language)
    {
        $keyboard = Keyboard::make()
            ->row([
                Keyboard::button($this->getMessage('help', $language)),
                Keyboard::button($this->getMessage('invite_friends', $language)),
            ])
            ->row([
                Keyboard::button($this->getMessage('support', $language)),
                Keyboard::button($this->getMessage('advertising', $language)),
            ])
            ->row([
                Keyboard::button($this->getMessage('about_us', $language)),
            ])
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false);

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->getMessage('main_menu', $language),
            'reply_markup' => $keyboard,
        ]);
    }

    /**
     * Get a localized message based on the selected language.
     */
    private function getMessage($key, $language)
    {
        $messages = [
            'en' => [
                'language_selected' => 'You have selected English.',
                'main_menu' => 'How can I help you?',
                'help' => 'Help ❓',
                'invite_friends' => 'Invite Friends 🗽',
                'support' => 'Support 🛟',
                'advertising' => 'Advertising 🍭',
                'about_us' => 'About US 🌀',
            ],
            'fa' => [
                'language_selected' => 'شما فارسی را انتخاب کرده‌اید.',
                'main_menu' => 'چگونه می‌توانم کمک کنم؟',
                'help' => 'راهنما ❓',
                'invite_friends' => 'دعوت از دوستان 🗽',
                'support' => 'پشتیبانی 🛟',
                'advertising' => 'تبلیغات 🍭',
                'about_us' => 'درباره ما 🌀',
            ],
        ];

        return $messages[$language][$key] ?? 'Message not found.';
    }
}
