<?php

require_once "Client.php";

/**
 * Class YTDL (PHP YTDL Library)
 *
 * This class handles the configuration and sending of requests to a video downloading API.
 * It allows you to set various parameters for downloading videos or audio, such as video quality,
 * codec, audio format, and filename pattern. The class provides methods to enable or disable specific
 * features like audio-only downloads, full audio from TikTok videos, and metadata options.
 * 
 * This library relies on Cobalt’s free API.
 * 
 * Sources:
 * - Cobalt Code: https://github.com/imputnet/cobalt
 * - Cobalt Site: https://cobalt.tools
 * - Cobalt API Docs: https://github.com/imputnet/cobalt/blob/current/docs/api.md
 * 
 * @package pira\ytdl
 * @version 1.7.0
 * @license MIT License
 * @link https://github.com/code3-dev/ytdl-php
 * @api https://github.com/imputnet/cobalt
 */

class YTDL
{
    /**
     * @var string URL encoded as URI, must be included in every request.
     */
    private $url;

    /**
     * @var string Video codec: h264, av1, vp9. Default is h264. Recommended for phones.
     */
    private $vCodec = 'h264';

    /**
     * @var string Video quality: 144, ..., 2160, max. Default is 720. Recommended for phones.
     */
    private $vQuality = '720';

    /**
     * @var string Audio format: best, mp3, ogg, wav, opus. Default is mp3.
     */
    private $aFormat = 'mp3';

    /**
     * @var string Filename pattern: classic, pretty, basic, nerdy. Default is classic.
     */
    private $filenamePattern = 'classic';

    /**
     * @var bool Whether to download only audio. Default is false.
     */
    private $isAudioOnly = false;

    /**
     * @var bool Whether to download the original sound from a TikTok video. Default is false.
     */
    private $isTTFullAudio = false;

    /**
     * @var bool Whether to mute the audio track in video downloads. Default is false.
     */
    private $isAudioMuted = false;

    /**
     * @var bool Whether to use Accept-Language header for YouTube video audio tracks. Default is false.
     */
    private $dubLang = false;

    /**
     * @var bool Whether to disable file metadata. Default is false.
     */
    private $disableMetadata = false;

    /**
     * @var bool Whether to convert Twitter gifs to .gif format. Default is false.
     */
    private $twitterGif = false;

    /**
     * @var bool Whether to prefer 1080p h265 videos for TikTok. Default is false.
     */
    private $tiktokH265 = false;

    /**
     * @var string|null Custom Accept-Language header value. Default is null.
     */
    private $acceptLanguage = null;

    /**
     * Constructor initializes the class with a URL.
     *
     * @param string $url The URL to be used in requests.
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Sets the video quality for downloads.
     *
     * @param string $quality The desired video quality (e.g., 144, 720, max).
     * @throws Exception If the provided quality is not valid.
     */
    public function setQuality(string $quality): void
    {
        $allowedQualities = ['max', '2160', '1440', '1080', '720', '480', '360', '240', '144'];
        if (!in_array($quality, $allowedQualities)) {
            throw new Exception('Invalid video quality');
        }
        $this->vQuality = $quality;
    }

    /**
     * Sets the filename pattern for downloaded files.
     *
     * Available patterns:
     * - classic: Standard naming for files.
     * - basic: Simplistic naming for files.
     * - pretty: More descriptive naming for files.
     * - nerdy: Detailed naming for files including additional metadata.
     *
     * @param string $pattern The desired filename pattern.
     * @throws Exception If the provided pattern is not valid.
     */
    public function setFilenamePattern(string $pattern): void
    {
        $allowedPatterns = ['classic', 'pretty', 'basic', 'nerdy'];
        if (!in_array($pattern, $allowedPatterns)) {
            throw new Exception('Invalid filename pattern');
        }
        $this->filenamePattern = $pattern;
    }

    /**
     * Sets the video codec for downloads.
     *
     * @param string $codec The desired video codec (e.g., h264, av1, vp9).
     * @throws Exception If the provided codec is not valid.
     */
    public function setVCodec(string $codec): void
    {
        $allowedCodecs = ['h264', 'av1', 'vp9'];
        if (!in_array($codec, $allowedCodecs)) {
            throw new Exception('Invalid video codec');
        }
        $this->vCodec = $codec;
    }

