<?php

declare(strict_types=1);

require_once "src/YTDL.php";

use Amp\ByteStream\ReadableStream;
use danog\MadelineProto\API;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\CommandType;
use danog\MadelineProto\EventHandler\Filter\FilterCommand;
use danog\MadelineProto\EventHandler\Media;
use danog\MadelineProto\EventHandler\Message\PrivateMessage;
use danog\MadelineProto\EventHandler\SimpleFilter\HasMedia;
use danog\MadelineProto\EventHandler\SimpleFilter\Incoming;
use danog\MadelineProto\EventHandler\SimpleFilter\IsNotEdited;
use danog\MadelineProto\FileCallback;
use danog\MadelineProto\Logger;
use danog\MadelineProto\ParseMode;
use danog\MadelineProto\RemoteUrl;
use danog\MadelineProto\RPCErrorException;
use danog\MadelineProto\Settings;
use danog\MadelineProto\SimpleEventHandler;
use League\Uri\Contracts\UriException;

if (class_exists(API::class)) {
    // Load MadelineProto if already installed
} elseif (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    if (!file_exists('madeline.php')) {
        copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
    }
    require_once 'madeline.php';
}

class MyEventHandler extends SimpleEventHandler
{
    public const START = "به ربات دانلود اینستاگرام خوش آمدید!\n\n" .
        "لطفاً یک لینک از اینستاگرام ارسال کنید تا من آن را برای شما دانلود کنم. فقط کافیست لینک مورد نظر خود را اینجا قرار دهید.";

    public const ADMIN = 'h3dev';
    public const BOT_USERNAME = 'TestIGDLv3bot';

    public function getReportPeers()
    {
        return [self::ADMIN];
    }

    public function __sleep(): array
    {
        return ['states'];
    }

    private array $states = [];

    #[FilterCommand('start', [CommandType::SLASH])]
    public function cmdStart(PrivateMessage&Incoming&IsNotEdited $message): void
    {
        $message->reply(self::START, ParseMode::MARKDOWN);
    }

    #[Handler]
    public function cmdProcessUrl(PrivateMessage&Incoming&IsNotEdited $message): void
    {
        $url = trim($message->message);
        if ($url != "/start") {
            if (filter_var($url, FILTER_VALIDATE_URL) && (str_starts_with($url, "https://www.instagram.com") || str_starts_with($url, "https://instagram.com"))) {
                $sent = $message->reply('در حال دریافت اطلاعات...');
                try {
                    $ytdl = new YTDL($url);
                    $response = $ytdl->sendRequest();

                    $timestamp = date('Ymd_His');
                    $randomNumber = rand(1000, 9999);

                    if (isset($response['data']['url'])) {
                        $sent->delete();
                        $name = "video_{$timestamp}_{$randomNumber}.mp4";
                        $caption = "قدرت گرفته از @" . self::BOT_USERNAME;
                        $this->cmdUpload(new RemoteUrl($response['data']['url']), $name, $caption, $message, 'video');
                    } elseif (isset($response['data']['picker'])) {
                        $index = 1;
                        foreach ($response['data']['picker'] as $item) {
                            $sent->delete();
                            $timestamp = date('Ymd_His');
                            $randomNumber = rand(1000, 9999);
                            $name = ($item['type'] === 'video') ? "video_{$timestamp}_{$randomNumber}.mp4" : "photo_{$timestamp}_{$randomNumber}.jpg";
                            $caption = "فایل {$index}\nقدرت گرفته از @" . self::BOT_USERNAME;

                            if ($item['type'] === 'video') {
                                $this->cmdUpload(new RemoteUrl($item['url']), $name, $caption, $message, 'video');
                            } elseif ($item['type'] === 'photo') {
                                $this->cmdUpload(new RemoteUrl($item['url']), $name, $caption, $message, 'photo');
                            }
                            $index++;
                        }
                    } else {
                        $sent->delete();
                        $message->reply("دانلود ویدیو با شکست مواجه شد. API در حال بروز رسانی است.", ParseMode::MARKDOWN);
                    }
                } catch (Exception $e) {
                    $sent->delete();
                    $message->reply("دانلود ویدیو با شکست مواجه شد: " . $e->getMessage(), ParseMode::MARKDOWN);
                }
            } else {
                $message->reply("لطفاً یک لینک معتبر ارسال کنید.", ParseMode::MARKDOWN);
            }
        }
    }

    private function cmdUpload(Media|RemoteUrl|ReadableStream $file, string $name, string $caption, PrivateMessage $message, string $type = 'document'): void
    {
        try {
            $sent = $message->reply('در حال آماده‌سازی...');
            $fileCallback = new FileCallback(
                $file,
                static function ($progress) use ($sent): void {
                    static $prev = 0;
                    $now = time();
                    if ($now - $prev < 10 && $progress < 100) {
                        return;
                    }

                    $prev = $now;
                    try {
                        $sent->editText("پیشرفت آپلود: $progress%");
                    } catch (RPCErrorException $e) {
                    }
                }
            );

            if ($type === 'photo') {
                // Send as photo
                $this->messages->sendMedia(
                    peer: $message->chatId,
                    reply_to_msg_id: $message->id,
                    media: [
                        '_' => 'inputMediaUploadedPhoto',
                        'file' => $fileCallback,
                    ],
                    message: $caption
                );
            } elseif ($type === 'video') {
                // Send as video
                $mediaAttributes = [
                    ['_' => 'documentAttributeFilename', 'file_name' => $name],
                    ['_' => 'documentAttributeVideo', 'supports_streaming' => true]
                ];
                $this->messages->sendMedia(
                    peer: $message->chatId,
                    reply_to_msg_id: $message->id,
                    media: [
                        '_' => 'inputMediaUploadedDocument',
                        'file' => $fileCallback,
                        'attributes' => $mediaAttributes,
                    ],
                    message: $caption
                );
            } else {
                // Default to document if the type is not specified correctly
                $mediaAttributes = [
                    ['_' => 'documentAttributeFilename', 'file_name' => $name]
                ];
                $this->messages->sendMedia(
                    peer: $message->chatId,
                    reply_to_msg_id: $message->id,
                    media: [
                        '_' => 'inputMediaUploadedDocument',
                        'file' => $fileCallback,
                        'attributes' => $mediaAttributes,
                    ],
                    message: $caption
                );
            }

            $sent->delete();
        } catch (Throwable $e) {
            if (!str_contains($e->getMessage(), 'Could not connect to URI') && !($e instanceof UriException) && !str_contains($e->getMessage(), 'URI')) {
                $this->report((string) $e);
                $this->logger((string) $e, Logger::FATAL_ERROR);
            }
            try {
                $sent->editText('خطا: ' . $e->getMessage());
            } catch (Throwable $e) {
                $this->logger((string) $e, Logger::FATAL_ERROR);
            }
        }
    }

    public static function main()
    {
        $settings = new Settings;
        $settings->getConnection()->setMaxMediaSocketCount(1000);
        $settings->getPeer()->setFullFetch(false)->setCacheAllPeersOnStartup(false);

        self::startAndLoop('bot.madeline', $settings);
    }
}

MyEventHandler::main();