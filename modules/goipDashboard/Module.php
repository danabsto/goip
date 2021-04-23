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

namespace app\modules\goipDashboard;

use app\modules\goipDashboard\assets\AssetsBundle;
use app\modules\goipDashboard\controllers\api\Api;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\base\Module as BaseModule;
use yii\console\Application as ConsoleApplication;
use yii\i18n\PhpMessageSource;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;

const PREFIX_TABLE = 'goip';

/**
 * goip-dashboard module definition class
 */
class Module extends BaseModule
{
    public $users = [];
    public $roles = [];

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\goipDashboard\controllers';
    public $defaultRoute = 'dashboard/index';

    public static function tableName($table): string
    {
        return '{{%' . PREFIX_TABLE . '_' . $table . '}}';
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        Yii::setAlias('@goip', '@app/modules/goipDashboard');
        if (Yii::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'app\modules\goipDashboard\commands';
        } else {
            AssetsBundle::register(Yii::$app->view);
        }
        if (!isset(Yii::$app->get('i18n')->translations['goip*'])) {
            Yii::$app->get('i18n')->translations['goip*'] = [
                'class' => PhpMessageSource::className(),
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en-US'
            ];
        }
    }

    /**
     * @param Action $action
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action): bool
    {
        if (Yii::$app instanceof ConsoleApplication) return true;
        if (!Yii::$app->user->isGuest) {
            if (in_array(Yii::$app->user->identity->username, $this->users)) return true;
            foreach ($this->roles as $role) if (Yii::$app->user->can($role)) return true;
        }
        if ($action->controller instanceof Controller) return parent::beforeAction($action);
        throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
    }
}