    /**
     * Sets the audio format for downloads.
     *
     * @param string $format The desired audio format (e.g., mp3, ogg, wav).
     * @throws Exception If the provided format is not valid.
     */
    public function setAFormat(string $format): void
    {
        $allowedFormats = ['best', 'mp3', 'ogg', 'wav', 'opus'];
        if (!in_array($format, $allowedFormats)) {
            throw new Exception('Invalid audio format');
        }
        $this->aFormat = $format;
    }

    /**
     * Sets the custom Accept-Language header value for requests.
     *
     * @param string $language The custom Accept-Language header value.
     */
    public function setAcceptLanguage(string $language): void
    {
        $this->acceptLanguage = $language;
    }

    /**
     * Enables downloading only audio.
     */
    public function enableAudioOnly(): void
    {
        $this->isAudioOnly = true;
    }

    /**
     * Enables downloading the original sound from a TikTok video.
     */
    public function enableTTFullAudio(): void
    {
        $this->isTTFullAudio = true;
    }

    /**
     * Enables muting the audio track in video downloads.
     */
    public function enableAudioMuted(): void
    {
        $this->isAudioMuted = true;
    }

    /**
     * Enables using the Accept-Language header for YouTube video audio tracks.
     */
    public function enableDubLang(): void
    {
        $this->dubLang = true;
    }

    /**
     * Enables disabling file metadata.
     */
    public function enableDisableMetadata(): void
    {
        $this->disableMetadata = true;
    }

    /**
     * Enables converting Twitter gifs to .gif format.
     */
    public function enableTwitterGif(): void
    {
        $this->twitterGif = true;
    }

    /**
     * Enables preferring 1080p h265 videos for TikTok.
     */
    public function enableTiktokH265(): void
    {
        $this->tiktokH265 = true;
    }


    /**
     * Sends the configured request to the API and returns the response.
     *
     * @return array An associative array containing the status and data of the response.
     */
    public function sendRequest(): array
    {
        $client = new Client;

        $client->Url('https://api.cobalt.tools/api/json');
        $client->Method('POST');

        $headers = [
            "Accept: application/json",
            "Content-Type: application/json"
        ];

        if ($this->acceptLanguage !== null) {
            $headers[] = "Accept-Language: " . $this->acceptLanguage;
        }

        $client->Headers($headers);
        $client->Timeout(0);

        $data = [
            "url" => $this->url,
        ];

        if (isset($this->vQuality)) {
            $data["vQuality"] = $this->vQuality;
        }
        if (isset($this->filenamePattern)) {
            $data["filenamePattern"] = $this->filenamePattern;
        }
        if ($this->isAudioOnly) {
            $data["isAudioOnly"] = $this->isAudioOnly;
        }
        if ($this->isTTFullAudio) {
            $data["isTTFullAudio"] = $this->isTTFullAudio;
        }
        if ($this->isAudioMuted) {
            $data["isAudioMuted"] = $this->isAudioMuted;
        }
        if ($this->dubLang) {
            $data["dubLang"] = $this->dubLang;
        }
        if ($this->disableMetadata) {
            $data["disableMetadata"] = $this->disableMetadata;
        }
        if ($this->twitterGif) {
            $data["twitterGif"] = $this->twitterGif;
        }
        if ($this->tiktokH265) {
            $data["tiktokH265"] = $this->tiktokH265;
        }
        if (isset($this->vCodec)) {
            $data["vCodec"] = $this->vCodec;
        }
        if (isset($this->aFormat)) {
            $data["aFormat"] = $this->aFormat;
        }

        $client->Body(json_encode($data));

        try {
            $request = $client->Send();
            $statusCode = $client->getStatus();
            $response = json_decode($request, true);

            if ($statusCode == 200) {
                return ["status" => true, "data" => $response];
            } else {
                return ["status" => false, "text" => $response["text"]];
            }
        } catch (Exception $e) {
            return ["status" => false, "text" => "Error in sending request. " . $e->getMessage()];
        }
    }
}
