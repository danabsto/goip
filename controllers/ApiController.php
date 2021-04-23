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

namespace app\controllers;

use app\models\Device;
use app\models\Line;
use app\models\Message;
use app\models\User;
use app\models\UserDevice;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;

class ApiController extends Controller
{
    /**
     * @return Device[]|array|ActiveRecord[]
     */
    public function actionGetDevice(): array
    {
        return Device::find()->all();
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionLogin(): array
    {
        $login = Yii::$app->request->get("login");
        $password = Yii::$app->request->get("password");
        $user = User::findOne(['username' => $login]);
        if (!$user || !Yii::$app->security->validatePassword($password, $user->getAttribute('password_hash')))
            throw new ForbiddenHttpException("Доступ запрещен");

        return ['login' => $user->username, "apikey" => $user->api_key];
    }

    /**
     * @return int[]
     * @throws ForbiddenHttpException
     */
    public function actionAddDevice(): array
    {
        $apikey = Yii::$app->request->get("apikey");
        $user = User::findUserByApiKey($apikey);
        if (!$user) throw new ForbiddenHttpException("Доступ запрещен");
        $token = Yii::$app->request->get("token");
        $userDevice = UserDevice::find()->where(["user_id" => $user->id, "token" => $token])->one();
        if (!$userDevice) {
            $userDevice = new UserDevice();
            $userDevice->token = $token;
            $userDevice->user_id = $user->id;
            $userDevice->type = 1;
            $userDevice->save();
        }
        return ["success" => 1];
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionGetSms(): array
    {
        $apikey = Yii::$app->request->get("apikey");
        $user = User::findUserByApiKey($apikey);
        if (!$user) throw new ForbiddenHttpException("Доступ запрещен");
        $data = [];
        $messages = Message::find()->orderBy(["tm" => SORT_DESC])->joinWith(["simcard" => function (ActiveQuery $q) use ($user) {
            $q->joinWith(["line" => function (ActiveQuery $q) use ($user) {
                $q->joinWith(["device" => function (ActiveQuery $q) use ($user) {
                    $q->andWhere(["user_id" => $user->id]);
                }]);
            }]);
        }])->limit(20)->all();
        foreach ($messages as $m)
            $data[] = ["id" => $m->id, "address" => $m->address, "tm" => $m->tm, "text" => $m->text];
        return $data;
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionGetDevices(): array
    {
        $apikey = Yii::$app->request->get("apikey");
        $user = User::findUserByApiKey($apikey);
        if (!$user) throw new ForbiddenHttpException("Доступ запрещен");
        $data = [];
        $devices = Device::find()->where(["user_id" => $user->id])->all();
        foreach ($devices as $d)
            $data[] = ["id" => $d->id, "title" => $d->title, "host" => $d->host, "port" => $d->port];
        return $data;
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionGetLines(): array
    {
        $apikey = Yii::$app->request->get("apikey");
        $user = User::findUserByApiKey($apikey);
        if (!$user) throw new ForbiddenHttpException("Доступ запрещен");
        $deviceID = Yii::$app->request->get("device_id");
        $data = [];
        $lines = Line::find()->where(["device_id" => $deviceID])->with("simcard")->orderBy(["number" => SORT_ASC])->all();
        foreach ($lines as $line)
            $data[] = [
                "id" => $line->id,
                "title" => $line->title,
                "number" => $line->number,
                "phone" => (string)$line->simcard->phone,
                "balance" => (double)$line->simcard->balance,
                "status" => $line->simcard ? 1 : 0
            ];
        return $data;
    }

}