<?php
/**
 * @copyright Copyright 2021 Undefined.team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace app\components\goip;

use app\components\GoipUnavailableException;

/**
 * Class Sms
 * @package GoipApi
 */
class Sms extends Basic
{
    const ROUTE_INBOX = '/tools.html';
    const ROUTE_SEND = '/sms_info.html';
    const ROUTE_STATUS = '/send_sms_status.xml';

    /**
     * @param int $lineID
     * @return mixed
     *
     * @throws GoipUnavailableException
     */
    public function getMessagesByLine(int $lineID)
    {
        $messages = $this->getMessages();
        return $messages[$lineID];
    }

    /**
     * @return array
     * @throws GoipUnavailableException
     */
    public function getMessages(): array
    {
        $messages = [];
        $result = $this->getRequest(self::ROUTE_INBOX, ['type' => 'sms_inbox']);
        preg_match_all('/sms=[\s]+(\[(?:".+",?){8,}])/', $result, $lines);
        foreach ($lines[1] as $i => $data) {
            $lineID = $i + 1;
            $messages[$lineID] = [];
            $messagesLine = json_decode($data);
            if (is_array($messagesLine)) foreach ($messagesLine as $message) {
                if (empty($message)) continue;
                list($date, $sender, $text) = explode(',', $message, 3);
                $messages[$lineID][] = ['date' => $date, 'sender' => $sender, 'text' => $text];
            }
        }
        return $messages;
    }

    /**
     * @param int $line
     * @param int $phone
     * @param String $message
     *
     * @throws GoipUnavailableException
     */
    public function send(int $line, int $phone, string $message)
    {
        $key = $this->getKey('sms', 'sms');
        if (strlen($phone) >= 10) $phone = "+$phone";

        $this->postRequest(self::ROUTE_SEND, ["type" => "sms"], [
            'line' . $line => 1,
            'smskey' => $key,
            'action' => 'SMS',
            'telnum' => $phone,
            'smscontent' => $message,
            'send' => 'Send'
        ]);
    }

    /**
     * @param string $line
     * @throws GoipUnavailableException
     */
    public function clear($line = "-1")
    {
        $this->getRequest(self::ROUTE_KEY,
            ['action' => 'del', 'type' => 'sms_inbox', 'line' => $line, 'pos' => '-1']);
    }
}