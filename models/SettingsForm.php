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

namespace app\models;

use dektrium\user\Mailer;

class SettingsForm extends \dektrium\user\models\SettingsForm
{
    /**
     * @var string
     */
    public $api_key;

    /**
     * @inheritdoc
     */
    public function __construct(Mailer $mailer, $config = [])
    {
        $this->setAttributes(['api_key' => $this->user->api_key], false);
        parent::__construct($mailer, $config);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['fieldLength'] = ['api_key', 'string', 'min' => 10, 'max' => 32];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function save(): bool
    {
        $this->user->api_key = $this->api_key;
        return parent::save();
    }

}