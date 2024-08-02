# Instagram-DL Telegram Bot

Welcome to the **Instagram-DL Telegram Bot** project! This bot allows you to download Instagram videos by simply sending a link through Telegram. It's built using MadelineProto.

## Features

- Download Instagram videos by sending the link to the bot.
- Automatically handles valid URL checking.
- Provides progress updates during the download process.

## Installation

### Requirements

- PHP 8.2 or higher
- Composer (optional, but recommended)

### Setup

#### Method 1: Using Git and Composer

1. **Clone the repository:**
   ```sh
   git clone https://github.com/code3-dev/Instagram-DL.git
   cd Instagram-DL
   ```

2. **Install dependencies:**
   ```sh
   composer install
   ```

3. **Run the bot:**
   ```sh
   php bot.php
   ```

#### Method 2: Using Composer Project Creator

1. **Create the project:**
   ```sh
   composer create-project pira/instagram mybot
   ```

2. **Navigate to the project directory:**
   ```sh
   cd mybot
   ```

3. **Run the bot:**
   ```sh
   php bot.php
   ```

#### Method 3: Running bot.php Only

1. **Download the `bot.php` file:**
   ```sh
   wget https://raw.githubusercontent.com/code3-dev/Instagram-DL/master/bot.php
   ```

2. **Run the bot:**
   ```sh
   php bot.php
   ```

## Usage

Once the bot is running, you can interact with it on Telegram. Here are some basic commands:

- **Start the bot:**
  ```text
  /start
  ```

- **Send an Instagram link:**
  Simply paste the Instagram video link into the chat, and the bot will download and send the video back to you.

## Project Structure

- `src/YTDL.php`: This file contains the logic for handling the download requests from Instagram.
- `bot.php`: The main entry point for running the Telegram bot.

## Code Overview

### Event Handlers

The bot uses event handlers to process commands and incoming messages. Here are some key components:

- **cmdStart:** Handles the `/start` command and sends a welcome message.
- **cmdProcessUrl:** Processes the Instagram URL sent by the user, validates it, and initiates the download process.
- **cmdUpload:** Manages the file upload process, providing progress updates.

### Main Function

The `main` function initializes the bot settings and starts the bot loop:
```php
public static function main()
{
    $settings = new Settings;
    $settings->getConnection()->setMaxMediaSocketCount(1000);
    $settings->getPeer()->setFullFetch(false)->setCacheAllPeersOnStartup(false);

    self::startAndLoop('bot.madeline', $settings);
}
```

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Authors

- **Hossein Pira** - [h3dev.pira@gmail.com](mailto:h3dev.pira@gmail.com)

## Acknowledgments

- This bot is built using [MadelineProto](https://docs.madelineproto.xyz).

## Integration with [PHP YTDL Library](https://github.com/code3-dev/ytdl-php)

This bot utilizes the YTDL class from the PHP YTDL Library to handle video downloads. Here is an overview of the YTDL class:

### YTDL Class

The [YTDL](https://github.com/code3-dev/ytdl-php) class handles the configuration and sending of requests to a video downloading API. It allows you to set various parameters for downloading videos or audio, such as video quality, codec, audio format, and filename pattern. The class provides methods to enable or disable specific features like audio-only downloads, full audio from TikTok videos, and metadata options.

#### Sources

- [Cobalt Code](https://github.com/imputnet/cobalt)
- [Cobalt Site](https://cobalt.tools)
- [Cobalt API Docs](https://github.com/imputnet/cobalt/blob/current/docs/api.md)
