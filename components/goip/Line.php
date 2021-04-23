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
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Class Line
 * @package GoipApi
 */
class Line extends Basic
{
    const ROUTE = "/status.html";

    private $lines = null;

    /**
     * @param $lineID
     * @throws GoipUnavailableException
     */
    public function changeStatus($lineID)
    {
        $line = $this->getStatus($lineID);
        if ($line["module"]) {
            $this->disable($lineID);
        } else {
            $this->enable($lineID);
        }
    }

    /**
     * @param null $lines
     * @return array|mixed|null
     *
     * @throws GoipUnavailableException
     */
    public function getStatus($lines = null)
    {
        if (is_null($lines)) return $this->getStatusAll();
        if (is_array($lines)) {
            return $this->getStatusMulti($lines);
        }
        if (is_int($lines)) {
            return $this->getStatusOne($lines);
        }
        return null;
    }

    /**
     * @return array
     *
     * @throws GoipUnavailableException
     */
    private function getStatusAll(): array
    {
        $lines = [];
        $result = $this->getRequest(self::ROUTE);
        $dom = new DOMDocument();
        libxml_use_internal_errors(TRUE);
        $dom->loadHTML($result);
        libxml_clear_errors();
        $dom->normalizeDocument();
        $xpath = new DOMXPath($dom);
        $trList = $xpath->query('//div[@id="gsm_info"]/table/tr');
        foreach ($trList as $tr) {
            $tdList = $tr->childNodes;
            $line = ["module" => false, "sim" => false, "gsm" => false, "tm" => '', "rsi" => 0, "operator" => ''];
            foreach ($tdList as $td)
                if ($td instanceof DOMElement) {
                    foreach ($td->attributes as $attr) {
                        if (preg_match('/l\d+_module_status_gsm/', $attr->nodeValue))
                            $line['id'] = (int)$td->nodeValue;
                        elseif (preg_match('/l\d+_module_status/', $attr->nodeValue))
                            $line['module'] = strip_tags($td->nodeValue) == "Y";
                        elseif (preg_match('/l\d+_gsm_sim/', $attr->nodeValue))
                            $line['sim'] = $td->nodeValue == "Y";
                        elseif (preg_match('/l\d+_gsm_status/', $attr->nodeValue))
                            $line['gsm'] = $td->nodeValue == "Y";
                        elseif (preg_match('/l\d+_cdrt/', $attr->nodeValue))
                            $line['tm'] = $td->nodeValue;
                        elseif (preg_match('/l\d+_gsm_signal/', $attr->nodeValue))
                            $line['rsi'] = (int)$td->nodeValue;
                        elseif (preg_match('/l\d+_gsm_cur_oper/', $attr->nodeValue))
                            $line['operator'] = (string)preg_replace("/\W\D/", "", $td->nodeValue);
                    }
                }
            if (!isset($line['id'])) continue;
            $lines[$line["id"]] = $line;
        }
        $this->lines = $lines;
        return $lines;
    }

    /**
     * @param array $lineIDs
     * @return array
     *
     * @throws GoipUnavailableException
     */
    private function getStatusMulti(array $lineIDs = []): array
    {
        $lines = $this->getStatusAll();
        $data = [];
        foreach ($lineIDs as $id) {
            if (array_key_exists($id, $lines)) $data[$id] = $lines[$id];
        }
        return $data;
    }

    /**
     * @param int $lineID
     * @return mixed|null
     *
     * @throws GoipUnavailableException
     */
    private function getStatusOne(int $lineID)
    {
        $lines = $this->lines ?? $this->getStatusAll();
        return array_key_exists($lineID, $lines) ? $lines[$lineID] : null;
    }

    /**
     * @param $lineID
     * @throws GoipUnavailableException
     */
    private function disable($lineID)
    {
        $this->getRequest(self::ROUTE, ["type" => "list", "down" => 1, "line" => $lineID]);
    }

    /**
     * @param $lineID
     * @throws GoipUnavailableException
     */
    private function enable($lineID)
    {
        $this->getRequest(self::ROUTE, ["type" => "list", "down" => 0, "line" => $lineID]);
    }

    /**
     * @param null $lines
     * @return array|mixed|null
     *
     * @throws GoipUnavailableException
     */
    public function getInfo($lines = null)
    {
        if (is_null($lines)) return $this->getInfoAll();
        if (is_array($lines)) {
            return $this->getStatusMulti($lines);
        }
        if (is_int($lines)) {
            return $this->getStatusOne($lines);
        }
        return null;
    }

    /**
     * @return array
     *
     * @throws GoipUnavailableException
     */
    private function getInfoAll(): array
    {
        $lines = [];
        $result = $this->getRequest(self::ROUTE, ["type" => "gsm"]);
        $dom = new DOMDocument();
        libxml_use_internal_errors(TRUE);
        $dom->loadHTML($result);
        libxml_clear_errors();
        $dom->normalizeDocument();
        $xpath = new DOMXPath($dom);
        $trList = $xpath->query('//div[@id="gsm_detail"]/table/tr');
        foreach ($trList as $tr) {
            $tdList = $tr->childNodes;
            $line = ['phone' => null, 'imei' => '', 'imsi' => '', 'iccid' => ''];
            foreach ($tdList as $td)
                if ($td instanceof DOMElement) {
                    foreach ($td->attributes as $attr) {
                        if (preg_match('/l\d+_module_status_gsm2/', $attr->nodeValue))
                            $line['id'] = (int)$td->nodeValue;
                        if (preg_match('/l\d+_gsm_number/', $attr->nodeValue))
                            $line['phone'] = (string)preg_replace('/\D/', "", $td->nodeValue);
                        if (preg_match('/l\d+_gsm_imei/', $attr->nodeValue))
                            $line['imei'] = (string)preg_replace('/\D\W/', "", $td->nodeValue);
                        if (preg_match('/l\d+_sim_imsi/', $attr->nodeValue))
                            $line['imsi'] = (string)preg_replace('/\D\W/', "", $td->nodeValue);
                        if (preg_match('/l\d+_sim_iccid/', $attr->nodeValue))
                            $line['iccid'] = (string)preg_replace("/\W\D/", "", $td->nodeValue);
                    }
                }
            if (!isset($line['id'])) continue;
            $status = $this->getStatus($line["id"]);
            $line = array_merge($line, $status);
            $lines[$line["id"]] = $line;
        }
        return $lines;
    }
}