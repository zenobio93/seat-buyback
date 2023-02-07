<?php

/*
This file is part of SeAT

Copyright (C) 2015 to 2020  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace H4zz4rdDev\Seat\SeatBuyback\Services;

use H4zz4rdDev\Seat\SeatBuyback\Exceptions\DiscordServiceWebhookUrlNotFoundException;
use H4zz4rdDev\Seat\SeatBuyback\Exceptions\SettingsServiceException;

/**
 * Class DiscordService
 */
class DiscordService {

    /**
     * @var SettingsService
     */
    private $settingsService;

    /**
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * @param $msg
     * @return void
     * @throws DiscordServiceWebhookUrlNotFoundException
     * @throws SettingsServiceException
     */
    private function send($msg) {
        $webhookUrl = $this->settingsService->get('admin_discord_webhook_url');

        if(filter_var($webhookUrl, FILTER_VALIDATE_URL) && !empty($webhookUrl)) {
            $ch = curl_init($webhookUrl);
            curl_setopt($ch,CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch,CURLOPT_POST, 1);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $msg);
            curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch,CURLOPT_HEADER, 0);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

            curl_exec($ch);
            curl_close($ch);
        } else {
            throw new DiscordServiceWebhookUrlNotFoundException(
                'WebhookUrl not found exception! Please set a proper URL!'
            );
        }
    }

    public function sendMessage($username, $userId) : void {
        $timestamp = date("c", strtotime("now"));

        $msg = json_encode([
            "username" => "SeAT-BuyBack",
            "avatar_url" => "https://avatars.githubusercontent.com/u/13915359?s=200&v=4",
            "tts" => false,
            "embeds" => [
                [
                    "title" => "New BuyBack: " . $username,
                    "type" => "rich",
                    "description" => "Nice value is shown here and item count!",
                    "timestamp" => $timestamp,
                    "color" => hexdec( "3366ff" ),
                    "thumbnail" => [
                        "url" => "https://images.evetech.net/characters/".$userId."/portrait"
                    ],
                    "fields" => [
                        [
                            "name" => "ISK Value",
                            "value" => "99.999.999 ISK",
                            "inline" => false
                        ],
                        [
                            "name" => "Item Count",
                            "value" => "99 Items",
                            "inline" => true
                        ]
                    ]
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        $this->send($msg);
    }
}