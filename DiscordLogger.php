<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class DiscordHandler extends AbstractProcessingHandler
{
    protected $webhookUrl;
    protected $client;
    protected $projectName;
    protected $serverUrl;

    public function __construct($webhookUrl, $level = Logger::ERROR, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->webhookUrl = $webhookUrl;
        $this->client = new Client();
        $this->projectName = env('APP_NAME', 'Laravel Project');
        $this->serverUrl = env('APP_URL', 'http://localhost');
    }

    protected function write(LogRecord $record): void
    {
        $content = $this->formatContent($record);
        $this->sendWithRetry($content);
    }

    protected function formatContent(LogRecord $record): string
    {
        // Extract only the first line of the message
        $messageLine = strtok($record->message, "\n");
        
        // Extract file information
        $fileInfo = $this->extractFileInfo($record);
        
        // Format the content
        $content = "**{$this->projectName} Error**\n";
        $content .= "Server: {$this->serverUrl}\n";
        $content .= "Level: **{$record->level->getName()}**\n";
        $content .= "Message: {$messageLine}\n";
        if ($fileInfo) {
            $content .= "File: {$fileInfo}\n";
        }
        
        return $content;
    }

    protected function extractFileInfo(LogRecord $record): ?string
    {
        if (isset($record->context['exception']) && $record->context['exception'] instanceof \Throwable) {
            $exception = $record->context['exception'];
            return $exception->getFile() . ':' . $exception->getLine();
        }
        return null;
    }

    protected function sendWithRetry($content, $retries = 3)
    {
        for ($i = 0; $i < $retries; $i++) {
            try {
                $this->client->post($this->webhookUrl, [
                    'json' => ['content' => $content]
                ]);
                return; // Success, exit the method
            } catch (ClientException $e) {
                if ($e->getResponse()->getStatusCode() == 429) {
                    $retryAfter = $e->getResponse()->getHeader('Retry-After')[0] ?? 1;
                    sleep((int)$retryAfter);
                } else {
                    throw $e; // Rethrow if it's not a rate limit error
                }
            }
        }
        // If we've exhausted all retries, log the error locally
        error_log("Failed to send message to Discord after $retries retries.");
    }
}

class DiscordLogger
{
    public function __invoke(array $config)
    {
        $logger = new Logger('discord');
        $logger->pushHandler(new DiscordHandler($config['url']));
        return $logger;
    }
}
